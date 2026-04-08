# Task Workflow Management System API Documentation

## Overview

This document describes the RESTful API for the Task Workflow Management System. The API provides endpoints for user authentication, task management, approval workflows, and audit logging.

## Base URL

```
http://localhost:8000/api
```

## Authentication

The API uses JWT (JSON Web Token) for authentication. Include the token in the Authorization header:

```
Authorization: Bearer <your_token>
```

## Response Format

All responses follow this structure:

### Success Response
```json
{
    "message": "Success message",
    "data": { }
}
```

### Error Response
```json
{
    "message": "Error message",
    "errors": { }
}
```

### Paginated Response
```json
{
    "current_page": 1,
    "data": [],
    "first_page_url": "...",
    "from": 1,
    "last_page": 1,
    "last_page_url": "...",
    "next_page_url": null,
    "path": "...",
    "per_page": 15,
    "prev_page_url": null,
    "to": 2,
    "total": 2
}
```

---

## Authentication Endpoints

### Register User

Create a new user account.

**Endpoint:** `POST /auth/register`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword",
    "password_confirmation": "securepassword"
}
```

**Response (201):**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "USER",
        "is_active": true,
        "created_at": "2026-04-08T07:00:00Z"
    },
    "authorization": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "type": "bearer"
    }
}
```

---

### Login

Authenticate user and receive JWT token.

**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response (200):**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "user@example.com",
        "role": "USER",
        "is_active": true
    },
    "authorization": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "type": "bearer"
    }
}
```

**Error Response (401):**
```json
{
    "message": "Invalid credentials"
}
```

**Error Response (403):**
```json
{
    "message": "Your account is inactive"
}
```

---

### Logout

Invalidate the current JWT token.

**Endpoint:** `POST /auth/logout`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "message": "Logged out successfully"
}
```

---

### Get Current User

Retrieve the authenticated user's profile.

**Endpoint:** `GET /auth/me`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "id": 1,
    "name": "Test User",
    "email": "user@example.com",
    "role": "USER",
    "is_active": true,
    "created_at": "2026-04-08T07:00:00Z"
}
```

---

### Refresh Token

Refresh the JWT token.

**Endpoint:** `POST /auth/refresh`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "authorization": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "type": "bearer"
    }
}
```

---

## User Management Endpoints

### List Users

Get all users (Admin only).

**Endpoint:** `GET /users`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| role | string | Filter by role (USER, ADMIN) |
| is_active | boolean | Filter by active status |
| per_page | integer | Number of results per page (default: 15) |

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com",
            "role": "ADMIN",
            "is_active": true,
            "created_at": "2026-04-08T07:00:00Z"
        }
    ],
    "total": 1
}
```

---

### Get User

Get a specific user by ID.

**Endpoint:** `GET /users/{id}`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "ADMIN",
    "is_active": true,
    "created_at": "2026-04-08T07:00:00Z"
}
```

---

### Update User

Update user information (Admin only).

**Endpoint:** `PUT /users/{id}`

**Headers:** `Authorization: Bearer <token>`

**Request Body:**
```json
{
    "name": "Updated Name",
    "email": "updated@example.com"
}
```

**Response (200):**
```json
{
    "message": "User updated successfully",
    "user": {
        "id": 1,
        "name": "Updated Name",
        "email": "updated@example.com",
        "role": "USER",
        "is_active": true
    }
}
```

---

### Update User Status

Activate or deactivate a user (Admin only).

**Endpoint:** `PATCH /users/{id}/status`

**Headers:** `Authorization: Bearer <token>`

**Request Body:**
```json
{
    "is_active": false
}
```

**Response (200):**
```json
{
    "message": "User status updated successfully",
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "role": "USER",
        "is_active": false
    }
}
```

---

### Get Profile

Get the authenticated user's own profile.

**Endpoint:** `GET /users/profile`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "id": 1,
    "name": "Test User",
    "email": "user@example.com",
    "role": "USER",
    "is_active": true
}
```

---

### Update Profile

Update the authenticated user's own profile.

**Endpoint:** `PUT /users/profile`

**Headers:** `Authorization: Bearer <token>`

**Request Body:**
```json
{
    "name": "New Name",
    "email": "newemail@example.com",
    "password": "newpassword",
    "password_confirmation": "newpassword"
}
```

**Response (200):**
```json
{
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "name": "New Name",
        "email": "newemail@example.com",
        "role": "USER",
        "is_active": true
    }
}
```

---

## Task Management Endpoints

### List Tasks

Get all tasks (Admin sees all, User sees own tasks).

**Endpoint:** `GET /tasks`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | Filter by status (PENDING, IN_PROGRESS, COMPLETED, APPROVED, REJECTED) |
| search | string | Search in title and description |
| per_page | integer | Number of results per page (default: 15) |

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "title": "Sample Task",
            "description": "Task description",
            "status": "PENDING",
            "created_by": 1,
            "updated_by": 1,
            "created_at": "2026-04-08T07:00:00Z",
            "updated_at": "2026-04-08T07:00:00Z"
        }
    ],
    "total": 1
}
```

---

### Create Task

Create a new task.

**Endpoint:** `POST /tasks`

**Headers:** `Authorization: Bearer <token>`

**Request Body:**
```json
{
    "title": "My New Task",
    "description": "Task description here"
}
```

**Response (201):**
```json
{
    "message": "Task created successfully",
    "task": {
        "id": 1,
        "user_id": 1,
        "title": "My New Task",
        "description": "Task description here",
        "status": "PENDING",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2026-04-08T07:00:00Z",
        "updated_at": "2026-04-08T07:00:00Z"
    }
}
```

---

### Get Task

Get a specific task by ID.

**Endpoint:** `GET /tasks/{id}`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "id": 1,
    "user_id": 1,
    "title": "Sample Task",
    "description": "Task description",
    "status": "PENDING",
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2026-04-08T07:00:00Z",
    "updated_at": "2026-04-08T07:00:00Z",
    "user": { },
    "comments": []
}
```

---

### Update Task

Update an existing task.

**Endpoint:** `PUT /tasks/{id}`

**Headers:** `Authorization: Bearer <token>`

**Request Body:**
```json
{
    "title": "Updated Title",
    "description": "Updated description",
    "status": "IN_PROGRESS"
}
```

**Response (200):**
```json
{
    "message": "Task updated successfully",
    "task": {
        "id": 1,
        "title": "Updated Title",
        "description": "Updated description",
        "status": "IN_PROGRESS"
    }
}
```

**Error Response (422) - Invalid Status Transition:**
```json
{
    "message": "Invalid status transition",
    "current_status": "PENDING",
    "requested_status": "APPROVED"
}
```

---

### Delete Task

Soft delete a task.

**Endpoint:** `DELETE /tasks/{id}`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "message": "Task deleted successfully"
}
```

---

### Approve Task

Approve a completed task (Admin only).

**Endpoint:** `POST /tasks/{id}/approve`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "message": "Task approved successfully",
    "task": {
        "id": 1,
        "status": "APPROVED"
    }
}
```

**Error Response (422):**
```json
{
    "message": "Only completed tasks can be approved"
}
```

---

### Reject Task

Reject a completed task (Admin only).

**Endpoint:** `POST /tasks/{id}/reject`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "message": "Task rejected successfully",
    "task": {
        "id": 1,
        "status": "REJECTED"
    }
}
```

---

## Comment Endpoints

### List Comments

Get all comments for a task.

**Endpoint:** `GET /tasks/{taskId}/comments`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "task_id": 1,
            "user_id": 1,
            "comment": "This is a comment",
            "created_at": "2026-04-08T07:00:00Z",
            "user": {
                "id": 1,
                "name": "Test User"
            }
        }
    ]
}
```

---

### Add Comment

Add a comment to a task.

**Endpoint:** `POST /tasks/{taskId}/comments`

**Headers:** `Authorization: Bearer <token>`

**Request Body:**
```json
{
    "comment": "This is my comment"
}
```

**Response (201):**
```json
{
    "message": "Comment added successfully",
    "comment": {
        "id": 1,
        "task_id": 1,
        "user_id": 1,
        "comment": "This is my comment",
        "created_at": "2026-04-08T07:00:00Z"
    }
}
```

---

### Delete Comment

Delete a comment.

**Endpoint:** `DELETE /tasks/{taskId}/comments/{commentId}`

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "message": "Comment deleted successfully"
}
```

---

## Audit Log Endpoints

### List Audit Logs

Get all audit logs (Admin only).

**Endpoint:** `GET /audit-logs`

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| entity_type | string | Filter by entity type (App\Models\User, App\Models\Task) |
| entity_id | integer | Filter by entity ID |
| action | string | Filter by action (created, updated, deleted, etc.) |
| user_id | integer | Filter by user ID |
| per_page | integer | Number of results per page |

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "entity_type": "App\\Models\\Task",
            "entity_id": 1,
            "action": "created",
            "user_id": 1,
            "old_values": null,
            "new_values": {
                "title": "My Task",
                "status": "PENDING"
            },
            "created_at": "2026-04-08T07:00:00Z"
        }
    ]
}
```

---

## Task Statuses

The task workflow includes the following statuses:

| Status | Description |
|--------|-------------|
| PENDING | Task created, awaiting action |
| IN_PROGRESS | Task is being worked on |
| COMPLETED | Task work is done, awaiting approval |
| APPROVED | Task approved by admin |
| REJECTED | Task rejected by admin |

### Valid Status Transitions

```
PENDING -> IN_PROGRESS
PENDING -> COMPLETED
IN_PROGRESS -> COMPLETED
COMPLETED -> APPROVED
COMPLETED -> REJECTED
REJECTED -> PENDING
```

---

## Role-Based Access Control

### User Roles

| Role | Permissions |
|------|-------------|
| USER | Create/update own tasks, view own profile, add comments |
| ADMIN | All USER permissions + manage users, view all tasks, approve/reject tasks, view audit logs |

### Authorization

- User endpoints (`/users`) - Admin only for listing, updating status
- Task approval/rejection - Admin only
- Audit logs - Admin only

---

## Testing Credentials

After seeding the database, you can use these credentials:

**Admin Account:**
- Email: admin@example.com
- Password: password

**User Account:**
- Email: user@example.com
- Password: password

---

## Error Codes

| Code | Description |
|------|-------------|
| 401 | Unauthenticated - Invalid or missing JWT token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Validation Error - Invalid input data |
| 500 | Server Error - Internal server error |
