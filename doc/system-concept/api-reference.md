# API Reference

## Overview

SelfHelp's API functionality is provided through the **sh-shp-api plugin**. The core SelfHelp system does not include built-in API endpoints - all API functionality is implemented as a plugin that extends the base system. This plugin provides RESTful APIs for mobile applications and external integrations, following a consistent envelope structure for all responses.

## Response Format

### Standard Response Envelope

All API responses follow a consistent JSON structure:

```json
{
    "status": "success|error",
    "message": "Human-readable message",
    "error": "Detailed error information (if applicable)",
    "logged_in": true|false,
    "meta": {
        "page": 1,
        "total_pages": 5,
        "per_page": 20,
        "total_count": 100
    },
    "data": {
        // Response payload
    }
}
```

### Success Response Example

```json
{
    "status": "success",
    "message": "Data retrieved successfully",
    "logged_in": true,
    "meta": {
        "total_count": 25,
        "per_page": 10,
        "page": 1
    },
    "data": {
        "users": [
            {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            }
        ]
    }
}
```

### Error Response Example

```json
{
    "status": "error",
    "message": "Invalid credentials",
    "error": "The provided username or password is incorrect",
    "logged_in": false,
    "data": null
}
```

## Authentication

### Web Authentication

Traditional session-based authentication for web browsers:

```php
// Login process
POST /login
Content-Type: application/x-www-form-urlencoded

username=user@example.com&password=secret123
```

### Mobile API Authentication

JWT token-based authentication for mobile apps:

```http
POST /ajax/authenticate
Content-Type: application/json

{
    "username": "user@example.com",
    "password": "secret123",
    "mobile": true
}
```

Response:
```json
{
    "status": "success",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "expires_at": 1640995200,
        "user": {
            "id": 1,
            "name": "John Doe",
            "groups": ["subject", "experiment"]
        }
    }
}
```

### Token Usage

Include the JWT token in Authorization header for authenticated requests:

```http
GET /ajax/get_user_data
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
X-Mobile: true
```

## Plugin-Based API Endpoints

All API functionality is provided through the **sh-shp-api plugin**. The API is organized into logical modules:

### Base Module (`/api/base/`)

#### Ping/Pong Health Check

```http
GET /api/base/ping
```

Response:
```json
{
    "response": "pong"
}
```

#### Greeting Endpoints

```http
GET /api/base/hallo/{name}
POST /api/base/hallo/{name}
```

Parameters:
- `{name}` (string): Name for greeting
- `my_name` (POST only): Your name for response

### Data Module (`/api/data/`)

#### Get User Data

```http
GET /api/my/data/table/{table_name}?filter=...
```

Retrieves data for the authenticated user from specified dataTable.

#### Get All Users Data (Admin)

```http
GET /api/data/table/{table_name}?filter=...
```

Retrieves data for all users from specified dataTable (admin access required).

#### Import Data

```http
POST /api/data/table/{table_name}
Content-Type: application/x-www-form-urlencoded or application/json
```

Creates new data records in the specified dataTable.

#### Update Data Record

```http
PUT /api/data/table/{table_name}/{record_id}
Content-Type: application/x-www-form-urlencoded or application/json
```

Updates an existing data record.

#### Create DataTable

```http
POST /api/data/table
Content-Type: application/json

{
    "name": "new_table",
    "displayName": "New Table Display Name"
}
```

Creates a new dataTable structure.

## API Architecture

### Request Flow

1. **Plugin Routing**: API plugin intercepts `/api/*` routes
2. **Authentication**: JWT token validation (if required)
3. **Authorization**: User permission checks
4. **Data Processing**: Business logic execution
5. **Response Formatting**: Consistent JSON envelope

### Authentication

The API plugin supports multiple authentication methods:

#### JWT Token Authentication
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

#### Session-Based Authentication
For web-integrated API calls using existing user sessions.

### Response Format

All API responses follow a consistent structure:

```json
{
    "response": {
        // Actual response data
    }
}
```

Success and error responses use the same envelope format.

## Error Codes

### Common HTTP Status Codes

- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Authentication required or failed
- `403 Forbidden`: Access denied
- `404 Not Found`: Resource not found
- `409 Conflict`: Resource conflict (e.g., duplicate entry)
- `422 Unprocessable Entity`: Validation errors
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

### Application Error Codes

```json
{
    "status": "error",
    "message": "Validation failed",
    "error": {
        "code": "VALIDATION_ERROR",
        "details": {
            "email": "Invalid email format",
            "password": "Password too weak"
        }
    }
}
```

Common error codes:
- `VALIDATION_ERROR`: Input validation failed
- `AUTHENTICATION_FAILED`: Invalid credentials
- `AUTHORIZATION_FAILED`: Insufficient permissions
- `RESOURCE_NOT_FOUND`: Requested resource doesn't exist
- `DATABASE_ERROR`: Database operation failed
- `FILE_UPLOAD_ERROR`: File upload failed
- `RATE_LIMIT_EXCEEDED`: Too many requests

## Rate Limiting

API requests are rate limited to prevent abuse:

- **Authenticated requests**: 1000 requests per hour
- **Anonymous requests**: 100 requests per hour
- **File uploads**: 50 uploads per hour
- **Admin operations**: 500 requests per hour

Rate limit headers in responses:
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 950
X-RateLimit-Reset: 1640995200
X-RateLimit-Retry-After: 3600 (when exceeded)
```

## Versioning

API versioning is handled through:

1. **URL versioning**: `/api/v1/endpoint`
2. **Accept header**: `Accept: application/vnd.selfhelp.v1+json`
3. **Query parameter**: `?api_version=1`

Current version: v1 (default)

## SDK and Libraries

### JavaScript SDK

```javascript
// Initialize SDK
const selfhelp = new SelfHelpSDK({
    baseUrl: 'https://selfhelp.example.com',
    token: 'jwt_token_here'
});

// Authenticate
const user = await selfhelp.auth.login('user@example.com', 'password');

// Get data
const data = await selfhelp.data.getFormData('survey_form');

// Submit form
const result = await selfhelp.data.submitForm('survey_form', formData);
```

### Mobile SDKs

Available for:
- **iOS** (Swift)
- **Android** (Kotlin/Java)
- **React Native**
- **Flutter**

## Webhooks

### Incoming Webhooks

SelfHelp can receive webhooks from external services:

```http
POST /webhook/external_service
X-Webhook-Signature: sha256=signature
Content-Type: application/json

{
    "event": "survey_completed",
    "data": {
        "survey_id": "123",
        "user_id": "456",
        "responses": {...}
    }
}
```

### Webhook Verification

```php
function verifyWebhookSignature($payload, $signature, $secret) {
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($signature, $expectedSignature);
}
```

### Outgoing Webhooks

SelfHelp can send webhooks to external services when events occur:

```php
// Configure webhook in form actions
$webhookConfig = [
    'url' => 'https://external-service.com/webhook',
    'method' => 'POST',
    'headers' => ['Authorization' => 'Bearer token'],
    'events' => ['form_submitted', 'user_registered']
];
```

## Testing

### API Testing Tools

```bash
# cURL examples
curl -X POST https://selfhelp.example.com/ajax/authenticate \
  -H "Content-Type: application/json" \
  -d '{"username":"user@example.com","password":"secret","mobile":true}'

# Test with authentication
curl -X GET https://selfhelp.example.com/ajax/get_user_info \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "X-Mobile: true"
```

### Postman Collection

Import the SelfHelp API collection:

```json
{
    "info": {
        "name": "SelfHelp API",
        "version": "7.6.3"
    },
    "variable": [
        {
            "key": "base_url",
            "value": "https://selfhelp.example.com"
        },
        {
            "key": "auth_token",
            "value": ""
        }
    ]
}
```

### Automated Testing

```javascript
// API test example
describe('SelfHelp API', () => {
    let authToken;

    beforeAll(async () => {
        const response = await request(app)
            .post('/ajax/authenticate')
            .send({
                username: 'test@example.com',
                password: 'password',
                mobile: true
            });
        authToken = response.body.data.token;
    });

    test('should get user info', async () => {
        const response = await request(app)
            .get('/ajax/get_user_info')
            .set('Authorization', `Bearer ${authToken}`)
            .set('X-Mobile', 'true');

        expect(response.status).toBe(200);
        expect(response.body.status).toBe('success');
        expect(response.body.data.user).toBeDefined();
    });
});
```

## Migration Guide

### API Changes Between Versions

#### Version 7.0.0
- Added mobile parameter to all endpoints
- Changed response envelope structure
- Added JWT authentication support

#### Version 6.0.0
- Restructured form data API
- Added webhook support
- Changed pagination parameters

### Backward Compatibility

- Old endpoints remain functional with deprecation warnings
- Migration period: 2 major versions
- Breaking changes announced in changelog

## Support

### Documentation

- **API Reference**: Complete endpoint documentation
- **SDK Guides**: Platform-specific integration guides
- **Code Examples**: Sample implementations
- **Troubleshooting**: Common issues and solutions

### Getting Help

1. Check the changelog for recent changes
2. Review existing issues and solutions
3. Create detailed bug reports with:
   - Request/response examples
   - Environment information
   - Steps to reproduce
4. Contact support with system logs and error details
