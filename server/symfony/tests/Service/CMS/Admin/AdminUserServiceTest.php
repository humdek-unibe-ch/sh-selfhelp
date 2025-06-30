<?php

namespace App\Tests\Service\CMS\Admin;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Lookup;
use App\Service\CMS\Admin\AdminUserService;
use App\Service\Auth\UserContextService;
use App\Repository\UserRepository;
use App\Repository\LookupRepository;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminUserServiceTest extends KernelTestCase
{
    private AdminUserService $adminUserService;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private LookupRepository $lookupRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private UserContextService $userContextService;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->lookupRepository = $container->get(LookupRepository::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->userContextService = $container->get(UserContextService::class);

        $this->adminUserService = new AdminUserService(
            $this->userContextService,
            $this->entityManager,
            $this->userRepository,
            $this->lookupRepository,
            $this->passwordHasher
        );
    }

    /**
     * @group user-service
     */
    public function testGetUsersWithDefaultParameters(): void
    {
        $result = $this->adminUserService->getUsers();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertIsArray($result['users']);
        $this->assertIsArray($result['pagination']);

        // Check pagination structure
        $pagination = $result['pagination'];
        $this->assertArrayHasKey('page', $pagination);
        $this->assertArrayHasKey('pageSize', $pagination);
        $this->assertArrayHasKey('totalCount', $pagination);
        $this->assertArrayHasKey('totalPages', $pagination);
        $this->assertArrayHasKey('hasNext', $pagination);
        $this->assertArrayHasKey('hasPrevious', $pagination);

        $this->assertSame(1, $pagination['page']);
        $this->assertSame(20, $pagination['pageSize']);
        $this->assertFalse($pagination['hasPrevious']);
    }

    /**
     * @group user-service
     */
    public function testGetUsersWithPagination(): void
    {
        $result = $this->adminUserService->getUsers(1, 5);

        $this->assertLessThanOrEqual(5, count($result['users']));
        $this->assertSame(1, $result['pagination']['page']);
        $this->assertSame(5, $result['pagination']['pageSize']);
    }

    /**
     * @group user-service
     */
    public function testGetUsersWithSearch(): void
    {
        $result = $this->adminUserService->getUsers(1, 20, 'admin');

        $this->assertIsArray($result['users']);
        
        // If there are results, verify they contain the search term
        foreach ($result['users'] as $user) {
            $containsSearch = stripos($user['email'], 'admin') !== false ||
                            stripos($user['name'], 'admin') !== false ||
                            stripos($user['user_name'] ?? '', 'admin') !== false;
            $this->assertTrue($containsSearch, 'Search result should contain search term');
        }
    }

    /**
     * @group user-service
     */
    public function testGetUsersWithSorting(): void
    {
        $result = $this->adminUserService->getUsers(1, 20, null, 'email', 'asc');

        $this->assertIsArray($result['users']);
        
        if (count($result['users']) > 1) {
            $emails = array_column($result['users'], 'email');
            $sortedEmails = $emails;
            sort($sortedEmails);
            $this->assertSame($sortedEmails, $emails, 'Users should be sorted by email in ascending order');
        }
    }

    /**
     * @group user-service
     */
    public function testCreateUserSuccess(): void
    {
        $userData = [
            'email' => 'test.service@example.com',
            'name' => 'Test Service User',
            'user_name' => 'testserviceuser',
            'password' => 'testpassword123',
            'blocked' => false
        ];

        $result = $this->adminUserService->createUser($userData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertSame($userData['email'], $result['email']);
        $this->assertSame($userData['name'], $result['name']);
        $this->assertSame($userData['user_name'], $result['user_name']);
        $this->assertSame($userData['blocked'], $result['blocked']);

        // Cleanup
        $this->adminUserService->deleteUser($result['id']);
    }

    /**
     * @group user-service
     */
    public function testCreateUserWithMissingEmailThrowsException(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Email is required');
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $userData = [
            'name' => 'Test User',
            'password' => 'testpassword123'
        ];

        $this->adminUserService->createUser($userData);
    }

    /**
     * @group user-service
     */
    public function testCreateUserWithDuplicateEmailThrowsException(): void
    {
        // First create a user
        $userData = [
            'email' => 'duplicate.service@example.com',
            'name' => 'Duplicate Service User',
            'password' => 'testpassword123'
        ];

        $result = $this->adminUserService->createUser($userData);
        $userId = $result['id'];

        // Try to create another user with the same email
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Email already exists');
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        try {
            $this->adminUserService->createUser($userData);
        } finally {
            // Cleanup
            $this->adminUserService->deleteUser($userId);
        }
    }

    /**
     * @group user-service
     */
    public function testGetUserByIdSuccess(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'get.test@example.com',
            'name' => 'Get Test User',
            'password' => 'testpassword123'
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        $result = $this->adminUserService->getUserById($userId);

        $this->assertIsArray($result);
        $this->assertSame($userId, $result['id']);
        $this->assertSame($userData['email'], $result['email']);
        $this->assertSame($userData['name'], $result['name']);
        
        // Detail view should include groups and roles
        $this->assertArrayHasKey('groups', $result);
        $this->assertArrayHasKey('roles', $result);
        $this->assertIsArray($result['groups']);
        $this->assertIsArray($result['roles']);

        // Cleanup
        $this->adminUserService->deleteUser($userId);
    }

    /**
     * @group user-service
     */
    public function testGetUserByIdNotFoundThrowsException(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        $this->adminUserService->getUserById(999999);
    }

    /**
     * @group user-service
     */
    public function testUpdateUserSuccess(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'update.test@example.com',
            'name' => 'Update Test User',
            'password' => 'testpassword123',
            'blocked' => false
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        // Update the user
        $updateData = [
            'name' => 'Updated Test User',
            'blocked' => true
        ];

        $result = $this->adminUserService->updateUser($userId, $updateData);

        $this->assertIsArray($result);
        $this->assertSame($userId, $result['id']);
        $this->assertSame($updateData['name'], $result['name']);
        $this->assertSame($updateData['blocked'], $result['blocked']);
        $this->assertSame($userData['email'], $result['email']); // Email should remain unchanged

        // Cleanup
        $this->adminUserService->deleteUser($userId);
    }

    /**
     * @group user-service
     */
    public function testUpdateUserNotFoundThrowsException(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        $this->adminUserService->updateUser(999999, ['name' => 'Test']);
    }

    /**
     * @group user-service
     */
    public function testToggleUserBlockSuccess(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'block.test@example.com',
            'name' => 'Block Test User',
            'password' => 'testpassword123',
            'blocked' => false
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        // Block the user
        $result = $this->adminUserService->toggleUserBlock($userId, true);
        $this->assertTrue($result['blocked']);

        // Unblock the user
        $result = $this->adminUserService->toggleUserBlock($userId, false);
        $this->assertFalse($result['blocked']);

        // Cleanup
        $this->adminUserService->deleteUser($userId);
    }

    /**
     * @group user-service
     */
    public function testDeleteUserSuccess(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'delete.test@example.com',
            'name' => 'Delete Test User',
            'password' => 'testpassword123'
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        // Delete the user
        $result = $this->adminUserService->deleteUser($userId);
        $this->assertTrue($result);

        // Verify user is deleted
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('User not found');
        $this->adminUserService->getUserById($userId);
    }

    /**
     * @group user-service
     */
    public function testDeleteUserNotFoundThrowsException(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('User not found');
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        $this->adminUserService->deleteUser(999999);
    }

    /**
     * @group user-service
     */
    public function testDeleteSystemUserThrowsException(): void
    {
        // Try to find an admin user
        $users = $this->adminUserService->getUsers(1, 100, 'admin');
        $adminUser = null;
        
        foreach ($users['users'] as $user) {
            if ($user['name'] === 'admin' || $user['name'] === 'tpf') {
                $adminUser = $user;
                break;
            }
        }

        if ($adminUser) {
            $this->expectException(ServiceException::class);
            $this->expectExceptionMessage('Cannot delete system users');
            $this->expectExceptionCode(Response::HTTP_FORBIDDEN);

            $this->adminUserService->deleteUser($adminUser['id']);
        } else {
            $this->markTestSkipped('No admin user found to test system user deletion protection');
        }
    }

    /**
     * @group user-service
     */
    public function testGetUserGroupsSuccess(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'groups.test@example.com',
            'name' => 'Groups Test User',
            'password' => 'testpassword123'
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        $result = $this->adminUserService->getUserGroups($userId);
        $this->assertIsArray($result);

        // Cleanup
        $this->adminUserService->deleteUser($userId);
    }

    /**
     * @group user-service
     */
    public function testGetUserRolesSuccess(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'roles.test@example.com',
            'name' => 'Roles Test User',
            'password' => 'testpassword123'
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        $result = $this->adminUserService->getUserRoles($userId);
        $this->assertIsArray($result);

        // Cleanup
        $this->adminUserService->deleteUser($userId);
    }

    /**
     * @group user-service
     */
    public function testInvalidPageParametersGetNormalized(): void
    {
        // Test with invalid page (less than 1)
        $result = $this->adminUserService->getUsers(0, 20);
        $this->assertSame(1, $result['pagination']['page']);

        // Test with invalid pageSize (less than 1)
        $result = $this->adminUserService->getUsers(1, 0);
        $this->assertSame(20, $result['pagination']['pageSize']);

        // Test with pageSize too large (greater than 100)
        $result = $this->adminUserService->getUsers(1, 150);
        $this->assertSame(20, $result['pagination']['pageSize']);
    }

    /**
     * @group user-service
     */
    public function testInvalidSortDirectionGetsNormalized(): void
    {
        $result = $this->adminUserService->getUsers(1, 20, null, 'email', 'invalid');
        
        // Should still work and default to 'asc'
        $this->assertIsArray($result);
        $this->assertArrayHasKey('users', $result);
    }

    /**
     * @group user-service
     */
    public function testSendActivationMailReturnsTrue(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'activation.test@example.com',
            'name' => 'Activation Test User',
            'password' => 'testpassword123'
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        $result = $this->adminUserService->sendActivationMail($userId);
        $this->assertTrue($result);

        // Cleanup
        $this->adminUserService->deleteUser($userId);
    }

    /**
     * @group user-service
     */
    public function testCleanUserDataReturnsTrue(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'clean.test@example.com',
            'name' => 'Clean Test User',
            'password' => 'testpassword123'
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        $result = $this->adminUserService->cleanUserData($userId);
        $this->assertTrue($result);

        // Cleanup
        $this->adminUserService->deleteUser($userId);
    }

    /**
     * @group user-service
     */
    public function testImpersonateUserReturnsToken(): void
    {
        // Create a test user first
        $userData = [
            'email' => 'impersonate.test@example.com',
            'name' => 'Impersonate Test User',
            'password' => 'testpassword123'
        ];

        $createdUser = $this->adminUserService->createUser($userData);
        $userId = $createdUser['id'];

        $result = $this->adminUserService->impersonateUser($userId);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('impersonation_token', $result);

        // Cleanup
        $this->adminUserService->deleteUser($userId);
    }
} 