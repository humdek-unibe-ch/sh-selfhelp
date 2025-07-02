<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Group;
use App\Entity\Page;
use App\Entity\AclGroup;

class AdminGroupControllerTest extends BaseControllerTest
{
    private ?int $testGroupId = null;
    private string $testGroupName = 'Test Group for API Tests';
    private array $testPageIds = [];
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->createTestPages();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestGroup();
        parent::tearDown();
    }

    private function createTestPages(): void
    {
        // Get some existing pages for testing ACLs
        $pages = $this->entityManager->getRepository(Page::class)
            ->createQueryBuilder('p')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
        
        foreach ($pages as $page) {
            $this->testPageIds[] = $page->getId();
        }
        
        // If no pages exist, create test pages
        if (empty($this->testPageIds)) {
            for ($i = 1; $i <= 3; $i++) {
                $page = new Page();
                $page->setKeyword('test_page_' . $i);
                $page->setUrl('/test-page-' . $i);
                $this->entityManager->persist($page);
                $this->testPageIds[] = $page->getId();
            }
            $this->entityManager->flush();
        }
    }

    private function cleanupTestGroup(): void
    {
        if ($this->testGroupId) {
            $group = $this->entityManager->getRepository(Group::class)->find($this->testGroupId);
            if ($group) {
                // Remove ACLs first
                $acls = $this->entityManager->getRepository(AclGroup::class)
                    ->findBy(['group' => $group]);
                foreach ($acls as $acl) {
                    $this->entityManager->remove($acl);
                }
                
                $this->entityManager->remove($group);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @group group-management
     */
    public function testGetGroupsListSuccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/groups',
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
        $this->assertArrayHasKey('groups', $data['data']);
        $this->assertArrayHasKey('pagination', $data['data']);
        
        // Validate pagination structure
        $pagination = $data['data']['pagination'];
        $this->assertArrayHasKey('page', $pagination);
        $this->assertArrayHasKey('pageSize', $pagination);
        $this->assertArrayHasKey('totalCount', $pagination);
        $this->assertArrayHasKey('totalPages', $pagination);
        $this->assertArrayHasKey('hasNext', $pagination);
        $this->assertArrayHasKey('hasPrevious', $pagination);
        
        // Validate groups array
        $this->assertIsArray($data['data']['groups']);
        
        if (!empty($data['data']['groups'])) {
            $group = $data['data']['groups'][0];
            $this->assertArrayHasKey('id', $group);
            $this->assertArrayHasKey('name', $group);
            $this->assertArrayHasKey('description', $group);
            $this->assertArrayHasKey('users_count', $group);
            $this->assertArrayHasKey('requires_2fa', $group);
        }
    }

    /**
     * @group group-management
     */
    public function testGetGroupsListWithPagination(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/groups?page=1&pageSize=5',
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
        $this->assertLessThanOrEqual(5, count($data['data']['groups']));
        $this->assertSame(1, $data['data']['pagination']['page']);
        $this->assertSame(5, $data['data']['pagination']['pageSize']);
    }

    /**
     * @group group-management
     */
    public function testGetGroupsListWithSearch(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/groups?search=admin',
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
        $this->assertArrayHasKey('groups', $data['data']);
    }

    /**
     * @group group-management
     */
    public function testCreateGroupSuccess(): void
    {
        $groupData = [
            'name' => $this->testGroupName,
            'description' => 'Test group description',
            'requires_2fa' => false
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/groups',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($groupData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        
        $group = $data['data'];
        $this->assertArrayHasKey('id', $group);
        $this->assertArrayHasKey('name', $group);
        $this->assertArrayHasKey('description', $group);
        $this->assertArrayHasKey('users_count', $group);
        $this->assertArrayHasKey('requires_2fa', $group);
        $this->assertArrayHasKey('users', $group);
        $this->assertArrayHasKey('acls', $group);
        
        $this->assertSame($groupData['name'], $group['name']);
        $this->assertSame($groupData['description'], $group['description']);
        $this->assertSame($groupData['requires_2fa'], $group['requires_2fa']);
        
        // Store the created group ID for other tests
        $this->testGroupId = $group['id'];
    }

    /**
     * @group group-management
     */
    public function testGetGroupByIdSuccess(): void
    {
        // First create a group for this test
        $this->testCreateGroupSuccess();
        
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/groups/' . $this->testGroupId,
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
        $group = $data['data'];
        
        $this->assertSame($this->testGroupId, $group['id']);
        $this->assertSame($this->testGroupName, $group['name']);
        $this->assertArrayHasKey('users', $group);
        $this->assertArrayHasKey('acls', $group);
        $this->assertIsArray($group['users']);
        $this->assertIsArray($group['acls']);
    }

    /**
     * @group group-management
     */
    public function testUpdateGroupSuccess(): void
    {
        // First create a group for this test
        $this->testCreateGroupSuccess();
        
        $updateData = [
            'name' => $this->testGroupName . ' Updated',
            'description' => 'Updated description',
            'requires_2fa' => true
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/groups/' . $this->testGroupId,
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
        $group = $data['data'];
        
        $this->assertSame($updateData['name'], $group['name']);
        $this->assertSame($updateData['description'], $group['description']);
        $this->assertSame($updateData['requires_2fa'], $group['requires_2fa']);
    }

    /**
     * @group group-management
     */
    public function testGetGroupAclsSuccess(): void
    {
        // First create a group for this test
        $this->testCreateGroupSuccess();
        
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/groups/' . $this->testGroupId . '/acls',
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
        $this->assertArrayHasKey('acls', $data['data']);
        $this->assertIsArray($data['data']['acls']);
    }

    /**
     * @group group-management
     */
    public function testUpdateGroupAclsSuccess(): void
    {
        // First create a group for this test
        $this->testCreateGroupSuccess();
        
        if (empty($this->testPageIds)) {
            $this->markTestSkipped('No pages available for ACL testing');
        }
        
        $aclData = [
            'acls' => [
                [
                    'page_id' => $this->testPageIds[0],
                    'acl_select' => true,
                    'acl_insert' => true,
                    'acl_update' => false,
                    'acl_delete' => false
                ],
                [
                    'page_id' => $this->testPageIds[1],
                    'acl_select' => true,
                    'acl_insert' => false,
                    'acl_update' => true,
                    'acl_delete' => false
                ]
            ]
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/groups/' . $this->testGroupId . '/acls',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($aclData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('acls', $data['data']);
        $this->assertCount(2, $data['data']['acls']);
    }

    /**
     * @group group-management
     */
    public function testCreateGroupWithDuplicateName(): void
    {
        // First create a group
        $this->testCreateGroupSuccess();
        
        // Try to create another group with the same name
        $groupData = [
            'name' => $this->testGroupName, // Same name
            'description' => 'Another test group',
            'requires_2fa' => false
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/groups',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($groupData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group group-management
     */
    public function testCreateGroupWithMissingName(): void
    {
        $groupData = [
            'description' => 'Test group without name',
            'requires_2fa' => false
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/groups',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($groupData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group group-management
     */
    public function testGetNonExistentGroup(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/groups/99999',
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
     * @group group-management
     */
    public function testDeleteGroupSuccess(): void
    {
        // First create a group for this test
        $this->testCreateGroupSuccess();
        
        $groupIdToDelete = $this->testGroupId;

        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/groups/' . $groupIdToDelete,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Verify the group is deleted
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/groups/' . $groupIdToDelete,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        
        // Clear the testGroupId since it's deleted
        $this->testGroupId = null;
    }

    /**
     * @group group-management
     */
    public function testUnauthorizedAccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/groups'
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
} 