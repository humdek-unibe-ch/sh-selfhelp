<?php
namespace App\Tests\Controller\Api\V1;

use App\Service\Core\LookupService;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Controller\Api\V1\Traits\ManagesTestPagesTrait; // Add the trait

class AdminPageControllerTest extends BaseControllerTest
{
    use ManagesTestPagesTrait; // Use the trait
    
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
        $this->deleteTestPageIfExistsWithKeyword(self::TEST_PAGE_KEYWORD); // Ensure clean state
        $this->createTestPageWithKeyword(self::TEST_PAGE_KEYWORD, LookupService::PAGE_ACCESS_TYPES_MOBILE_AND_WEB);
        $this->deleteTestPageWithKeyword(self::TEST_PAGE_KEYWORD);
    }
    
    /**
     * @group admin
     */
    public function testUpdatePage(): void
    {
        // Ensure the test page doesn't exist from a previous failed run
        $this->deleteTestPageIfExistsWithKeyword(self::TEST_PAGE_KEYWORD);

        // First, create the test page
        $this->createTestPageWithKeyword(self::TEST_PAGE_KEYWORD, LookupService::PAGE_ACCESS_TYPES_MOBILE_AND_WEB);

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
            // Clean up: delete the test page
            $this->deleteTestPageIfExistsWithKeyword(self::TEST_PAGE_KEYWORD); // Use ifExists for robustness in cleanup
        }
    }
}
