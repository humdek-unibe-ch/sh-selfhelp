<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Role;
use App\Entity\Permission;

class AdminRoleControllerTest extends BaseControllerTest
{
    private ?int $testRoleId = null;
    private string $testRoleName = 'Test Role for API Tests';
    private array $testPermissionIds = [];
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->createTestPermissions();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestRole();
        parent::tearDown();
    }

    private function createTestPermissions(): void
    {
        // Create test permissions if they don't exist
        $permissionNames = ['test_permission_1', 'test_permission_2', 'test_permission_3'];
        
        foreach ($permissionNames as $name) {
            $permission = $this->entityManager->getRepository(Permission::class)
                ->findOneBy(['name' => $name]);
            
            if (!$permission) {
                $permission = new Permission();
                $permission->setName($name);
                $permission->setDescription('Test permission: ' . $name);
                $this->entityManager->persist($permission);
            }
            $this->testPermissionIds[] = $permission->getId();
        }
        
        $this->entityManager->flush();
    }

    private function cleanupTestRole(): void
    {
        if ($this->testRoleId) {
            $role = $this->entityManager->getRepository(Role::class)->find($this->testRoleId);
            if ($role) {
                $this->entityManager->remove($role);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @group role-management
     */
    public function testGetRolesListSuccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        
        // Validate response structure
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('roles', $data['data']);
        $this->assertArrayHasKey('pagination', $data['data']);
        
        // Validate pagination structure
        $pagination = $data['data']['pagination'];
        $this->assertArrayHasKey('page', $pagination);
        $this->assertArrayHasKey('pageSize', $pagination);
        $this->assertArrayHasKey('totalCount', $pagination);
        $this->assertArrayHasKey('totalPages', $pagination);
        $this->assertArrayHasKey('hasNext', $pagination);
        $this->assertArrayHasKey('hasPrevious', $pagination);
        
        // Validate roles array
        $this->assertIsArray($data['data']['roles']);
        
        if (!empty($data['data']['roles'])) {
            $role = $data['data']['roles'][0];
            $this->assertArrayHasKey('id', $role);
            $this->assertArrayHasKey('name', $role);
            $this->assertArrayHasKey('description', $role);
            $this->assertArrayHasKey('permission_count', $role);
            $this->assertArrayHasKey('user_count', $role);
        }
    }

    /**
     * @group role-management
     */
    public function testGetRolesListWithPagination(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles?page=1&pageSize=5',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertLessThanOrEqual(5, count($data['data']['roles']));
        $this->assertSame(1, $data['data']['pagination']['page']);
        $this->assertSame(5, $data['data']['pagination']['pageSize']);
    }

    /**
     * @group role-management
     */
    public function testGetRolesListWithSearch(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles?search=admin',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('roles', $data['data']);
    }

    /**
     * @group role-management
     */
    public function testCreateRoleSuccess(): void
    {
        $roleData = [
            'name' => $this->testRoleName,
            'description' => 'Test role description'
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/roles',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($roleData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        
        $role = $data['data'];
        $this->assertArrayHasKey('id', $role);
        $this->assertArrayHasKey('name', $role);
        $this->assertArrayHasKey('description', $role);
        $this->assertArrayHasKey('permission_count', $role);
        $this->assertArrayHasKey('user_count', $role);
        $this->assertArrayHasKey('permissions', $role);
        $this->assertArrayHasKey('users', $role);
        
        $this->assertSame($roleData['name'], $role['name']);
        $this->assertSame($roleData['description'], $role['description']);
        
        // Store the created role ID for other tests
        $this->testRoleId = $role['id'];
    }

    /**
     * @group role-management
     */
    public function testGetRoleByIdSuccess(): void
    {
        // First create a role for this test
        $this->testCreateRoleSuccess();
        
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles/' . $this->testRoleId,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $role = $data['data'];
        
        $this->assertSame($this->testRoleId, $role['id']);
        $this->assertSame($this->testRoleName, $role['name']);
        $this->assertArrayHasKey('permissions', $role);
        $this->assertArrayHasKey('users', $role);
        $this->assertIsArray($role['permissions']);
        $this->assertIsArray($role['users']);
    }

    /**
     * @group role-management
     */
    public function testUpdateRoleSuccess(): void
    {
        // First create a role for this test
        $this->testCreateRoleSuccess();
        
        $updateData = [
            'name' => $this->testRoleName . ' Updated',
            'description' => 'Updated description'
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/roles/' . $this->testRoleId,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $role = $data['data'];
        
        $this->assertSame($updateData['name'], $role['name']);
        $this->assertSame($updateData['description'], $role['description']);
    }

    /**
     * @group role-management
     */
    public function testGetRolePermissionsSuccess(): void
    {
        // First create a role for this test
        $this->testCreateRoleSuccess();
        
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles/' . $this->testRoleId . '/permissions',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('permissions', $data['data']);
        $this->assertIsArray($data['data']['permissions']);
    }

    /**
     * @group role-management
     */
    public function testAddPermissionsToRoleSuccess(): void
    {
        // First create a role for this test
        $this->testCreateRoleSuccess();
        
        $permissionData = [
            'permission_ids' => array_slice($this->testPermissionIds, 0, 2) // Add first 2 permissions
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/roles/' . $this->testRoleId . '/permissions',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($permissionData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('permissions', $data['data']);
        $this->assertCount(2, $data['data']['permissions']);
    }

    /**
     * @group role-management
     */
    public function testRemovePermissionsFromRoleSuccess(): void
    {
        // First create a role and add permissions
        $this->testAddPermissionsToRoleSuccess();
        
        $permissionData = [
            'permission_ids' => array_slice($this->testPermissionIds, 0, 1) // Remove first permission
        ];

        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/roles/' . $this->testRoleId . '/permissions',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($permissionData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('permissions', $data['data']);
        $this->assertCount(1, $data['data']['permissions']); // Should have 1 permission left
    }

    /**
     * @group role-management
     */
    public function testUpdateRolePermissionsSuccess(): void
    {
        // First create a role for this test
        $this->testCreateRoleSuccess();
        
        $permissionData = [
            'permission_ids' => $this->testPermissionIds // All test permissions
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/roles/' . $this->testRoleId . '/permissions',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($permissionData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('permissions', $data['data']);
        $this->assertCount(3, $data['data']['permissions']); // Should have all 3 permissions
    }

    /**
     * @group role-management
     */
    public function testCreateRoleWithDuplicateName(): void
    {
        // First create a role
        $this->testCreateRoleSuccess();
        
        // Try to create another role with the same name
        $roleData = [
            'name' => $this->testRoleName, // Same name
            'description' => 'Another test role'
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/roles',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($roleData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group role-management
     */
    public function testCreateRoleWithMissingName(): void
    {
        $roleData = [
            'description' => 'Test role without name'
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/roles',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($roleData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group role-management
     */
    public function testGetNonExistentRole(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles/99999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @group role-management
     */
    public function testDeleteRoleSuccess(): void
    {
        // First create a role for this test
        $this->testCreateRoleSuccess();
        
        $roleIdToDelete = $this->testRoleId;

        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/roles/' . $roleIdToDelete,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Verify the role is deleted
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles/' . $roleIdToDelete,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        
        // Clear the testRoleId since it's deleted
        $this->testRoleId = null;
    }

    /**
     * @group role-management
     */
    public function testUnauthorizedAccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles'
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    private function getAdminRole(): array
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/roles?search=admin',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        return !empty($data['data']['roles']) ? $data['data']['roles'][0] : [];
    }
} 