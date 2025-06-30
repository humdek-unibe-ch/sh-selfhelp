<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;

class AdminUserControllerTest extends BaseControllerTest
{
    private int $testUserId;
    private string $testUserEmail = 'test.user@example.com';

    /**
     * @group user-management
     */
    public function testGetUsersListSuccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users',
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
        $this->assertArrayHasKey('users', $data['data']);
        $this->assertArrayHasKey('pagination', $data['data']);
        
        // Validate pagination structure
        $pagination = $data['data']['pagination'];
        $this->assertArrayHasKey('page', $pagination);
        $this->assertArrayHasKey('pageSize', $pagination);
        $this->assertArrayHasKey('totalCount', $pagination);
        $this->assertArrayHasKey('totalPages', $pagination);
        $this->assertArrayHasKey('hasNext', $pagination);
        $this->assertArrayHasKey('hasPrevious', $pagination);
        
        // Validate users array
        $this->assertIsArray($data['data']['users']);
        
        if (!empty($data['data']['users'])) {
            $user = $data['data']['users'][0];
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('email', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertArrayHasKey('last_login', $user);
            $this->assertArrayHasKey('status', $user);
            $this->assertArrayHasKey('blocked', $user);
            $this->assertArrayHasKey('code', $user);
            $this->assertArrayHasKey('groups', $user);
            $this->assertArrayHasKey('user_activity', $user);
            $this->assertArrayHasKey('user_type_code', $user);
            $this->assertArrayHasKey('user_type', $user);
        }
    }

    /**
     * @group user-management
     */
    public function testGetUsersListWithPagination(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users?page=1&pageSize=5',
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
        $this->assertLessThanOrEqual(5, count($data['data']['users']));
        $this->assertSame(1, $data['data']['pagination']['page']);
        $this->assertSame(5, $data['data']['pagination']['pageSize']);
    }

    /**
     * @group user-management
     */
    public function testGetUsersListWithSearch(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users?search=admin',
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
        $this->assertIsArray($data['data']['users']);
        
        // Verify search results contain the search term
        if (!empty($data['data']['users'])) {
            foreach ($data['data']['users'] as $user) {
                $containsSearch = stripos($user['email'], 'admin') !== false ||
                                stripos($user['name'], 'admin') !== false ||
                                stripos($user['user_name'] ?? '', 'admin') !== false;
                $this->assertTrue($containsSearch, 'Search result should contain search term');
            }
        }
    }

    /**
     * @group user-management
     */
    public function testGetUsersListWithSorting(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users?sort=email&sortDirection=asc',
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
        $this->assertIsArray($data['data']['users']);
        
        // Verify sorting - emails should be in ascending order
        if (count($data['data']['users']) > 1) {
            $emails = array_column($data['data']['users'], 'email');
            $sortedEmails = $emails;
            sort($sortedEmails);
            $this->assertSame($sortedEmails, $emails, 'Users should be sorted by email in ascending order');
        }
    }

    /**
     * @group user-management
     */
    public function testCreateUserSuccess(): void
    {
        $userData = [
            'email' => $this->testUserEmail,
            'name' => 'Test User',
            'user_name' => 'testuser',
            'password' => 'testpassword123',
            'blocked' => false,
            'validation_code' => 'TEST123'
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/users',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertSame($userData['email'], $data['data']['email']);
        $this->assertSame($userData['name'], $data['data']['name']);
        $this->assertSame($userData['user_name'], $data['data']['user_name']);
        $this->assertSame($userData['blocked'], $data['data']['blocked']);

        // Store the created user ID for cleanup
        $this->testUserId = $data['data']['id'];
    }

    /**
     * @group user-management
     */
    public function testCreateUserWithInvalidEmail(): void
    {
        $userData = [
            'email' => 'invalid-email',
            'name' => 'Test User',
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/users',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group user-management
     */
    public function testCreateUserWithMissingEmail(): void
    {
        $userData = [
            'name' => 'Test User',
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/users',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group user-management
     * @depends testCreateUserSuccess
     */
    public function testGetUserByIdSuccess(): void
    {
        if (!isset($this->testUserId)) {
            $this->markTestSkipped('Test user not created');
        }

        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users/' . $this->testUserId,
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
        $this->assertSame($this->testUserId, $data['data']['id']);
        $this->assertSame($this->testUserEmail, $data['data']['email']);
        
        // Verify detail view includes additional fields
        $this->assertArrayHasKey('groups', $data['data']);
        $this->assertArrayHasKey('roles', $data['data']);
        $this->assertIsArray($data['data']['groups']);
        $this->assertIsArray($data['data']['roles']);
    }

    /**
     * @group user-management
     */
    public function testGetUserByIdNotFound(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users/999999',
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
     * @group user-management
     * @depends testCreateUserSuccess
     */
    public function testUpdateUserSuccess(): void
    {
        if (!isset($this->testUserId)) {
            $this->markTestSkipped('Test user not created');
        }

        $updateData = [
            'name' => 'Updated Test User',
            'blocked' => true
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/users/' . $this->testUserId,
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
        $this->assertArrayHasKey('data', $data);
        $this->assertSame($updateData['name'], $data['data']['name']);
        $this->assertSame($updateData['blocked'], $data['data']['blocked']);
    }

    /**
     * @group user-management
     * @depends testCreateUserSuccess
     */
    public function testToggleUserBlockSuccess(): void
    {
        if (!isset($this->testUserId)) {
            $this->markTestSkipped('Test user not created');
        }

        $this->client->request(
            'PATCH',
            '/cms-api/v1/admin/users/' . $this->testUserId . '/block',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode(['blocked' => false])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertSame(false, $data['data']['blocked']);
    }

    /**
     * @group user-management
     * @depends testCreateUserSuccess
     */
    public function testGetUserGroupsSuccess(): void
    {
        if (!isset($this->testUserId)) {
            $this->markTestSkipped('Test user not created');
        }

        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users/' . $this->testUserId . '/groups',
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
        $this->assertIsArray($data['data']);
    }

    /**
     * @group user-management
     * @depends testCreateUserSuccess
     */
    public function testGetUserRolesSuccess(): void
    {
        if (!isset($this->testUserId)) {
            $this->markTestSkipped('Test user not created');
        }

        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users/' . $this->testUserId . '/roles',
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
        $this->assertIsArray($data['data']);
    }

    /**
     * @group user-management
     */
    public function testUnauthorizedAccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @group user-management
     * @depends testCreateUserSuccess
     */
    public function testDeleteUserSuccess(): void
    {
        if (!isset($this->testUserId)) {
            $this->markTestSkipped('Test user not created');
        }

        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/users/' . $this->testUserId,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        // Verify user is deleted
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users/' . $this->testUserId,
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
     * @group user-management
     */
    public function testDeleteSystemUserForbidden(): void
    {
        // Try to delete admin user (should be forbidden)
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/users?search=admin',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        if (!empty($data['data']['users'])) {
            $adminUser = null;
            foreach ($data['data']['users'] as $user) {
                if ($user['name'] === 'admin' || $user['name'] === 'tpf') {
                    $adminUser = $user;
                    break;
                }
            }

            if ($adminUser) {
                $this->client->request(
                    'DELETE',
                    '/cms-api/v1/admin/users/' . $adminUser['id'],
                    [],
                    [],
                    [
                        'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                        'CONTENT_TYPE' => 'application/json'
                    ]
                );

                $response = $this->client->getResponse();
                $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
            }
        }
    }

    /**
     * @group user-management
     */
    public function testCreateUserWithDuplicateEmail(): void
    {
        // First create a user
        $userData = [
            'email' => 'duplicate.test@example.com',
            'name' => 'Duplicate Test User',
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/users',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $createdUserId = $data['data']['id'];

        // Try to create another user with the same email
        $this->client->request(
            'POST',
            '/cms-api/v1/admin/users',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // Cleanup
        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/users/' . $createdUserId,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );
    }

    protected function tearDown(): void
    {
        // Cleanup any remaining test data
        if (isset($this->testUserId)) {
            $this->client->request(
                'DELETE',
                '/cms-api/v1/admin/users/' . $this->testUserId,
                [],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                    'CONTENT_TYPE' => 'application/json'
                ]
            );
        }
        
        parent::tearDown();
    }
} 