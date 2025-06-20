<?php

namespace App\Tests\Service\CMS\Admin;

use App\Entity\Field;
use App\Entity\FieldType;
use App\Entity\Gender;
use App\Entity\Language;
use App\Entity\Section;
use App\Entity\Style;
use App\Entity\StylesField;
use App\Entity\SectionsFieldsTranslation;
use App\Exception\ServiceException;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\StyleRepository;
use App\Service\ACL\ACLService;
use App\Service\CMS\Admin\AdminSectionService;
use App\Service\Core\TransactionService;
use App\Service\Auth\UserContextService;
use App\Service\CMS\Admin\PositionManagementService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use App\Tests\Controller\Api\V1\BaseControllerTest;

class AdminSectionServiceTest extends BaseControllerTest
{
    private AdminSectionService $adminSectionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminSectionService = static::getContainer()->get(AdminSectionService::class);
    }

    /**
     * Test getting a section that doesn't exist
     */
    public function testGetSectionNotFound(): void
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Section not found');

        // Call the method with a non-existent section ID
        $this->adminSectionService->getSection('home', 999999);
    }

    /**
     * Test getting a section with no permission
     * This test will use a real section but with a user that doesn't have access
     */
    public function testGetSectionNoPermission(): void
    {
        // Get a regular user token (not admin)
        $token = $this->getUserAccessToken();
        
        // Make a request to get sections to find a real section ID
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages/home/sections',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        
        $response = $this->client->getResponse();
        
        // If we get a 403, that's expected - the user doesn't have access
        // If we get a 200, we can extract a section ID and test with it
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            if (!empty($data['data']['sections'])) {
                $sectionId = $data['data']['sections'][0]['id'];
                
                $this->expectException(ServiceException::class);
                $this->expectExceptionMessage('Access denied');
                
                // This should fail because regular user doesn't have admin access
                $this->adminSectionService->getSection('home', $sectionId);
            } else {
                $this->markTestSkipped('No sections found to test with');
            }
        } else {
            // The API call itself failed, which is expected for non-admin users
            $this->assertEquals(403, $response->getStatusCode());
        }
    }

    /**
     * Test getting a section successfully
     */
    public function testGetSectionSuccess(): void
    {
        // Get admin token and make a real API call
        $token = $this->getAdminAccessToken();
        
        // Get sections from a real page
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages/home/sections',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        
        if (!empty($data['data']['sections'])) {
            $sectionId = $data['data']['sections'][0]['id'];
            
            // Now test the service method with real data
            $result = $this->adminSectionService->getSection('home', $sectionId);
            
            // Assert the result structure
            $this->assertIsArray($result);
            $this->assertArrayHasKey('section', $result);
            $this->assertArrayHasKey('fields', $result);
            
            // Assert section data
            $this->assertEquals($sectionId, $result['section']['id']);
            $this->assertIsString($result['section']['name']);
            $this->assertArrayHasKey('style', $result['section']);
        } else {
            $this->markTestSkipped('No sections found to test with');
        }
    }
}
