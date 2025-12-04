# API Reference

This guide documents the REST API provided by the SelfHelp CMS sh-shp-api plugin.

---

## Table of Contents

1. [API Overview](#api-overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Base Endpoints](#base-endpoints)
5. [Data Endpoints](#data-endpoints)
6. [Error Handling](#error-handling)
7. [Rate Limiting](#rate-limiting)

---

## API Overview

### Architecture

The SelfHelp API is provided through the **sh-shp-api** plugin:

```
server/plugins/sh-shp-api/
├── server/
│   ├── api/
│   │   ├── ApiBase.php      # Base endpoints (/api/base/)
│   │   ├── ApiData.php      # Data endpoints (/api/data/)
│   │   └── ApiRequest.php   # Base request handler
│   └── service/
│       └── globals.php      # HTTP constants
```

### Base URL

```
https://your-domain.com/api/{module}/{endpoint}
```

### HTTP Methods

| Method | Purpose |
|--------|---------|
| `GET` | Retrieve data |
| `POST` | Create data |
| `PUT` | Update data |
| `DELETE` | Delete data |

### Content Types

**Request:**
- `application/json`
- `application/x-www-form-urlencoded`

**Response:**
- `application/json`

---

## Authentication

### API Key Authentication

All API requests require authentication via the `X-API-Key` header:

```http
GET /api/data/table/users
X-API-Key: your-api-key-here
```

### Obtaining an API Key

API keys are managed in the `users_api` table:

```sql
-- View existing keys
SELECT * FROM users_api WHERE id_users = :user_id;
```

### Authentication Flow

```
1. Request received
2. Extract X-API-Key header
3. Lookup user by token
4. Verify user exists and active
5. Check ACL permissions
6. Set session user ID
7. Process request
```

### Permission Verification

API endpoints respect the same ACL permissions as web pages:

- User must have appropriate page permissions
- `acl_select` for GET requests
- `acl_insert` for POST requests
- `acl_update` for PUT requests
- `acl_delete` for DELETE requests

---

## Response Format

### Standard Response Structure

All API responses follow this format:

```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": {...}
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `timestamp` | string | Response timestamp |
| `status` | integer | HTTP status code |
| `message` | string | Status message |
| `response` | mixed | Response data |
| `error_message` | string | Error details (on error) |

### Success Response Example

```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  ]
}
```

### Error Response Example

```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 404,
  "message": "Not Found",
  "error_message": "The table does not exist!"
}
```

---

## Base Endpoints

### Health Check

Test API connectivity.

**Request:**
```http
GET /api/base/ping
X-API-Key: your-api-key
```

**Response:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": "pong"
}
```

### Greeting

Test endpoint with parameters.

**GET Request:**
```http
GET /api/base/hallo/{name}
X-API-Key: your-api-key
```

**Response:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": "Hallo {name}"
}
```

**POST Request:**
```http
POST /api/base/hallo/{name}
X-API-Key: your-api-key
Content-Type: application/json

{
  "my_name": "API User"
}
```

**Response:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": "Hallo {name}. My name is: API User"
}
```

---

## Data Endpoints

### Get User Data

Retrieve data from a dataTable for the current user.

**Request:**
```http
GET /api/my/data/table/{table_name}?filter=...
X-API-Key: your-api-key
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `table_name` | string | Name of the dataTable |
| `filter` | string | Optional SQL filter clause |

**Example:**
```http
GET /api/my/data/table/user_profile?filter=ORDER BY id DESC
X-API-Key: your-api-key
```

**Response:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": [
    {
      "id": 1,
      "field_name": "value",
      "_json": {"parsed": "json_data"}
    }
  ]
}
```

### Get All Users Data (Admin)

Retrieve data for all users (requires admin access).

**Request:**
```http
GET /api/data/table/{table_name}?filter=...
X-API-Key: admin-api-key
```

**Example:**
```http
GET /api/data/table/contact_submissions?filter=AND timestamp > '2024-01-01'
X-API-Key: admin-api-key
```

### Create DataTable

Create a new dataTable.

**Request:**
```http
POST /api/data/table
X-API-Key: your-api-key
Content-Type: application/json

{
  "name": "new_table",
  "displayName": "New Table Display Name"
}
```

**Response:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": 123
}
```

> Note: `response` contains the new table ID.

### Import Data

Add a new record to a dataTable.

**Request (JSON):**
```http
POST /api/data/table/{table_name}
X-API-Key: your-api-key
Content-Type: application/json

{
  "field1": "value1",
  "field2": "value2"
}
```

**Request (Form Data):**
```http
POST /api/data/table/{table_name}
X-API-Key: your-api-key
Content-Type: application/x-www-form-urlencoded

field1=value1&field2=value2
```

**Response:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": 456
}
```

> Note: `response` contains the new record ID.

### Update Data Record

Update an existing record.

**Request:**
```http
PUT /api/data/table/{table_name}/{record_id}
X-API-Key: your-api-key
Content-Type: application/json

{
  "field1": "updated_value1",
  "field2": "updated_value2"
}
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `table_name` | string | Name of the dataTable |
| `record_id` | integer | ID of the record to update |

**Response:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 200,
  "message": "OK",
  "response": 456
}
```

---

## Error Handling

### HTTP Status Codes

| Code | Constant | Description |
|------|----------|-------------|
| 200 | `HTTP_OK` | Success |
| 201 | `HTTP_CREATED` | Resource created |
| 400 | `HTTP_BAD_REQUEST` | Invalid request |
| 401 | `HTTP_UNAUTHORIZED` | Authentication required |
| 403 | `HTTP_FORBIDDEN` | Permission denied |
| 404 | `HTTP_NOT_FOUND` | Resource not found |
| 409 | `HTTP_CONFLICT` | Resource conflict |
| 500 | `HTTP_INTERNAL_SERVER_ERROR` | Server error |

### Common Errors

**Authentication Error:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 401,
  "message": "Unauthorized",
  "error_message": "Invalid or missing API key"
}
```

**Not Found Error:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 404,
  "message": "Not Found",
  "error_message": "The table does not exist!"
}
```

**Conflict Error:**
```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 409,
  "message": "Conflict",
  "error_message": "The table already exists!"
}
```

### Error Handling Best Practices

1. **Always check status code** – Don't assume success
2. **Parse error_message** – Contains specific error details
3. **Handle authentication errors** – Refresh or regenerate API key
4. **Log errors** – For debugging

---

## Rate Limiting

### Limits

API calls may be rate limited to prevent abuse:

| Limit Type | Value |
|------------|-------|
| Requests per minute | 60 |
| Requests per hour | 1000 |

### Rate Limit Response

When rate limited:

```json
{
  "timestamp": "2024-01-15 10:30:00",
  "status": 429,
  "message": "Too Many Requests",
  "error_message": "Rate limit exceeded. Try again in 60 seconds."
}
```

---

## API Logging

### Request Logging

All API requests are logged to the `apiLogs` table:

```sql
SELECT * FROM apiLogs ORDER BY timestamp DESC LIMIT 100;
```

### Log Fields

| Field | Description |
|-------|-------------|
| `id_users` | Authenticated user ID |
| `remote_addr` | Client IP address |
| `target_url` | Requested endpoint |
| `post_params` | Request body |
| `status` | Response status |
| `return_response` | Response body |
| `timestamp` | Request time |

---

## Testing

### Using cURL

**Ping Test:**
```bash
curl -X GET "https://your-site.com/api/base/ping" \
     -H "X-API-Key: your-api-key"
```

**Get Data:**
```bash
curl -X GET "https://your-site.com/api/my/data/table/profile" \
     -H "X-API-Key: your-api-key"
```

**Create Record:**
```bash
curl -X POST "https://your-site.com/api/data/table/contacts" \
     -H "X-API-Key: your-api-key" \
     -H "Content-Type: application/json" \
     -d '{"name": "John", "email": "john@example.com"}'
```

**Update Record:**
```bash
curl -X PUT "https://your-site.com/api/data/table/contacts/123" \
     -H "X-API-Key: your-api-key" \
     -H "Content-Type: application/json" \
     -d '{"name": "John Updated"}'
```

### Using JavaScript

```javascript
// Fetch data
const response = await fetch('/api/my/data/table/profile', {
  method: 'GET',
  headers: {
    'X-API-Key': 'your-api-key',
    'Content-Type': 'application/json'
  }
});

const data = await response.json();
console.log(data.response);
```

---

## Mobile API Access

### Page Content Retrieval

Mobile apps can retrieve page content as JSON:

```http
POST /page-keyword
Content-Type: application/x-www-form-urlencoded

mobile=1
```

**Response:**

```json
{
  "page": "home",
  "title": "Home Page",
  "sections": [
    {
      "style_name": "card",
      "css": "custom-class",
      "title": {"content": "Welcome"},
      "children": [...]
    }
  ]
}
```

### Mobile Authentication

Mobile apps can authenticate via:

```http
POST /login
Content-Type: application/x-www-form-urlencoded

mobile=1&email=user@example.com&password=secret
```

---

*Previous: [Examples and Tutorials](examples-and-tutorials.md) | Next: [Glossary](glossary.md)*

