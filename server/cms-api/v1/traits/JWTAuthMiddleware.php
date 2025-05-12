<?php

/**
 * @trait JWTAuthMiddleware
 * @brief Middleware trait for handling JWT-based authentication in API requests
 * 
 * This trait provides authentication and user data access functionality for API endpoints.
 * It validates JWT tokens, handles open access pages, and provides methods to access
 * user data stored in the token.
 */
trait JWTAuthMiddleware
{
    /** @var \stdClass|null Current user's decoded JWT payload */
    private $currentUser = null;

    /**
     * @brief Authenticate the current API request
     * 
     * Validates the JWT token from the Authorization header if the page requires authentication.
     * Only accepts access tokens for API authentication, as refresh tokens should only be used
     * to obtain new access tokens through a dedicated refresh endpoint.
     * 
     * @throws Exception If token is missing or invalid for protected pages
     */
    protected function authenticate_request(): void
    {
        $token = $this->get_bearer_token();
        if (!$token) {
            $this->response->set_logged_in(false);
            $_SESSION['id_user'] = GUEST_USER_ID;
            return;
        }

        $jwtService = new JWTService(db: $this->db);
        $payload = $jwtService->validate_token(token: $token);

        // Ensure only access tokens are used for API authentication
        // Refresh tokens should only be used with the token refresh endpoint
        if (!$payload || $payload->type !== 'access') {
            $this->response->set_logged_in(false);
            $_SESSION['id_user'] = GUEST_USER_ID;
            return;
        }

        // Store user data from token and set logged_in status
        $this->currentUser = $payload;
        $_SESSION['id_user'] = $this->get_user_id();
        $this->response->set_logged_in(true);
    }

    /**
     * @brief Extract Bearer token from Authorization header
     * 
     * Retrieves and parses the JWT token from the Authorization header.
     * Expects the token in the format: "Bearer <token>"
     * 
     * @return string|null The JWT token if found, null otherwise
     */
    private function get_bearer_token(): ?string
    {
        static $headers = null;
        $headers ??= getallheaders();
        
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        return preg_match('/^Bearer\s+(.+)$/i', $auth, $matches) ? $matches[1] : null;
    }

    /**
     * @brief Get the authenticated user's ID
     * 
     * @return int User ID from token or GUEST_USER_ID if not authenticated
     */
    protected function get_user_id(): int
    {
        return $this->currentUser?->sub ?? GUEST_USER_ID;
    }

    /**
     * @brief Check if the current request is authenticated
     * 
     * @return bool True if user is logged in, false otherwise
     */
    protected function is_logged_in(): bool
    {
        return $this->get_user_id() !== GUEST_USER_ID;
    }

    /**
     * @brief Handle token refresh request
     * 
     * Validates a refresh token and generates a new access token if valid.
     * This should be used in a dedicated refresh token endpoint.
     * 
     * @param string $refreshToken The refresh token to validate
     * @return string New access token
     * @throws Exception If refresh token is invalid
     */
    protected function handle_token_refresh(string $refreshToken): string
    {
        $jwtService = new JWTService(db: $this->db);
        $payload = $jwtService->validate_refresh_token($refreshToken);

        if (!$payload) {
            throw new Exception('Invalid refresh token');
        }

        // Generate new access token with fresh user data
        return $jwtService->generate_access_token(user_id: $payload['sub']);
    }
}
