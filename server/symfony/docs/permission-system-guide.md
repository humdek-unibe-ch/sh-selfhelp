# SH-SelfHelp Permission System Implementation Guide

This guide provides practical instructions for working with the permission system in the SH-SelfHelp Symfony application. It covers how to implement, use, and extend the permission-based security architecture.

## 1. Permission System Overview

The permission system is built on three key components:

1. **Database-Driven Permissions**: Permissions are stored in the database and associated with routes and user groups
2. **Dynamic Route Loading**: Routes and their permissions are loaded from the database at runtime
3. **Multi-Layer Authorization**: Security is enforced through Symfony's security system, a custom event listener, and voters

## 2. Database Schema

### 2.1 Core Tables

- **`permissions`**: Stores all available permissions
  ```sql
  CREATE TABLE permissions (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL UNIQUE,
      description VARCHAR(255) NOT NULL
  );
  ```

- **`api_routes`**: Stores API route definitions
  ```sql
  CREATE TABLE api_routes (
      id INT AUTO_INCREMENT PRIMARY KEY,
      route_name VARCHAR(100) NOT NULL,
      path VARCHAR(255) NOT NULL,
      controller VARCHAR(255) NOT NULL,
      methods VARCHAR(50) NOT NULL,
      requirements JSON NULL,
      params JSON NULL,
      version VARCHAR(10) NOT NULL DEFAULT 'v1',
      UNIQUE KEY uniq_version_path (version, path),
      UNIQUE KEY uniq_route_name_version (route_name, version)
  );
  ```

- **`api_routes_permissions`**: Junction table linking routes to permissions
  ```sql
  CREATE TABLE api_routes_permissions (
      id_api_routes INT NOT NULL,
      id_permissions INT NOT NULL,
      PRIMARY KEY (id_api_routes, id_permissions),
      FOREIGN KEY (id_api_routes) REFERENCES api_routes(id) ON DELETE CASCADE,
      FOREIGN KEY (id_permissions) REFERENCES permissions(id) ON DELETE CASCADE
  );
  ```

- **`user_groups_permissions`**: Junction table linking user groups to permissions
  ```sql
  CREATE TABLE user_groups_permissions (
      id_user_groups INT NOT NULL,
      id_permissions INT NOT NULL,
      PRIMARY KEY (id_user_groups, id_permissions),
      FOREIGN KEY (id_user_groups) REFERENCES user_groups(id) ON DELETE CASCADE,
      FOREIGN KEY (id_permissions) REFERENCES permissions(id) ON DELETE CASCADE
  );
  ```

## 3. Implementation Components

### 3.1 Entity Classes

#### Permission Entity

```php
// src/Entity/Permission.php
namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permissions')]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: ApiRoute::class, mappedBy: 'permissions')]
    private Collection $apiRoutes;

    #[ORM\ManyToMany(targetEntity: UserGroup::class, mappedBy: 'permissions')]
    private Collection $userGroups;

    public function __construct()
    {
        $this->apiRoutes = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, ApiRoute>
     */
    public function getApiRoutes(): Collection
    {
        return $this->apiRoutes;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }
}
```

#### User Entity (Relevant Methods)

```php
// src/Entity/User.php (partial)
namespace App\Entity;

// ... other imports and properties

/**
 * Get all permission names for this user based on their groups
 * 
 * @return string[] Array of permission names
 */
public function getPermissionNames(): array
{
    $permissions = [];
    
    // Get permissions from all user groups
    foreach ($this->getUserGroups() as $group) {
        foreach ($group->getPermissions() as $permission) {
            $permissions[] = $permission->getName();
        }
    }
    
    return array_unique($permissions);
}
```

### 3.2 Repository Classes

#### ApiRouteRepository

```php
// src/Repository/ApiRouteRepository.php (partial)
namespace App\Repository;

use App\Entity\ApiRoute;
use App\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApiRouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiRoute::class);
    }

    /**
     * Find all available API versions
     * 
     * @return string[] Array of version strings (e.g., ['v1', 'v2'])
     */
    public function findAllVersions(): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('DISTINCT r.version')
            ->getQuery()
            ->getScalarResult();
        
        return array_column($result, 'version');
    }

    /**
     * Find all routes for a specific version
     * 
     * @param string $version API version (e.g., 'v1')
     * @return ApiRoute[] Array of ApiRoute entities
     */
    public function findAllRoutesByVersion(string $version): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.version = :version')
            ->setParameter('version', $version)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all permissions associated with a route
     * 
     * @param int $routeId Route ID
     * @return Permission[] Array of Permission entities
     */
    public function findPermissionsForRoute(int $routeId): array
    {
        $route = $this->find($routeId);
        
        if (!$route) {
            return [];
        }
        
        return $route->getPermissions()->toArray();
    }
}
```

### 3.3 Service Classes

#### UserContextService

```php
// src/Service/Auth/UserContextService.php
namespace App\Service\Auth;

use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

class UserContextService
{
    public function __construct(private Security $security) {}

    /**
     * Returns the current authenticated User entity or null if not authenticated.
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user : null;
    }
}
```

### 3.4 Event Listeners

#### ApiSecurityListener

```php
// src/EventListener/ApiSecurityListener.php
namespace App\EventListener;

use App\Service\Auth\UserContextService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listener that checks if the user has the required permissions for the API route
 * 
 * This listener runs on the kernel.controller event, which occurs after the route has been
 * matched and the controller has been resolved, but before the controller is executed.
 * At this point, the authentication process has been completed, so we can reliably
 * check if the user has the required permissions.
 */
class ApiSecurityListener implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private UserContextService $userContextService,
        private LoggerInterface $logger
    ) {}
    
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Use kernel.controller event which runs after authentication
            KernelEvents::CONTROLLER => ['onKernelController', 10], // Priority 10
        ];
    }
    
    /**
     * Checks if the user has the required permissions for the API route
     * This runs after authentication has been processed
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        
        // Only check API routes
        $path = $request->getPathInfo();
        if (!str_starts_with($path, '/cms-api/')) {
            return;
        }
        
        // Skip OPTIONS requests (CORS preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return;
        }
        
        try {
            // Get the current route name
            $routeName = $request->attributes->get('_route');
            if (!$routeName) {
                // No route matched, skip permission check
                return;
            }
            
            // Get the route from the router
            $route = $this->router->getRouteCollection()->get($routeName);
            if (!$route) {
                // Route not found in collection, skip permission check
                return;
            }
            
            // Get the required permissions from the route options
            $requiredPermissions = $route->getOption('permissions') ?? [];
            
            // If no permissions are required for this route, allow access
            if (empty($requiredPermissions)) {
                return;
            }
            
            // Get the current user using UserContextService
            // At this point authentication has been completed
            $user = $this->userContextService->getCurrentUser();
            if (!$user) {
                throw new AccessDeniedException('User not authenticated.');
            }
            
            // Get the user's permissions
            $userPermissions = $user->getPermissionNames();
            
            $this->logger->debug('Checking permissions for route', [
                'route' => $routeName,
                'requiredPermissions' => $requiredPermissions,
                'userPermissions' => $userPermissions
            ]);
            
            // Check if the user has at least one of the required permissions
            $hasPermission = false;
            foreach ($requiredPermissions as $permission) {
                if (in_array($permission, $userPermissions)) {
                    $hasPermission = true;
                    break;
                }
            }
            
            // If the user doesn't have any of the required permissions, deny access
            if (!$hasPermission) {
                $this->logger->warning('Access denied to API route', [
                    'route' => $routeName,
                    'path' => $path,
                    'requiredPermissions' => $requiredPermissions,
                    'userId' => $user->getId()
                ]);
                
                throw new AccessDeniedException('You do not have permission to access this API endpoint.');
            }
        } catch (AccessDeniedException $e) {
            // Let the ApiExceptionListener handle this exception
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Error in API security check', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Let the ApiExceptionListener handle this exception
            throw new AccessDeniedException('An error occurred while checking permissions.', $e);
        }
    }
}
```

### 3.5 Voters

#### ApiRouteVoter

```php
// src/Security/Voter/ApiRouteVoter.php
namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Psr\Log\LoggerInterface;

/**
 * Voter to check if a user has the required permissions for an API route
 */
class ApiRouteVoter extends Voter
{
    public const API_ROUTE_ACCESS = 'api_route_access';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Only support our custom attribute
        return $attribute === self::API_ROUTE_ACCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // If the user is anonymous, deny access
        if (!$user instanceof User) {
            return false;
        }

        // Get the current request
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        // Get the matched route
        $routeName = $request->attributes->get('_route');
        if (!$routeName) {
            // No route matched, allow access by default (other security measures will apply)
            return true;
        }

        try {
            // Get the route from the router
            $route = $this->router->getRouteCollection()->get($routeName);
            if (!$route) {
                // Route not found, allow access by default (other security measures will apply)
                return true;
            }

            // Get the required permissions from the route options
            $requiredPermissions = $route->getOption('permissions') ?? [];
            
            // If no permissions are required, allow access
            if (empty($requiredPermissions)) {
                return true;
            }

            // Get the user's permissions
            $userPermissions = $user->getPermissionNames();
            
            // Log permission check for debugging
            $this->logger->debug('Checking permissions for route', [
                'route' => $routeName,
                'requiredPermissions' => $requiredPermissions,
                'userPermissions' => $userPermissions
            ]);

            // Check if the user has at least one of the required permissions
            foreach ($requiredPermissions as $permission) {
                if (in_array($permission, $userPermissions)) {
                    return true;
                }
            }

            // User doesn't have any of the required permissions
            $this->logger->info('Access denied to route due to missing permissions', [
                'route' => $routeName,
                'requiredPermissions' => $requiredPermissions,
                'userId' => $user->getId()
            ]);
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Error checking route permissions', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In case of error, deny access by default
            return false;
        }
    }
}
```

## 4. Practical Usage Guide

### 4.1 Adding New Permissions

To add a new permission to the system:

1. **Create the permission in the database**:

```sql
INSERT INTO permissions (name, description) 
VALUES ('manage_users', 'Allows managing user accounts');
```

2. **Assign the permission to user groups**:

```sql
INSERT INTO user_groups_permissions (id_user_groups, id_permissions)
VALUES (
    (SELECT id FROM user_groups WHERE name = 'Administrators'),
    (SELECT id FROM permissions WHERE name = 'manage_users')
);
```

3. **Associate the permission with API routes**:

```sql
INSERT INTO api_routes_permissions (id_api_routes, id_permissions)
VALUES (
    (SELECT id FROM api_routes WHERE route_name = 'user_management' AND version = 'v1'),
    (SELECT id FROM permissions WHERE name = 'manage_users')
);
```

### 4.2 Creating New API Routes with Permissions

To create a new API route with specific permissions:

1. **Add the route to the database**:

```sql
INSERT INTO api_routes (route_name, path, controller, methods, version)
VALUES (
    'user_management',
    '/users',
    'App\\Controller\\Api\\V1\\User\\UserController::manage',
    'GET,POST,PUT,DELETE',
    'v1'
);
```

2. **Associate permissions with the route**:

```sql
INSERT INTO api_routes_permissions (id_api_routes, id_permissions)
VALUES (
    (SELECT id FROM api_routes WHERE route_name = 'user_management' AND version = 'v1'),
    (SELECT id FROM permissions WHERE name = 'manage_users')
);
```

3. **Create the controller with the specified action**:

```php
// src/Controller/Api/V1/User/UserController.php
namespace App\Controller\Api\V1\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * Manage users (create, read, update, delete)
     * 
     * This endpoint will be accessible only to users with the 'manage_users' permission
     */
    public function manage(Request $request): JsonResponse
    {
        // Implementation...
        
        return $this->json(['message' => 'User management endpoint']);
    }
}
```

### 4.3 Using the Voter in Controllers

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

### 4.4 Creating Custom Voters for Specific Resources

For resource-specific permissions:

```php
// src/Security/Voter/UserVoter.php
namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const EDIT = 'edit_user';
    public const VIEW = 'view_user';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW]) && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();
        
        if (!$currentUser instanceof User) {
            return false;
        }
        
        // Get the user being accessed
        $user = $subject;
        
        switch ($attribute) {
            case self::VIEW:
                // Users can view themselves or if they have the view_users permission
                return $user->getId() === $currentUser->getId() || 
                       in_array('view_users', $currentUser->getPermissionNames());
                
            case self::EDIT:
                // Users can edit themselves or if they have the manage_users permission
                return $user->getId() === $currentUser->getId() || 
                       in_array('manage_users', $currentUser->getPermissionNames());
        }
        
        return false;
    }
}
```

Usage in a controller:

```php
// In UserController
public function viewUser(User $user)
{
    // Check if current user can view the requested user
    $this->denyAccessUnlessGranted('view_user', $user);
    
    // Continue with controller logic
}
```

## 5. Testing the Permission System

### 5.1 Unit Testing Voters

```php
// tests/Security/Voter/ApiRouteVoterTest.php
namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Security\Voter\ApiRouteVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Psr\Log\LoggerInterface;

class ApiRouteVoterTest extends TestCase
{
    private ApiRouteVoter $voter;
    private RequestStack $requestStack;
    private RouterInterface $router;
    private LoggerInterface $logger;
    
    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->voter = new ApiRouteVoter(
            $this->requestStack,
            $this->router,
            $this->logger
        );
    }
    
    public function testVoteOnAttributeWithRequiredPermission(): void
    {
        // Create a mock user with permissions
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getPermissionNames')->willReturn(['view_users']);
        
        // Create a mock token with the user
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        
        // Create a mock request with a route
        $request = $this->createMock(Request::class);
        $request->attributes = new \Symfony\Component\HttpFoundation\ParameterBag([
            '_route' => 'test_route'
        ]);
        
        // Set up the request stack to return our mock request
        $this->requestStack->method('getCurrentRequest')->willReturn($request);
        
        // Create a route with required permissions
        $route = new Route('/test');
        $route->setOption('permissions', ['view_users']);
        
        // Create a route collection with our route
        $routeCollection = new RouteCollection();
        $routeCollection->add('test_route', $route);
        
        // Set up the router to return our route collection
        $this->router->method('getRouteCollection')->willReturn($routeCollection);
        
        // Test the voter
        $result = $this->voter->vote($token, null, ['api_route_access']);
        
        // Assert that access is granted
        $this->assertEquals(1, $result); // 1 means granted
    }
    
    // Add more test cases...
}
```

### 5.2 Functional Testing

```php
// tests/Controller/Api/SecurityTest.php
namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityTest extends WebTestCase
{
    public function testApiRouteWithRequiredPermission(): void
    {
        $client = static::createClient();
        
        // Log in as a user with the required permission
        // ... authentication logic ...
        
        // Make a request to a protected route
        $client->request('GET', '/cms-api/v1/users');
        
        // Assert that access is granted
        $this->assertResponseIsSuccessful();
    }
    
    public function testApiRouteWithoutRequiredPermission(): void
    {
        $client = static::createClient();
        
        // Log in as a user without the required permission
        // ... authentication logic ...
        
        // Make a request to a protected route
        $client->request('GET', '/cms-api/v1/users');
        
        // Assert that access is denied
        $this->assertResponseStatusCodeSame(403);
    }
    
    // Add more test cases...
}
```

## 6. Troubleshooting

### 6.1 Common Issues

1. **Route Not Found**: If your route is not being loaded:
   - Check that it exists in the `api_routes` table
   - Verify the version matches (e.g., 'v1')
   - Ensure the controller and action exist

2. **Permission Not Applied**: If permissions are not being checked:
   - Verify the permission exists in the `permissions` table
   - Check that it's associated with the route in `api_routes_permissions`
   - Ensure the user's group has the permission in `user_groups_permissions`

3. **User Not Authenticated**: If authentication fails:
   - Check JWT token validity
   - Verify the authenticator is configured correctly
   - Look for errors in the security logs

### 6.2 Debugging Tips

1. **Enable Debug Logging**:
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

2. **Check Database Consistency**:
   ```sql
   -- Check if route has permissions
   SELECT r.route_name, p.name 
   FROM api_routes r
   JOIN api_routes_permissions rp ON r.id = rp.id_api_routes
   JOIN permissions p ON p.id = rp.id_permissions
   WHERE r.route_name = 'your_route_name';
   
   -- Check if user group has permissions
   SELECT g.name AS group_name, p.name AS permission_name
   FROM user_groups g
   JOIN user_groups_permissions gp ON g.id = gp.id_user_groups
   JOIN permissions p ON p.id = gp.id_permissions
   WHERE g.id = (SELECT id_user_groups FROM users_groups WHERE id_users = YOUR_USER_ID);
   ```

3. **Test with Symfony Console**:
   ```bash
   # Check if a route exists
   php bin/console debug:router your_route_name
   
   # Check security voters
   php bin/console debug:event-dispatcher kernel.controller
   ```

## 7. Best Practices

1. **Use Descriptive Permission Names**: Choose clear, descriptive names for permissions (e.g., `view_users`, `edit_content`).

2. **Group Related Permissions**: Organize permissions logically (e.g., all content-related permissions together).

3. **Least Privilege Principle**: Assign only the minimum permissions necessary for each user group.

4. **Document Permissions**: Maintain documentation of all permissions and their purposes.

5. **Audit Permission Changes**: Log changes to permissions and permission assignments.

6. **Test Authorization Logic**: Write tests to verify that permission checks work correctly.

7. **Use Voters for Complex Logic**: For complex authorization decisions, create custom voters rather than embedding logic in controllers.

8. **Centralize Permission Checks**: Avoid duplicating permission-checking logic across the application.
