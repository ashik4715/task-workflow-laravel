# QA Documentation - Task Workflow Management System

## How do you ensure only ADMIN can manage users?

User management is protected at multiple levels:

1. **Route Level**: The `role:ADMIN` middleware protects all user management routes:
```php
Route::middleware('role:ADMIN')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    // ...
});
```

2. **Controller Level**: Additional checks in the destroy method prevent deleting own account and ADMIN users:
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
    // ...
}
```

## How are you validating and extracting user information from JWT?

JWT validation is handled by the `tymon/jwt-auth` package:

1. **Token Generation**: The `AuthController` uses `JWTAuth::attempt()` to validate credentials and generate tokens
2. **User Extraction**: The `getJWTIdentifier()` method in the User model returns the user's primary key
3. **Custom Claims**: The `getJWTCustomClaims()` method adds custom data (like role) to the token
4. **Authentication**: All API controllers use `auth()->user()` or `$request->user()` to retrieve the authenticated user from the token

Example:
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

1. **Middleware Level**: Laravel middleware in `app/Http/Middleware/RoleMiddleware.php` checks user roles
2. **Controller Level**: Controllers check `$user->isAdmin()` before allowing admin actions
3. **Route Level**: The `role:ADMIN` middleware protects admin-only routes in `routes/api.php` and `routes/web.php`

Example:
```php
public function update(Request $request, Task $task)
{
    $user = Auth::user();
    if (!$user->isAdmin()) {
        abort(403, 'Only administrators can update tasks.');
    }
    // ...
}
```

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
2. The controller returns a 302 redirect with an error message: "Invalid status transition from {current} to {attempted}"
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

This is called in controllers after any create, update, or delete operation:
- `createdBy` and `updatedBy` - automatically captured from `auth()->id()`
- `createdAt` and `updatedAt` - automatic Laravel timestamps

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

### Laravel Policies/Gates

Gates can be used for method-level security. Example policy for Task:

```php
// In AuthServiceProvider
Gate::define('manage-task', function ($user, $task) {
    return $user->isAdmin() || $task->user_id === $user->id;
});

Gate::define('approve-task', function ($user) {
    return $user->isAdmin();
});
```

Usage in controller:
```php
if (Gate::denies('approve-task')) {
    abort(403);
}
```

## How do you prevent unauthorized API access?

1. **JWT Authentication**: All protected routes require valid JWT token
2. **Role Middleware**: `role:ADMIN` middleware blocks non-admin users
3. **Ownership Checks**: Controllers verify task ownership before allowing modifications
4. **Input Validation**: All requests are validated using Laravel's validation
5. **CSRF Protection**: Web routes use CSRF tokens
6. **Audit Logging**: All important actions are logged for security review

## Database Schema

### Users Table
- id, name, email, password, role (USER/ADMIN), role_id, is_active, timestamps

### Tasks Table
- id, user_id, title, description, status, created_by, updated_by, timestamps, soft deletes

### Task Comments Table
- id, task_id, user_id, comment, timestamps

### Audit Logs Table
- id, entity_type, entity_id, action, old_values, new_values, user_id, timestamps

### Permissions Table
- id, name, description, timestamps

### Role Permissions Table (pivot)
- id, role_id, permission_id, timestamps

### User Permissions Table (pivot)
- id, user_id, permission_id, timestamps
