<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../BaseApiRequest.php";
require_once __DIR__ . "/../CmsApiResponse.php";
require_once __DIR__ . "/../../../service/globals.php";
require_once __DIR__ . "/../../../service/JWTService.php";

/**
 * @class AuthCmsApi
 * @brief API class for handling user authentication
 * @extends BaseApiRequest
 * 
 * This class provides authentication endpoints for the CMS API, including
 * user login functionality with JWT token generation. It supports both
 * regular email-based and anonymous username-based authentication.
 * 
 * Endpoints:
 * - POST /login: Authenticates user credentials and returns JWT tokens
 * - POST /refresh: Refreshes expired access token using refresh token
 */
class AuthCmsApi extends BaseApiRequest
{
    /** @var JWTService Service for JWT token operations */
    private JWTService $jwt_service;

    /**
     * @brief Constructor for AuthCmsApi class
     * 
     * Initializes the authentication API with necessary services and
     * sets up the JWT service for token generation.
     * 
     * @param object $services Service container with required dependencies
     * @param string $keyword API endpoint keyword identifier
     */
    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
        $this->jwt_service = new JWTService(db: $this->db);
    }

    /**
     * @brief Handle user login request
     * 
     * Authenticates user credentials and generates JWT tokens upon successful login.
     * Returns both access token for immediate use and refresh token for obtaining
     * new access tokens when they expire.
     * 
     * Request Parameters:
     * - user: Username or email address
     * - password: User's password
     * 
     * Response:
     * - Success: JWT access and refresh tokens with user data
     * - Error: Authentication error message
     * 
     * @param string $user Username or email
     * @param string $password User password
     * @throws Exception If credentials are invalid
     */
    public function post_login($user, $password): void
    {
        $user = $this->login->validate_user($user, $password);

        if (!$user) {
            $this->error_response(error: 'Invalid credentials');
        }

        // Generate access and refresh tokens
        $access_token = $this->jwt_service->generate_access_token(user_id: $user['id']);
        $refresh_token = $this->jwt_service->generate_refresh_token(user_id: $user['id']);

        $this->response->set_logged_in(logged_in: true);

        // Return successful response with tokens
        $this->response->set_data(data: [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_in' => $this->jwt_service->get_access_token_expiration(),
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * @brief Handle token refresh request
     * 
     * Validates a refresh token and generates a new access token.
     * The refresh token remains valid and can be used for future refreshes
     * until it expires.
     * 
     * Request Parameters:
     * - refresh_token: Valid refresh token
     * 
     * Response:
     * - Success: New access token
     * - Error: Token validation error message
     * 
     * @param string $refresh_token Refresh token to validate
     * @throws Exception If refresh token is invalid
     */
    public function post_refresh_token($refresh_token): void
    {
        try {
            $access_token = $this->handle_token_refresh($refresh_token);

            $this->response->set_data(data: [
                'access_token' => $access_token,
                'expires_in' => $this->jwt_service->get_access_token_expiration(),
                'token_type' => 'Bearer'
            ]);
        } catch (Exception $e) {
            $this->error_response(error: $e->getMessage());
        }
    }

    /**
     * @brief Handle user logout
     * 
     * Invalidates both access and refresh tokens, effectively logging out the user
     * from the system. The refresh token will be revoked from the database.
     * 
     * @param string $access_token The current access token to invalidate
     * @param string $refresh_token The current refresh token to revoke
     * @throws Exception If token validation fails
     */
    public function post_logout($access_token, $refresh_token): void
    {
        try {
            // Validate both tokens
            $access_payload = $this->jwt_service->validate_token($access_token);
            $refresh_payload = $this->jwt_service->validate_refresh_token($refresh_token);

            if (!$access_payload || !$refresh_payload) {
                throw new Exception('Invalid tokens provided');
            }

            // Revoke the refresh token from the database
            $this->jwt_service->revoke_refresh_token($refresh_token);

            // Set logged out status
            $this->response->set_logged_in(false);
            
            // Return success response
            $this->response->set_status(200)
                          ->set_message('Successfully logged out');   
        } catch (Exception $e) {
            $this->error_response($e->getMessage());
        }
    }
}
?>
