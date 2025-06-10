<?php
namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Component\HttpFoundation\Response;

class BaseControllerTest extends WebTestCase
{
    protected $jsonSchemaValidationService;
    protected $client;

    private $adminAccessToken;
    private $userAccessToken;
    
    // Test page keyword to use for create/delete tests
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->jsonSchemaValidationService = self::getContainer()->get(JsonSchemaValidationService::class);
    }

    protected function getAdminAccessToken(): string
    {
        if ($this->adminAccessToken) {
            return $this->adminAccessToken;
        }

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
        $this->adminAccessToken = $data['data']['access_token'];
        return $this->adminAccessToken; 
    }
    
    protected function getUserAccessToken(): string
    {
        if ($this->userAccessToken) {
            return $this->userAccessToken;
        }

        // Use a regular user from your fixtures
        // Note: This should be a non-admin user with limited permissions
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user@example.com', // Replace with an actual regular user
                'password' => 'password123',   // Replace with the actual password
            ])
        );
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'User login failed.');
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $data['data']);
        $this->userAccessToken = $data['data']['access_token'];
        return $this->userAccessToken; 
    }

    /**
     * @group smoke
     */
    public function testSmokeTest(): void
    {
        $this->assertTrue(true, 'Basic assertion to ensure tests can run.');
    }
}