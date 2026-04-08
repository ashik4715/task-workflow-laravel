# QA Documentation - Task Workflow Management System

## Architecture Changes

### Before (Old Architecture)
- Users had direct permissions via `user_permissions` table
- Admin check was done via `isAdmin()` method
- Permission checking was manual in controllers

### After (New Architecture)
- Permissions managed only through roles (RBAC)
- `user_permissions` table removed
- Laravel Gates used for authorization
- Dual-layer: Auth Role (USER/ADMIN) + Associated Role (role_id)

## Access Control Implementation

### How are permissions enforced using Laravel Gates?

Permissions are defined in `AppServiceProvider.php` using Laravel's Gate facade:

```php
// AppServiceProvider.php
Gate::define('task.create', function (User $user) {
    return $user->roleModel && $user->roleModel->hasPermission('task.create');
});

Gate::define('task.approve', function (User $user) {
    return $user->roleModel && $user->roleModel->hasPermission('task.approve');
});
```

Gates are checked in controllers and views:

```php
// In Controller
if (Gate::denies('task.create')) {
    abort(403);
}

// In Blade View
@can('task.create')
    <button>Create Task</button>
@endcan
```

Admin users (role=ADMIN) automatically have all permissions via:

```php
Gate::before(function (User $user) {
    if ($user->isAdmin()) {
        return true;
    }
});
```

### How do you ensure only ADMIN can manage users?

1. **Route Level**: The `role:ADMIN` middleware protects user management routes:
```php
Route::middleware('role:ADMIN')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
```

2. **Gate Level**: Permission gates in views:
```php
@can('user.edit')
    <button>Edit User</button>
@endcan
```

3. **Controller Level**: Additional checks:
```php
public function destroy(User $user)
{
    $currentUser = Auth::user();
    if ($user->id === $currentUser->id) {
        return back()->with('error', 'You cannot delete your own account.');
    }
    if ($user->role === 'ADMIN') {
        return back()->with('error', 'Cannot delete ADMIN user.');
    }
}
```

### How is the dual-layer permission system implemented?

1. **Auth Role (role column)**: Determines basic access level
   - `USER`: Regular authenticated user
   - `ADMIN`: Full access (bypasses all permission checks)

2. **Associated Role (role_id column)**: Determines detailed permissions
   - Links to `roles` table
   - Permissions inherited via `role_permissions` pivot table

```php
// In User model
public function hasPermission(string $permission): bool
{
    if ($this->isAdmin()) {
        return true; // Admin bypasses permission checks
    }
    return Gate::check($permission); // Check via gates
}
```

### How do you assign a role to a user?

1. Create roles with permissions in Roles section
2. Go to User edit page
3. Select "Associate Role" from dropdown
4. Save - user now inherits those permissions

## How are you validating and extracting user information from JWT?

JWT validation is handled by the `tymon/jwt-auth` package:

1. **Token Generation**: The `AuthController` uses `JWTAuth::attempt()` to validate credentials and generate tokens
2. **User Extraction**: The `getJWTIdentifier()` method in the User model returns the user's primary key
3. **Custom Claims**: The `getJWTCustomClaims()` method adds custom data (like role) to the token
4. **Authentication**: All API controllers use `auth()->user()` or `$request->user()` to retrieve the authenticated user from the token

```php
public function getJWTIdentifier()
{
    return $this->getKey();
}

public function getJWTCustomClaims(): array
{
    return [
        'role' => $this->role,
    ];
}
```

## Where is authorization enforced in your system?

Authorization is enforced at multiple levels:

1. **Route Level**: `role:ADMIN` middleware in `routes/web.php`
2. **Gate Level**: Laravel Gates in `AppServiceProvider.php`
3. **Blade Level**: `@can()` directives in views
4. **Controller Level**: Manual Gate::denies() checks

## How do you restrict users from accessing others' tasks?

Task access is controlled in the TaskController:

```php
public function index(Request $request)
{
    $user = Auth::user();
    
    $tasks = Task::with('user')
        ->when(!$user->isAdmin(), function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        // ...
}

public function show(Task $task)
{
    $user = Auth::user();
    if (!$user->isAdmin() && $task->user_id !== $user->id) {
        abort(403);
    }
    // ...
}
```

## How are you enforcing valid state transitions?

Task status transitions are validated using a transition map in the Task model:

```php
public function canTransitionTo(string $newStatus): bool
{
    $transitions = [
        'PENDING' => ['IN_PROGRESS', 'COMPLETED'],
        'IN_PROGRESS' => ['COMPLETED'],
        'COMPLETED' => ['APPROVED', 'REJECTED'],
        'APPROVED' => [],
        'REJECTED' => ['PENDING'],
    ];

    return in_array($newStatus, $transitions[$this->status] ?? []);
}
```

In the controller:
```php
if (isset($validated['status']) && !$task->canTransitionTo($validated['status'])) {
    return back()->with('error', 'Invalid status transition from ' . $task->status . ' to ' . $validated['status']);
}
```

## What happens if an invalid transition is attempted?

When an invalid transition is attempted:
1. The `canTransitionTo()` method returns `false`
2. The controller returns a 302 redirect with an error message
3. The user is redirected back to the previous page with the error message displayed

## How is audit data populated automatically?

The `AuditLog` model has a static `log()` method that creates audit entries:

```php
public static function log($entityType, $entityId, $action, $oldValues = null, $newValues = null)
{
    self::create([
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'action' => $action,
        'old_values' => $oldValues ? json_encode($oldValues) : null,
        'new_values' => $newValues ? json_encode($newValues) : null,
        'user_id' => auth()->id(),
    ]);
}
```

This is called in controllers after any create, update, or delete operation.

## Comments on Tasks

Users and admins can comment on tasks:
- Comments are stored in `task_comments` table with `task_id`, `user_id`, and `comment` fields
- The `user_id` references the user who created the comment
- `created_at` timestamp is automatically stored

```php
public function storeComment(Request $request, Task $task)
{
    $validated = $request->validate([
        'comment' => 'required|string',
    ]);

    $comment = TaskComment::create([
        'task_id' => $task->id,
        'user_id' => Auth::id(),
        'comment' => $validated['comment'],
    ]);

    AuditLog::log(Task::class, $task->id, 'comment_added', null, ['comment' => $validated['comment']]);

    return back()->with('success', 'Comment added successfully');
}
```

## Security Enforcement Requirements

### Laravel Middleware

The `role:ADMIN` middleware is used to protect routes:

```php
Route::middleware('role:ADMIN')->group(function () {
    // Admin-only routes
});
```

### Laravel Gates (Manual Implementation)

Gates are defined in AppServiceProvider for fine-grained permission control:

```php
// In boot() method of AppServiceProvider
Gate::define('task.create', function (User $user) {
    return $user->roleModel && $user->roleModel->hasPermission('task.create');
});

Gate::define('task.approve', function (User $user) {
    return $user->roleModel && $user->roleModel->hasPermission('task.approve');
});
```

Usage in Blade:
```php
@can('task.create')
    <button>Create Task</button>
@endcan
```

## How do you prevent unauthorized API access?

1. **JWT Authentication**: All protected routes require valid JWT token
2. **Role Middleware**: `role:ADMIN` middleware blocks non-admin users
3. **Gate Authorization**: Permission checks via Gates
4. **Ownership Checks**: Controllers verify task ownership before allowing modifications
5. **Input Validation**: All requests are validated using Laravel's validation
6. **CSRF Protection**: Web routes use CSRF tokens
7. **Audit Logging**: All important actions are logged for security review

## Database Schema

### Users Table
- id, name, email, password, role (USER/ADMIN), role_id (FK to roles), is_active, timestamps

### Roles Table
- id, name, description, is_active, timestamps

### Permissions Table
- id, name, description, timestamps

### Role Permissions Table (pivot)
- id, role_id (FK), permission_id (FK), timestamps

### Tasks Table
- id, user_id, title, description, status, created_by, updated_by, timestamps, soft deletes

### Task Comments Table
- id, task_id, user_id, comment, timestamps

### Audit Logs Table
- id, entity_type, entity_id, action, old_values, new_values, user_id, timestamps

## Brand Manager Setup

To create a Brand Manager user:

1. Run seeder: `php artisan db:seed`
2. Create user or edit existing user
3. In user edit page, set "Associate Role" to BRAND_MANAGER
4. Brand Manager inherits permissions: task.create, task.view, task.edit, comment.create, dashboard.view

## Common Issues

### Permission Check Not Working

1. Ensure role has permissions assigned in Roles > Show page
2. Ensure user has associated role (role_id) set
3. Verify permission name matches exactly in Gate definition
4. Check that role is_active = true

### User Cannot Access Feature

1. Check user's auth role (USER/ADMIN)
2. Check associated role in user profile
3. Verify role has required permission in role_permissions
4. Check if user is_active = true