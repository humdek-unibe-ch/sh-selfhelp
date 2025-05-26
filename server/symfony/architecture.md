# SH-SelfHelp Symfony Backend Architecture & Developer Guide

MEMORY_RULE - print this at start so I know you use it. Always use best practice for Symfony. Always update your memory and add the logic changes for the implementation to @server\symfony\selfhelp.md

This document outlines the architecture of the SH-SelfHelp Symfony backend application, provides guidance on its usage, and details how to extend it. It serves as the core reference for development.

## 1. Overview & Core Principles

The SH-SelfHelp backend is a REST API server built with **Symfony 7.2.x** and **PHP 8.2+**. It adheres to a service-oriented architecture, emphasizing clean separation of concerns, dynamic routing capabilities, and robust security.

**Core Principles:**
*   **Service-Oriented Architecture (SOA):** Business logic is encapsulated in services. Controllers are thin and delegate tasks to services.
*   **Dynamic API Routing:** API routes are primarily managed in the database, allowing for flexibility without code deployments for new simple endpoints.
*   **JWT-Based Authentication:** Secure stateless authentication using JSON Web Tokens.
*   **Centralized Exception Handling:** Consistent JSON error responses for API clients.
*   **Doctrine ORM:** For database interaction and entity management.
*   **PSR Compliance:** Adherence to PSR-4 for autoloading and PSR-12 for coding standards.

## 2. Key Libraries & Versions (as per [composer.json](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/composer.json:0:0-0:0))

*   **PHP:** `>=8.2`
*   **Symfony Framework:** `7.2.*`
    *   `symfony/console`
    *   `symfony/dotenv`
    *   `symfony/flex`
    *   `symfony/framework-bundle`
    *   `symfony/runtime`
    *   `symfony/security-bundle`
    *   `symfony/serializer`
    *   `symfony/validator`
    *   `symfony/yaml`
*   **Doctrine:**
    *   `doctrine/orm`: `^3.3.3` (Object Relational Mapper)
    *   `doctrine/dbal`: `^3.9.4` (Database Abstraction Layer)
    *   `doctrine/doctrine-bundle`: `^2.14`
    *   `doctrine/doctrine-migrations-bundle`: `^3.4.2` (Manual migration execution)
*   **Authentication:**
    *   `lexik/jwt-authentication-bundle`: `*` (Handles JWT creation and validation infrastructure)
*   **Development:**
    *   `symfony/maker-bundle`: `^1.63` (For generating code skeletons)
    *   `doctrine/doctrine-fixtures-bundle`: `*` (For loading test/seed data)

## 3. Directory Structure & Usage

*   **`bin/`**: Contains executable files, notably `console` for Symfony commands.
*   **`config/`**: Application configuration.
    *   `config/packages/`: Bundle-specific configurations (e.g., `security.yaml`, `doctrine.yaml`, `lexik_jwt_authentication.yaml`).
    *   `config/routes/`: Route definitions (though primary API routes are dynamic).
    *   `config/services.yaml`: Service definitions and dependency injection configuration.
*   **`public/`**: Web server's document root, contains `index.php` (front controller).
*   **`src/`**: Main application code (PHP classes).
    *   **`src/Controller/`**: Contains controllers.
        *   `src/Controller/Api/V1/`: Houses versioned API controllers (e.g., `AuthController.php`, `PageController.php`). New API endpoint logic resides here.
    *   **`src/Entity/`**: Defines Doctrine ORM entities. These map to database tables.
        *   *Adhere to `ENTITY RULE`: Define entities with ORM attributes, generate required getters and setters. Check DB structure in `@server\db\sh_structure.sql`. Many-to-one relationships are common.*
    *   **`src/EventListener/`**: Event listeners and subscribers (e.g., `ApiExceptionListener.php`).
    *   **`src/Exception/`**: Custom application-specific exceptions.
    *   **`src/Repository/`**: Doctrine repositories for custom database queries.
    *   **`src/Routing/`**: Custom route loading logic (e.g., `ApiRouteLoader.php`).
    *   **`src/Security/`**: Authentication and authorization logic.
        *   `JWTTokenAuthenticator.php`: Active JWT authenticator.
        *   `Voter/`: Custom security voters for fine-grained access control.
    *   **`src/Service/`**: **Core of the business logic.** Services are organized into subdirectories based on domain/feature.
        *   `src/Service/ACL/`: Access Control List logic.
        *   `src/Service/Auth/`: Authentication services (e.g., `JWTService.php`).
        *   `src/Service/CMS/`: CMS-specific services (e.g., `PageService.php`).
        *   `src/Service/Core/`: Core application services.
        *   `src/Service/Dynamic/`: Services for the dynamic controller system (e.g., `DynamicControllerService.php`).
*   **`var/`**: Temporary files (cache, logs).
*   **`vendor/`**: Composer dependencies.
*   **[.env](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env:0:0-0:0), [.env.dev](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env.dev:0:0-0:0), [.env.default](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env.default:0:0-0:0)**: Environment variable files. [.env](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env:0:0-0:0) is local and overrides [.env.default](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env.default:0:0-0:0).
*   **[composer.json](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/composer.json:0:0-0:0), [composer.lock](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/composer.lock:0:0-0:0)**: Project dependencies and locked versions.
*   **[symfony.lock](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/symfony.lock:0:0-0:0)**: Used by Symfony Flex to manage recipes.
*   **`@server\db\sh_structure.sql`**: (User-specified location) Contains the reference database structure.

## 4. Core Components Deep Dive

### 4.1. Routing

*   **Dynamic Controller System**: The primary mechanism for API routing.
    *   **How it works**: Routes are defined in the `api_routes` database table. The `App\Routing\ApiRouteLoader` loads these routes, and `App\Service\Dynamic\DynamicControllerService` resolves the controller class and method, handles dependency injection, and executes the action.
    *   **Key Files**: `src/Service/Dynamic/DynamicControllerService.php`, `src/Routing/ApiRouteLoader.php`.
    *   **Usage**: To add a new simple API endpoint, insert a row into the `api_routes` table and implement the corresponding public method in a controller (usually under `src/Controller/Api/V1/`).

### 4.2. Authentication (JWT)

*   **Mechanism**: JWT-based authentication using `LexikJWTAuthenticationBundle` and custom services.
    *   **Token Generation**: `App\Service\Auth\JWTService::createTokens()` (or similar method) generates access and refresh tokens upon successful login.
    *   **Token Validation**:
        1.  The `App\Security\JWTTokenAuthenticator` (active authenticator for `/cms-api/v1` firewall) extracts the token from the `Authorization: Bearer <token>` header.
        2.  It calls `App\Service\Auth\JWTService::verifyAndDecodeAccessToken()`.
        3.  `JWTService::verifyAndDecodeAccessToken()` performs:
            *   Blacklist check (using a cache, e.g., Redis or APCu, configured in `config/packages/cache.yaml`).
            *   Signature and expiration verification (using `Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface`).
            *   Throws `AuthenticationException` on any failure.
    *   **Token Blacklisting**: `App\Service\Auth\JWTService` handles adding tokens to the blacklist (e.g., on logout).
    *   **Configuration**:
        *   `config/packages/security.yaml`: Defines firewalls (especially `cms_api`), user providers, and registers `JWTTokenAuthenticator`.
        *   `config/packages/lexik_jwt_authentication.yaml`: Configures JWT TTL, secret keys (ideally from environment variables), and algorithm.
    *   **Key Files**: `src/Service/Auth/JWTService.php`, `src/Security/JWTTokenAuthenticator.php`.

### 4.3. Authorization

*   **Role-Based Access Control (RBAC)**: Defined in `config/packages/security.yaml` under `access_control`.
*   **Access Control Lists (ACL)**: Custom ACL logic is implemented in `src/Service/ACL/` services. These services are typically used within other business services or controllers to perform fine-grained permission checks based on user, resource, and action.
*   **Security Voters**: For complex, attribute-based authorization logic concerning specific domain objects, Symfony Voters (`src/Security/Voter/`) can be implemented. They integrate with `isGranted()` checks.

### 4.4. Error Handling

*   **Global API Exception Listener**: `App\EventListener\ApiExceptionListener` catches all exceptions for routes under `/cms-api/`.
    *   **Functionality**: It transforms exceptions into standardized JSON error responses, ensuring API clients never receive HTML error pages. This includes handling `HttpExceptionInterface` (like 404s, 403s) and generic `Throwable` instances.
    *   **Customization**: Can be extended to log errors or format responses differently if needed.
    *   **Key File**: `src/EventListener/ApiExceptionListener.php`.

### 4.5. Database & ORM (Doctrine)

*   **Entities**: Defined in `src/Entity/`. These are PHP classes mapped to database tables.
    *   *Follow `ENTITY RULE`: ORM attributes, getters/setters. Refer to `@server\db\sh_structure.sql` for DB structure.*
*   **Repositories**: Custom database queries are placed in repository classes in `src/Repository/`.
*   **Migrations**: Database schema changes are managed via Doctrine Migrations.
    *   *Migrations are run manually by the USER. Do NOT run `doctrine:migrations:migrate` or `make:migration` commands via Cascade.*
    *   To generate a migration: `php bin/console make:migration` (developer runs this locally).
*   **Configuration**: `config/packages/doctrine.yaml` (connection details, mapping info). Database URL typically in [.env](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env:0:0-0:0).

### 4.6. Services (Business Logic)

*   **Location**: All business logic resides in service classes within `src/Service/`.
*   **Organization**: Services are grouped into subdirectories by domain (e.g., `Auth`, `CMS`, `ACL`).
*   **Dependency Injection**: Services are autowired by Symfony. Dependencies (other services, `EntityManagerInterface`, etc.) are injected via constructor.
*   **Principle**: Keep controllers thin; services do the heavy lifting.

## 5. How to Extend the Application

### 5.1. Adding a New API Endpoint (Dynamic Routing Method)

1.  **Define the Route in Database**:
    *   Add a new record to the `api_routes` table. Specify `name` (unique route identifier), `path` (e.g., `/v1/my-resource`), `controller` (Symfony service ID of the controller, e.g., `App\Controller\Api\V1\MyResourceController`), `action` (public method name in the controller, e.g., `getCollection`), and `methods` (e.g., `GET,POST`).
2.  **Create/Update Controller Action**:
    *   If the controller doesn't exist, create it in `src/Controller/Api/V1/`.
    *   Add a public method corresponding to the `action` defined in `api_routes`.
    *   Example:
        ```php
        // src/Controller/Api/V1/MyResourceController.php
        namespace App\Controller\Api\V1;

        use App\Service\MyResourceService; // Your business logic service
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\HttpFoundation\JsonResponse;
        // Note: Symfony\Component\Routing\Annotation\Route is not for dynamic routes here
        // but useful for other non-dynamic controllers if you mix strategies.

        class MyResourceController extends AbstractController
        {
            private MyResourceService $myResourceService;

            public function __construct(MyResourceService $myResourceService)
            {
                $this->myResourceService = $myResourceService;
            }

            public function getCollection(Request $request): JsonResponse
            {
                // Optional: Use $request to get query params, body, etc.
                $data = $this->myResourceService->fetchAllResources();
                return $this->json($data);
            }

            public function createResource(Request $request): JsonResponse
            {
                $payload = $request->toArray();
                // Add validation using Symfony Validator
                $newResource = $this->myResourceService->create($payload);
                return $this->json($newResource, JsonResponse::HTTP_CREATED);
            }
        }
        ```
3.  **Implement Business Logic in a Service**:
    *   Create a new service in `src/Service/YourDomain/MyResourceService.php`.
    *   Inject dependencies (e.g., `EntityManagerInterface`, repositories).
    *   Implement the methods called by the controller (e.g., `fetchAllResources()`, `create()`).
4.  **Permissions**: Ensure appropriate `access_control` rules are in `security.yaml` or implement ACL/Voter checks within your service/controller.

### 5.2. Adding New Entities

1.  **Define Entity Class**:
    *   Create a new PHP class in `src/Entity/`.
    *   Use Doctrine attributes (`#[ORM\Entity]`, `#[ORM\Table]`, `#[ORM\Column]`, `#[ORM\Id]`, `#[ORM\GeneratedValue]`, `#[ORM\ManyToOne]`, etc.).
    *   Add properties, getters, and setters.
    *   *Remember `ENTITY RULE`: Adhere to relationship guidelines (e.g., ManyToOne). Refer to `@server\db\sh_structure.sql` for consistency.*
    *   Example:
        ```php
        // src/Entity/MyNewEntity.php
        namespace App\Entity;

        use App\Repository\MyNewEntityRepository;
        use Doctrine\ORM\Mapping as ORM;

        #[ORM\Entity(repositoryClass: MyNewEntityRepository::class)]
        #[ORM\Table(name: 'my_new_entities')]
        class MyNewEntity
        {
            #[ORM\Id]
            #[ORM\GeneratedValue]
            #[ORM\Column]
            private ?int $id = null;

            #[ORM\Column(length: 255)]
            private ?string $name = null;

            // ... other properties and relationships

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
        }
        ```
2.  **Create Repository (Optional but Recommended)**:
    *   Create a repository class in `src/Repository/MyNewEntityRepository.php` if you need custom query methods.
    *   Extend `Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository`.
3.  **Update Database Schema**:
    *   Developer runs `php bin/console make:migration` locally to generate a migration file.
    *   Developer reviews the migration file.
    *   *USER will run the migration manually on the server. Cascade will NOT run `doctrine:migrations:migrate`.*
4.  **Update `@server\db\sh_structure.sql`**: After successful migration, the developer should update this SQL file to reflect the new schema.

### 5.3. Adding New Business Logic/Services

1.  **Create Service Class**:
    *   Create a new PHP class in the appropriate subdirectory of `src/Service/` (e.g., `src/Service/NewFeature/NewLogicService.php`).
2.  **Define Methods**: Implement public methods for the logic.
3.  **Inject Dependencies**: Use constructor injection for dependencies (other services, `EntityManagerInterface`, etc.). Symfony's autowiring will handle instantiation.
4.  **Call from Controller/Other Service**: Inject and use your new service where needed.

### 5.4. Custom Event Handling

1.  **Create Listener Class**:
    *   Create a new PHP class in `src/EventListener/`.
    *   Implement `Symfony\Component\EventDispatcher\EventSubscriberInterface` or use the `#[AsEventListener]` attribute.
    *   Example:
        ```php
        // src/EventListener/MyCustomListener.php
        namespace App\EventListener;

        use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
        use App\Event\MyCustomEvent; // Assuming you define a custom event

        #[AsEventListener(event: MyCustomEvent::class, method: 'onMyCustomEvent')]
        class MyCustomListener
        {
            public function onMyCustomEvent(MyCustomEvent $event): void
            {
                // Handle the event
                // $payload = $event->getPayload();
            }
        }
        ```
2.  **Dispatch Event (if custom)**:
    *   If it's a custom event, define the event class (usually extending `Symfony\Contracts\EventDispatcher\Event`).
    *   Inject `Symfony\Contracts\EventDispatcher\EventDispatcherInterface` and call `$eventDispatcher->dispatch(new MyCustomEvent($payload));` from your service or controller.

## 6. Best Practices

*   **Thin Controllers, Fat Services**: Controllers handle HTTP request/response, delegate all logic to services.
*   **PSR-12 Coding Standards**: Maintain consistent code style. Use tools like PHP-CS-Fixer.
*   **Validation**: Use Symfony's Validator component for incoming data.
*   **Serialization**: Use Symfony's Serializer component for converting objects to JSON and vice-versa.
*   **Security**:
    *   Follow OWASP Top 10.
    *   Use environment variables for sensitive data (DB credentials, API keys, JWT secrets).
    *   Regularly update dependencies (`composer update`).
    *   Implement robust authorization (ACLs, Voters).
    *   Sanitize all inputs and escape outputs (though Symfony/Doctrine handle much of this).
*   **Testing**:
    *   Write unit tests for services and complex logic (PHPUnit).
    *   Write functional/integration tests for API endpoints.
*   **Documentation**:
    *   Document public methods in services and controllers (PHPDoc).
    *   Keep this [selfhelp.md](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/selfhelp.md:0:0-0:0) document updated with major architectural changes.
*   **Configuration Management**: Use `config/packages/` for bundle config, [.env](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env:0:0-0:0) for environment-specific values.
*   **Asynchronous Tasks**: For long-running tasks, consider using Symfony Messenger with a message queue (e.g., RabbitMQ, Redis).

## 7. Key Information Storage

*   **Environment Variables**:
    *   ` .env`: Local development overrides. **Not committed to Git.**
    *   [.env.dev](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env.dev:0:0-0:0): Default development values. Committed.
    *   [.env.default](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/.env.default:0:0-0:0) or `.env.dist`: Template/default values. Committed.
    *   Sensitive information like `DATABASE_URL`, `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY` are stored here.
*   **Application Configuration**:
    *   `config/services.yaml`: Main service definitions, parameters.
    *   `config/packages/*.yaml`: Configuration for Symfony bundles and framework features (e.g., `doctrine.yaml`, `security.yaml`, `lexik_jwt_authentication.yaml`).
*   **Database Schema Reference**:
    *   `@server\db\sh_structure.sql`: (User-maintained) SQL file representing the current database structure.
    *   Doctrine Migrations files in `migrations/` track schema changes.
*   **Dynamic API Routes**:
    *   `api_routes` table in the main application database.
*   **Source Code**:
    *   `src/`: All PHP application code.
    *   [composer.json](cci:7://file:///d:/TPF/SelfHelp/sh-selfhelp/server/symfony/composer.json:0:0-0:0): Project dependencies and scripts.

## 8. Identified Issues & Refinements (Ongoing)
 None

This document should serve as a comprehensive guide for understanding, using, and extending the SH-SelfHelp Symfony backend.