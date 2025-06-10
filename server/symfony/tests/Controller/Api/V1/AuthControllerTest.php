<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Users2faCode;
use Doctrine\ORM\EntityManagerInterface;

class AuthControllerTest extends BaseControllerTest
{    
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

    /**
     * @group auth
     * @group 2fa
     */
    public function testTwoFactorAuthenticationSuccess(): void
    {
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'stefankod@abv.bg', // User with 2FA enabled
                'password' => 'q1w2e3r4',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Initial login for 2FA user should be HTTP 200. Response: ' . $response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertIsArray($responseData, 'Initial 2FA login response is not valid JSON. Response: ' . $response->getContent());
        $this->assertSame(Response::HTTP_OK, $responseData['status'] ?? null, 'Initial 2FA login JSON status should be 200. Response: ' . $response->getContent());
        $this->assertTrue($responseData['data']['requires_2fa'] ?? false, 'requires_2fa flag should be true. Response: ' . $response->getContent());
        $this->assertArrayHasKey('id_users', $responseData['data'], 'id_users missing in 2FA response data. Response: ' . $response->getContent());
        $this->assertNotEmpty($responseData['data']['id_users'], 'id_users should not be empty in 2FA response data. Response: ' . $response->getContent());

        $userId = $responseData['data']['id_users'];

        // Fetch the 2FA code from the database
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $twoFactorCodeEntity = $entityManager->getRepository(Users2faCode::class)->findOneBy(
            ['user' => $userId, 'isUsed' => false],
            ['createdAt' => 'DESC'] // Get the latest valid code
        );

        $this->assertNotNull($twoFactorCodeEntity, '2FA code entity not found in database for user ID: ' . $userId);
        $correctTwoFactorCode = $twoFactorCodeEntity->getCode();
        $this->assertNotEmpty($correctTwoFactorCode, 'Fetched 2FA code from database is empty.');

        // Step 2: Verify 2FA code
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/two-factor-verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'id_users' => $userId,
                'code' => $correctTwoFactorCode,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), '2FA verification success should be HTTP 200. Response: ' . $response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertIsArray($responseData, '2FA verification success response is not valid JSON. Response: ' . $response->getContent());
        $this->assertSame(Response::HTTP_OK, $responseData['status'] ?? null, '2FA success JSON status should be 200. Response: ' . $response->getContent());
        $this->assertArrayHasKey('data', $responseData, 'Response data key missing after 2FA. Response: ' . $response->getContent());
        $this->assertArrayHasKey('access_token', $responseData['data'], 'Access token missing after 2FA. Response: ' . $response->getContent());
        $this->assertNotEmpty($responseData['data']['access_token'], 'Access token should not be empty after 2FA. Response: ' . $response->getContent());
        $this->assertTrue($responseData['logged_in'] ?? false, 'Logged in flag should be true after 2FA. Response: ' . $response->getContent());
    }

    /**
     * @group auth
     * @group 2fa
     */
    public function testTwoFactorAuthenticationFailureInvalidCode(): void
    {
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'stefankod@abv.bg', // User with 2FA enabled
                'password' => 'q1w2e3r4',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Initial login for 2FA user (failure test) should be HTTP 200. Response: ' . $response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['data']['requires_2fa'] ?? false, 'requires_2fa flag should be true (failure test). Response: ' . $response->getContent());
        $userId = $responseData['data']['id_users'] ?? null;
        $this->assertNotNull($userId, 'User ID should be present for 2FA failure test. Response: ' . $response->getContent());

        $incorrectTwoFactorCode = '654321'; // Incorrect 2FA code

        // Step 2: Verify 2FA code with incorrect code
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/two-factor-verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'id_users' => $userId,
                'code' => $incorrectTwoFactorCode,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), '2FA verification with invalid code should be HTTP 401. Response: ' . $response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertIsArray($responseData, '2FA verification failure response is not valid JSON. Response: ' . $response->getContent());
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseData['status'] ?? null, '2FA failure JSON status should be 401. Response: ' . $response->getContent());
        $this->assertNotEmpty($responseData['message'] ?? '', 'Main error message should not be empty for 2FA failure. Response: ' . $response->getContent());
        $this->assertEquals('Unauthorized', $responseData['message'] ?? '', 'Incorrect main error message for 2FA failure. Response: ' . $response->getContent());
        $this->assertNotEmpty($responseData['error'] ?? '', 'Detailed error content should not be empty for 2FA failure. Response: ' . $response->getContent());
        $this->assertEquals('Invalid or expired verification code', $responseData['error'] ?? '', 'Incorrect detailed error content for 2FA failure. Response: ' . $response->getContent());
    }

    /**
     * @group auth
     * @group refresh_token
     */
    public function testRefreshTokenSuccess(): void
    {
        // Step 1: Login to get initial tokens (using a non-2FA user for simplicity here)
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'stefan.kodzhabashev@gmail.com', // Assuming this user does not have 2FA
                'password' => 'q1w2e3r4',
            ])
        );
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Login for refresh token test failed. Response: ' . $response->getContent());
        $loginData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('refresh_token', $loginData['data'], 'Refresh token missing from login response.');
        $refreshToken = $loginData['data']['refresh_token'];

        // Step 2: Use refresh token
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/refresh-token',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'refresh_token' => $refreshToken,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Refresh token request failed. Response: ' . $response->getContent());
        $refreshData = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_OK, $refreshData['status'] ?? null, 'Refresh token JSON status should be 200. Response: ' . $response->getContent());
        $this->assertArrayHasKey('access_token', $refreshData['data'], 'New access token missing from refresh response.');
        $this->assertNotEmpty($refreshData['data']['access_token'], 'New access token is empty.');
        $this->assertArrayHasKey('refresh_token', $refreshData['data'], 'New refresh token missing from refresh response.');
        $this->assertNotEmpty($refreshData['data']['refresh_token'], 'New refresh token is empty.');
        $this->assertNotEquals($refreshToken, $refreshData['data']['refresh_token'], 'New refresh token should be different from the old one.');
    }

    /**
     * @group auth
     * @group refresh_token
     */
    public function testRefreshTokenFailureInvalidToken(): void
    {
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/refresh-token',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'refresh_token' => 'invalid.refresh.token.string',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), 'Refresh token with invalid token should fail. Response: ' . $response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $responseData['status'] ?? null, 'Refresh token failure JSON status should be 401. Response: ' . $response->getContent());
    }

    /**
     * @group auth
     * @group logout
     */
    public function testLogoutSuccess(): void
    {
        // Step 1: Login to get an access token
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
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Login for logout test failed. Response: ' . $response->getContent());
        $loginData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $loginData['data'], 'Access token missing from login response for logout test.');
        $accessToken = $loginData['data']['access_token'];

        // Step 2: Logout
        $this->client->request(
            'POST',
            '/cms-api/v1/auth/logout',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken, 'CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Logout request failed. Response: ' . $response->getContent());
        $logoutData = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $logoutData['status'] ?? null, 'Logout JSON status should be 200. Response: ' . $response->getContent());
        $this->assertEquals('OK', $logoutData['message'] ?? '', 'Logout message incorrect. Expected "OK". Response: ' . $response->getContent());
        // The 'logged_in' flag in the logout response itself might be true, as it reflects the state *before* logout for that specific request.
        // The crucial part is that the token is no longer valid for subsequent requests.
        $this->assertTrue($logoutData['logged_in'] ?? false, 'Logout JSON logged_in flag should be true in the response. Response: ' . $response->getContent());
        $this->assertArrayHasKey('data', $logoutData, 'Logout response should contain a data field.');
        $this->assertIsArray($logoutData['data'], 'Logout data field should be an array.');
        $this->assertEquals('Access token was blacklisted. No refresh token was sent.', $logoutData['data']['message'] ?? '', 'Logout data message incorrect. Response: ' . $response->getContent());


        // Step 3: Verify token is invalidated by trying to access a protected route
        // Attempt to use the token again, e.g., to get user profile
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages', // Using a protected admin route
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken, 'CONTENT_TYPE' => 'application/json']
        );
        // After logout, accessing a protected route should result in an unauthorized error (401)
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode(), 'Access token should be invalid after logout. Attempt to access /cms-api/v1/admin/pages did not return 401.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Avoid memory leaks
        $this->client = null;
    }
}
