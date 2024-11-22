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
 * - POST /login: Authenticates user credentials and returns JWT token
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
     * Authenticates user credentials and generates a JWT token upon successful login.
     * Supports both email and username-based authentication based on system configuration.
     * 
     * Request Parameters:
     * - user: Username or email address
     * - password: User's password
     * 
     * Response:
     * - Success: JWT access token with user data
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

        // Generate access token with user data
        $accessToken = $this->jwtService->generateAccessToken(user: $user);

        // Return successful response with token
        $this->success_response([
            'access_token' => $accessToken,
            'expires_in' => 3600,
            'token_type' => 'Bearer'
        ]);
    }
}
?>
