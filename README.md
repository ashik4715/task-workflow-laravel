# Task Workflow Management System

A Laravel-based REST API for managing tasks with approval workflows, user roles, and audit tracking.

## What This System Does

This is a backend system that allows:
- Users to create and manage their own tasks
- Tasks to go through a workflow (Pending → In Progress → Completed → Approved/Rejected)
- Administrators to approve or reject completed tasks
- Everyone to add comments on tasks
- All actions to be tracked for security

## User Roles

### Regular User (USER)
- Create new tasks
- Update own tasks
- Mark tasks as completed
- Add comments to tasks
- View own profile

### Administrator (ADMIN)
- All regular user permissions
- View all users
- Change user status (active/inactive)
- View all tasks
- Approve or reject completed tasks
- View audit logs

## Quick Start

### 1. Install the Project

```bash
composer install
```

### 2. Set Up Environment

Copy the example environment file:
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

Register as a new user:

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

Get your authentication token:

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'
```

The response will give you a token. Use this token for all future requests.

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
Stores all user accounts with role (USER or ADMIN) and active status.

### tasks
Stores all tasks with title, description, status, and who created/updated.

### task_comments
Stores comments on tasks with user reference and timestamp.

### audit_logs
Tracks all important actions for security and debugging.

## Security Features

1. **Password Encryption**: All passwords are encrypted using BCrypt
2. **JWT Tokens**: Secure authentication with token expiration
3. **Role-Based Access**: Different permissions for users and admins
4. **Audit Logging**: Every important action is recorded

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/    # API controllers
│   └── Middleware/         # Role checking
├── Models/                 # Database models
config/                    # Configuration files
database/
├── migrations/            # Database structure
└── seeders/              # Sample data
routes/
└── api.php               # API routes
```

## Common Issues and Solutions

### Token Expiration
If your token expires, use the refresh endpoint:
```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 403 Forbidden Error
This means you don't have permission. Make sure:
- You are using the correct role token
- Your account is active

### Invalid Status Transition
You cannot change to every status. Check the workflow section above.

## API Documentation

For complete API details, see [api_documentation.md](api_documentation.md)

## Requirements Met

- JWT-based authentication
- Role-Based Access Control (RBAC)
- Task workflow with approval process
- Audit tracking
- User management
- Task management with filtering and pagination
- Comments system

## License

This project is open source and available for learning and development.
