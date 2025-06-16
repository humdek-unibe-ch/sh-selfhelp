<?php
namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;

class AdminSectionControllerTest extends BaseControllerTest
{
    private const TEST_PAGE_KEYWORD = "home"; // Using an existing page for testing
    private $testSectionId = null; // Will store the ID of a test section for child section tests

    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Create a test section if needed for child section tests
        if (!$this->testSectionId) {
            $this->createTestSection();
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
            'styleId' => 1, // Assuming style ID 1 exists
            'position' => 0 // Add at the beginning
        ];
        
        // Send request to create a page section
        $this->client->request(
            'POST',
            '/api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD . '/sections/create',
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
            'styleId' => 2, // Assuming style ID 2 exists
            'position' => 0 // Add at the beginning
        ];
        
        // Send request to create a child section
        $this->client->request(
            'POST',
            '/api/v1/admin/sections/' . $this->testSectionId . '/sections/create',
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
            '/api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD . '/sections/create',
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
     * Helper method to create a test section for child section tests
     */
    private function createTestSection(): void
    {
        // Get JWT token for authentication
        $token = $this->getAdminAccessToken();
        
        // Create request data
        $requestData = [
            'styleId' => 1, // Assuming style ID 1 exists
            'position' => 999 // Add at the end to minimize disruption
        ];
        
        // Send request to create a page section
        $this->client->request(
            'POST',
            '/api/v1/admin/pages/' . self::TEST_PAGE_KEYWORD . '/sections/create',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );
        
        // Parse response data
        $responseContent = $this->client->getResponse()->getContent();
        $data = json_decode($responseContent);
        
        // Store the section ID for later use
        $this->testSectionId = $data->data->id;
    }
    
    /**
     * Clean up after tests
     */
    protected function tearDown(): void
    {
        // Clean up any test data if needed
        parent::tearDown();
    }
}
