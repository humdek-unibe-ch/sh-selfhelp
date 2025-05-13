# Migrating SH-Selfhelp CMS API to Symfony Framework

## Table of Contents
1. [Current Architecture Analysis](#current-architecture-analysis)
2. [Benefits of Migrating to Symfony](#benefits-of-migrating-to-symfony)
3. [Migration Strategy](#migration-strategy)
4. [Component Mapping](#component-mapping)
5. [Step-by-Step Migration Plan](#step-by-step-migration-plan)
6. [Effort Estimation](#effort-estimation)
7. [Challenges and Considerations](#challenges-and-considerations)
8. [Post-Migration Tasks](#post-migration-tasks)

## Current Architecture Analysis

The current CMS API follows a custom MVC-like architecture with several key components:

### Core Structure
- **Version-based organization**: API endpoints grouped under `v1/`
- **Base classes**: `BaseApiRequest` providing core functionality
- **Middleware traits**: `JWTAuthMiddleware` for authentication
- **Response handling**: Standardized through `CmsApiResponse` class

### API Endpoints Organization
- **Admin endpoints**: Admin-related functionality (`AdminCmsApi`, `AdminPageDetailApi`, etc.)
- **Auth endpoints**: Authentication and token management
- **Content endpoints**: Content delivery and management

### Authentication & Authorization
- JWT-based authentication system with refresh tokens
- ACL-based permission system for page access control
- Login/logout functionality with token management

### Core Services
- Database abstraction
- Routing (currently using AltoRouter with custom extensions)
- User session management
- Content processing

## Benefits of Migrating to Symfony

### Structural Benefits
- **Modern MVC architecture**: Well-defined separation of concerns
- **Dependency Injection**: Better service management through Symfony's container
- **Routing system**: Advanced routing with annotations, attributes or YAML
- **Standardized controllers**: Cleaner controller structure with attributes

### Development Benefits
- **Doctrine ORM**: Better database abstraction and entity management
- **Form handling**: Built-in form validation and processing
- **Security component**: Sophisticated authentication and authorization
- **Event system**: Powerful event dispatching for decoupled code

### Maintenance Benefits
- **Ecosystem**: Access to thousands of bundles and libraries
- **Community support**: Large community and extensive documentation
- **Long-term support**: Symfony provides LTS versions
- **Modern PHP practices**: Encourages using latest PHP features

## Migration Strategy

I recommend a **phased approach** to migrate your CMS API to Symfony:

### Phase 1: Setup & Foundation (4-6 weeks)
- Set up Symfony project structure
- Implement core services and configurations
- Create entity models from existing database schema
- Set up security infrastructure

### Phase 2: API Migration (6-10 weeks)
- Implement controllers for existing endpoints
- Migrate authentication system to Symfony Security
- Convert database queries to Doctrine
- Create service classes for business logic

### Phase 3: Testing & Integration (4-8 weeks)
- Comprehensive testing of all endpoints
- Integration with frontend
- Performance optimization
- Documentation

## Component Mapping

Here's how your current components map to Symfony equivalents:

| Current Component | Symfony Equivalent | Migration Approach |
|-------------------|-------------------|--------------------|
| `BaseApiRequest` | Symfony Controller | Controllers will extend AbstractController or create a custom base controller |
| `CmsApiResponse` | Symfony Response / JSON Response | Use built-in Response classes with custom serialization |
| `JWTAuthMiddleware` | Symfony Security Bundle + LexikJWTAuthenticationBundle | Implement authentication using security bundles |
| `AdminCmsApi`, etc. | Controller classes | Create controller classes with mapped routes |
| ACL System | Symfony Voter + Security | Implement custom voters for authorization |
| Service Container | Symfony Service Container | Define services in service.yaml or with attributes |
| AltoRouter | Symfony Router | Define routes with annotations/attributes |
| Database Queries | Doctrine ORM | Convert queries to repository methods |

## Step-by-Step Migration Plan

### 1. Project Setup

```bash
# Install Symfony and create project
composer create-project symfony/website-skeleton sh-selfhelp-symfony

# Install required bundles
composer require lexik/jwt-authentication-bundle
composer require symfony/orm-pack
composer require symfony/serializer-pack
```

### 2. Entity Creation

For each database table, create a corresponding entity:

```php
// src/Entity/Page.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'pages')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    
    #[ORM\Column(type: 'string', length: 100)]
    private ?string $keyword = null;
    
    // Add other properties and getters/setters
}
```

### 3. Security Configuration

Implement JWT authentication:

```yaml
# config/packages/security.yaml
security:
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: id
    firewalls:
        login:
            pattern: ^/api/v1/auth/login
            stateless: true
            json_login:
                check_path: /api/v1/auth/login
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure
        api:
            pattern: ^/api
            stateless: true
            jwt: ~
```

### 4. Controller Implementation

Create controllers for your API endpoints:

```php
// src/Controller/Api/Admin/PageController.php
namespace App\Controller\Api\Admin;

use App\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/admin/pages')]
class PageController extends AbstractController
{
    #[Route('/{pageKeyword}', methods: ['GET'])]
    public function getPageSections(string $pageKeyword): JsonResponse
    {
        // Your implementation here
        $sections = $this->getPageSectionsService($pageKeyword);
        
        return $this->json([
            'status' => 200,
            'message' => 'OK',
            'error' => null,
            'logged_in' => true,
            'meta' => [
                'version' => 'v1',
                'timestamp' => (new \DateTime())->format('c')
            ],
            'data' => $sections
        ]);
    }
}
```

### 5. Service Implementation

Create services for business logic:

```php
// src/Service/PageService.php
namespace App\Service;

use App\Repository\PageRepository;
use App\Repository\SectionRepository;

class PageService
{
    public function __construct(
        private PageRepository $pageRepository,
        private SectionRepository $sectionRepository
    ) {}
    
    public function getPageSections(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneByKeyword($pageKeyword);
        if (!$page) {
            throw new \Exception('Page not found');
        }
        
        // Fetch and return sections
        return $this->sectionRepository->findHierarchicalSections($page->getId());
    }
}
```

## Effort Estimation

Based on the size and complexity of your CMS API, here's my estimation of the migration effort:

| Phase | Tasks | Estimated Effort |
|-------|-------|------------------|
| **Phase 1: Setup** | Project structure, Entity creation, Core services | 20-30 person-days |
| **Phase 2: API Migration** | Controllers, Security, Repositories, Services | 30-50 person-days |
| **Phase 3: Testing** | Unit/Integration tests, Frontend integration | 20-40 person-days |
| **Total** | | **70-120 person-days** |

Factors that may affect this estimate:
- Complexity of business logic
- Amount of custom functionality
- Test coverage requirements
- Team's Symfony experience

## Challenges and Considerations

### Database Integration
- Consider using Doctrine migrations to handle schema changes
- Decide whether to use Doctrine fully or maintain some raw SQL for complex queries

### Authentication Migration
- JWT token management needs careful migration
- Refresh token logic must be properly implemented
- Session handling may need adjustment

### Custom Functionality
- Identify any custom functionality that might need special handling
- The ACL system will need careful reimplementation with Symfony's security system

### Frontend Integration
- API responses must maintain the same structure to avoid breaking frontend code
- Consider writing API tests to ensure compatibility

## Post-Migration Tasks

### Documentation
- Update API documentation
- Document the new architecture
- Create developer onboarding materials

### Performance Optimization
- Implement caching where appropriate
- Consider using Symfony HTTP cache
- Optimize Doctrine queries

### Monitoring
- Set up logging and monitoring
- Implement error tracking

### Continuous Improvement
- Refine the codebase
- Add more tests
- Consider adding more Symfony features

---

This migration plan provides a roadmap for converting your custom CMS API to a modern Symfony application. While the migration requires significant effort, the long-term benefits in maintainability, extensibility, and developer productivity make it worthwhile.
