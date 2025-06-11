<?php
namespace App\Tests\Controller\Api\V1;

use App\Service\Core\LookupService;
use Symfony\Component\HttpFoundation\Response;

class AdminPageControllerTest extends BaseControllerTest
{    
    private const TEST_PAGE_KEYWORD = "test_test";
    private const TITLE_FIELD_ID = 22; // ID of the title field

    private const DEFAULT_LANGUAGE_ID = 2; // ID of the default language

    /**
     * @group admin
     */
    public function testGetPageSections(): void
    {
        // Authenticate as admin and request /cms-api/v1/admin/pages/home/sections
        $token = $this->getAdminAccessToken();
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages/home/sections',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Admin get page sections failed.');
        
        // Decode as object (not array) for schema validation
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');

        // Validate response against JSON schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data, // Validate the full response object
            'responses/admin/page_sections' // Schema for page sections
        );
        $this->assertEmpty($validationErrors, "Response for /cms-api/v1/admin/pages/task/sections failed schema validation:\n" . implode("\n", $validationErrors));
    }

    /**
     * @group admin
     */
    public function testGetPagesFromAdmin(): void
    {
        // Authenticate as admin and request /cms-api/v1/admin/pages
        $token = $this->getAdminAccessToken();
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Admin get pages failed.');
        
        // Decode as object (not array) for schema validation
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');

        // Validate response against JSON schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data, // Validate the full response object
            'responses/common/_acl_page_definition' // Schema for page list
        );
        $this->assertEmpty($validationErrors, "Response for /cms-api/v1/admin/pages failed schema validation:\n" . implode("\n", $validationErrors));
    }
    
    /**
     * @group admin
     */
    public function testGetPageFields(): void
    {
        // Authenticate as admin and request page fields for 'home' page
        $token = $this->getAdminAccessToken();
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages/home',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Admin get page fields failed.');
        
        // Decode as object (not array) for schema validation
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');
        $this->assertTrue(property_exists($data->data, 'page'), 'Response data does not have page property');
        $this->assertTrue(property_exists($data->data, 'fields'), 'Response data does not have fields property');
        $this->assertIsArray($data->data->fields, 'Fields property is not an array');

        // Validate response against JSON schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data, // Validate the full response object
            'responses/admin/get_page_fields' // Schema for page fields
        );
        $this->assertEmpty($validationErrors, "Response for /cms-api/v1/admin/pages/home/fields failed schema validation:\n" . implode("\n", $validationErrors));
    }
    
    /**
     * @group admin
     */
    public function testCreateAndDeletePage(): void
    {
        // First, make sure the test page doesn't already exist
        $this->deleteTestPageIfExists();
        
        // Create a test page
        $this->createTestPage();
        
        // Delete the test page
        $this->deleteTestPage();
    }
    
    /**
     * @group admin
     */
    public function testUpdatePage(): void
    {
        // First, make sure the test page doesn't already exist
        $this->deleteTestPageIfExists();
        
        // Create a test page
        $this->createTestPage();
        
        try {
            // Update the page
            $token = $this->getAdminAccessToken();
            
            // Prepare the update data
            $updateData = [
                'pageData' => [
                    'headless' => true,  // Update headless to true
                    'navPosition' => 55  // Change nav position
                ],
                'fields' => [
                    [
                        'fieldId' => self::TITLE_FIELD_ID,  // Title field ID
                        'languageId' => self::DEFAULT_LANGUAGE_ID,                  // Default language ID (corrected from 2 to 1)
                        'content' => 'Test Test'            // New title content
                    ]
                ]
            ];
            
            // Send the update request
            $this->client->request(
                'PUT',
                '/cms-api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD,
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
                json_encode($updateData)
            );
            
            $response = $this->client->getResponse();
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Failed to update test page');
            
            // Validate response against JSON schema
            $data = json_decode($response->getContent());
            $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');
            
            $validationErrors = $this->jsonSchemaValidationService->validate(
                $data,
                'responses/admin/get_page_fields' // Schema for page with fields
            );
            $this->assertEmpty($validationErrors, "Response for PUT /cms-api/v1/admin/pages/" . self::TEST_PAGE_KEYWORD . " failed schema validation:\n" . implode("\n", $validationErrors));
            
            // Verify the page was updated by fetching it
            $this->client->request(
                'GET',
                '/cms-api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD,
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
            );
            
            $response = $this->client->getResponse();
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Failed to fetch updated test page');
            
            // Check that the changes were applied
            $pageData = json_decode($response->getContent(), true);
            $this->assertTrue($pageData['data']['page']['headless'], 'headless should be true after update');
            
            // Check for the updated title field
            $titleFieldFound = false;
            foreach ($pageData['data']['fields'] as $field) {
                if ($field['id'] === self::TITLE_FIELD_ID) {
                    foreach ($field['translations'] as $translation) {
                        if ($translation['language_id'] === self::DEFAULT_LANGUAGE_ID) {
                            $this->assertEquals('Test Test', $translation['content'], 'Title field was not updated correctly');
                            $titleFieldFound = true;
                            break;
                        }
                    }
                }
            }
            
            $this->assertTrue($titleFieldFound, 'Title field with ID ' . self::TITLE_FIELD_ID . ' not found in response');
            
        } finally {
            // Clean up by deleting the test page
            $this->deleteTestPage();
        }
    }
    
    /**
     * Create a test page with keyword 'test_test'
     */
    private function createTestPage(): void
    {
        $token = $this->getAdminAccessToken();
        
        // Create a new page
        $this->client->request(
            'POST',
            '/cms-api/v1/admin/pages',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode([
                'keyword' => self::TEST_PAGE_KEYWORD,
                'pageAccessTypeCode' => LookupService::PAGE_ACCESS_TYPES_MOBILE_AND_WEB,
                'headless' => false,
                'openAccess' => true,
                'url' => '/' . self::TEST_PAGE_KEYWORD,
                'navPosition' => 100,
                'footerPosition' => null,
                'parent' => null
            ])
        );
        
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), 'Failed to create test page');
        
        // Validate response against JSON schema
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');
        $this->assertTrue(property_exists($data->data, 'keyword'), 'Response data does not have keyword property');
        $this->assertSame(self::TEST_PAGE_KEYWORD, $data->data->keyword, 'Returned page keyword does not match');
        
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data,
            'responses/admin/page'
        );
        $this->assertEmpty($validationErrors, "Response for POST /cms-api/v1/admin/pages failed schema validation:\n" . implode("\n", $validationErrors));
        
        // Verify the page was created by fetching it
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Failed to fetch created test page');
    }
    
    /**
     * Delete the test page with keyword 'test_test'
     */
    private function deleteTestPage(): void
    {
        $token = $this->getAdminAccessToken();
        
        // Delete the page
        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Failed to delete test page');
        
        // Validate response against JSON schema
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');
        $this->assertTrue(property_exists($data->data, 'keyword'), 'Response data does not have keyword property');
        $this->assertSame(self::TEST_PAGE_KEYWORD, $data->data->keyword, 'Returned keyword does not match');
        
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data,
            'responses/admin/page'
        );
        $this->assertEmpty($validationErrors, "Response for DELETE /cms-api/v1/admin/pages/" . self::TEST_PAGE_KEYWORD . " failed schema validation:\n" . implode("\n", $validationErrors));
        
        // Verify the page was deleted by trying to fetch it (should return 404)
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode(), 'Page was not properly deleted');
    }
    
    /**
     * Helper method to delete the test page if it exists
     * This ensures tests can be run multiple times without failing
     */
    private function deleteTestPageIfExists(): void
    {
        $token = $this->getAdminAccessToken();
        
        // Check if the page exists
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );
        
        $response = $this->client->getResponse();
        
        // If page exists (status 200), delete it
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->client->request(
                'DELETE',
                '/cms-api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD,
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
            );
            
            // Wait a moment to ensure deletion is complete
            sleep(1);
        }
    }
}
