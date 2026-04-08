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

## Frequently Asked Questions

### How do permissions work now?

1. Admin creates roles and assigns permissions in Roles section
2. Users are assigned an associated role in their profile
3. Permissions are checked via Laravel Gates in AppServiceProvider
4. Admin users (role=ADMIN) have automatic access to everything

### How do I create a Brand Manager?

1. Run `php artisan db:seed` to create roles and permissions
2. Create a user with role=USER
3. In user edit, set "Associate Role" to BRAND_MANAGER
4. The user will inherit Brand Manager permissions

### Why was user_permissions removed?

The user_permissions table was redundant. Permissions are now managed through:
- Roles define permission sets
- Users are assigned roles
- All permission checks go through the role

This is cleaner and follows standard RBAC practices.

### How is authorization enforced?

1. **Middleware**: `role:ADMIN` protects admin routes
2. **Gates**: Defined in AppServiceProvider, checked via Gate::allows()
3. **Blade**: @can('permission') directives
4. **Controller**: Manual Gate::denies() checks

### Can I add permissions directly to a user?

No. All permissions come through the associated role. To customize a user's permissions:
1. Create a new role with the desired permissions
2. Assign that role to the user

## License

This project is open source and available for learning and development.