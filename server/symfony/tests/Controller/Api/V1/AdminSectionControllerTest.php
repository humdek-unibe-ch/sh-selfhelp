<?php
namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;
use App\Tests\Controller\Api\V1\Traits\ManagesTestPagesTrait; // Add the trait

class AdminSectionControllerTest extends BaseControllerTest
{
    use ManagesTestPagesTrait; // Use the trait

    private const TEST_PAGE_KEYWORD = "home"; // Using an existing page for testing
    private const LIFECYCLE_TEST_PAGE_KEYWORD = 'sections_lifecycle_test_page';    
    private const DEFAULT_STYLE_ID_1 = 3; // Container style
    private const DEFAULT_STYLE_ID_2 = 40; // Div style
    private const DEFAULT_LANGUAGE_ID = 2; // Assuming default language ID for page creation

    private $testSectionId = null; // Will store the ID of a test section for child section tests

    /**
     * @group admin
     * @group section-lifecycle
     */
    public function testSectionLifecycleOnPage(): void
    {
        $token = $this->getAdminAccessToken();
        $pageKeyword = self::LIFECYCLE_TEST_PAGE_KEYWORD;
        $section1Id = null;
        $section2Id = null;
        $section3Id = null;

        // Ensure clean state and cleanup
        $this->deleteTestPageIfExistsWithKeyword($pageKeyword); // Use trait method for robust cleanup

        try {
            // Use trait method to create the page. Default access type is PUBLIC_PAGE.
            $this->createTestPageWithKeyword($pageKeyword);


            // 1. Add Section 1 (S1)
            $this->client->request(
                'POST',
                sprintf('/cms-api/v1/admin/pages/%s/sections/create', $pageKeyword),
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
                json_encode(['styleId' => self::DEFAULT_STYLE_ID_1, 'position' => 0])
            );
            $response = $this->client->getResponse();
            $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), 'Failed to create section 1');
            $responseContentS1 = $response->getContent();
            $decodedResponseS1 = json_decode($responseContentS1);
            $validationErrors = $this->jsonSchemaValidationService->validate($decodedResponseS1, 'responses/section/section_created_minimal');
            $this->assertEmpty($validationErrors, 'S1 creation response does not match schema: ' . implode(', ', $validationErrors));
            $section1Data = $decodedResponseS1->data;
            $section1Id = $section1Data->id;
            $this->assertNotNull($section1Id, 'Section 1 ID is null');

            // 2. Add Section 2 (S2)
            $this->client->request(
                'POST',
                sprintf('/cms-api/v1/admin/pages/%s/sections/create', $pageKeyword),
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
                json_encode(['styleId' => self::DEFAULT_STYLE_ID_2, 'position' => 1])
            );
            $response = $this->client->getResponse();
            $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), 'Failed to create section 2');
            $responseContentS2 = $response->getContent();
            $decodedResponseS2 = json_decode($responseContentS2);
            $validationErrors = $this->jsonSchemaValidationService->validate($decodedResponseS2, 'responses/section/section_created_minimal');
            $this->assertEmpty($validationErrors, 'S2 creation response does not match schema: ' . implode(', ', $validationErrors));
            $section2Data = $decodedResponseS2->data;
            $section2Id = $section2Data->id;
            $this->assertNotNull($section2Id, 'Section 2 ID is null');

            // 3. Verify S1, S2 are on the page
            $this->client->request('GET', sprintf('/cms-api/v1/admin/pages/%s/sections', $pageKeyword), [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);
            $response = $this->client->getResponse();
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $responseContentGet1 = $response->getContent();
            $decodedResponseGet1 = json_decode($responseContentGet1);
            $validationErrors = $this->jsonSchemaValidationService->validate($decodedResponseGet1, 'responses/admin/page_sections');
            $this->assertEmpty($validationErrors, 'Get sections (S1,S2) response does not match schema: ' . implode(', ', $validationErrors));
            $pageSectionsData = $decodedResponseGet1->data;
            $this->assertCount(2, $pageSectionsData->sections, 'Incorrect number of sections after adding S1, S2');
            $this->assertSame($section1Id, $pageSectionsData->sections[0]->id, 'S1 not at position 0');
            $this->assertSame(0, $pageSectionsData->sections[0]->position, 'S1 position incorrect');
            $this->assertSame($section2Id, $pageSectionsData->sections[1]->id, 'S2 not at position 1');
            $this->assertSame(10, $pageSectionsData->sections[1]->position, 'S2 position incorrect');

            // 4. Add S3 as child of S1 (first section)
            $this->client->request(
                'POST',
                sprintf('/cms-api/v1/admin/pages/%s/sections/%d/sections/create', $pageKeyword, $section1Id),
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
                json_encode(['styleId' => self::DEFAULT_STYLE_ID_1, 'position' => 0])
            );
            $responseS3 = $this->client->getResponse();
            $this->assertSame(Response::HTTP_CREATED, $responseS3->getStatusCode(), 'Failed to create child section S3');
            $responseContentS3 = $responseS3->getContent();
            $decodedResponseS3 = json_decode($responseContentS3);
            $validationErrors = $this->jsonSchemaValidationService->validate($decodedResponseS3, 'responses/section/section_created_minimal');
            $this->assertEmpty($validationErrors, 'S3 creation response does not match schema: ' . implode(', ', $validationErrors));
            $section3Data = $decodedResponseS3->data;
            $section3Id = $section3Data->id;
            $this->assertNotNull($section3Id, 'Section 3 ID is null');

            // 5. Verify Nested Structure (S1[S3], S2)
            $this->client->request('GET', sprintf('/cms-api/v1/admin/pages/%s/sections', $pageKeyword), [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);
            $responseGet3 = $this->client->getResponse();
            $this->assertSame(Response::HTTP_OK, $responseGet3->getStatusCode(), 'Failed to get sections after adding S3');
            $responseContentGet3 = $responseGet3->getContent();
            $decodedResponseGet3 = json_decode($responseContentGet3);
            $validationErrors = $this->jsonSchemaValidationService->validate($decodedResponseGet3, 'responses/admin/page_sections');
            $this->assertEmpty($validationErrors, 'Get sections (S1[S3],S2) response does not match schema: ' . implode(', ', $validationErrors));
            $pageSectionsData = $decodedResponseGet3->data;
            $this->assertCount(2, $pageSectionsData->sections, 'Incorrect number of top-level sections after adding S3');
            $this->assertSame($section1Id, $pageSectionsData->sections[0]->id, 'S1 is not the first section after S3 add');
            $this->assertCount(1, $pageSectionsData->sections[0]->children, 'S1 does not have one child (S3) after S3 add');
            $this->assertSame($section3Id, $pageSectionsData->sections[0]->children[0]->id, 'S3 is not the child of S1 after S3 add');
            $this->assertSame($section2Id, $pageSectionsData->sections[1]->id, 'S2 is not the second section after S3 add');
            $this->assertEmpty($pageSectionsData->sections[1]->children, 'S2 should not have children after S3 add');

            // 6. Remove Child Section S3 from S1
            $this->client->request(
                'DELETE',
                sprintf('/cms-api/v1/admin/pages/%s/sections/%d', $pageKeyword, $section3Id),
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
            );
            $this->assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode(), 'Failed to remove child section S3');

            // 7. Verify S3 Removal (S1, S2)
            $this->client->request('GET', sprintf('/cms-api/v1/admin/pages/%s/sections', $pageKeyword), [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);
            $responseGet4 = $this->client->getResponse();
            $this->assertSame(Response::HTTP_OK, $responseGet4->getStatusCode(), 'Failed to get sections after S3 removal');
            $responseContentGet4 = $responseGet4->getContent();
            $decodedResponseGet4 = json_decode($responseContentGet4);
            $validationErrors = $this->jsonSchemaValidationService->validate($decodedResponseGet4, 'responses/admin/page_sections');
            $this->assertEmpty($validationErrors, 'Get sections (after S3 removal) response does not match schema: ' . implode(', ', $validationErrors));
            $pageSectionsData = $decodedResponseGet4->data;
            $this->assertCount(2, $pageSectionsData->sections, 'Incorrect number of top-level sections after removing S3');
            $this->assertSame($section1Id, $pageSectionsData->sections[0]->id, 'S1 not first after S3 removal');
            $this->assertEmpty($pageSectionsData->sections[0]->children, 'S1 should have no children after S3 removal');
            $this->assertSame($section2Id, $pageSectionsData->sections[1]->id, 'S2 not second after S3 removal');

            // 8. Remove S2 from page
            $this->client->request(
                'DELETE',
                sprintf('/cms-api/v1/admin/pages/%s/sections/%d', $pageKeyword, $section2Id),
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
            );
            $this->assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode(), 'Failed to remove section S2');

            // 9. Remove S1 from page
            $this->client->request(
                'DELETE',
                sprintf('/cms-api/v1/admin/pages/%s/sections/%d', $pageKeyword, $section1Id),
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
            );
            $this->assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode(), 'Failed to remove section S1');

            // 10. Verify All Sections Gone
            $this->client->request('GET', sprintf('/cms-api/v1/admin/pages/%s/sections', $pageKeyword), [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);
            $responseGet5 = $this->client->getResponse();
            $this->assertSame(Response::HTTP_OK, $responseGet5->getStatusCode(), 'Failed to get sections after all deletes');
            $responseContentGet5 = $responseGet5->getContent();
            $decodedResponseGet5 = json_decode($responseContentGet5);
            $validationErrors = $this->jsonSchemaValidationService->validate($decodedResponseGet5, 'responses/admin/page_sections');
            $this->assertEmpty($validationErrors, 'Get sections (all gone) response does not match schema: ' . implode(', ', $validationErrors));
            $pageSectionsData = $decodedResponseGet5->data;
            $this->assertEmpty($pageSectionsData->sections, 'Sections array not empty after deleting all sections');

        } finally {
            // Cleanup: Delete the test page using the trait method for robustness
            $this->deleteTestPageIfExistsWithKeyword($pageKeyword);
        }
    }

    // ... existing methods follow ...

    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Create a test section if needed for child section tests (for testCreateChildSection)
        $testName = $this->name ?? null;
        if ($testName === 'testCreateChildSection' && !$this->testSectionId) {
             // Only create if testCreateChildSection is running and it's not already created
            $this->createTestSectionForChildTest(); 
        }
    }

    /**
     * Test creating a page section
     */
    public function testCreatePageSection(): void
    {
        // Get JWT token for authentication
        $token = $this->getAdminAccessToken();
        
        // Create request data
        $requestData = [
            'styleId' => self::DEFAULT_STYLE_ID_1, 
            'position' => 0 // Add at the beginning
        ];
        
        // Send request to create a page section
        $this->client->request(
            'POST',
            sprintf('/cms-api/v1/admin/pages/%s/sections/create', self::TEST_PAGE_KEYWORD),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );
        
        // Check response
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        
        // Parse response data
        $responseContent = $this->client->getResponse()->getContent();
        $data = json_decode($responseContent);
        
        // Validate response structure
        $this->assertNotNull($data);
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');
        $this->assertTrue(property_exists($data->data, 'id'), 'Response does not have section ID');
        $this->assertTrue(property_exists($data->data, 'position'), 'Response does not have position property');
        
        // Validate response against JSON schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data,
            'responses/page/section_added'
        );
        $this->assertEmpty($validationErrors, 'Response does not match schema: ' . implode(', ', $validationErrors));
    }
    
    /**
     * Test creating a child section
     */
    public function testCreateChildSection(): void
    {
        // Get JWT token for authentication
        $token = $this->getAdminAccessToken();
        
        // Create request data
        $requestData = [
            'styleId' => self::DEFAULT_STYLE_ID_2, 
            'position' => 0 // Add at the beginning
        ];
        
        // Send request to create a child section
        $this->client->request(
            'POST',
            sprintf('/cms-api/v1/admin/pages/home/sections/%d/sections/create', $this->testSectionId),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );
        
        // Check response
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        
        // Parse response data
        $responseContent = $this->client->getResponse()->getContent();
        $data = json_decode($responseContent);
        
        // Validate response structure
        $this->assertNotNull($data);
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');
        $this->assertTrue(property_exists($data->data, 'id'), 'Response does not have section ID');
        $this->assertTrue(property_exists($data->data, 'position'), 'Response does not have position property');
        
        // Validate response against JSON schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data,
            'responses/section/child_section_added'
        );
        $this->assertEmpty($validationErrors, 'Response does not match schema: ' . implode(', ', $validationErrors));
    }
    
    /**
     * Test getting a section with fields and translations
     * @group admin
     * @group section-get
     */
    public function testGetSectionWithFields(): void
    {
        // Get JWT token for authentication
        $token = $this->getAdminAccessToken();
        
        // First, create a test section to retrieve
        $this->createTestSectionForChildTest();
        $this->assertNotNull($this->testSectionId, 'Failed to create test section');
        
        // Send request to get the section
        $this->client->request(
            'GET',
            sprintf('/cms-api/v1/admin/pages/home/sections/%d', $this->testSectionId),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );
        
        // Check response (should be 200 OK)
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Failed to get section: ' . $response->getContent());
        
        // Parse response data
        $responseContent = $response->getContent();
        $data = json_decode($responseContent);
        
        // Validate response structure
        $this->assertNotNull($data);
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');
        $this->assertTrue(property_exists($data->data, 'section'), 'Response data does not have section property');
        $this->assertTrue(property_exists($data->data, 'fields'), 'Response data does not have fields property');
        $this->assertTrue(property_exists($data->data, 'languages'), 'Response data does not have languages property');
        
        // Validate section data
        $section = $data->data->section;
        $this->assertEquals($this->testSectionId, $section->id, 'Section ID mismatch');
        $this->assertTrue(property_exists($section, 'name'), 'Section does not have name property');
        $this->assertNotNull($section->style, 'Section style is null');
        $this->assertEquals(self::DEFAULT_STYLE_ID_1, $section->style->id, 'Section style ID mismatch');
        
        // Validate fields array (may be empty depending on the style)
        $this->assertIsArray($data->data->fields, 'Fields is not an array');
        
        // Validate languages array (may be empty if no translations exist)
        $this->assertIsArray($data->data->languages, 'Languages is not an array');
        
        // Validate against JSON schema
        $validationErrors = $this->jsonSchemaValidationService->validate($data, 'responses/admin/section');
        $this->assertEmpty($validationErrors, 'Response does not match schema: ' . implode(', ', $validationErrors));
    }
    
    /**
     * Test validation errors when creating a page section
     */
    public function testCreatePageSectionValidationErrors(): void
    {
        // Get JWT token for authentication
        $token = $this->getAdminAccessToken();
        
        // Create invalid request data (missing required fields)
        $requestData = [
            // Missing styleId and position
        ];
        
        // Send request to create a page section
        $this->client->request(
            'POST',
            sprintf('/cms-api/v1/admin/pages/%s/sections/create', self::TEST_PAGE_KEYWORD),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );
        
        // Check response (should be 400 Bad Request)
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        
        // Parse response data
        $responseContent = $this->client->getResponse()->getContent();
        $data = json_decode($responseContent);
        
        // Validate error response structure
        $this->assertNotNull($data);
        $this->assertTrue(property_exists($data, 'errors'), 'Response does not have errors property');
        $this->assertNotEmpty($data->errors, 'No validation errors returned');
    }
    
    /**
     * Helper method to create a test section for child section tests (testCreateChildSection).
     * This is kept separate from the lifecycle page to avoid interference.
     */
    private function createTestSectionForChildTest(): void
    {
        if ($this->testSectionId) return; // Already created

        // Get JWT token for authentication
        $token = $this->getAdminAccessToken();
        
        // Create request data
        $requestData = [
            'styleId' => self::DEFAULT_STYLE_ID_1, 
            'position' => 999 // Add at the end to minimize disruption on 'home' page
        ];
        
        // Send request to create a page section on the 'home' page
        $this->client->request(
            'POST',
            sprintf('/cms-api/v1/admin/pages/%s/sections/create', self::TEST_PAGE_KEYWORD), // Uses 'home'
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );
        
        $response = $this->client->getResponse();
        if ($response->getStatusCode() === Response::HTTP_CREATED) {
            $data = json_decode($response->getContent());
            if ($data && property_exists($data, 'data') && property_exists($data->data, 'id')) {
                 $this->testSectionId = $data->data->id;
            } else {
                $this->fail('Failed to create test section for child test: ID missing in response.');
            }
        } else {
            $this->fail('Failed to create test section for child test: ' . $response->getContent());
        }
    }
    
    /**
     * Clean up after tests
     */
    protected function tearDown(): void
    {
        // Clean up the section created by createTestSectionForChildTest if it exists
        if ($this->testSectionId) {
            $token = $this->getAdminAccessToken();
            // Use the correct delete route for sections
            $this->client->request(
                'DELETE',
                sprintf('/cms-api/v1/admin/pages/home/sections/%d', $this->testSectionId),
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
            );
            // We don't strictly check the response here, as it's just a cleanup attempt.
            $this->testSectionId = null;
        }
        parent::tearDown();
    }
}
{
    
}
