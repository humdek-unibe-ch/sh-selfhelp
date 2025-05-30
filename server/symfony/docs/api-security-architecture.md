# SH-SelfHelp API Security Architecture

This document outlines the security architecture for the SH-SelfHelp Symfony REST API, focusing on authentication, authorization, and permission management.

## 1. Authentication Flow

The application uses JWT (JSON Web Token) authentication implemented with LexikJWTBundle.

### 1.1 Authentication Process

1. **Login Request**: Client sends credentials to `/cms-api/v1/auth/login`
2. **Credential Validation**: `AuthController::login` validates credentials using `LoginService`
3. **JWT Generation**: On successful validation, a JWT token is generated
4. **Token Response**: Token is returned to the client
5. **Subsequent Requests**: Client includes the token in the `Authorization` header
6. **Token Validation**: `JWTTokenAuthenticator` validates the token for each request
7. **User Loading**: On successful validation, the user is loaded and set in the security context

### 1.2 Key Components

- **`JWTTokenAuthenticator`**: Custom authenticator that validates JWT tokens
- **`JWTService`**: Service for token validation, blacklisting, and generation
- **`UserContextService`**: Service to access the currently authenticated user

## 2. Authorization System

The application uses a multi-layered approach to authorization:

### 2.1 Layer 1: Symfony Security (Firewall & Access Control)

Configured in `config/packages/security.yaml`, this provides basic route-based access control:

```yaml
security:
    firewalls:
        api:
            pattern: ^/cms-api/v1
            stateless: true
            custom_authenticators:
                - App\Security\JWTTokenAuthenticator
    
    access_control:
        - { path: ^/cms-api/v1/auth, roles: PUBLIC_ACCESS }
        - { path: ^/cms-api/v1,       roles: PUBLIC_ACCESS }
        - { path: ^/cms-api/v1/admin, roles: IS_AUTHENTICATED_FULLY }
```

### 2.2 Layer 2: API Route Permissions (Database-Driven)

Routes are defined in the database (`api_routes` table) with associated permissions:

1. **Route Definition**: Routes are stored in the `api_routes` table
2. **Permission Association**: Permissions are associated with routes in the `api_routes_permissions` junction table
3. **Dynamic Loading**: `ApiRouteLoader` loads routes from the database and attaches permissions as route options
4. **Permission Check**: `ApiSecurityListener` checks if the user has the required permissions for the route

### 2.3 Layer 3: Voter-Based Authorization (Fine-Grained)

For more complex authorization logic:

1. **Voters**: Custom voters like `ApiRouteVoter` implement specific authorization logic
2. **Controller Usage**: Controllers can use `$this->isGranted()` for fine-grained checks

## 3. Permission System

### 3.1 Database Structure

- **`permissions`**: Stores available permissions
  - `id`: Primary key
  - `name`: Unique permission name (e.g., "view_users")
  - `description`: Human-readable description

- **`api_routes_permissions`**: Junction table linking routes to permissions
  - `id_api_routes`: Foreign key to `api_routes`
  - `id_permissions`: Foreign key to `permissions`

- **`user_groups_permissions`**: Junction table linking user groups to permissions
  - `id_user_groups`: Foreign key to `user_groups`
  - `id_permissions`: Foreign key to `permissions`

### 3.2 Entity Relationships

- **`Permission` Entity**: Represents a permission in the system
- **`ApiRoute` Entity**: Represents an API route with associated permissions
- **`User` Entity**: Has a method `getPermissionNames()` that returns all permissions the user has via their groups

## 4. Implementation Details

### 4.1 ApiSecurityListener

The `ApiSecurityListener` is a key component that checks if a user has the required permissions for an API route:

1. **Event Subscription**: Listens to the `kernel.controller` event, which occurs after authentication
2. **Permission Extraction**: Extracts required permissions from the route options
3. **User Permissions**: Gets the user's permissions via `UserContextService`
4. **Permission Check**: Verifies if the user has at least one of the required permissions
5. **Access Decision**: Allows or denies access based on the permission check

```php
// ApiSecurityListener.php (simplified)
public function onKernelController(ControllerEvent $event): void
{
    // Get route and required permissions
    $requiredPermissions = $route->getOption('permissions') ?? [];
    
    // If no permissions required, allow access
    if (empty($requiredPermissions)) {
        return;
    }
    
    // Get user and their permissions
    $user = $this->userContextService->getCurrentUser();
    $userPermissions = $user->getPermissionNames();
    
    // Check if user has at least one required permission
    $hasPermission = false;
    foreach ($requiredPermissions as $permission) {
        if (in_array($permission, $userPermissions)) {
            $hasPermission = true;
            break;
        }
    }
    
    // Deny access if no matching permission
    if (!$hasPermission) {
        throw new AccessDeniedException('You do not have permission to access this API endpoint.');
    }
}
```

### 4.2 ApiRouteVoter

The `ApiRouteVoter` provides a more flexible way to check permissions:

```php
// ApiRouteVoter.php (simplified)
protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
{
    // Get user, route, and required permissions
    $user = $token->getUser();
    $requiredPermissions = $route->getOption('permissions') ?? [];
    
    // Check permissions
    foreach ($requiredPermissions as $permission) {
        if (in_array($permission, $user->getPermissionNames())) {
            return true;
        }
    }
    
    return false;
}
```

## 5. How to Use and Extend the Permission System

### 5.1 Adding a New Permission

1. **Insert into Database**:
   ```sql
   INSERT INTO permissions (name, description) 
   VALUES ('manage_content', 'Allows managing content pages');
   ```

2. **Assign to User Groups**:
   ```sql
   INSERT INTO user_groups_permissions (id_user_groups, id_permissions)
   VALUES (1, (SELECT id FROM permissions WHERE name = 'manage_content'));
   ```

### 5.2 Creating a New API Route with Permissions

1. **Insert Route into Database**:
   ```sql
   INSERT INTO api_routes (route_name, path, controller, methods, version)
   VALUES ('content_management', '/content', 'App\\Controller\\Api\\V1\\Content\\ContentController::manage', 'POST,PUT,DELETE', 'v1');
   ```

2. **Assign Permissions to Route**:
   ```sql
   INSERT INTO api_routes_permissions (id_api_routes, id_permissions)
   VALUES (
       (SELECT id FROM api_routes WHERE route_name = 'content_management' AND version = 'v1'),
       (SELECT id FROM permissions WHERE name = 'manage_content')
   );
   ```

### 5.3 Using the Voter in Controllers

For more complex permission checks, use the voter pattern:

```php
// In a controller
public function someAction(Request $request)
{
    // Check if user can access the API route
    if (!$this->isGranted('api_route_access')) {
        throw $this->createAccessDeniedException('Permission denied');
    }
    
    // Continue with controller logic
}
```

### 5.4 Custom Permission Checks

For complex scenarios, create custom voters:

```php
// Example custom voter
class ContentVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'edit_content' && $subject instanceof Content;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // Check if user is the content owner or has admin permission
        return $subject->getOwnerId() === $user->getId() || 
               in_array('admin_content', $user->getPermissionNames());
    }
}
```

## 6. Best Practices

1. **Use Descriptive Permission Names**: Choose clear, descriptive names for permissions (e.g., `view_users`, `edit_content`).

2. **Group Related Permissions**: Organize permissions logically (e.g., all content-related permissions together).

3. **Least Privilege Principle**: Assign only the minimum permissions necessary for each user group.

4. **Document Permissions**: Maintain documentation of all permissions and their purposes.

5. **Audit Permission Changes**: Log changes to permissions and permission assignments.

6. **Test Authorization Logic**: Write tests to verify that permission checks work correctly.

7. **Use Voters for Complex Logic**: For complex authorization decisions, create custom voters rather than embedding logic in controllers.

8. **Centralize Permission Checks**: Avoid duplicating permission-checking logic across the application.

## 7. Troubleshooting

### 7.1 Common Issues

1. **User Not Authenticated**: If `UserContextService->getCurrentUser()` returns null when it shouldn't, check:
   - Is the JWT token included in the request?
   - Is the token valid and not expired?
   - Is the authenticator working correctly?

2. **Permission Denied Unexpectedly**: If a user is denied access when they should have permission:
   - Check if the user has the required permission in the database
   - Verify that the route has the correct permissions associated
   - Look for typos in permission names

3. **Route Not Found**: If a route is not being loaded:
   - Verify the route exists in the `api_routes` table
   - Check that the controller and action exist and are correctly specified

### 7.2 Debugging

1. **Enable Debug Logging**: Set the logger to debug level to see detailed permission checks:
   ```yaml
   # config/packages/dev/monolog.yaml
   monolog:
       handlers:
           security:
               level: debug
               type: stream
               path: "%kernel.logs_dir%/security.log"
               channels: [security]
   ```

2. **Check Security Logs**: Look for log entries from `ApiSecurityListener` and `ApiRouteVoter`.

3. **Use the Profiler**: In development, use the Symfony Profiler to inspect security details.
