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
    public function POST_login($user, $password): void
    {
        $user = $this->login->validate_user($user, $password);

        if (!$user) {
            $this->error_response(error: 'Invalid credentials', status: 401);
        } else if ($user == '2fa') {
            // Return successful response with tokens
            $this->response->set_data(data: [
                'two_factor' => [
                    "required" => true,
                    "id_users" => $_SESSION['2fa_user']['id'],
                ]
            ]);
            return;
        }

        // Generate access and refresh tokens
        $access_token = $this->jwt_service->generate_access_token(user_id: $user['id']);
        $refresh_token = $this->jwt_service->generate_refresh_token(user_id: $user['id']);

        $this->response->set_logged_in(logged_in: true);

        // Return successful response with tokens
        $this->response->set_data(data: [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_in' => ACCESS_TOKEN_EXPIRATION,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * @brief Handle two-factor authentication
     * 
     * Verifies the provided 2FA code and generates access and refresh tokens
     * upon successful verification.
     * 
     * Request Parameters:
     * - code: 2FA verification code
     * 
     * Response:
     * - Success: JWT access and refresh tokens with user data
     * - Error: Authentication error message
     * 
     * @param string $code 2FA verification code
     * @throws Exception If verification fails
     */
    public function POST_two_factor_verify($code, $id_users): void
    {
        $_SESSION['2fa_user']['id'] = $id_users;
        $result = $this->login->verify_2fa_code($code);

        if (!$result) {
            $this->error_response(error: 'Invalid credentials', status: 200);
            return;
        } else {
            // Generate access and refresh tokens
            $access_token = $this->jwt_service->generate_access_token(user_id: $_SESSION['id_user']);
            $refresh_token = $this->jwt_service->generate_refresh_token(user_id: $_SESSION['id_user']);

            $this->response->set_logged_in(logged_in: true);

            // Return successful response with tokens
            $this->response->set_data(data: [
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
                'expires_in' => ACCESS_TOKEN_EXPIRATION,
                'token_type' => 'Bearer',
                'redirect_to' => isset($_SESSION['last_url']) ? $_SESSION['last_url'] : $this->router->generate('home')
            ]);
        }
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
    public function POST_refresh_token($refresh_token): void
    {
        try {
            $access_token = $this->handle_token_refresh($refresh_token);

            if ($access_token) {
                $this->response->set_logged_in(logged_in: true);
            }

            $this->response->set_data(data: [
                'access_token' => $access_token,
                'expires_in' => ACCESS_TOKEN_EXPIRATION,
                'token_type' => 'Bearer',
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
    public function POST_logout($access_token, $refresh_token): void
    {
        try {
            // Try to validate access token but continue even if it fails
            try {
                $access_payload = $this->jwt_service->validate_token($access_token);
            } catch (Exception $e) {
                // Ignore access token validation errors
            }

            // Validate and revoke refresh token if it exists
            if ($refresh_token) {
                try {
                    $refresh_payload = $this->jwt_service->validate_refresh_token($refresh_token);
                    $this->jwt_service->revoke_refresh_token($refresh_token);
                } catch (Exception $e) {
                    // If refresh token is invalid, we can ignore the error
                    // The token might already be expired or revoked
                }
            }

            // Always set logged out status and return success
            $this->response->set_logged_in(false);
            $this->response->set_status(200)
                ->set_message('Successfully logged out');
        } catch (Exception $e) {
            // This catch block will only trigger for unexpected errors
            $this->error_response('An unexpected error occurred during logout');
        }
    }
}
?>
