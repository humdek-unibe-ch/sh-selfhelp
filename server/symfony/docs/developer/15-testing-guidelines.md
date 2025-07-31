# Testing Guidelines

## 🧪 Overview

The SelfHelp Symfony Backend follows a comprehensive testing strategy that emphasizes real database testing, API integration testing, and thorough validation of all system components. **No data mocking is used** - all tests execute against actual test databases to ensure real-world behavior.

## 🏗️ Testing Architecture

```mermaid
graph TD
    A[Test Suite] --> B[Unit Tests]
    A --> C[Integration Tests]
    A --> D[API Tests]
    A --> E[Database Tests]
    
    subgraph "Test Types"
        F[Service Tests]
        G[Controller Tests]
        H[Entity Tests]
        I[Repository Tests]
        J[Security Tests]
    end
    
    subgraph "Test Database"
        K[Test MySQL Database]
        L[Test Data Fixtures]
        M[Transaction Rollback]
    end
    
    B --> F
    C --> G
    D --> H
    E --> I
    
    F --> K
    G --> K
    H --> K
    I --> K
    J --> K
    
    K --> L
    K --> M
```

## 🔧 Test Configuration

### PHPUnit Configuration
```xml
<!-- phpunit.xml.dist -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         executionOrder="depends,defects"
         failOnRisky="true"
         failOnWarning="true"
         stopOnFailure="false">
    
    <php>
        <ini name="display_errors" value="1" />
        <ini name="error_reporting" value="-1" />
        <server name="APP_ENV" value="test" force="true" />
        <server name="SHELL_VERBOSITY" value="-1" />
        <server name="SYMFONY_PHPUNIT_REMOVE" value="" />
        <server name="SYMFONY_PHPUNIT_VERSION" value="9.5" />
    </php>

    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Api">
            <directory>tests/Api</directory>
        </testsuite>
    </testsuites>

    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">src</directory>
        </include>
        <exclude>
            <directory>src/DataFixtures</directory>
            <file>src/Kernel.php</file>
        </exclude>
    </coverage>

    <listeners>
        <listener class="Symfony\Bridge\PhpUnit\SymfonyTestsListener" />
    </listeners>

    <extensions>
        <extension class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension" />
    </extensions>
</phpunit>
```

### Test Environment Configuration
```yaml
# config/packages/test/doctrine.yaml
doctrine:
    dbal:
        # Use test database
        url: '%env(resolve:DATABASE_TEST_URL)%'
        # Enable SQL logging for debugging
        logging: true
        profiling: true
    orm:
        # Faster test execution
        auto_generate_proxy_classes: true
        metadata_cache_driver:
            type: array
        query_cache_driver:
            type: array
        result_cache_driver:
            type: array
```

### Environment Variables
```bash
# .env.test
DATABASE_TEST_URL="mysql://user:password@127.0.0.1:3306/selfhelp_test"
APP_ENV=test
APP_DEBUG=true
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private-test.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public-test.pem
```

## 📋 Testing Principles

### 1. **No Data Mocking**
All tests execute against real test database with actual data:

```php
<?php
// ❌ Wrong - Don't mock data
public function testCreatePage(): void
{
    $mockRepository = $this->createMock(PageRepository::class);
    $mockRepository->method('save')->willReturn(new Page());
    // ... mock setup
}

// ✅ Correct - Use real database
public function testCreatePage(): void
{
    $pageData = [
        'keyword' => 'test-page-' . uniqid(),
        'pageType' => $this->getTestPageType()->getId(),
        'url' => '/test-page'
    ];
    
    $page = $this->adminPageService->createPage($pageData);
    
    $this->assertInstanceOf(Page::class, $page);
    $this->assertEquals('test-page-' . uniqid(), $page->getKeyword());
    
    // Verify in database
    $savedPage = $this->pageRepository->find($page->getId());
    $this->assertNotNull($savedPage);
}
```

### 2. **API Testing Focus**
Test complete API endpoints rather than individual components:

```php
<?php
public function testCreatePageEndpoint(): void
{
    $pageData = [
        'keyword' => 'api-test-page',
        'pageType' => 1,
        'url' => '/api-test-page',
        'isHeadless' => false
    ];

    $response = $this->client->request('POST', '/cms-api/v1/admin/pages', [
        'json' => $pageData,
        'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken()]
    ]);

    $this->assertResponseStatusCodeSame(201);
    $this->assertResponseHeaderSame('content-type', 'application/json');
    
    $data = json_decode($response->getContent(), true);
    $this->assertEquals('api-test-page', $data['data']['keyword']);
    
    // Verify in database
    $page = $this->pageRepository->findOneBy(['keyword' => 'api-test-page']);
    $this->assertNotNull($page);
}
```

### 3. **Transaction Rollback**
Use database transactions that rollback after each test:

```php
<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Start transaction for each test
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback transaction after each test
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        parent::tearDown();
    }
}
```

## 🔧 Test Base Classes

### ApiTestCase - For API Testing
```php
<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;

abstract class ApiTestCase extends WebTestCase
{
    protected $client;
    protected $entityManager;
    
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();
            
        // Start transaction
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback transaction
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        parent::tearDown();
    }

    /**
     * Create test user and return JWT token
     */
    protected function getAuthToken(?User $user = null): string
    {
        if (!$user) {
            $user = $this->createTestUser();
        }

        $jwtService = static::getContainer()->get('App\Service\Auth\JWTService');
        return $jwtService->createToken($user);
    }

    /**
     * Create test user with admin permissions
     */
    protected function createTestUser(array $userData = []): User
    {
        $user = new User();
        $user->setUsername($userData['username'] ?? 'test_user_' . uniqid());
        $user->setEmail($userData['email'] ?? 'test_' . uniqid() . '@example.com');
        $user->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        $user->setIsActive(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Add to admin group
        $adminGroup = $this->getAdminGroup();
        $userGroup = new UsersGroup();
        $userGroup->setUser($user);
        $userGroup->setGroup($adminGroup);
        
        $this->entityManager->persist($userGroup);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Get or create admin group
     */
    protected function getAdminGroup(): Group
    {
        $group = $this->entityManager->getRepository(Group::class)
            ->findOneBy(['name' => 'admin']);
            
        if (!$group) {
            $group = new Group();
            $group->setName('admin');
            $group->setDescription('Administrator group');
            $group->setIsActive(true);
            
            $this->entityManager->persist($group);
            $this->entityManager->flush();
        }
        
        return $group;
    }

    /**
     * Assert JSON response structure
     */
    protected function assertJsonResponse(Response $response, int $expectedStatus = 200): array
    {
        $this->assertResponseStatusCodeSame($expectedStatus);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $content = $response->getContent();
        $this->assertJson($content);
        
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        
        // Verify standard response envelope
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('logged_in', $data);
        $this->assertArrayHasKey('meta', $data);
        
        $this->assertEquals($expectedStatus, $data['status']);
        
        return $data;
    }

    /**
     * Assert error response structure
     */
    protected function assertErrorResponse(Response $response, int $expectedStatus, string $expectedError = null): array
    {
        $data = $this->assertJsonResponse($response, $expectedStatus);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertNotNull($data['error']);
        
        if ($expectedError) {
            $this->assertStringContainsString($expectedError, $data['error']);
        }
        
        return $data;
    }
}
```

### ServiceTestCase - For Service Testing
```php
<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class ServiceTestCase extends KernelTestCase
{
    protected $entityManager;
    protected $container;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $this->entityManager = $this->container
            ->get('doctrine')
            ->getManager();

        // Start transaction
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback transaction
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }

        parent::tearDown();
    }

    /**
     * Get service from container
     */
    protected function getService(string $serviceClass)
    {
        return $this->container->get($serviceClass);
    }

    /**
     * Create test entities with realistic data
     */
    protected function createTestPage(array $data = []): Page
    {
        $page = new Page();
        $page->setKeyword($data['keyword'] ?? 'test-page-' . uniqid());
        $page->setUrl($data['url'] ?? '/test-page');
        $page->setProtocol($data['protocol'] ?? 'https');
        $page->setIsHeadless($data['isHeadless'] ?? false);
        $page->setIsOpenAccess($data['isOpenAccess'] ?? false);
        $page->setIsSystem($data['isSystem'] ?? false);

        // Set required relationships
        $pageType = $this->getTestPageType();
        $page->setPageType($pageType);

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    protected function getTestPageType(): PageType
    {
        $pageType = $this->entityManager->getRepository(PageType::class)
            ->findOneBy(['name' => 'test']);
            
        if (!$pageType) {
            $pageType = new PageType();
            $pageType->setName('test');
            $pageType->setDescription('Test page type');
            $pageType->setIsActive(true);
            
            $this->entityManager->persist($pageType);
            $this->entityManager->flush();
        }
        
        return $pageType;
    }
}
```

## 📊 Test Categories

### 1. Unit Tests - Service Layer
```php
<?php
namespace App\Tests\Unit\Service\CMS\Admin;

use App\Tests\ServiceTestCase;
use App\Service\CMS\Admin\AdminPageService;

class AdminPageServiceTest extends ServiceTestCase
{
    private AdminPageService $adminPageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminPageService = $this->getService(AdminPageService::class);
    }

    public function testCreatePage(): void
    {
        $pageData = [
            'keyword' => 'test-service-page',
            'pageType' => $this->getTestPageType()->getId(),
            'url' => '/test-service-page',
            'isHeadless' => false,
            'isOpenAccess' => true
        ];

        $page = $this->adminPageService->createPage($pageData);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('test-service-page', $page->getKeyword());
        $this->assertEquals('/test-service-page', $page->getUrl());
        $this->assertTrue($page->getIsOpenAccess());

        // Verify persistence
        $savedPage = $this->entityManager->find(Page::class, $page->getId());
        $this->assertNotNull($savedPage);
        $this->assertEquals('test-service-page', $savedPage->getKeyword());
    }

    public function testUpdatePage(): void
    {
        $page = $this->createTestPage(['keyword' => 'original-keyword']);
        $originalId = $page->getId();

        $updateData = [
            'keyword' => 'updated-keyword',
            'url' => '/updated-url',
            'isHeadless' => true
        ];

        $updatedPage = $this->adminPageService->updatePage($page, $updateData);

        $this->assertEquals($originalId, $updatedPage->getId());
        $this->assertEquals('updated-keyword', $updatedPage->getKeyword());
        $this->assertEquals('/updated-url', $updatedPage->getUrl());
        $this->assertTrue($updatedPage->getIsHeadless());

        // Verify in database
        $this->entityManager->refresh($updatedPage);
        $this->assertEquals('updated-keyword', $updatedPage->getKeyword());
    }

    public function testDeletePage(): void
    {
        $page = $this->createTestPage();
        $pageId = $page->getId();

        $result = $this->adminPageService->deletePage($pageId);

        $this->assertTrue($result);

        // Verify deletion
        $deletedPage = $this->entityManager->find(Page::class, $pageId);
        $this->assertNull($deletedPage);
    }

    public function testCreatePageWithInvalidData(): void
    {
        $this->expectException(\Exception::class);

        $pageData = [
            'keyword' => '', // Invalid: empty keyword
            'pageType' => 999999 // Invalid: non-existent page type
        ];

        $this->adminPageService->createPage($pageData);
    }
}
```

### 2. Integration Tests - Controller Layer
```php
<?php
namespace App\Tests\Integration\Controller\Api\V1\Admin;

use App\Tests\ApiTestCase;

class AdminPageControllerTest extends ApiTestCase
{
    public function testGetPages(): void
    {
        // Create test data
        $this->createTestPage(['keyword' => 'page1']);
        $this->createTestPage(['keyword' => 'page2']);

        $response = $this->client->request('GET', '/cms-api/v1/admin/pages', [
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken()]
        ]);

        $data = $this->assertJsonResponse($response, 200);
        
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertGreaterThanOrEqual(2, count($data['data']));
        
        // Verify response structure
        foreach ($data['data'] as $page) {
            $this->assertArrayHasKey('id', $page);
            $this->assertArrayHasKey('keyword', $page);
            $this->assertArrayHasKey('url', $page);
        }
    }

    public function testCreatePage(): void
    {
        $pageData = [
            'keyword' => 'integration-test-page',
            'pageType' => $this->getTestPageType()->getId(),
            'url' => '/integration-test-page',
            'isHeadless' => false,
            'navPosition' => 5,
            'isOpenAccess' => true
        ];

        $response = $this->client->request('POST', '/cms-api/v1/admin/pages', [
            'json' => $pageData,
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken()]
        ]);

        $data = $this->assertJsonResponse($response, 201);
        
        $this->assertArrayHasKey('data', $data);
        $page = $data['data'];
        
        $this->assertEquals('integration-test-page', $page['keyword']);
        $this->assertEquals('/integration-test-page', $page['url']);
        $this->assertFalse($page['isHeadless']);
        $this->assertEquals(5, $page['navPosition']);
        $this->assertTrue($page['isOpenAccess']);

        // Verify in database
        $savedPage = $this->entityManager->getRepository(Page::class)
            ->findOneBy(['keyword' => 'integration-test-page']);
        $this->assertNotNull($savedPage);
    }

    public function testCreatePageWithValidationErrors(): void
    {
        $invalidPageData = [
            'keyword' => '', // Invalid: empty
            'pageType' => 'invalid', // Invalid: should be integer
            'url' => 'invalid-url', // Invalid: should start with /
            'navPosition' => -1 // Invalid: should be positive
        ];

        $response = $this->client->request('POST', '/cms-api/v1/admin/pages', [
            'json' => $invalidPageData,
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken()]
        ]);

        $data = $this->assertErrorResponse($response, 400, 'Validation failed');
        
        $this->assertArrayHasKey('error_details', $data);
        $this->assertArrayHasKey('validation_errors', $data['error_details']);
        $this->assertIsArray($data['error_details']['validation_errors']);
        $this->assertNotEmpty($data['error_details']['validation_errors']);
    }

    public function testUpdatePage(): void
    {
        $page = $this->createTestPage(['keyword' => 'update-test']);

        $updateData = [
            'keyword' => 'updated-test-page',
            'url' => '/updated-test-page',
            'isHeadless' => true
        ];

        $response = $this->client->request('PUT', "/cms-api/v1/admin/pages/{$page->getKeyword()}", [
            'json' => $updateData,
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken()]
        ]);

        $data = $this->assertJsonResponse($response, 200);
        
        $updatedPage = $data['data'];
        $this->assertEquals('updated-test-page', $updatedPage['keyword']);
        $this->assertEquals('/updated-test-page', $updatedPage['url']);
        $this->assertTrue($updatedPage['isHeadless']);
    }

    public function testDeletePage(): void
    {
        $page = $this->createTestPage(['keyword' => 'delete-test']);

        $response = $this->client->request('DELETE', "/cms-api/v1/admin/pages/{$page->getKeyword()}", [
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken()]
        ]);

        $this->assertResponseStatusCodeSame(204);

        // Verify deletion
        $deletedPage = $this->entityManager->find(Page::class, $page->getId());
        $this->assertNull($deletedPage);
    }

    public function testUnauthorizedAccess(): void
    {
        $response = $this->client->request('GET', '/cms-api/v1/admin/pages');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testInsufficientPermissions(): void
    {
        // Create user without admin permissions
        $limitedUser = $this->createTestUser(['username' => 'limited_user']);
        // Don't add to admin group

        $response = $this->client->request('GET', '/cms-api/v1/admin/pages', [
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken($limitedUser)]
        ]);

        $this->assertResponseStatusCodeSame(403);
    }
}
```

### 3. Database Tests - Repository Layer
```php
<?php
namespace App\Tests\Unit\Repository;

use App\Tests\ServiceTestCase;
use App\Repository\PageRepository;

class PageRepositoryTest extends ServiceTestCase
{
    private PageRepository $pageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRepository = $this->entityManager->getRepository(Page::class);
    }

    public function testFindByKeyword(): void
    {
        $page = $this->createTestPage(['keyword' => 'repo-test-page']);

        $foundPage = $this->pageRepository->findOneBy(['keyword' => 'repo-test-page']);

        $this->assertNotNull($foundPage);
        $this->assertEquals($page->getId(), $foundPage->getId());
        $this->assertEquals('repo-test-page', $foundPage->getKeyword());
    }

    public function testFindPagesWithSections(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection();
        $this->addSectionToPage($page, $section);

        $pagesWithSections = $this->pageRepository->findPagesWithSections();

        $this->assertNotEmpty($pagesWithSections);
        
        $foundPage = null;
        foreach ($pagesWithSections as $pageWithSection) {
            if ($pageWithSection->getId() === $page->getId()) {
                $foundPage = $pageWithSection;
                break;
            }
        }
        
        $this->assertNotNull($foundPage);
    }

    public function testFindActivePages(): void
    {
        $activePage = $this->createTestPage(['keyword' => 'active-page']);
        $inactivePage = $this->createTestPage(['keyword' => 'inactive-page']);
        
        // Make one page inactive (assuming there's an isActive field)
        // This would depend on your actual Page entity structure

        $activePages = $this->pageRepository->findActivePages();

        $this->assertNotEmpty($activePages);
        
        $activeKeywords = array_map(fn($page) => $page->getKeyword(), $activePages);
        $this->assertContains('active-page', $activeKeywords);
    }
}
```

### 4. Security Tests
```php
<?php
namespace App\Tests\Integration\Security;

use App\Tests\ApiTestCase;

class ACLSecurityTest extends ApiTestCase
{
    public function testPageAccessControl(): void
    {
        $user = $this->createTestUser();
        $page = $this->createTestPage();
        
        // Test without ACL permissions
        $response = $this->client->request('PUT', "/cms-api/v1/admin/pages/{$page->getKeyword()}", [
            'json' => ['keyword' => 'updated-keyword'],
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken($user)]
        ]);
        
        $this->assertResponseStatusCodeSame(403);
        
        // Grant ACL permissions
        $aclService = $this->getService('App\Service\ACL\ACLService');
        $aclService->setUserPagePermissions($user->getId(), $page->getId(), true, false, true, false);
        
        // Test with ACL permissions
        $response = $this->client->request('PUT', "/cms-api/v1/admin/pages/{$page->getKeyword()}", [
            'json' => ['keyword' => 'updated-keyword'],
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken($user)]
        ]);
        
        $this->assertResponseIsSuccessful();
    }

    public function testJWTTokenValidation(): void
    {
        // Test with invalid token
        $response = $this->client->request('GET', '/cms-api/v1/admin/pages', [
            'headers' => ['Authorization' => 'Bearer invalid_token']
        ]);
        
        $this->assertResponseStatusCodeSame(401);
        
        // Test with expired token (would need to create expired token)
        // This would require mocking the JWT service or creating an actually expired token
    }

    public function testRoutePermissions(): void
    {
        $user = $this->createTestUser();
        
        // Remove admin permissions
        $this->removeUserFromAdminGroup($user);
        
        $response = $this->client->request('GET', '/cms-api/v1/admin/pages', [
            'headers' => ['Authorization' => 'Bearer ' . $this->getAuthToken($user)]
        ]);
        
        $this->assertResponseStatusCodeSame(403);
    }
}
```

## 🔧 Test Data Management

### Test Fixtures
```php
<?php
namespace App\Tests\Fixtures;

class TestDataFactory
{
    public static function createPageData(array $overrides = []): array
    {
        return array_merge([
            'keyword' => 'test-page-' . uniqid(),
            'pageType' => 1,
            'url' => '/test-page',
            'protocol' => 'https',
            'isHeadless' => false,
            'navPosition' => null,
            'footerPosition' => null,
            'isOpenAccess' => false,
            'isSystem' => false
        ], $overrides);
    }

    public static function createUserData(array $overrides = []): array
    {
        return array_merge([
            'username' => 'test_user_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => 'password123',
            'isActive' => true
        ], $overrides);
    }

    public static function createSectionData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'test-section-' . uniqid(),
            'styleId' => 1,
            'position' => 1
        ], $overrides);
    }
}
```

### Database Cleanup
```php
<?php
namespace App\Tests\Traits;

trait DatabaseCleanupTrait
{
    protected function cleanupTestData(): void
    {
        // Clean up test entities in reverse dependency order
        $this->entityManager->createQuery('DELETE FROM App\Entity\PagesSection ps WHERE ps.page IN (SELECT p FROM App\Entity\Page p WHERE p.keyword LIKE :pattern)')
            ->setParameter('pattern', 'test-%')
            ->execute();
            
        $this->entityManager->createQuery('DELETE FROM App\Entity\Page p WHERE p.keyword LIKE :pattern')
            ->setParameter('pattern', 'test-%')
            ->execute();
            
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u WHERE u.username LIKE :pattern')
            ->setParameter('pattern', 'test_user_%')
            ->execute();
    }
}
```

## 📊 Test Coverage

### Coverage Configuration
```xml
<!-- phpunit.xml.dist coverage section -->
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">src</directory>
    </include>
    <exclude>
        <directory>src/DataFixtures</directory>
        <file>src/Kernel.php</file>
    </exclude>
    <report>
        <html outputDirectory="var/coverage" lowUpperBound="35" highLowerBound="70"/>
        <text outputFile="php://stdout" showUncoveredFiles="false"/>
        <clover outputFile="var/coverage/clover.xml"/>
    </report>
</coverage>
```

### Coverage Goals
- **Services**: 90%+ coverage
- **Controllers**: 85%+ coverage  
- **Entities**: 80%+ coverage
- **Repositories**: 85%+ coverage
- **Overall**: 85%+ coverage

## 🚀 Running Tests

### Command Line Usage
```bash
# Run all tests
php bin/phpunit

# Run specific test suite
php bin/phpunit --testsuite=Unit
php bin/phpunit --testsuite=Integration
php bin/phpunit --testsuite=Api

# Run specific test class
php bin/phpunit tests/Unit/Service/CMS/Admin/AdminPageServiceTest.php

# Run with coverage
php bin/phpunit --coverage-html var/coverage

# Run with specific configuration
php bin/phpunit -c phpunit.xml.dist

# Run tests in parallel (if configured)
php bin/phpunit --parallel=4
```

### Continuous Integration
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: selfhelp_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          extensions: pdo_mysql, intl, zip
          
      - name: Install dependencies
        run: composer install --prefer-dist --no-progress
        
      - name: Setup test database
        run: |
          php bin/console doctrine:database:create --env=test
          php bin/console doctrine:schema:update --force --env=test
          
      - name: Run tests
        run: php bin/phpunit --coverage-clover=coverage.xml
        
      - name: Upload coverage
        uses: codecov/codecov-action@v1
        with:
          file: ./coverage.xml
```

## 🎯 Best Practices

### 1. Test Naming
- Use descriptive test method names
- Follow pattern: `test{Action}{Scenario}{ExpectedResult}`
- Examples: `testCreatePageWithValidData`, `testUpdatePageWithInvalidKeyword`

### 2. Test Structure (AAA Pattern)
```php
public function testCreatePage(): void
{
    // Arrange
    $pageData = TestDataFactory::createPageData(['keyword' => 'test-page']);
    
    // Act
    $page = $this->adminPageService->createPage($pageData);
    
    // Assert
    $this->assertInstanceOf(Page::class, $page);
    $this->assertEquals('test-page', $page->getKeyword());
}
```

### 3. Test Independence
- Each test should be independent
- Use transactions for automatic cleanup
- Don't rely on test execution order
- Create fresh test data for each test

### 4. Realistic Test Data
- Use realistic data that matches production scenarios
- Test edge cases and boundary conditions
- Include both positive and negative test cases
- Test error conditions and exception handling

### 5. Performance Testing
```php
public function testPageCreationPerformance(): void
{
    $startTime = microtime(true);
    
    for ($i = 0; $i < 100; $i++) {
        $pageData = TestDataFactory::createPageData(['keyword' => 'perf-test-' . $i]);
        $this->adminPageService->createPage($pageData);
    }
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    
    // Assert reasonable performance (adjust threshold as needed)
    $this->assertLessThan(5.0, $duration, 'Creating 100 pages should take less than 5 seconds');
}
```

---

**Next**: [Deployment Process](./16-deployment-process.md)