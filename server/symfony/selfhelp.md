# SH-Self-help Symfony Backend Documentation

## Project Overview and Architecture

The SH-Selfhelp backend is a REST API server built with **Symfony 7.2.x** and **PHP 8.3+**. It implements a service-oriented architecture with clean separation of concerns, dynamic API routing, JWT-based authentication, and comprehensive JSON schema validation.

### Core Principles
* **Service-Oriented Architecture**: Business logic is encapsulated in services; controllers are thin delegates
* **Dynamic API Routing**: API routes are configured in the database, providing flexibility without code deployments
* **JWT-Based Authentication**: Secure stateless authentication with token blacklisting and permissions
* **JSON Schema Validation**: Request and response validation against defined schemas
* **Centralized Exception Handling**: Consistent API response envelopes for all endpoints

### Directory Structure

```
/config                        # Configuration files
  /packages                    # Bundle-specific configurations
  /schemas                     # JSON Schema validation files
    /api
      /v1
        /entities              # Entity schemas
        /requests              # Request validation schemas
        /responses             # Response schemas
          /common              # Common response structures
  /services.yaml              # Service definitions

/src                          # Application source code
  /Controller                 # Controllers (thin request handlers)
    /Api
      /V1                     # API version 1 controllers
        /Admin                # Administrative endpoints
        /Auth                 # Authentication endpoints
        /Public               # Public endpoints
  /Entity                     # Doctrine ORM entities
  /Repository                 # Doctrine repositories
  /Service                    # Business logic services
    /ACL                      # Access control services
    /Auth                     # Authentication services
    /CMS                      # Content management services
      /Admin                  # Admin-specific services
      /Frontend               # Frontend-specific services
    /Core                     # Core application services
  /Security                   # Security components
    /Voter                    # Authorization voters
  /Util                       # Utility classes
  /Routing                    # Custom route loading
```

## API Architecture

### API Versioning

The API uses a robust versioning system to maintain backward compatibility while allowing for future evolution. Version information is incorporated into multiple components:

1. **URL Path**: All API endpoints follow the pattern `/cms-api/v1/...`
2. **Controller Namespaces**: Controllers are organized in versioned directories (`src/Controller/Api/V1/`)
3. **Route Database Records**: Routes include version information in the `api_routes` table
4. **JSON Schema Files**: Schemas are organized by version in `/config/schemas/api/v1/`
5. **Response Envelope**: Each response includes the API version in its metadata

### Dynamic API Routing

All API routes are dynamically loaded from the database through a custom route loader. This provides several advantages:

1. **Runtime Route Management**: Routes can be modified without code deployment
2. **Permission-Based Access Control**: Routes are associated with permissions in the database
3. **Flexible Configuration**: Path parameters, requirements, and methods can be adjusted without code changes

The `ApiRouteLoader` service extends Symfony's `Loader` class to fetch routes from the database and convert them into Symfony Route objects. Routes are organized by API version and include specifications for controller, action method, path parameters, and allowed HTTP methods.

### Controller Organization

Controllers are thin request handlers that follow these principles:

1. **Domain Separation**: Controllers are organized by domain (Admin, Auth, Public)
2. **Delegation to Services**: Business logic is implemented in services, not controllers
3. **Common Base Functionality**: Controllers extend from base classes that provide shared functionality
4. **Request Validation**: Controllers use the `RequestValidatorTrait` to validate incoming requests against JSON schemas

### Adding a New API Version

To add a new API version (e.g., V2):

1. Create a new directory structure under `src/Controller/Api/V2/`
2. Create new JSON schema directories under `/config/schemas/api/v2/`
3. Add new routes to the `api_routes` table with `version='v2'`
4. Implement the new version-specific controllers and services

## Service Layer Organization

### Directory Structure
```
src/Service/
├── Auth/                     # Authentication related services

## Utility Classes

### Directory Structure
```
src/Util/
├── EntityUtil.php           # Utility for entity operations
```

### EntityUtil

The `EntityUtil` class provides utility methods for working with entities:

- `convertEntityToArray(object $entity): array`: Converts a Doctrine entity or any object to an array representation. Handles nested objects, collections, and scalar values appropriately.

Usage example:
```php
use App\Util\EntityUtil;

$entityArray = EntityUtil::convertEntityToArray($entity);
```

## Doctrine DBAL Best Practices

### Parameter Binding

When executing SQL queries with Doctrine DBAL, always use `bindParam()` or `bindValue()` instead of passing parameters directly to `executeQuery()` or `execute()`.

**Correct approach:**
```php
$stmt = $conn->prepare($sql);
$stmt->bindValue('param_name', $value, \PDO::PARAM_TYPE);
$result = $stmt->executeQuery();
```

**Deprecated approach (avoid):**
```php
$result = $conn->executeQuery($sql, ['param_name' => $value]);
```

This approach provides better type safety through explicit PDO parameter type specification:

- `\PDO::PARAM_INT` for integers
- `\PDO::PARAM_STR` for strings
- `\PDO::PARAM_BOOL` for booleans
- `\PDO::PARAM_NULL` for null values

## Service Layer Architecture

The service layer implements the core business logic of the application, following a domain-driven design. Services are organized by domain and responsibility, with clear separation of concerns.

### Service Organization

```
src/Service/
├── Auth/                    # Authentication services
│   ├── JWTService.php       # JWT token creation, validation, and blacklisting
│   └── LoginService.php     # User authentication and token management
├── CMS/                     # Content Management System services
│   ├── Admin/               # Administrative services
│   │   ├── AdminPageService.php     # Page management for admins
│   │   ├── AdminSectionService.php  # Section management
│   │   └── AdminLookupService.php   # Lookup data management
│   └── Frontend/            # Frontend services
│       └── PageService.php  # Public page access
├── ACL/                     # Access Control services
│   └── ACLService.php       # Permission management
├── Core/                    # Core framework services
│   ├── ApiResponseFormatter.php # Standard API response formatting
│   └── TransactionLogService.php  # Audit logging
└── Routing/                 # Routing services
    └── ApiRouteLoader.php   # Database-driven route loading
```

### Service Layer Principles

1. **Transactional Integrity**: Services use database transactions to ensure data consistency
2. **Permission Enforcement**: Services perform access control checks before modifying data
3. **Audit Logging**: Changes to core entities are logged for audit trails
4. **Error Isolation**: Exceptions are caught and translated to appropriate API responses
5. **Dependency Injection**: Services receive their dependencies through constructor injection

### Base Service Functionality

Many services extend from abstract base classes or use traits that provide common functionality:

- **EntityManagerAware**: Provides access to Doctrine ORM's EntityManager
- **LoggerAware**: Adds logging capabilities
- **RequestValidatorTrait**: Adds JSON Schema validation for requests
- **TransactionHandlerTrait**: Provides transaction management helpers
5. **Scalability**: New services can be added to the appropriate domain

### Service Categories

#### Auth Services
Services related to authentication, user context, and security.

#### CMS Services
Services for content management, split into Admin (backend management) and Frontend (public-facing content delivery).

#### ACL Services
Services for access control and permissions management.

#### Core Services
Foundational services that provide base functionality for other services.

#### Dynamic Services
Services for dynamic routing and controller handling.

## API Response Structure
All API responses follow a standardized format:

```json
{
    "status": 200, // The HTTP status code (e.g., 200 for OK, 401 for Unauthorized)
    "message": "OK", // Or a human-readable message corresponding to the status
    "error": null, // Contains error details if the request failed
    "logged_in": true, // boolean indicating authentication status
    "meta": {
        "version": "v1",
        "timestamp": "2025-06-02T16:35:00+02:00" // Example timestamp
    },
    "data": {} // Your response data here, or null for some errors
}
```

## JSON Schema Validation

The SH-Selfhelp backend uses JSON Schema for validating all API requests and responses. This ensures consistent data structures, clear API contracts, and automatic validation with detailed error messages.

### Schema Organization

Schemas are organized in the `/config/schemas/api/v1/` directory with the following structure:

```
/config/schemas/api/v1/
  /entities/                # Base entity schemas mirroring database structures
    pageEntity.json         # Core page entity structure
    lookupEntity.json       # Reusable lookup type entity
    ...
  /requests/               # Request validation schemas
    /admin/                # Admin API endpoints
      create_page.json
      update_page.json
      ...
    /frontend/             # Frontend API endpoints
      ...
  /responses/              # Response validation schemas
    /admin/                # Admin API responses
      page.json
      ...
    /frontend/             # Frontend API responses
      ...
    /common/               # Shared response components
      _response_envelope.json
```

### Schema Best Practices

1. **Entity-Schema Alignment**:
   - Request/response schemas should mirror entity structures
   - Property names should directly match entity field names (in camelCase)
   - Property types should align with entity field types
   - Required fields in schemas should correspond to non-nullable entity fields

2. **Naming Conventions**:
   - Use camelCase for all property names in schemas (e.g., `navPosition` not `nav_position`)
   - Use consistent names across entities and schemas (e.g., `headless` not `is_headless`)
   - Keep property names concise but descriptive

3. **Schema Composition**:
   - Use `$ref` to reference entity schemas in request/response schemas
   - Use `allOf` to extend or compose schema types
   - Break complex structures into smaller, reusable components

### Integration with Controllers

Controllers use the `RequestValidatorTrait` to validate requests:

```php
// Example from AdminPageController.php
public function updatePage(Request $request, string $page_keyword): JsonResponse
{
    try {
        // Validates request against the JSON schema
        $data = $this->validateRequest(
            $request, 
            'requests/admin/update_page', 
            $this->jsonSchemaValidationService
        );
        
        // Update the page using validated data
        $page = $this->adminPageService->updatePage(
            $page_keyword,
            $data['pageData'],
            $data['fields']
        );
        
        // Format response with standard envelope
        return $this->responseFormatter->formatSuccess($pageWithFields);
    } catch (ServiceException $e) {
        return $this->responseFormatter->formatException($e);
    }
}
```

### Validation Error Handling

Validation errors are automatically captured and returned in standardized format:

```json
{
    "status": 400,
    "message": "Bad Request",
    "error": "Validation failed",
    "logged_in": true,
    "meta": { "version": "v1", "timestamp": "..." },
    "data": null,
    "validation": [
        "Field 'pageData.headless': Boolean value expected"
    ]
}
```

## API Versioning and Database-Driven Routing

### Core Dynamic Controller System (2025-05-21)

The SH-Selfhelp Symfony backend uses a dynamic controller loading system as the core architectural component for handling API routes. This system allows routes to be defined in the database and loaded at runtime, without requiring code changes for new endpoints.

**Key Components:**

1. **DynamicControllerService**: Core service that dynamically calls controllers based on route names
   - Located at `src/Service/Dynamic/DynamicControllerService.php`
   - Resolves controller class and method from database entries
   - Handles dependency injection for controllers
   - Integrates with ACL for permission checks
   - Provides standardized API responses
   - Uses caching for performance optimization

2. **ApiRouteRepository**: Database access for route definitions
   - Maps route names to controller classes and methods

3. **Database Table**: `api_routes` stores all route definitions
   - Columns: `id`, `route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`

**Example Flow:**

```
Request → Router → DynamicControllerService.handle(routeName) → 
  → Get route from DB → ACL check → Instantiate controller → Call method → Return response
```

**Adding New Routes:**

Routes are added via SQL inserts into the `api_routes` table:

```sql
INSERT INTO `api_routes` (`route_name`,`version`,`path`,`controller`,`methods`,`requirements`,`params`) VALUES
('pages','v1','/pages','App\\Controller\\Api\\V1\\Frontend\\PageController::getPages','GET',NULL,NULL);
```

**Best Practices:**

- Always use the dynamic controller system for API routes
- Keep controller methods focused on a single responsibility
- Use standardized response formats via ApiResponseFormatter
- Include proper error handling in all controller methods
- Cache expensive operations where possible

### API Versioning System

The API supports versioning to maintain backward compatibility while evolving the API. The versioning system consists of several components:

1. **ApiVersionResolver**: Detects API versions from requests
2. **ApiVersionListener**: Integrates version detection into the request flow
3. **ApiRouteLoader**: Loads routes from the database and maps them to versioned controllers
4. **Versioned Controllers**: Implement API endpoints for specific versions

### Version Detection

API versions can be specified in two ways:

1. **URL Path**: `/cms-api/v1/...`
2. **Accept Header**: `Accept: application/vnd.self-help.v1+json`

If no version is specified, the default version (v1) is used.

### Database-Driven Routing

All API routes are dynamically loaded from the database. You do not need to edit YAML, PHP, or use fixtures/commands for route registration. To add or modify an API route, simply insert or update the relevant entry in the `api_routes` table.

```sql
CREATE TABLE `api_routes` (
  `id`           INT             NOT NULL AUTO_INCREMENT,
  `route_name`   VARCHAR(100)    NOT NULL,
  `version`      VARCHAR(10)     NOT NULL DEFAULT 'v1',
  `path`         VARCHAR(255)    NOT NULL,
  `controller`   VARCHAR(255)    NOT NULL,
  `methods`      VARCHAR(50)     NOT NULL,
  `requirements` JSON            NULL,
  `params`       JSON            NULL COMMENT 'Expected parameters: name → {in: body|query, required: bool}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_route_name_version` (`route_name`, `version`),
  UNIQUE KEY `uniq_version_path` (`version`, `path`)
);
```

### Controller Mapping

The system automatically maps controllers from the database to the versioned namespace structure:

```
Database controller: App\Controller\AuthController::login
↓
Actual controller: App\Controller\Api\V1\Auth\AuthController::login
```

This mapping is handled by the `ApiRouteLoader::mapControllerToVersionedNamespace()` method.

## Security Architecture

### JWT Authentication System

The SH-Self-help backend uses JSON Web Tokens (JWT) for stateless API authentication. This system provides secure access control without maintaining server-side sessions.

#### Key Components:

1. **JWTService**
   - Located at `src/Service/Auth/JWTService.php`
   - Manages token creation, validation, and blacklisting
   - Handles refresh token operations
   - Responsible for token signing using RSA/public-private key pair

2. **JWTTokenAuthenticator**
   - Implements Symfony's `AuthenticatorInterface`
   - Extracts tokens from request headers
   - Validates token signatures and expiration
   - Loads user identity from token payload

3. **Security Configuration**
   - Located in `config/packages/security.yaml`
   - Defines the JWT-secured firewall for API routes
   - Configures access control rules and authentication providers

#### Authentication Flow:

1. **Login Process**
   - User submits credentials to `/cms-api/v1/auth/login`
   - Upon successful validation, system generates and returns:
     - JWT token (short-lived, typically 1 hour)
     - Refresh token (long-lived, typically 2 weeks)

2. **Request Authentication**
   - Client includes JWT in `Authorization: Bearer {token}` header
   - `JWTTokenAuthenticator` validates token and loads user identity
   - If token is invalid or expired, 401 Unauthorized response is returned

3. **Token Refresh**
   - When JWT expires, client can send refresh token to `/cms-api/v1/auth/refresh-token`
   - System validates refresh token and issues a new JWT if valid
   - Refresh tokens are stored in the database and can be invalidated

4. **Logout Process**
   - Client calls `/cms-api/v1/auth/logout` endpoint
   - Current token is added to blacklist to prevent reuse
   - Refresh tokens are invalidated in the database

The system will automatically load and map the routes to the correct controllers.

## JWT Key Generation and Configuration

### JWT Firewall and User Provider Configuration

- The `security.yaml` file configures a `cms_api` firewall for `/cms-api` endpoints. This firewall uses the `jwt: ~` authenticator from LexikJWTAuthenticationBundle.
- The user provider is set to use the `App\Entity\User` entity, and the property is `username` (which should match the `username` claim in your JWT token, typically the user's email).
- Example JWT payload:
  ```json
  {
    "iat": 1747236586,
    "exp": 1747240186,
    "roles": ["ROLE_USER"],
    "username": "user@email.com"
  }
  ```
- For `/cms-api` endpoints, always send the JWT as:
  ```
  Authorization: Bearer <token>
  ```
- If `$this->getUser()` in a controller returns `null`, check that:
    - The `cms_api` firewall covers your route (pattern: `^/cms-api`).
    - The provider property matches the JWT claim (e.g., `username`).
    - The JWT is valid and not expired.

### JWT Key Pair Generation for Token Authentication

If you encounter errors like `Unable to create a signed JWT from the given configuration`, you must generate the required PEM keys for JWT authentication.

**Step-by-step instructions:**

1. **Create the JWT key directory:**
   ```bash
   mkdir -p config/jwt
   ```
   (This should be run from the Symfony project root.)

2. **Generate the private key:**
   ```bash
   openssl genrsa -out config/jwt/private.pem 4096
   ```

3. **Generate the public key:**
   ```bash
   openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
   ```

4. **(Optional) Use a passphrase for production:**
   ```bash
   openssl genrsa -aes256 -out config/jwt/private.pem 4096
   openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
   ```
   Then set your passphrase in `.env` as `JWT_PASSPHRASE=your_passphrase`.

5. **Update configuration:**
   Ensure your `lexik_jwt_authentication.yaml` or equivalent config points to these keys and uses the correct passphrase.

**Notes:**
- Both `private.pem` and `public.pem` must be readable by the PHP process.
- These steps are required for initial installation and whenever the keys are missing or need to be rotated.

## JSON Schema Validation

### Overview

The Self-help backend uses JSON Schema to validate all API requests and responses. This ensures consistency, enforces contracts between client and server, and provides clear error messages when validation fails.

### Schema Organization

Schemas are stored in the `/config/schemas/api/v1/` directory and are organized as follows:

```
schemas/
├── api/
│   └── v1/
│       ├── entities/         # Base entity schemas matching database entities
│       │   ├── pageEntity.json
│       │   ├── userEntity.json
│       │   └── ...
│       ├── requests/         # Request validation schemas
│       │   ├── admin/
│       │   │   ├── page/
│       │   │   │   ├── create.json
│       │   │   │   ├── update.json
│       │   │   │   └── ...
│       │   └── ...
│       └── responses/        # Response validation schemas
│           ├── common/       # Shared response components
│           │   └── _response_envelope.json
│           ├── admin/
│           │   ├── page/
│           │   │   ├── list.json
│           │   │   ├── get.json
│           │   │   └── ...
│           └── ...
```

### Schema Composition Pattern

The system uses a composable pattern for building schemas:

1. **Entity Schemas**: Define base data structures that mirror database entities
   - Example: `pageEntity.json` defines the core structure of a Page entity
   - Properties map directly to entity fields
   - Required fields match non-nullable database columns

2. **Request Schemas**: Define validation rules for API inputs
   - Reference entity schemas using `$ref`
   - May include only a subset of entity fields for partial updates
   - Add endpoint-specific validation rules

3. **Response Schemas**: Define expected API responses
   - Use the standard envelope structure from `_response_envelope.json`
   - Reference entity schemas for the `data` property
   - Handle collections of entities with array schemas

### Integration with Controllers

Controllers use the `RequestValidatorTrait` to validate requests:

```php
<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Api\RequestValidatorTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPageController
{
    use RequestValidatorTrait;
    
    public function createPage(Request $request): JsonResponse
    {
        // Validate request against schema
        $data = $this->validateRequest($request, 'admin/page/create');
        
        // Process validated data
        $page = $this->adminPageService->createPage($data);
        
        // Return formatted response
        return $this->apiResponseFormatter->formatResponse($page);
    }
}
```

### Schema Validation Process

1. **Request Parsing**: The JSON body is decoded from the request
2. **Schema Loading**: The appropriate schema is loaded based on the endpoint
3. **Validation**: Request data is validated against the schema
4. **Error Collection**: Validation errors are collected and formatted
5. **Exception Handling**: `RequestValidationException` is thrown if validation fails

### Example Schema: Page Entity

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "id": { "type": "integer" },
    "title": { "type": "string" },
    "slug": { "type": "string", "pattern": "^[a-z0-9-]+$" },
    "status": { "type": "string", "enum": ["draft", "published", "archived"] },
    "created_at": { "type": "string", "format": "date-time" },
    "updated_at": { "type": "string", "format": "date-time" }
  },
  "required": ["title", "slug", "status"]
}
```

### API Error Response with Validation Errors

```json
{
  "status": 422,
  "message": "Validation Failed",
  "error": "Request validation failed",
  "logged_in": true,
  "meta": {
    "version": "v1",
    "timestamp": "2025-06-02T16:35:00+02:00"
  },
  "data": null,
  "validation": [
    "Field 'slug': Pattern does not match '^[a-z0-9-]+$'",
    "Field 'status': Value must be one of: draft, published, archived"
  ]
}
```

## Dynamic API Routes

### Overview

Dynamic API routes are stored in the `api_routes` database table and loaded by the custom loader (`ApiRouteLoader`). Each route entry defines the HTTP path, controller, method(s), and optional requirements (e.g., parameter regexes). The entity representing a route is `App\Entity\ApiRoute`.

**Key columns:**
- `name`: The unique route name (e.g., `content_page`)
- `path`: The URL path (e.g., `/pages/{page_keyword}`)
- `controller`: The controller and method to handle the route (e.g., `App\\Controller\\ContentController::getPage`)
- `methods`: HTTP methods as a comma-separated string (e.g., `GET`, `POST`)
- `requirements`: (Optional) JSON string for parameter requirements (e.g., `{ "page_keyword": "[A-Za-z0-9_-]+" }`)
- `version`: The API version (e.g., `v1`, `v2`)

## Protocol Field Handling in Page Service (2025-06-06)

The `PageService::getAllAccessiblePagesForUser()` method has been updated to ensure that the `protocol` field is always set for pages. This addresses validation errors in the schema that requires the protocol field to be a non-null string.

## Transaction Logging (2025-06-06)

### Overview

Transaction logging has been implemented to track create, update, and delete operations throughout the application. This provides an audit trail of changes made to the system, enhancing security and accountability.

### Implementation Details

The transaction logging system consists of:

1. **Transaction Entity**: Stores transaction records with fields for:
   - Transaction time
   - Transaction type (create, update, delete)
   - User who performed the action
   - Table name affected
   - Record ID affected
   - Transaction log data (optional JSON data with full record details)

2. **TransactionService**: Service responsible for creating transaction logs:

```php
public function logTransaction(
    string $transactionTypeCode,
    string $transactionByCode,
    string $tableName,
    int $entryId,
    bool $includeRowData = false,
    ?string $notes = null
): void
```

### Integration

Transaction logging has been integrated into key operations:

- **Page Creation**: Logs a 'create' transaction after a page is successfully created
- **Page Deletion**: Logs a 'delete' transaction after a page is successfully deleted

The transaction logs include the page ID and keyword, providing context for the operation performed.

> **Note**: ACL entries are automatically deleted via foreign key constraints with cascade on delete, removing the need for explicit ACL deletion calls.

### Transaction Logging in LanguageService (2025-06-10)

The `LanguageService` has been updated to include transaction management and logging for all CRUD operations, following the same pattern as `AdminPageService`. This ensures all language changes are properly tracked for audit and debugging purposes.

#### Implementation Details

- **Transaction Management**: All CRUD operations (`createLanguage`, `updateLanguage`, `deleteLanguage`) now use explicit transaction management with `beginTransaction()`, `commit()`, and `rollback()` on exceptions.

- **Transaction Logging**: Each operation logs appropriate transaction details:
  - **Create**: Logs an `insert` transaction with the new language entity
  - **Update**: Logs an `update` transaction with the updated language entity
  - **Delete**: Logs a `delete` transaction with the language entity before deletion

- **Error Handling**: All methods maintain consistent error handling by rethrowing exceptions after rollback.

#### Example Implementation

```php
public function updateLanguage(Language $language): Language
{
    // Validation logic...
    
    $this->entityManager->beginTransaction();
    try {
        // Store original for comparison
        $originalLanguage = clone $language;
        
        // Update logic...
        $this->entityManager->flush();
        
        // Log the transaction
        $this->transactionService->logTransaction(
            LookupService::TRANSACTION_TYPES_UPDATE,
            LookupService::TRANSACTION_BY_BY_USER,
            'language',
            $language->getId(),
            $language,
            'Language updated: ' . $language->getLanguage() . ' (' . $language->getLocale() . ')'
        );
        
        $this->entityManager->commit();
        return $language;
    } catch (\Throwable $e) {
        $this->entityManager->rollback();
        throw $e;
    }
}
```

#### Best Practice

All service methods that modify data should:
1. Use explicit transaction management
2. Log transactions with appropriate details
3. Include proper error handling with rollback

## Controller Architecture (2025-06-10)

### BaseApiController Architecture

The `BaseApiController` class serves as the foundation for all API controllers in the application. It provides standardized response formatting and error handling capabilities.

### Key Features

- **Standardized Response Formatting**: All API responses follow a consistent structure
- **Centralized Error Handling**: Common error handling patterns are implemented once
- **Service Method Execution**: The `executeServiceMethod()` helper provides a consistent pattern for executing service methods with proper error handling

## BaseApiController Implementation (2025-06-10)

The `BaseApiController` class has been implemented as a foundation for all API controllers. It provides common functionality for handling API requests and responses.

### Key Features

- **Constructor Injection**: Uses constructor injection for the `ApiResponseFormatter` service
- **Response Formatting**: Provides methods for formatting success, error, and exception responses
- **Service Method Execution**: Implements a reusable pattern for executing service methods with consistent error handling

### Implementation Details

```php
namespace App\Controller;

use App\Service\Core\ApiResponseFormatter;

## Styles and Sections API (2025-06-15)

### Overview

The Styles and Sections API provides endpoints for retrieving style information and creating sections on pages or within other sections. This API follows the standard patterns established for the Self-help backend, including JSON schema validation, standardized response envelopes, and database-driven routing.

### Key Components

#### StyleController

The `StyleController` provides endpoints for retrieving style information:

- **GET /api/v1/styles**: Returns all styles grouped by their style groups
  - Uses the `view_styles` database view for efficient data retrieval
  - Returns styles organized by style groups with proper ordering

#### SectionController

The `SectionController` provides endpoints for creating sections:

- **POST /api/v1/sections/page**: Creates a new section on a page
  - Parameters: `pageKeyword`, `position`, `styleId`
  - Creates a section and adds it to the specified page at the given position

- **POST /api/v1/sections/section**: Creates a new child section inside another section
  - Parameters: `parentSectionId`, `position`, `styleId`
  - Creates a section and adds it as a child to the specified parent section at the given position

#### SectionService

The `SectionService` handles the business logic for creating sections:

- **Section Naming Convention**: Sections are named using the pattern `unixTimestamp-styleName`
- **Transaction Management**: All operations use explicit transaction management
- **Database Structure**: Uses the `pages_sections` and `sections_hierarchy` tables to manage relationships

### JSON Schema Validation

The API uses JSON schemas for request and response validation:

- **Entity Schemas**:
  - `styleEntity.json`: Defines the structure of a style entity
  - `styleGroupEntity.json`: Defines the structure of a style group entity
  - `sectionEntity.json`: Defines the structure of a section entity

- **Request Schemas**:
  - `create_page_section.json`: Validates requests to create sections on pages
  - `create_child_section.json`: Validates requests to create child sections

- **Response Schemas**:
  - `styleGroups.json`: Validates responses for the styles endpoint
  - `section_created.json`: Validates responses for section creation endpoints

### API Routes

The Styles and Sections API uses a dual routing approach:

1. **Database-driven Dynamic Routing**: Routes are registered in the `api_routes` table for runtime loading:

```sql
INSERT INTO `api_routes` (`route_name`, `path`, `controller`, `methods`, `requirements`, `params`, `version`) VALUES
('api_v1_styles_get', '/api/v1/styles', 'App\\Controller\\Api\\V1\\StyleController::getStyles', 'GET', NULL, NULL, 'v1'),
('api_v1_sections_page_create', '/api/v1/sections/page', 'App\\Controller\\Api\\V1\\SectionController::createPageSection', 'POST', NULL, '{"pageKeyword":{"in":"body","required":true},"position":{"in":"body","required":true},"styleId":{"in":"body","required":true}}', 'v1'),
('api_v1_sections_section_create', '/api/v1/sections/section', 'App\\Controller\\Api\\V1\\SectionController::createChildSection', 'POST', NULL, '{"parentSectionId":{"in":"body","required":true},"position":{"in":"body","required":true},"styleId":{"in":"body","required":true}}', 'v1');
```

2. **Symfony Route Attributes**: Controller methods are also decorated with Symfony route attributes for IDE support and static analysis:

```php
// SectionController.php
#[Route('/api/v1/sections/page', name: 'api_v1_sections_page_create', methods: ['POST'])]
public function createPageSection(Request $request): JsonResponse

#[Route('/api/v1/sections/section', name: 'api_v1_sections_section_create', methods: ['POST'])]
public function createChildSection(Request $request): JsonResponse

// StyleController.php
#[Route('/api/v1/styles', name: 'api_v1_styles_get', methods: ['GET'])]
public function getStyles(): JsonResponse
```

This dual approach ensures routes are properly registered both in the database for dynamic loading and in the code for static analysis and IDE support.

### Implementation Details

1. **StyleRepository**: Extended with a `findAllStylesGroupedByGroup()` method that uses raw SQL to query the `view_styles` view
2. **SectionService**: Implements methods for creating sections and managing their relationships
   - `createPageSection()`: Creates a section and links it to a page
   - `createChildSection()`: Creates a section and links it as a child to another section
   - `addPageSection()` and `addChildSection()`: Handle the relationship management
3. **RequestValidatorTrait**: Used in controllers to validate incoming requests against JSON schemas
4. **ApiResponseFormatter**: Used to format all responses with the standard envelope structure
5. **JsonSchemaValidator**: Used to validate responses against JSON schemas
6. **Route Configuration**: Both database-driven dynamic routing and Symfony attribute-based routing are used
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseApiController extends AbstractController
{
    protected ApiResponseFormatter $responseFormatter;

    public function __construct(ApiResponseFormatter $responseFormatter)
    {
        $this->responseFormatter = $responseFormatter;
    }

    protected function formatSuccess($data, ?string $schema = null, int $statusCode = 200): JsonResponse
    {
        return $this->responseFormatter->formatSuccess($data, $schema, $statusCode);
    }

    protected function formatError(string $message, int $statusCode = 400, ?string $code = null): JsonResponse
    {
        return $this->responseFormatter->formatError($message, $statusCode, $code);
    }

    protected function formatException(\Exception $exception): JsonResponse
    {
        return $this->responseFormatter->formatException($exception);
    }

    protected function executeServiceMethod(callable $serviceMethod, array $additionalData = [], ?string $schema = null, int $statusCode = 200): JsonResponse
    {
        try {
            $result = $serviceMethod();
            
            if ($additionalData) {
                if (is_array($result)) {
                    $result = array_merge($result, $additionalData);
                } else {
                    $result = ['result' => $result, ...$additionalData];
                }
            }
            
            return $this->formatSuccess($result, $schema, $statusCode);
        } catch (\Exception $e) {
            return $this->formatException($e);
        }
    }
}
```

## AdminPageController Refactoring (2025-06-10)

The `AdminPageController` has been refactored to extend `BaseApiController` and use its helper methods for consistent API response handling.

### Key Changes

- **Extended BaseApiController**: Now extends `BaseApiController` instead of `AbstractController`
- **Constructor Injection**: Uses constructor injection for dependencies
- **Consistent Method Pattern**: All controller methods now use the `executeServiceMethod()` helper for consistent error handling

### Example Method Implementation

```php
public function getPages(): JsonResponse
{
    return $this->executeServiceMethod(function() {
        return $this->adminPageService->getPages();
    }, [], 'responses/admin/pages');
}

public function getPageFields(string $page_keyword): JsonResponse
{
    return $this->executeServiceMethod(function() use ($page_keyword) {
        return $this->adminPageService->getPageFields($page_keyword);
    }, ['page_keyword' => $page_keyword], 'responses/admin/page_fields');
}

public function getPageSections(string $page_keyword): JsonResponse
{
    return $this->executeServiceMethod(function() use ($page_keyword) {
        $sections = $this->adminPageService->getPageSections($page_keyword);
        return [
            'page_keyword' => $page_keyword,
            'sections' => $sections
        ];
    }, [], 'responses/admin/page_sections');
}

public function updatePage(string $page_keyword, Request $request): JsonResponse
{
    return $this->executeServiceMethod(function() use ($page_keyword, $request) {
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        if (!isset($data['pageData']) || !isset($data['fieldTranslations']) || !is_array($data['fieldTranslations'])) {
            throw new BadRequestHttpException('Invalid request format. Expected pageData and fieldTranslations.');
        }
        
        // Validate that each translation has the required fields
        foreach ($data['fieldTranslations'] as $translation) {
            if (!isset($translation['idFields']) || !isset($translation['idLanguages']) || !isset($translation['content'])) {
                throw new BadRequestHttpException('Each translation must include idFields, idLanguages, and content.');
            }
        }
        
        // Call service method to update page
        $page = $this->adminPageService->updatePage(
            $page_keyword,
            $data['pageData'],
            $data['fieldTranslations']
        );
        
        // Return updated page with fields
        return $this->adminPageService->getPageFields($page->getKeyword());
    }, [], 'responses/admin/page_fields');
}
```
- **Service Method Execution**: Simplified pattern for executing service methods with automatic error handling
- **Constructor Injection**: The response formatter is injected through the constructor

### Implementation

The `BaseApiController` uses constructor injection with the `#[Autowire]` attribute to get the response formatter service:

```php
abstract class BaseApiController extends AbstractController
{
    protected ApiResponseFormatter $responseFormatter;
    
    /**
     * Constructor with auto-wired ApiResponseFormatter
     */
    public function __construct(
        #[Autowire(service: 'App\Service\ApiResponseFormatter')] ApiResponseFormatter $responseFormatter
    ) {
        $this->responseFormatter = $responseFormatter;
    }
    
    // Helper methods for formatting responses
}
```

### Child Controller Implementation

When creating a new API controller, extend the `BaseApiController` and pass the formatter to the parent constructor:

```php
class LanguageController extends BaseApiController
{
    private LanguageService $languageService;

    public function __construct(LanguageService $languageService, ApiResponseFormatter $responseFormatter) {
        parent::__construct($responseFormatter);
        $this->languageService = $languageService;
    }
    
    // Controller methods using formatSuccess(), formatError(), etc.
}
```

### Helper Methods

The BaseApiController provides several helper methods for consistent response formatting:

1. **formatSuccess($data, ?string $schema = null, int $statusCode)**: 
   - Formats successful responses with data
   - Optionally validates against a JSON schema
   - Returns a JsonResponse with the specified status code

2. **formatError(string $message, int $statusCode, array $errors = [])**: 
   - Formats error responses with a message and optional error details
   - Returns a JsonResponse with the specified status code

3. **formatException(\Exception $exception, bool $isAuthenticated = false)**:
   - Formats exception responses with appropriate error details
   - Handles authentication context for error details exposure

4. **executeServiceMethod(callable $serviceMethod, array $additionalData = [])**:
   - Executes a service method with automatic error handling
   - Wraps the result in a success response or handles exceptions
   - Adds authentication context to error responses

## Language Management API (2025-06-10)

The Language Management API provides endpoints for managing system languages. It includes public routes for listing languages (excluding the default language) and admin routes for CRUD operations on languages.

### Key Components

#### Entity and Repository

- **Language Entity**: Represents a language in the system with fields: `id`, `locale`, `language`, and `csvSeparator`.
- **LanguageRepository**: Provides methods to fetch all languages and all languages except the default one (ID > 1).

#### Service Layer

- **LanguageService**: Encapsulates business logic for language CRUD operations.
  - Prevents deletion or update of the default language (ID = 1).
  - Uses `EntityUtil::convertEntityToArray` for consistent entity-to-array conversion.
  - Throws appropriate HTTP exceptions for error handling.

#### Controllers

- **LanguageController** (Frontend): Public access to list languages (excluding default).
- **LanguageAdminController** (Admin): Admin operations for language management (list all, get by ID, create, update, delete).

#### JSON Schemas

- **languageEntity.json**: Reusable schema for Language entity.
- **get_languages.json**: Schema for listing languages response.
- **get_language.json**: Schema for single language retrieval response.
- **delete_language.json**: Schema for language deletion response.

#### Security

- Admin language management is protected by the `ROLE_CMS_ADMIN` role.
- The role is defined in the `lookups` table under `userRoles` type.

### API Routes

Routes are added to the `api_routes` table:

```sql
-- Public route to get all languages (except default)
INSERT INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('languages_get_all', 'v1', '/languages', 'App\\Controller\\Api\\V1\\Frontend\\LanguageController::getAllLanguages', 'GET', NULL, NULL);

-- Admin routes for language management
INSERT INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_languages_get_all', 'v1', '/admin/languages', 'App\\Controller\\Api\\V1\\Admin\\LanguageAdminController::getAllLanguages', 'GET', NULL, NULL),
('admin_languages_get_one', 'v1', '/admin/languages/{id}', 'App\\Controller\\Api\\V1\\Admin\\LanguageAdminController::getLanguage', 'GET', JSON_OBJECT('id', '[0-9]+'), JSON_OBJECT('id', JSON_OBJECT('in', 'path', 'required', true))),
('admin_languages_create', 'v1', '/admin/languages', 'App\\Controller\\Api\\V1\\Admin\\LanguageAdminController::createLanguage', 'POST', NULL, JSON_OBJECT('locale', JSON_OBJECT('in', 'body', 'required', true), 'language', JSON_OBJECT('in', 'body', 'required', true), 'csv_separator', JSON_OBJECT('in', 'body', 'required', false))),
('admin_languages_update', 'v1', '/admin/languages/{id}', 'App\\Controller\\Api\\V1\\Admin\\LanguageAdminController::updateLanguage', 'PUT', JSON_OBJECT('id', '[0-9]+'), JSON_OBJECT('id', JSON_OBJECT('in', 'path', 'required', true), 'locale', JSON_OBJECT('in', 'body', 'required', false), 'language', JSON_OBJECT('in', 'body', 'required', false), 'csv_separator', JSON_OBJECT('in', 'body', 'required', false))),
('admin_languages_delete', 'v1', '/admin/languages/{id}', 'App\\Controller\\Api\\V1\\Admin\\LanguageAdminController::deleteLanguage', 'DELETE', JSON_OBJECT('id', '[0-9]+'), JSON_OBJECT('id', JSON_OBJECT('in', 'path', 'required', true)));
```

### Security Configuration

The security configuration has been updated to protect language management endpoints:

```yaml
access_control:
    - { path: ^/cms-api/v1/auth, roles: PUBLIC_ACCESS }
    - { path: ^/cms-api/v1/admin/languages, roles: ROLE_CMS_ADMIN }
    - { path: ^/cms-api/v1, roles: PUBLIC_ACCESS }
    - { path: ^/cms-api/v1/admin, roles: IS_AUTHENTICATED_FULLY }
```

### Testing

Comprehensive tests have been implemented for the language management functionality:

- **LanguageServiceTest**: Unit tests for the LanguageService.
- **LanguageControllerTest**: Functional tests for both public and admin language API endpoints.

Tests cover successful operations, validation errors, and security restrictions (e.g., preventing deletion of the default language).

### API Response Schema Standardization (2025-06-11)

All API response schemas now follow a consistent envelope pattern using JSON Schema composition with `allOf`. This standardization ensures that all API responses have a consistent structure across the application.

#### Response Envelope Structure

The common response envelope (`_response_envelope.json`) defines the standard structure for all API responses:

```json
{
    "status": 200,           // HTTP status code
    "message": "Success",    // Human-readable message
    "error": null,          // Error message (null for success)
    "logged_in": true,      // Authentication status
    "meta": {               // Metadata
        "version": "v1",    // API version
        "timestamp": "..."   // Response timestamp
    },
    "data": { ... }        // Response payload (varies by endpoint)
}
```

#### Schema Implementation

Response schemas use the `allOf` composition pattern to combine the common envelope with endpoint-specific data structures:

```json
{
    "$schema": "http://json-schema.org/draft-07/schema#",
    "title": "API Response Schema",
    "type": "object",
    "allOf": [
        { "$ref": "../common/_response_envelope.json" }
    ],
    "properties": {
        "data": {
            "description": "Response data specific to this endpoint",
            "$ref": "../../entities/someEntity.json"
            // Or other data structure definition
        }
    },
    "required": ["data"]
}
```

#### Benefits

- **Consistency**: All API responses follow the same structure
- **Reusability**: Common envelope is defined once and reused
- **Maintainability**: Changes to the envelope structure only need to be made in one place
- **Documentation**: Clear schema definition for API consumers

#### Property Naming Conventions

When defining JSON schemas for API responses, ensure that property names match exactly what the API returns:

- Use camelCase for property names in schemas (e.g., `csvSeparator` not `csv_separator`)
- Ensure consistency between entity property names, serialization groups, and schema definitions
- When referencing entity schemas in response schemas, verify that property names align with the serialized output

## Security Configuration Best Practices (2025-06-10)

When configuring security in Symfony, especially for API endpoints, follow these best practices:

### Access Control Rule Ordering

In Symfony's security system, only the first matching access control rule is applied. Therefore, the order of rules is critical:

```yaml
access_control:
    # More specific paths first
    - { path: ^/cms-api/v1/auth, roles: PUBLIC_ACCESS }
    - { path: ^/cms-api/v1/admin, roles: IS_AUTHENTICATED_FULLY }
    # More general paths last
    - { path: ^/cms-api/v1, roles: PUBLIC_ACCESS }
```

### Best Practices

1. **Order from specific to general**: Always put more specific path patterns before more general ones
2. **Test thoroughly**: Verify access control works as expected for all endpoints
3. **Use appropriate roles**: Match the required security level to the sensitivity of the endpoint
4. **Document security requirements**: Ensure API documentation includes authentication requirements

## API Test Refactoring and Fixes

### BaseControllerTest Implementation

A new `BaseControllerTest` class has been created to serve as the foundation for all API controller tests. This class:

1. Extends Symfony's `WebTestCase` and provides common setup for API tests
2. Injects the `JsonSchemaValidationService` for response validation
3. Provides helper methods for obtaining authentication tokens:
   - `getAdminAccessToken()`: Retrieves and caches an admin JWT token
   - `getUserAccessToken()`: Retrieves and caches a regular user JWT token
4. Includes a smoke test annotated with `@group smoke` to verify basic setup

### Test Organization with Groups

Tests are now organized using PHPUnit groups to allow selective test execution:

```php
/**
 * @group public
 */
public function testPublicEndpoint(): void
{
    // Test code for public endpoint
}

/**
 * @group admin
 */
public function testAdminEndpoint(): void
{
    // Test code for admin endpoint
}
```

### Schema Validation in Tests

API response validation follows these best practices:

1. Use camelCase property names in test data to match schema definitions
2. Skip schema validation temporarily when schemas are being updated
3. Use appropriate token retrieval method based on endpoint security requirements
4. Group related assertions together for better readability

### Property Naming Consistency

To ensure schema validation passes in tests:

1. Use camelCase for all property names in API requests and responses (e.g., `csvSeparator` instead of `csv_separator`)
2. Maintain consistency between entity property names, serialization groups, and schema definitions
3. Update test data to match the expected property naming conventions

## Page Deletion Functionality (2025-06-06)

### Backend Implementation

The page deletion functionality is implemented in the `AdminPageService::deletePage()` method. This method:

1. Validates that the page exists
2. Checks if the current user has delete permissions for the page
3. Ensures the page has no child pages (preventing orphaned pages)
4. Deletes page field translations
5. Removes the page entity
6. Logs the transaction using the TransactionService

All operations are wrapped in a database transaction to ensure consistency. If any step fails, the transaction is rolled back.

```php
public function deletePage(string $pageKeyword): void
{
    $this->entityManager->beginTransaction();
    try {
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Check permissions and child pages...
        
        // Store page info for logging before deletion
        $pageKeywordForLog = $page->getKeyword();
        $pageIdForLog = $page->getId();
        
        // Delete the page
        $this->entityManager->remove($page);
        $this->entityManager->flush();
        
        // Log the transaction
        $this->transactionService->logTransaction(
            'delete',
            'user',
            'pages',
            $pageIdForLog,
            false,
            'Page deleted with keyword: ' . $pageKeywordForLog
        );
        
        $this->entityManager->commit();
    } catch (\Throwable $e) {
        $this->entityManager->rollback();
        throw $e;
    }
}
```

> **Note**: ACL entries are automatically deleted via foreign key constraints with cascade on delete, so explicit deletion is no longer needed.

### API Response Schema

The delete page API response follows a standardized format defined in `config/schemas/api/v1/responses/admin/delete_page.json`:

```json
{
    "$schema": "http://json-schema.org/draft-07/schema#",
    "type": "object",
    "required": ["page_keyword"],
    "properties": {
        "page_keyword": {
            "type": "string",
            "description": "The keyword of the deleted page"
        }
    },
    "additionalProperties": false
}
```

### Testing

Added comprehensive tests in `AdminPageControllerTest` to verify the page deletion functionality:

- **testCreateAndDeletePage()**: Tests the full lifecycle of creating and deleting a test page
- Helper methods for creating, deleting, and verifying page operations
- Schema validation to ensure API responses conform to the defined schemas

Implementation details:

- For each page, if the `protocol` field is missing or null, a default value is set
- If the page URL contains a protocol (e.g., "http://" or "https://"), that protocol is extracted and used
- Otherwise, "https" is used as the default protocol
- This ensures all pages have a valid protocol value that passes schema validation

```php
// Set default protocol if missing
if (!isset($page['protocol']) || $page['protocol'] === null) {
    // Extract protocol from URL if possible, otherwise default to https
    if (!empty($page['url']) && strpos($page['url'], '://') !== false) {
        $parts = parse_url($page['url']);
        $page['protocol'] = $parts['scheme'] ?? 'https';
    } else {
        $page['protocol'] = 'https';
    }
}
```

## Page Fields and Translations (2025-06-06)

### Overview

The system now supports retrieving page fields with their translations. This functionality is implemented in the `AdminPageService::getPageWithFields()` method, which returns a page along with its fields and their translations in multiple languages.

### Key Components

- **PagesFieldRepository**: A new repository that provides methods to fetch page fields with their translations
- **AdminPageService::getPageWithFields()**: Returns page data along with fields and translations
- **AdminPageController::getPageFields()**: API endpoint that formats and returns the data

### Data Structure

The API returns data in the following format:

```json
{
  "status": 200,
  "message": "OK",
  "error": null,
  "logged_in": true,
  "data": {
    "page_id": 123,
    "page_keyword": "about-us",
    "fields": [
      {
        "id": 1,
        "name": "title",
        "type": "text",
        "default_value": "About Us",
        "help": "Main page title",
        "translations": [
          {
            "language_id": 1,
            "language_code": "en",
            "content": "About Us"
          },
          {
            "language_id": 2,
            "language_code": "de",
            "content": "Über uns"
          }
        ]
      }
    ]
  }
}
```

### Implementation Details

- Fields are fetched using a join query that combines data from `PagesField`, `Field`, and `PagesFieldsTranslation` entities
- Translations are grouped by field for easy access
- The response includes both the field metadata and all available translations

### JSON Schema Validation (2025-06-07)

- The `getPageWithFields` API response is validated against a JSON schema defined in `config/schemas/api/v1/responses/admin/get_page_fields.json`
- The schema extends the common response envelope and defines the structure for page data and fields array
- Fields are categorized as either content fields (`display=1`) or property fields (`display=0`)
- Content fields support multiple language translations, while property fields have a single property value with `language_id=1`
- The schema validation is performed by the `JsonSchemaValidationService` in the test environment

```php
// Example test for getPageFields API
public function testGetPageFields(): void
{
    $token = $this->getAdminAccessToken();
    $this->client->request(
        'GET',
        '/cms-api/v1/admin/pages/home/fields',
        [],
        [],
        ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
    );
    $response = $this->client->getResponse();
    
    // Validate response against JSON schema
    $validationErrors = $this->jsonSchemaValidationService->validate(
        json_decode($response->getContent()), 
        'responses/admin/get_page_fields'
    );
    $this->assertEmpty($validationErrors);
}

### JSON Schema Structure and Reuse (2025-06-08)

To improve maintainability and reduce duplication, the JSON schemas have been refactored to use a modular approach with reusable entity definitions:

#### Directory Structure
```
config/schemas/api/v1/
├── common/                  # Common schema definitions
│   └── _response_envelope.json  # Base API response envelope
├── entities/                # Entity schema definitions
│   ├── pageEntity.json     # Page entity schema
│   ├── fieldEntity.json    # Field entity schema
│   └── lookupEntity.json   # Lookup entity schema
└── responses/              # API response schemas
    └── admin/              # Admin API responses
        ├── create_page.json    # Create page response
        └── get_page_fields.json # Get page fields response
```

#### Entity Schemas

Entity schemas define the structure of domain objects that are reused across multiple API endpoints:

- **pageEntity.json**: Defines the structure of a Page entity with all its properties and relationships
- **fieldEntity.json**: Defines a Field entity with its translations

#### Schema References

API response schemas use `$ref` to reference entity schemas:

```json
{
    "properties": {
        "data": {
            "$ref": "../../entities/pageEntity.json"
        }
    }
}
```

For nested entities like lookups, the schema uses `allOf` to combine the base lookup schema with additional constraints:

```json
"action": {
    "allOf": [
        {
            "$ref": "./lookupEntity.json"
        },
        {
            "properties": {
                "typeCode": {
                    "const": "pageActions"
                }
            }
        }
    ]
}
```

#### Benefits

- **Reduced Duplication**: Common entity structures are defined once and reused
- **Consistency**: Ensures consistent validation across different endpoints
- **Maintainability**: Changes to entity structure only need to be made in one place
- **Documentation**: Provides clear structure for API consumers

## ACL Integration (2025-05-15)

### ACL Repository and Caching (2025-05-21)

The ACL system now uses a repository pattern with in-memory caching to optimize performance. This ensures that ACL checks are only executed once per request, even if needed multiple times.

**Key Components:**

1. **AclRepository**: Repository for efficient ACL queries
   - Located at `src/Repository/AclRepository.php`
   - Uses Doctrine QueryBuilder for optimized queries
   - Implements in-memory caching to prevent duplicate database calls

2. **ACLService**: Service for ACL operations
   - Uses AclRepository for data access
   - Provides methods for checking access and retrieving all user ACLs

**Implementation:**

```php
// AclRepository - getUserAcl method with in-memory caching
public function getUserAcl(int $userId, ?int $pageId = -1): array
{
    // Check in-memory cache first
    $cacheKey = $userId . '_' . $pageId;
    if (isset($this->userAclCache[$cacheKey])) {
        return $this->userAclCache[$cacheKey];
    }
    
    // Query logic here...
    
    // Cache results before returning
    $this->userAclCache[$cacheKey] = $result;
    return $result;
}
```

**Best Practices:**

- Use `ACLService::getAllUserAcls()` when you need ACLs for multiple pages
- Use `ACLService::hasAccess()` for checking access to a specific page
- The repository handles caching automatically - no need for manual cache management
- For bulk operations, fetch all ACLs once and filter in memory

**Performance Benefits:**

- Eliminates redundant database queries
- Reduces database load
- Improves response times for complex pages with multiple ACL checks
- Maintains consistency of ACL checks within a single request


## Doctrine Entity Attribute Mapping (2025-05-15)

**This section lists all Doctrine entity attributes in `src/Entity` for onboarding and reference.**

### AclGroup
- `id_groups`: int (PK, FK to Group)
- `id_pages`: int (PK, FK to Page)
- `acl_select`: bool
- `acl_insert`: bool
- `acl_update`: bool
- `acl_delete`: bool

### AclUser
- `id_users`: int (PK, FK to User)
- `id_pages`: int (PK, FK to Page)
- `acl_select`: bool
- `acl_insert`: bool
- `acl_update`: bool
- `acl_delete`: bool

### Action
- `id`: int (PK)
- `name`: string (unique)

### ActivityType
- `id`: int (PK)
- `name`: string

### ApiRoute
- `id`: int (PK)
- `route_name`: string (unique)
- `path`: string
- `controller`: string
- `methods`: string
- `requirements`: json/array (nullable)
- `params`: json/array (nullable)
- `version`: string (default 'v1')

### Asset
- `id`: int (PK)
- `id_assetTypes`: int (FK to Lookup)
- `folder`: string (nullable)
- `file_name`: string (nullable)
- `file_path`: string

### CallbackLog
- `id`: int (PK)
- `callback_date`: datetime
- `remote_addr`: string (nullable)
- `redirect_url`: string (nullable)
- `callback_params`: text (nullable)
- `status`: string (nullable)
- `callback_output`: text (nullable)

### Chat
- `id`: int (PK)
- `id_snd`: int (FK to User)
- `id_rcv`: int (nullable, FK to User)
- `content`: text
- `timestamp`: datetime
- `id_rcv_group`: int (FK to Group)

### ChatRecipiant
- `id_users`: int (PK, FK to User)
- `id_chat`: int (PK, FK to Chat)
- `id_room_users`: int (nullable, FK to ChatRoomUser)
- `is_new`: bool

### ChatRoom
- `id`: int (PK)
- `name`: string
- `description`: string (nullable)
- `created_at`: datetime

### ChatRoomUser
- `id`: int (PK)
- `id_users`: int (FK to User)
- `id_chatRoom`: int (FK to ChatRoom)
- `is_admin`: bool
- `joined_at`: datetime

### CmsPreference
- `id`: int (PK)
- `callback_api_key`: string (nullable)
- `default_language_id`: int (nullable, FK to Language)
- `anonymous_users`: int
- `firebase_config`: string (nullable)

### CodesGroup
- `code`: string (PK, FK to ValidationCode)
- `id_groups`: int (PK, FK to Group)

### DataCell
- `id_dataRows`: int (PK, FK to DataRow)
- `id_dataCols`: int (PK, FK to DataCol)
- `value`: text

### DataCol
- `id`: int (PK)
- `name`: string (nullable)
- `id_dataTables`: int (nullable, FK to DataTable)

### DataRow
- `id`: int (PK)
- `id_dataTables`: int (nullable, FK to DataTable)
- `timestamp`: datetime
- `id_users`: int (nullable, FK to User)
- `id_actionTriggerTypes`: int (nullable, FK to Lookup)

### DataTable
- `id`: int (PK)
- `name`: string
- `timestamp`: datetime
- `displayName`: string (nullable)

### Field
- `id`: int (PK)
- `name`: string
- `id_type`: int (FK to FieldType)
- `display`: bool

### FieldType
- `id`: int (PK)
- `name`: string
- `position`: int

### FormAction
- `id`: int (PK)
- `name`: string
- `actionTriggerType`: object (ManyToOne to Lookup)
- `dataTable`: object (ManyToOne to DataTable)
- `config`: text (nullable)

### Gender
- `id`: int (PK)
- `name`: string

### Group
- `id`: int (PK)
- `name`: string
- `description`: string
- `id_group_types`: int (nullable, FK to Lookup)
- `requires_2fa`: bool

### Hook
- `id`: int (PK)
- `id_hookTypes`: int (FK to Lookup)
- `name`: string (nullable)
- `description`: string (nullable)
- `class`: string
- `function`: string
- `exec_class`: string
- `exec_function`: string
- `priority`: int (nullable)

### Language
- `id`: int (PK)
- `locale`: string
- `language`: string
- `csv_separator`: string

### Library
- `id`: int (PK)
- `name`: string (nullable)
- `version`: string (nullable)
- `license`: string (nullable)
- `comments`: string (nullable)

### LogPerformance
- `id_user_activity`: int (PK, FK to UserActivity)
- `log`: text (nullable)

### Lookup
- `id`: int (PK)
- `type_code`: string
- `lookup_code`: string (nullable)
- `lookup_value`: string (nullable)
- `lookup_description`: string (nullable)

### MailAttachment
- `id`: int (PK)
- `id_mailQueue`: int (FK to MailQueue)
- `attachment_name`: string (nullable)
- `attachment_path`: string
- `attachment_url`: string
- `template_path`: string

### MailQueue
- `id`: int (PK)
- `from_email`: string
- `from_name`: string
- `reply_to`: string
- `recipient_emails`: text
- `cc_emails`: string (nullable)
- `bcc_emails`: string (nullable)
- `subject`: string
- `body`: text
- `is_html`: int (nullable)

### Notification
- `id`: int (PK)
- `subject`: string
- `body`: text
- `url`: string (nullable)

### Page
- `id`: int (PK)
- `keyword`: string (unique)
- `url`: string (nullable)
- `protocol`: string (nullable)
- `id_actions`: int (nullable, FK to Action)
- `id_navigation_section`: int (nullable, FK to Section)
- `parent`: int (nullable, FK to Page)
- `is_headless`: bool
- `nav_position`: int (nullable)
- `footer_position`: int (nullable)
- `id_type`: int (FK to PageType)
- `id_pageAccessTypes`: int (nullable, FK to Lookup)
- `is_open_access`: bool (nullable)
- `is_system`: bool (nullable)

### PageType
- `id`: int (PK)
- `name`: string

### PageTypeField
- `id_pageType`: int (PK, FK to PageType)
- `id_fields`: int (PK, FK to Field)
- `default_value`: string (nullable)
- `help`: text (nullable)

### PagesField
- `id_pages`: int (PK, FK to Page)
- `id_fields`: int (PK, FK to Field)
- `default_value`: string (nullable)
- `help`: text (nullable)

### PagesFieldsTranslation
- `id_pages`: int (PK, FK to Page)
- `id_fields`: int (PK, FK to Field)
- `id_languages`: int (PK, FK to Language)
- `content`: text

### PagesSection
- `id_pages`: int (PK, FK to Page)
- `id_sections`: int (PK, FK to Section)
- `position`: int (nullable)

### Plugin
- `id`: int (PK)
- `name`: string (nullable)
- `version`: string (nullable)

### QualtricsAction
- `id`: int (PK)
- `id_qualtricsProjects`: int (FK to QualtricsProject)
- `id_qualtricsSurveys`: int (FK to QualtricsSurvey)
- `name`: string
- `id_qualtricsProjectActionTriggerTypes`: int (FK to Lookup)
- `id_qualtricsActionScheduleTypes`: int (FK to Lookup)
- `id_qualtricsSurveys_reminder`: int (nullable, FK to QualtricsSurvey)
- `schedule_info`: text (nullable)
- `id_qualtricsActions`: int (nullable, FK to QualtricsAction)

### QualtricsActionsFunction
- `id_qualtricsActions`: int (PK, FK to QualtricsAction)
- `id_lookups`: int (PK, FK to Lookup)

### QualtricsActionsGroup
- `id_qualtricsActions`: int (PK, FK to QualtricsAction)
- `id_groups`: int (PK, FK to Group)

### QualtricsProject
- `id`: int (PK)
- `name`: string
- `description`: string (nullable)
- `qualtrics_api`: string (nullable)
- `api_library_id`: string (nullable)
- `api_mailing_group_id`: string (nullable)
- `created_on`: datetime
- `edited_on`: datetime

### QualtricsReminder
- `id_qualtricsSurveys`: int (PK, FK to QualtricsSurvey)
- `id_users`: int (PK, FK to User)
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)

### QualtricsSurvey
- `id`: int (PK)
- `name`: string
- `description`: string (nullable)
- `qualtrics_survey_id`: string (nullable)
- `id_qualtricsSurveyTypes`: int (FK to Lookup)
- `participant_variable`: string (nullable)
- `group_variable`: int (nullable)
- `created_on`: datetime
- `edited_on`: datetime
- `config`: text (nullable)

### QualtricsSurveysResponse
- `id`: int (PK)
- `id_users`: int (FK to User)
- `id_surveys`: int (FK to QualtricsSurvey)
- `id_qualtricsProjectActionTriggerTypes`: int (FK to Lookup)
- `survey_response_id`: string (nullable)
- `started_on`: datetime
- `edited_on`: datetime
### RefreshToken
- `id`: bigint (PK)
- `id_users`: bigint
- `token_hash`: string
- `expires_at`: datetime
- `created_at`: datetime (nullable)

### ScheduledJob
- `id`: int (PK)
- `id_jobTypes`: int (FK to Lookup)
- `id_jobStatus`: int (FK to Lookup)
- `description`: string (nullable)
- `date_create`: datetime
- `date_to_be_executed`: datetime (nullable)
- `date_executed`: datetime (nullable)
- `config`: string (nullable)

### ScheduledJobsFormAction
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_formActions`: int (PK, FK to FormAction)
- `id_dataRows`: int (nullable, FK to DataRow)

### ScheduledJobsMailQueue
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_mailQueue`: int (PK, FK to MailQueue)

### ScheduledJobsNotification
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_notifications`: int (PK, FK to Notification)

### ScheduledJobsQualtricsAction
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_qualtricsActions`: int (PK, FK to QualtricsAction)

### ScheduledJobsReminder
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_dataTables`: int (PK, FK to DataTable)
- `session_start_date`: datetime (nullable)
- `session_end_date`: datetime (nullable)

### ScheduledJobsTask
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_tasks`: int (PK, FK to Task)

### ScheduledJobsUser
- `id_users`: int (PK, FK to User)
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)

### Section
- `id`: int (PK)
- `id_styles`: int (FK to Style)
- `name`: string

### SectionsFieldsTranslation
- `id_sections`: int (PK, FK to Section)
- `id_fields`: int (PK, FK to Field)
- `id_languages`: int (PK, FK to Language)
- `content`: text
- `meta`: string (nullable)

### SectionsHierarchy
- `parent`: int (PK, FK to Section)
- `child`: int (PK, FK to Section)
- `position`: int (nullable)

### SectionsNavigation
- `parent`: int (PK, FK to Section)
- `child`: int (PK, FK to Section)
- `id_pages`: int (FK to Page)
- `position`: int

### Style
- `id`: int (PK)
- `name`: string
- `id_type`: int (FK to Lookup with type_code = 'styleType')
- `id_group`: int (FK to StyleGroup)
- `description`: text (nullable)

### StyleGroup
- `id`: int (PK)
- `name`: string
- `description`: text (nullable)
- `position`: int (nullable)

### StylesField
- `id_styles`: int (PK, FK to Style)
- `id_fields`: int (PK, FK to Field)
- `default_value`: string (nullable)
- `help`: text (nullable)
- `disabled`: bool
- `hidden`: int (nullable)

### Task
- `id`: int (PK)
- `config`: text (nullable)

### Transaction
- `id`: int (PK)
- `transaction_time`: datetime
- `id_transactionTypes`: int (nullable, FK to Lookup)
- `id_transactionBy`: int (nullable, FK to Lookup)
- `id_users`: int (nullable, FK to User)
- `table_name`: string (nullable)
- `id_table_name`: int (nullable)
- `transaction_log`: text (nullable)

### User
- `id`: int (PK)
- `email`: string
- `name`: string (nullable)
- `password`: string (nullable)
- `id_genders`: int (nullable, FK to Gender)
- `blocked`: bool
- `id_status`: int (nullable, FK to UserStatus)
- `intern`: bool
- `token`: string (nullable)
- `id_languages`: int (nullable, FK to Language)
- `is_reminded`: bool
- `last_login`: date (nullable)
- `last_url`: string (nullable)
- `device_id`: string (nullable)
- `device_token`: string (nullable)
- `security_questions`: string (nullable)
- `user_name`: string (nullable)
- `id_userTypes`: int (FK to Lookup)

### UserActivity
- `id`: int (PK)
- `id_users`: int (FK to User)
- `url`: string
- `timestamp`: datetime
- `id_type`: int (FK to ActivityType)
- `exec_time`: decimal (nullable)
- `keyword`: string (nullable)
- `params`: string (nullable)
- `mobile`: bool (nullable)

### Users2faCode
- `id`: int (PK)
- `id_users`: int (FK to User)
- `code`: string
- `created_at`: datetime
- `expires_at`: datetime
- `is_used`: bool

### UsersGroup
- `id_users`: int (PK, FK to User)
- `id_groups`: int (PK, FK to Group)

### UserStatus
- `id`: int (PK)
- `name`: string
- `description`: string

### ValidationCode
- `code`: string (PK)
- `id_users`: int (nullable, FK to User)
- `created`: datetime
- `consumed`: datetime (nullable)

### Version
- `id`: int (PK)
- `version`: string (nullable)

### PageType
- `id`: int (PK)
- `name`: string (unique)

### Chat
- `id`: int (PK)
- `id_snd`: int
- `id_rcv`: int (nullable)
- `content`: text
- `timestamp`: datetime
- `id_rcv_group`: int

### ChatRecipiant
- `id_users`: int (PK)
- `id_chat`: int (PK)
- `id_room_users`: int (nullable)
- `is_new`: bool (default 1)

### CmsPreference
- `id`: int (PK)
- `callback_api_key`: string (nullable, length 500)
- `default_language_id`: int (nullable)
- `anonymous_users`: int (default 0)
- `firebase_config`: string (nullable, length 10000)

### CodesGroup
- `code`: string (PK, length 16)
- `id_groups`: int (PK)

### DataCell
- `id_dataRows`: int (PK)
- `id_dataCols`: int (PK)
- `value`: text

### DataCol
- `id`: int (PK)
- `name`: string (nullable, length 255)
- `id_dataTables`: int (nullable)

### DataRow
- `id`: int (PK)
- `id_dataTables`: int (nullable)
- `timestamp`: datetime
- `id_users`: int (nullable)
- `id_actionTriggerTypes`: int (nullable)

### DataTable
- `id`: int (PK)
- `name`: string (length 100)
- `timestamp`: datetime
- `displayName`: string (nullable, length 1000)

### Field
- `id`: int (PK)
- `name`: string (length 100)
- `id_type`: int
- `display`: bool (default 1)

### FieldType
- `id`: int (PK)
- `name`: string (length 100)
- `position`: int

### FormAction
- `id`: int (PK)
- `name`: string (length 200)
- `id_formProjectActionTriggerTypes`: int
- `config`: text (nullable)
- `id_dataTables`: int (nullable)

### Gender
- `id`: int (PK)
- `name`: string (length 20)

### Group
- `id`: int (PK)
- `name`: string (length 100)
- `description`: string (length 250)
- `id_group_types`: int (nullable)
- `requires_2fa`: bool (default 0)

### Hook
- `id`: int (PK)
- `id_hookTypes`: int
- `name`: string (nullable, length 100)
- `description`: string (nullable, length 1000)
- `class`: string (length 100)
- `function`: string (length 100)
- `exec_class`: string (length 100)
- `exec_function`: string (length 100)
- `priority`: int (default 10)

### Language
- `id`: int (PK)
- `locale`: string (length 5)
- `language`: string (length 100)
- `csv_separator`: string (length 1, default ',')

### Library
- `id`: int (PK)
- `name`: string (nullable, length 250)
- `version`: string (nullable, length 500)
- `license`: string (nullable, length 1000)
- `comments`: string (nullable, length 1000)

### MailAttachment
- `id`: int (PK)
- `id_mailQueue`: int
- `attachment_name`: string (nullable, length 1000)
- `attachment_path`: string (length 1000)
- `attachment_url`: string (length 1000)
- `template_path`: string (length 1000, default '')

### MailQueue
- `id`: int (PK)
- `from_email`: string (length 100)
- `from_name`: string (length 100)
- `reply_to`: string (length 100)
- `recipient_emails`: text
- `cc_emails`: string (nullable, length 1000)
- `bcc_emails`: string (nullable, length 1000)
- `subject`: string (length 1000)
- `body`: text
- `is_html`: bool (default 1)

### Notification
- `id`: int (PK)
- `subject`: string (length 1000)
- `body`: text
- `url`: string (nullable, length 100)

### Plugin
- `id`: int (PK)
- `name`: string (nullable, length 100)
- `version`: string (nullable, length 500)

### QualtricsProject
- `id`: int (PK)
- `name`: string (length 200)
- `description`: string (nullable, length 1000)
- `qualtrics_api`: string (nullable, length 100)
- `api_library_id`: string (nullable, length 100)
- `api_mailing_group_id`: string (nullable, length 100)
- `created_on`: datetime
- `edited_on`: datetime

### QualtricsSurvey
- `id`: int (PK)
- `name`: string (length 200)
- `description`: string (nullable, length 1000)
- `qualtrics_survey_id`: string (nullable, length 100)
- `id_qualtricsSurveyTypes`: int
- `participant_variable`: string (nullable, length 100)
- `group_variable`: int (default 0)
- `created_on`: datetime
- `edited_on`: datetime
- `config`: text (nullable)

### QualtricsSurveysResponse
- `id`: int (PK)
- `id_users`: int
- `id_surveys`: int
- `id_qualtricsProjectActionTriggerTypes`: int
- `survey_response_id`: string (nullable, length 100)
- `started_on`: datetime
- `edited_on`: datetime

### ScheduledJob
- `id`: int (PK)
- `id_jobTypes`: int
- `id_jobStatus`: int
- `description`: string (nullable, length 1000)
- `date_create`: datetime
- `date_to_be_executed`: datetime (nullable)
- `date_executed`: datetime (nullable)
- `config`: string (nullable, length 1000)

### ScheduledJobsFormAction
- `id_scheduledJobs`: int (PK)
- `id_formActions`: int (PK)
- `id_dataRows`: int (nullable)

### ScheduledJobsMailQueue
- `id_scheduledJobs`: int (PK)
- `id_mailQueue`: int (PK)

### ScheduledJobsNotification
- `id_scheduledJobs`: int (PK)
- `id_notifications`: int (PK)

### ScheduledJobsQualtricsAction
- `id_scheduledJobs`: int (PK)
- `id_qualtricsActions`: int (PK)

### RefreshToken
- `id`: bigint (PK)
- `user`: FK to User
- `token_hash`: string
- `expires_at`: datetime
- `created_at`: datetime

### Section
- `id`: int (PK)
- `style`: FK to Style
- `name`: string (unique)

### Style
- `id`: int (PK)
- `name`: string (unique)
- `type`: FK to StyleType
- `group`: FK to StyleGroup
- `description`: text (nullable)

### StyleGroup
- `id`: int (PK)
- `name`: string (unique)
- `description`: text (nullable)
- `position`: int (nullable)

### StyleType
- `id`: int (PK)
- `name`: string

### User
- `id`: int (PK)
- `email`: string (unique)
- `name`: string (nullable)
- `password`: string (nullable)
- `id_genders`: int (nullable)
- `id_languages`: int (nullable)
- `id_status`: int (nullable)
- `blocked`: bool
- `intern`: bool
- `last_login`: datetime (nullable)
- `last_url`: string (nullable)
- `user_name`: string (nullable)
- `is_reminded`: bool
- `token`: string (nullable)
- `twoFactorRequired`: bool
- `id_userTypes`: int (nullable)


### Canonical ACL Source: get_user_acl Stored Procedure

### Global User Context Service
- Use `App\Service\UserContextService` to get the current authenticated user entity anywhere in Symfony.
- Call `$this->userContext->getCurrentUser()` to get the `App\Entity\User|null`.
- This avoids duplicating user-casting logic and is the recommended pattern for all services and controllers.
- Example:
  ```php
  public function __construct(UserContextService $userContext) { ... }
  $user = $this->userContext->getCurrentUser();
  ```

- All access checks in Symfony now use the stored procedure `get_user_acl(user_id, page_id)`.
- The procedure returns columns: acl_select, acl_insert, acl_update, acl_delete, etc.
- The access type (`select`, `insert`, `update`, `delete`) is mapped to the corresponding column.
- The check is: if the column value is `1`, access is granted; otherwise, denied.
- This matches the core logic from the legacy PHP implementation and is now the canonical approach for ACL in this project.
- Example:
  ```php
  // In ACLService
  $sql = 'CALL get_user_acl(:userId, :pageId)';
  $stmt = $connection->prepare($sql);
  $result = $stmt->executeQuery(['userId' => $userId, 'pageId' => $pageId])->fetchAssociative();
  $hasAccess = ((int)$result['acl_select'] === 1); // for 'select' access
  ```
- This is documented in project memory for all contributors.

## Dynamic API Route Management

All API routes are dynamically loaded from the `api_routes` database table. Developers manage routes by inserting/updating records in this table. There is no need to use YAML, PHP, fixtures, or import commands for route registration. The only supported method is direct SQL/database insertion.

### API Versioning

The `api_routes` table now includes a `version` column (e.g., `v1`, `v2`). This allows you to maintain and serve multiple API versions in parallel. Each route record must specify the version it belongs to.

**Example table structure:**
```sql
CREATE TABLE `api_routes` (
  `id`           INT             NOT NULL AUTO_INCREMENT,
  `route_name`   VARCHAR(100)    NOT NULL,
  `version`      VARCHAR(10)     NOT NULL DEFAULT 'v1',
  `path`         VARCHAR(255)    NOT NULL,
  `controller`   VARCHAR(255)    NOT NULL,
  `methods`      VARCHAR(50)     NOT NULL,
  `requirements` JSON            NULL,
  `params`       JSON            NULL COMMENT 'Expected parameters: name → {in: body|query, required: bool}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_route_name_version` (`route_name`, `version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Example insert:**
```sql
INSERT INTO `api_routes` (`route_name`,`version`,`path`,...) VALUES
('auth_login','v1','/auth/login',...),
('auth_login','v2','/auth/login',...);
```

### How to Add a New Version (v2+)
1. **Database**: Insert new routes into `api_routes` with `version = 'v2'` and update `controller`/`path` as needed.
2. **Controllers**: Place v2 controllers in `src/Controller/Api/V2/` (and `Admin/` subfolder for admin routes).
3. **Routing Config**: In `config/routes/selfhelp_api.yaml`, duplicate the v1 blocks, rename to v2, and update prefixes/resources:
    ```yaml
    # ── V2 API ROUTES ──
    selfhelp_api_v2:
        resource: 'api_v2.yaml'
        prefix:   '/cms-api/v2'
    selfhelp_api_v2_controllers:
        resource: ../../src/Controller/Api/V2/
        type: attribute
        prefix: '/cms-api/v2'
    selfhelp_admin_v2_controllers:
        resource: ../../src/Controller/Api/V2/Admin/
        type: attribute
        prefix: '/cms-api/v2/admin'
    ```
4. **Loader**: The loader/repository (`ApiRouteRepository`) should filter by version (e.g., `findAllRoutesByVersion('v2')`).

### Entities

### Page Entity Update (Step 879)
- Aligned the `Page` entity with the latest `pages` table schema in `sh_structure.sql`.
- Removed legacy fields `id_type` and `id_pageAccessTypes` (and their getters/setters) which were not needed as properties, since relationships are handled via Doctrine ORM attributes.
- Ensured all ORM attributes match the SQL table, including column types, nullability, and relationships:
  - ManyToOne relationships for `action` (Lookup), `navigationSection` (Section), `parentPage` (self-referencing Page), `pageType` (PageType), and `pageAccessType` (Lookup).
  - All columns (`keyword`, `url`, `protocol`, `is_headless`, `nav_position`, `footer_position`, `is_open_access`, `is_system`) are present and use correct types.
- All required getters and setters are present and up-to-date, following ENTITY RULE and Symfony best practice.
- Ready for further use in the application and for Doctrine migrations (manual run only).

### AclGroup Entity Update (Step 892)
- Refactored `AclGroup` entity to use `ManyToOne` associations for foreign keys:
  - `group` property references `Group` entity via `id_groups` (CASCADE delete).
  - `page` property references `Page` entity via `id_pages` (CASCADE delete).
- Composite primary key is now object-based (`group` and `page`).
- Removed old int properties for keys, updated all getters/setters to use object associations.
- Fully aligned with DB schema and ENTITY RULE for maintainability and best practice.

### DataTable, DataRow, DataCol, DataCell Refactor (2024-xx-xx)

#### Summary
The entity relationships for tabular data have been refactored to follow Doctrine ORM and Symfony best practices, as per ENTITY RULE and MEMORY_RULE. This enables robust, maintainable, and navigable associations for all tabular data in the system.

#### Relationships
- **DataTable**
  - `OneToMany` to `DataRow` (dataRows)
  - `OneToMany` to `DataCol` (dataCols)
- **DataRow**
  - `ManyToOne` to `DataTable` (dataTable)
  - `OneToMany` to `DataCell` (dataCells)
- **DataCol**
  - `ManyToOne` to `DataTable` (dataTable)
  - `OneToMany` to `DataCell` (dataCells)
- **DataCell**
  - Composite PK: (`dataRow`, `dataCol`)
  - `ManyToOne` to `DataRow` (dataRow)
  - `ManyToOne` to `DataCol` (dataCol)

#### Implementation Notes
- All integer FK fields were replaced by proper Doctrine ORM relations using PHP 8 attributes.
- Navigation methods (getter/setter/add/remove) were implemented for all entity relationships.
- DataCell now uses a composite primary key of (`dataRow`, `dataCol`) and only one property per relation.
- All changes follow ENTITY RULE and have been tested for bidirectional navigation.

#### Manual Migration Required
After these changes, you must manually run doctrine:migrations:diff and doctrine:migrations:migrate to update the database schema.

### Entity and Loader
- The `ApiRoute` entity has a `version` property.
- The loader queries only the routes for the requested version.

### Best Practices
- Always specify the correct version when adding or updating routes.
- Document route changes and new versions for your team.

## How to Add a Route

All API routes are now dynamically loaded from the `api_routes` table. To add or update a route, insert or update a record in the table. The table now supports POST routes and parameter definitions via the `params` JSON column.

### 2. Manually Inserting into the Database

You can insert routes directly using SQL. Example:

```sql
INSERT INTO api_routes (name, path, controller, methods, requirements, version)
VALUES (
    'admin_page_sections',
    '/admin/pages/{page_keyword}/sections',
    'App\\Controller\\AdminController::getPageSections',
    'GET',
    '{"page_keyword": "[A-Za-z0-9_-]+"}'
);
```

- Double backslashes (`\\`) are required in the `controller` field for PHP namespace escaping.
- The `requirements` field is a JSON object (or NULL if not needed).

## Example Route Entry

| route_name          | path                                 | controller                                         | methods | requirements                              | params                                                                                  |
|---------------------|--------------------------------------|----------------------------------------------------|---------|--------------------------------------------|-----------------------------------------------------------------------------------------|
| auth_login          | /auth/login                          | App\\Controller\\AuthController::POST_login         | POST    | NULL                                       | {"user": {"in": "body", "required": true}, "password": {"in": "body", "required": true}} |
| content_page        | /pages/{page_keyword}                | App\\Controller\\ContentController::getPage        | GET     | {"page_keyword": "[A-Za-z0-9_-]+"}       | NULL                                                                                    |

## Parameter Requirements and POST Parameters

- The `requirements` field is a JSON object where keys are parameter names and values are regex patterns for path variables.
- The `params` field is a JSON object describing expected parameters for POST/PUT requests. For each parameter:
    - `in`: Where to expect the parameter (`body` or `query`).
    - `required`: Whether the parameter is required (`true`/`false`).
- Example for a POST route:
    ```json
    {
      "user": {"in": "body", "required": true},
      "password": {"in": "body", "required": true}
    }
    ```
- If not needed, set to NULL or leave blank.

## How Dynamic Routes Work

1. On cache warmup or server start, the `ApiRouteLoader` loads all entries from `api_routes` and registers them as Symfony routes.
2. Requests matching these paths are dispatched to the specified controller and method.
3. If you update the database, clear the Symfony cache to reload the routes:
   ```sh
   php bin/console cache:clear
   ```

## Best Practices
- Always use unique route names.
- Use double backslashes in the controller field for namespaces.
- Validate your regexes in the `requirements` field.
- After adding or updating routes, clear the cache.
- Use fixtures for version-controlled, repeatable route setups.
- Use the `debug:router` command to verify loaded routes:
  ```sh
  php bin/console debug:router | findstr cms-api
  ```

## Public API Endpoint: GET /cms-api/v1/pages (2025-05-21)

- Returns all pages accessible to the current user, filtered by ACL and access type.
- Business logic migrated from legacy NavigationApi::GET_all_routes().
- Uses stored procedure `get_user_acl(:uid, -1)` to get all pages and user ACL in one call.
- Removes pages of the opposite access type (web/mobile), only returns pages with:
  - `acl_select` == 1
  - `id_actions` == 3 (published)
  - `id_type` in [2, 3, 4] (core, experiment, open)
  - `url` not empty
- Implemented in Symfony:
  - Controller: `App\Controller\Api\V1\Frontend\PageController::getPages()`
  - Service: `App\Service\CMS\Frontend\PageService::getAllAccessiblePagesForUser()`
  - Repository: `App\Repository\PageRepository::getLookupIdByCode()`
- Returns JSON: `{ status: 'success', data: [ ...pages... ] }`
- Follows REST and Symfony best practices.

---

## Global API JSON Error Handling (2025-05-21)

All `/cms-api/` endpoints now return JSON error responses, even for 404 and uncaught exceptions. This is handled by a global event listener:

- File: `src/EventListener/ApiExceptionListener.php`
- Catches all exceptions for API routes (path starts with `/cms-api/`).
- Returns a standard JSON structure for errors, e.g.:
  ```json
  {
    "status": "error",
    "code": 404,
    "message": "No route found for GET /cms-api/v1/pages"
  }
  ```
- Ensures clients never receive HTML error pages from API endpoints.
- Follows REST best practices for error reporting.

**Implementation:**
```php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ApiExceptionListener
{
    #[AsEventListener]
    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        if (strpos($request->getPathInfo(), '/cms-api/') !== 0) {
            return;
        }
        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $message = $exception->getMessage() ?: 'An error occurred';
        $data = [
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message,
        ];
        $response = new JsonResponse($data, $statusCode);
        $event->setResponse($response);
    }
}
```

**To customize:**
- Edit `ApiExceptionListener.php` for advanced error formatting or logging.
- The listener is auto-registered via PHP 8 attributes.

## Troubleshooting
- If a route does not appear, ensure it is in the database and the cache is cleared.
- If you get duplicate routes, check for static YAML/PHP definitions and remove them.
- Use the `app:import-api-routes` command for bulk updates or imports.

---

For more advanced usage, see `src/Entity/ApiRoute.php`

## API Testing Setup

This section outlines the setup for writing and running automated tests for the API, primarily using PHPUnit and Symfony's `WebTestCase`.

### Core Stack
- **PHPUnit**: The primary testing framework.
- **Symfony `WebTestCase`**: Used for functional tests that make HTTP requests to your API endpoints.
- **Symfony `PantherTestCase`**: Can be used for end-to-end tests that require a real browser (optional, for more complex UI-driven API interactions if needed).

### Configuration Files
- **`phpunit.xml.dist`**: Located in the project root (`d:\TPF\SelfHelp\sh-selfhelp\server\symfony\phpunit.xml.dist`). Configures PHPUnit, sets the `APP_ENV` to `test`, and bootstraps the Symfony environment via `tests/bootstrap.php`.
- **`tests/bootstrap.php`**: Loads the test environment, including `.env.test`.
- **`.env.test`**: Located in the project root (`d:\TPF\SelfHelp\sh-selfhelp\server\symfony\.env.test`). Contains environment-specific variables for testing:
    - `APP_ENV=test`
    - `APP_SECRET`: **Crucial! Set this to a unique, strong random string.**
    - `DATABASE_URL`: Defines the database connection for tests. Defaults to SQLite (`sqlite:///%kernel.project_dir%/var/data.db.test`). You can change this to a dedicated test PostgreSQL or MySQL database.
    - `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY`, `JWT_PASSPHRASE`: Configure these to point to your test JWT keys and passphrase. You may need to generate separate keys for the test environment (e.g., in `config/jwt/test/`).
    - `MESSENGER_TRANSPORT_DSN='sync://'`: Disables asynchronous message processing for tests by default.
- **`config/packages/test/doctrine.yaml`**: Configures Doctrine specifically for the test environment, ensuring it uses the `DATABASE_URL` from `.env.test`.
- **`config/packages/test/framework.yaml`**: Contains framework-specific overrides for the test environment, such as using mock session storage and disabling the profiler for performance.

### Test Directory Structure
- Tests reside in the `d:\TPF\SelfHelp\sh-selfhelp\server\symfony\tests\` directory.
- API controller tests are typically organized mirroring the `src/Controller/Api/` structure, e.g., `tests/Controller/Api/V1/AuthControllerTest.php`.
- *Note*: If directories like `config/packages/test/` or `tests/Controller/Api/V1/` do not exist, they may need to be created manually if the development tools fail to create them automatically during file generation.

### Database Setup for Tests
1.  **Schema**: Your test database needs the correct schema.
    -   You can manage this via migrations: `php bin/console doctrine:migrations:migrate --env=test`
2.  **Fixtures (Test Data)**: For predictable test outcomes, you'll need test data.
    -   Install `doctrine/fixtures-bundle`: `composer require --dev doctrine/fixtures-bundle`
    -   Create fixture classes in `src/DataFixtures/` (e.g., `UserFixtures.php`).
    -   Load fixtures: `php bin/console doctrine:fixtures:load --env=test --no-interaction`
3.  **Test Isolation (Highly Recommended)**:
    -   Consider using **`DAMA\DoctrineTestBundle`** (`composer require --dev dama/doctrine-test-bundle`). This bundle wraps each test in a database transaction and rolls it back afterwards. This provides excellent isolation and speed, as you typically only need to load fixtures once per test suite (or less frequently).
    -   If using `DAMA\DoctrineTestBundle`, uncomment its extension in `phpunit.xml.dist`.
    -   Alternatively, you can manually manage database state in your test `setUp()` / `tearDown()` methods by dropping/creating the database or truncating tables, but this is slower and more complex.

### Running Tests
1.  Navigate to the Symfony project root in your terminal: `d:\TPF\SelfHelp\sh-selfhelp\server\symfony\`
2.  **Run all tests**:
    ```bash
    php bin/phpunit
    ```
3.  **Run tests in a specific file**:
    ```bash
    php bin/phpunit tests/Controller/Api/V1/AuthControllerTest.php
    ```
4.  **Run a specific test method within a file**:
    ```bash
    php bin/phpunit --filter testLoginSuccessWithValidCredentials tests/Controller/Api/V1/AuthControllerTest.php
    ```
5.  **Run tests belonging to a specific group** (defined by `@group groupName` annotation in your test methods or classes):
    ```bash
    php bin/phpunit --group auth
    ```

### Example Test
An initial test class `tests/Controller/Api/V1/AuthControllerTest.php` has been created as a starting point. It demonstrates:
- Basic setup extending `WebTestCase`.
- Making POST requests with JSON payloads.
- Asserting response status codes and JSON content.
- Placeholders for testing various authentication scenarios.

Remember to adapt and expand upon these tests to cover all aspects of your API, including request validation (JSON schemas), error handling, business logic, and security.
