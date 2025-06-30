# User Management API Documentation

## Overview

The User Management API provides comprehensive functionality for managing users in the system with server-side pagination, search, sorting, and complete CRUD operations. This implementation follows Symfony best practices and provides efficient handling of large user datasets.

## Key Features

- **Pagination**: Server-side pagination to handle large user datasets efficiently
- **Search**: Search users by email, name, or username
- **Sorting**: Sort by email, name, last_login, blocked status, or user_type
- **CRUD Operations**: Complete Create, Read, Update, Delete operations
- **User Groups Management**: Add/remove users from groups
- **User Roles Management**: Add/remove roles from users
- **Validation Codes**: Handle validation codes for user registration
- **Block/Unblock**: Block or unblock users
- **Additional Actions**: Send activation mail, clean user data, impersonate users

## API Endpoints

### 1. Get Users (Paginated)
```
GET /cms-api/v1/admin/users
```

**Query Parameters:**
- `page` (int, optional): Page number (default: 1)
- `pageSize` (int, optional): Items per page (default: 20, max: 100)
- `search` (string, optional): Search term for email, name, or username
- `sort` (string, optional): Sort field (email, name, last_login, blocked, user_type)
- `sortDirection` (string, optional): Sort direction (asc, desc, default: asc)

**Response:**
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 1,
        "email": "user@example.com",
        "name": "John Doe",
        "last_login": "2024-01-15 (5 days ago)",
        "status": "Active",
        "blocked": false,
        "code": "ABC123",
        "groups": "admin; users",
        "user_activity": 45,
        "user_type_code": "admin",
        "user_type": "Administrator"
      }
    ],
    "pagination": {
      "page": 1,
      "pageSize": 20,
      "totalCount": 150,
      "totalPages": 8,
      "hasNext": true,
      "hasPrevious": false
    }
  }
}
```

### 2. Get Single User
```
GET /cms-api/v1/admin/users/{userId}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "user_name": "johndoe",
    "id_genders": 1,
    "id_languages": 1,
    "id_userTypes": 72,
    "groups": [
      {"id": 1, "name": "admin", "description": "Administrator group"}
    ],
    "roles": [
      {"id": 1, "name": "ROLE_ADMIN", "description": "Administrator role"}
    ]
  }
}
```

### 3. Create User
```
POST /cms-api/v1/admin/users
```

**Request Body:**
```json
{
  "email": "newuser@example.com",
  "name": "New User",
  "user_name": "newuser",
  "password": "securepassword",
  "user_type_id": 72,
  "blocked": false,
  "id_genders": 1,
  "id_languages": 1,
  "validation_code": "CODE123",
  "group_ids": [1, 2],
  "role_ids": [1]
}
```

### 4. Update User
```
PUT /cms-api/v1/admin/users/{userId}
```

**Request Body:**
```json
{
  "email": "updated@example.com",
  "name": "Updated Name",
  "blocked": false
}
```

### 5. Delete User
```
DELETE /cms-api/v1/admin/users/{userId}
```

### 6. Block/Unblock User
```
PATCH /cms-api/v1/admin/users/{userId}/block
```

**Request Body:**
```json
{
  "blocked": true
}
```

### 7. User Groups Management

#### Get User Groups
```
GET /cms-api/v1/admin/users/{userId}/groups
```

#### Add Groups to User
```
POST /cms-api/v1/admin/users/{userId}/groups
```

**Request Body:**
```json
{
  "group_ids": [1, 2, 3]
}
```

#### Remove Groups from User
```
DELETE /cms-api/v1/admin/users/{userId}/groups
```

**Request Body:**
```json
{
  "group_ids": [2, 3]
}
```

### 8. User Roles Management

#### Get User Roles
```
GET /cms-api/v1/admin/users/{userId}/roles
```

#### Add Roles to User
```
POST /cms-api/v1/admin/users/{userId}/roles
```

**Request Body:**
```json
{
  "role_ids": [1, 2]
}
```

#### Remove Roles from User
```
DELETE /cms-api/v1/admin/users/{userId}/roles
```

**Request Body:**
```json
{
  "role_ids": [2]
}
```

### 9. Additional Actions

#### Send Activation Mail
```
POST /cms-api/v1/admin/users/{userId}/send-activation-mail
```

#### Clean User Data
```
POST /cms-api/v1/admin/users/{userId}/clean-data
```

#### Impersonate User
```
POST /cms-api/v1/admin/users/{userId}/impersonate
```

## Validation Code Handling

When creating a user with a validation code:

1. **If code exists and is not consumed**: The code will be assigned to the user and marked as consumed
2. **If code exists and is already consumed**: An error will be returned
3. **If code doesn't exist**: A new validation code will be created and assigned to the user

## Database Structure

### Key Tables
- `users`: Main user table
- `users_groups`: Many-to-many relationship between users and groups
- `users_roles`: Many-to-many relationship between users and roles
- `validation_codes`: Validation codes for user registration
- `lookups`: Lookup values for user types and statuses

### New Database View
A new view `view_users_management` has been created that provides optimized data for user management operations, including:
- User basic information
- Last login with days calculation
- Status information
- Validation codes
- Aggregated groups and roles
- User activity statistics

## Performance Considerations

1. **Pagination**: Always use pagination for user lists to avoid loading large datasets
2. **Indexing**: Proper indexes are added on frequently queried fields (email, name, blocked, etc.)
3. **Query Optimization**: The service uses optimized queries with proper joins and grouping
4. **Caching**: Consider implementing caching for frequently accessed user data

## Security Features

1. **System User Protection**: Admin and TPF users cannot be deleted
2. **Validation**: Comprehensive validation for email uniqueness, username uniqueness
3. **Password Hashing**: Passwords are properly hashed using Symfony's password hasher
4. **Access Control**: All endpoints should be protected with proper ACL checks

## Error Handling

The API returns appropriate HTTP status codes:
- `200`: Success
- `201`: Created (for new users)
- `400`: Bad Request (validation errors)
- `403`: Forbidden (insufficient permissions)
- `404`: Not Found (user not found)
- `500`: Internal Server Error

## Example Usage

### Frontend Implementation Example

```javascript
// Get users with pagination and search
const getUsers = async (page = 1, search = '', sort = 'email') => {
  const response = await fetch(`/cms-api/v1/admin/users?page=${page}&search=${search}&sort=${sort}`);
  return response.json();
};

// Create new user
const createUser = async (userData) => {
  const response = await fetch('/cms-api/v1/admin/users', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(userData)
  });
  return response.json();
};

// Block user
const blockUser = async (userId, blocked = true) => {
  const response = await fetch(`/cms-api/v1/admin/users/${userId}/block`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ blocked })
  });
  return response.json();
};
```

## Best Practices

1. **Always use pagination** when displaying user lists
2. **Implement proper search** to help users find specific accounts quickly
3. **Validate input** on both client and server side
4. **Handle errors gracefully** with user-friendly messages
5. **Use confirmation dialogs** for destructive operations like user deletion
6. **Implement real-time updates** for user status changes
7. **Log important actions** for audit trails

## Future Enhancements

- **Bulk operations**: Add endpoints for bulk user operations
- **Advanced filtering**: Add more sophisticated filtering options
- **User import/export**: CSV import/export functionality
- **User activity tracking**: Enhanced user activity monitoring
- **Password reset**: Automated password reset functionality
- **Email notifications**: Automated email notifications for user actions 