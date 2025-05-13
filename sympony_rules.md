---
description: Enforces best practices for PHP development, focusing on context-aware code generation, modern patterns, and maintainable architecture. Provides comprehensive guidelines for writing clean, efficient, and secure PHP code with proper context.
globs: **/*.php
---
# PHP Best Practices

You are an expert in PHP programming, Laravel, Symfony, and related PHP technologies.
You understand modern PHP development practices, architectural patterns, and the importance of providing complete context in code generation.

### Context-Aware Code Generation
- Always provide complete class context including namespaces and use statements
- Include relevant configuration files (composer.json) when generating projects
- Generate complete method signatures with proper parameters, return types, and PHPDoc
- Include comprehensive PHPDoc blocks explaining the purpose, parameters, and return values
- Provide context about the class's role in the larger system architecture

### Code Style and Structure
- Follow PSR-12 style guide and clean code principles
- Structure code in logical namespaces following domain-driven design
- Implement proper separation of concerns (controllers, models, services, repositories)
- Use modern PHP features (typed properties, attributes, enums) appropriately
- Maintain consistent code formatting using PHP-CS-Fixer
- Use proper autoloading and namespace structure

### Framework Best Practices
- Use Symfony best practices and patterns
- Implement proper dependency injection and service containers
- Configure proper routing and middleware
- Use proper ORM patterns and database migrations
- Implement proper error handling and logging
- Configure proper testing setup (PHPUnit, Pest)

### Testing and Quality
- Write comprehensive unit tests with proper test context
- Include integration tests for critical paths
- Use proper mocking strategies with PHPUnit
- Implement E2E tests with Laravel Dusk or similar
- Include performance tests for critical components
- Maintain high test coverage for core business logic

### Security and Performance
- Implement proper input validation and sanitization
- Use secure authentication and token management
- Configure proper CORS and CSRF protection
- Implement rate limiting and request validation
- Use proper caching strategies
- Optimize database queries and indexes

### API Design
- Follow RESTful principles with proper HTTP methods
- Use proper status codes and error responses
- Implement proper versioning strategies
- Document APIs using OpenAPI/Swagger
- Include proper request/response validation
- Implement proper pagination and filtering

### Database and Data Access
- Use proper ORM patterns (Eloquent, Doctrine)
- Implement proper transaction management
- Use database migrations
- Optimize queries and use proper indexing
- Implement proper connection pooling
- Use proper database isolation levels

### Build and Deployment
- Use Composer for dependency management
- Implement proper CI/CD pipelines
- Use Docker for containerization
- Configure proper environment variables
- Implement proper logging and monitoring
- Use proper deployment strategies

### Examples

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Cache;

/**
 * UserService handles user-related operations.
 * Provides methods for user management and authentication.
 */
class UserService
{
    private $apiClient;
    private $cache;

    public function __construct($apiClient, $cache)
    {
        $this->apiClient = $apiClient;
        $this->cache = $cache;
    }

    /**
     * Finds a user by their email address.
     *
     * @param string $email The email address to search for
     * @return User|null The user if found, null otherwise
     * @throws ApiException If the request fails
     */
    public function findUserByEmail(string $email): ?User
    {
        try {
            $cachedUser = $this->cache->get("user:{$email}");
            if ($cachedUser) {
                return new User($cachedUser);
            }

            $userData = $this->apiClient->get("/users?email={$email}");
            if ($userData) {
                $user = new User($userData);
                $this->cache->set("user:{$email}", $userData);
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            throw new ApiException("Failed to find user by email: " . $e->getMessage());
        }
    }
}

/**
 * Tests for UserService functionality.
 */
class UserServiceTest extends \PHPUnit\Framework\TestCase
{
    private $service;
    private $apiClient;
    private $cache;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(\App\Clients\ApiClient::class);
        $this->cache = $this->createMock(\Illuminate\Cache\Repository::class);
        $this->service = new UserService($this->apiClient, $this->cache);
    }

    public function testFindUserByEmailWhenUserExists()
    {
        // Given
        $email = "test@example.com";
        $userData = ["id" => 1, "email" => $email];
        $this->apiClient->method('get')->willReturn($userData);

        // When
        $result = $this->service->findUserByEmail($email);

        // Then
        $this->assertNotNull($result);
        $this->assertEquals($email, $result->getEmail());
        $this->apiClient->expects($this->once())
            ->method('get')
            ->with("/users?email={$email}");
    }

    public function testFindUserByEmailWhenUserNotFound()
    {
        // Given
        $email = "nonexistent@example.com";
        $this->apiClient->method('get')->willReturn(null);

        // When
        $result = $this->service->findUserByEmail($email);

        // Then
        $this->assertNull($result);
        $this->apiClient->expects($this->once())
            ->method('get')
            ->with("/users?email={$email}");
    }
}
