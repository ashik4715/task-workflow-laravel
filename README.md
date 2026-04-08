# Task Workflow Management System

A Laravel-based REST API for managing tasks with approval workflows, user roles, permissions, and audit tracking.

## What This System Does

This is a backend system that allows:
- Users to create and manage their own tasks
- Tasks to go through a workflow (Pending → In Progress → Completed → Approved/Rejected)
- Administrators to approve or reject completed tasks
- Everyone to add comments on tasks
- All actions to be tracked for security
- Role-based permission management with Gate::authorize()

## Architecture Overview

### Two-Level Access Control System

The system uses a **dual-layer permission model**:

1. **Auth Role (role column)** - Determines high-level access:
   - `USER` - Regular authenticated user
   - `ADMIN` - Full administrative access

2. **Associated Role (role_id column)** - Determines granular permissions:
   - Links to the `roles` table
   - Permissions are managed through `role_permissions` pivot table
   - Users inherit permissions from their assigned role

### Permission Flow

```
User Model (role: USER/ADMIN)
    └── role_id → Role Model (has permissions via role_permissions)
              └── Permissions (via BelongsToMany relationship)
```

### Removed: user_permissions Table

The `user_permissions` table has been removed. Permissions are now managed exclusively through:
- **Roles**: Define sets of permissions
- **Role Permissions**: Link tables that connect roles to permissions
- **User Role Assignment**: Users are assigned to roles which grant permissions

## User Roles

### Auth Role (role column)
- **USER**: Regular user with basic task access
- **ADMIN**: Full system access with all capabilities

### Associated Roles (via role_id)
- **ADMIN**: Full permissions (all CRUD + approve/reject)
- **MANAGER**: Task management + approval/rejection
- **BRAND_MANAGER**: Limited task operations
- **USER**: Basic task creation and editing

## Access Control Implementation

### Using Laravel Gates (Manual Implementation)

The system uses Laravel's native Gate facade for authorization:

```php
// In AppServiceProvider - Define gates
Gate::define('task.create', function (User $user) {
    return $user->roleModel && $user->roleModel->hasPermission('task.create');
});

// In controllers/views - Check permissions
if (Gate::allows('task.create')) { ... }

// Or using Blade directives
@can('task.create')
    <button>Create Task</button>
@endcan
```

### Middleware Protection

- `role:ADMIN` middleware protects admin routes
- Gates provide method-level authorization

## Quick Start

### 1. Install the Project

```bash
composer install
```

### 2. Set Up Environment

```bash
copy .env.example .env
```

### 3. Generate Keys

```bash
php artisan key:generate
php artisan jwt:secret
```

### 4. Set Up Database

```bash
php artisan migrate
php artisan db:seed
```

### 5. Run the Application

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## How to Use the API

### Step 1: Create an Account

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Step 2: Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'
```

### Step 3: Use the Token

Include the token in your request headers:

```bash
-H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Testing the System

After setting up, you can test with these accounts:

| Account Type | Email | Password |
|--------------|-------|----------|
| Admin | admin@example.com | password |
| User | user@example.com | password |
| Brand Manager | brandmanager@example.com | password |
| Manager | manager@example.com | password |

## API Examples

### Create a Task

```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Build a website",
    "description": "Create a simple business website"
  }'
```

### Update Task Status

```bash
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "COMPLETED"
  }'
```

### Admin Approves a Task

```bash
curl -X POST http://localhost:8000/api/tasks/1/approve \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

### Add a Comment

```bash
curl -X POST http://localhost:8000/api/tasks/1/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "This looks great!"
  }'
```

## Task Workflow

Tasks move through these stages:

```
PENDING → IN_PROGRESS → COMPLETED → APPROVED
                ↓                 
            COMPLETED → REJECTED → PENDING
```

### Status Explanation

| Status | Meaning |
|--------|---------|
| PENDING | Task just created, no work started |
| IN_PROGRESS | Someone is working on the task |
| COMPLETED | Work is done, waiting for approval |
| APPROVED | Admin said it's good |
| REJECTED | Admin said it needs more work |

### Status Rules

- Anyone can change PENDING to IN_PROGRESS or COMPLETED
- IN_PROGRESS can only go to COMPLETED
- Only ADMIN can approve or reject COMPLETED tasks
- REJECTED tasks can go back to PENDING

## Database Tables

### users
Stores all user accounts with:
- `role` (USER/ADMIN) - Auth level
- `role_id` - Associated role from roles table

### roles
Stores role definitions with permissions

### permissions
Stores all available permissions

### role_permissions (Pivot)
Links roles to permissions (Many-to-Many)

### tasks
Stores all tasks with title, description, status, and owner

### task_comments
Stores comments on tasks

### audit_logs
Tracks all important actions for security

## Security Features

1. **Password Encryption**: BCrypt hashing
2. **JWT Tokens**: Secure authentication
3. **Role-Based Access Control**: Two-level auth system
4. **Gate Authorization**: Permission checking via Laravel Gates
5. **Audit Logging**: All actions tracked

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/    # API controllers
│   ├── Controllers/Web/    # Web controllers
│   └── Middleware/         # Role checking
├── Models/                 # Database models (User, Role, Permission, Task)
├── Providers/             # AppServiceProvider (Gates defined here)
config/                    # Configuration files
database/
├── migrations/            # Database structure
└── seeders/              # Sample data
routes/
├── api.php               # API routes
└── web.php               # Web routes
```

## Common Issues and Solutions

### Token Expiration
```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 403 Forbidden Error
- Verify your auth role (USER/ADMIN)
- Check associated role permissions in roles.show blade
- Ensure role has required permissions assigned

### Invalid Status Transition
Check the workflow section above for valid transitions.

## API Documentation

For complete API details, see [api_documentation.md](api_documentation.md)

## Requirements Met

- JWT-based authentication
- Role-Based Access Control (RBAC)
- Two-level permission system (Auth Role + Associated Role)
- Laravel Gates for authorization
- Task workflow with approval
- Audit tracking
- User management with role assignment
- Task management with filtering/pagination
- Comments system

# QA Documentation - Task Workflow Management System

## Architecture Changes

### (Architecture)
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

## License

This project is open source and available for learning and development.