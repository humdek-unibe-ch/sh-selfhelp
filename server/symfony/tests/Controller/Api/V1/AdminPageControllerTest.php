<?php
namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Service\JSON\JsonSchemaValidationService;

class AdminPageControllerTest extends WebTestCase
{
    protected $jsonSchemaValidationService;
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->jsonSchemaValidationService = self::getContainer()->get(JsonSchemaValidationService::class);
    }

    private function getAdminAccessToken(): string
    {
        // Use a real admin user from your fixtures
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'stefan.kodzhabashev@gmail.com',
                'password' => 'q1w2e3r4',
            ])
        );
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Admin login failed.');
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $data['data']);
        return $data['data']['access_token'];
    }

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
            'responses/common/_page_definition' // Schema for page list
        );
        $this->assertEmpty($validationErrors, "Response for /cms-api/v1/admin/pages failed schema validation:\n" . implode("\n", $validationErrors));
    }
}
