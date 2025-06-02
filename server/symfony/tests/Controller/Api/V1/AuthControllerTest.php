<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        // Note: Ensure your .env.test has APP_SECRET and JWT keys configured.
        // You might need to generate test JWT keys and place them in config/jwt/test/
        // and update .env.test with JWT_SECRET_KEY, JWT_PUBLIC_KEY, and JWT_PASSPHRASE.

        // Optional: Logic to ensure the test database is clean and has necessary fixtures.
        // This could involve running console commands or using a bundle like DAMA\DoctrineTestBundle.
        // Example (if not using DAMA bundle and need to reset DB per test or suite):
        // use Symfony\Component\Console\Application;
        // use Symfony\Component\Console\Input\ArrayInput;
        // $kernel = self::bootKernel();
        // $application = new Application($kernel);
        // $application->setAutoExit(false);
        //
        // $application->run(new ArrayInput([
        //     'command' => 'doctrine:database:drop',
        //     '--force' => true,
        //     '--if-exists' => true,
        //     '--env' => 'test',
        // ]));
        // $application->run(new ArrayInput([
        //     'command' => 'doctrine:database:create',
        //     '--env' => 'test',
        // ]));
        // $application->run(new ArrayInput([
        //     'command' => 'doctrine:migrations:migrate',
        //     '--no-interaction' => true,
        //     '--env' => 'test',
        // ]));
        // $application->run(new ArrayInput([
        //     'command' => 'doctrine:fixtures:load',
        //     '--no-interaction' => true,
        //     '--env' => 'test',
        // ]));
    }

    public function testSmokeTest(): void
    {
        $this->assertTrue(true, 'Basic assertion to ensure tests can run.');
    }

    public function testLoginPageIsReachable(): void
    {
        // This is a very basic test.
        // Your actual login endpoint might be POST only and expect JSON.
        // This test just checks if the route *could* be matched by a GET request
        // if it were defined, or if a POST to it doesn't immediately 500.
        // Adjust according to your actual login route definition.
        $this->client->request('GET', '/cms-api/v1/auth/login'); // Or POST if GET is not allowed
        
        // We expect either a 200 (if GET shows a form, unlikely for API)
        // or 405 (Method Not Allowed, if GET is not supported for a POST-only endpoint)
        // or 400/422 if it's POST and requires a body.
        // This is a loose assertion just to see if the route is recognized.
        $this->assertNotEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode(), "Login route should exist.");
    }

    /**
     * @group auth
     */
    public function testLoginSuccessWithValidCredentials(): void
    {
        // IMPORTANT: You need a test user in your database for this to work.
        // Load fixtures or ensure a user exists.
        // Replace with actual test user credentials.
        $testUserEmail = 'stefan.kodzhabashev@gmail.com'; 
        $testUserPassword = 'q1w2e3r4';

        // Create a user for testing if one doesn't exist or load fixtures
        // For now, this test assumes the user exists.

        $this->client->request(
            'POST',
            '/cms-api/v1/auth/login', // Your actual login endpoint
            [], // Parameters
            [], // Files
            ['CONTENT_TYPE' => 'application/json'], // Server environment
            json_encode([ // Raw body
                'email' => $testUserEmail,
                'password' => $testUserPassword,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), "Expected HTTP 200 OK. Got: " . $response->getStatusCode() . " Response: " . $response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertIsArray($responseData, 'Response is not valid JSON. Response: ' . $response->getContent());
        $this->assertSame(Response::HTTP_OK, $responseData['status'] ?? null, 'JSON status field should be HTTP 200. Response: ' . $response->getContent());
        $this->assertArrayHasKey('data', $responseData, 'Response data key missing. Response: ' . $response->getContent());
        $this->assertArrayHasKey('access_token', $responseData['data'], 'Access token (access_token) missing in response data.');
        $this->assertNotEmpty($responseData['data']['access_token'], 'Access token (access_token) is empty.');

        // Further assertions based on your 'auth_login_success_response.json' schema
        // For example, checking 'meta', 'logged_in' fields
        $this->assertTrue($responseData['logged_in'] ?? false, 'Logged in flag should be true at the root level.');
    }

    /**
     * @group auth
     */
    public function testLoginFailureWithInvalidCredentials(): void
    {
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'stefan.kodzhabashev@gmail.com',
                'password' => 'wrongpassword',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), "Expected HTTP 401 Unauthorized. Got: " . $response->getStatusCode() . " Response: " . $response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertIsArray($responseData, 'Response is not valid JSON. Response: ' . $response->getContent());
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseData['status'] ?? null, 'JSON status field should be HTTP 401. Response: ' . $response->getContent());
        $this->assertNotEmpty($responseData['message'] ?? '', 'Error message should not be empty.');
    }

    // Add more tests:
    // - Login with 2FA required (if applicable)
    // - 2FA verification success/failure
    // - Refresh token success/failure
    // - Logout
    // - Accessing protected routes with/without valid token
    // - Testing JSON schema validation for request payloads

    protected function tearDown(): void
    {
        parent::tearDown();
        // Avoid memory leaks
        $this->client = null;
    }
}
