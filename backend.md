# SH-Selfhelp Backend API Rules

## Core Rules

1. API is RESTful, versioned (v1), and follows MVC architecture
2. Three modules: Content API, Auth API, Admin API
3. All requests/responses use JSON format
4. Set `X-Client-Type` header to `web` for frontend requests
5. Authentication uses JWT with dual-token system (access + refresh)
6. Include tokens in Authorization header: `Bearer <token>`
7. Handle token expiration with automatic refresh mechanism
8. All responses follow standard format with status, message, error, data fields
9. Implement proper error handling for all status codes (400, 401, 403, 404, 500)

## Authentication Endpoints

```
POST /api/v1/auth/login
- Request: {user, password}
- Response: {access_token, refresh_token, expires_in, token_type}

POST /api/v1/auth/refresh_token
- Request: {refresh_token}
- Response: {access_token, expires_in, token_type}

POST /api/v1/auth/logout
- Request: {access_token, refresh_token}
- Response: {success message}
```

## Content Endpoints

```
GET /api/v1/content/page/:keyword
- Response: {id, title, keyword, content, styles, meta}

GET /api/v1/content/all_routes
- Response: Array of navigation items with {id, title, keyword, url, children}
```

## Admin Endpoints

```
GET /api/v1/admin/access
- Response: {access: true/false}

GET /api/v1/admin/pages
- Response: Array of page objects with {id, title, keyword, status, timestamps}
```

## Response Format

```json
{
  "status": 200,                // HTTP status code
  "message": "OK",            // Status message
  "error": null,              // Error message if any
  "logged_in": true,          // Authentication status
  "meta": {                   // Metadata
    "version": "v1",
    "timestamp": "2025-05-09T08:57:13+02:00"
  },
  "data": {}                  // Response data
}
```

## Implementation Rules

1. Store tokens securely (HTTP-only cookies recommended)
2. Implement token refresh before access token expires
3. Send appropriate error messages to users
4. Handle offline state gracefully in mobile contexts
5. Validate all user inputs before sending to API
6. Render dynamic content structures from content API
7. Enforce access control based on user permissions
8. Respect API rate limits if implemented
9. Log API errors for debugging
10. Use HTTPS for all API communications
