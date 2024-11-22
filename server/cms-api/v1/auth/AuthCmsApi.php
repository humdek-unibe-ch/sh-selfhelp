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
    private JWTService $jwtService;

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
        $this->jwtService = new JWTService(db: $this->db);
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
            $this->error_response(error: 'Invalid credentials');
        }

        // Generate access and refresh tokens
        $accessToken = $this->jwtService->generateAccessToken(user_id: $user['id']);
        $refreshToken = $this->jwtService->generateRefreshToken(user_id: $user['id']);

        // Return successful response with tokens
        $this->success_response([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600,
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
    public function POST_refresh_token($refresh_token): void
    {
        try {
            $accessToken = $this->handleTokenRefresh($refresh_token);
            
            $this->success_response([
                'access_token' => $accessToken,
                'expires_in' => 3600,
                'token_type' => 'Bearer'
            ]);
        } catch (Exception $e) {
            $this->error_response(error: $e->getMessage());
        }
    }
}
?>
