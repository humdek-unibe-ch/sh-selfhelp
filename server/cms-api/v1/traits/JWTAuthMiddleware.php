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
    protected function authenticateRequest(): void
    {
        $token = $this->getBearerToken();
        if (!$token) {
            $this->response->set_logged_in(false);
            return;
        }

        $jwtService = new JWTService(db: $this->db);
        $payload = $jwtService->validateToken(token: $token);

        // Ensure only access tokens are used for API authentication
        // Refresh tokens should only be used with the token refresh endpoint
        if (!$payload || $payload->type !== 'access') {
            $this->response->set_logged_in(false);
            return;
        }

        // Store user data from token and set logged_in status
        $this->currentUser = $payload;
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
    private function getBearerToken(): ?string
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @brief Get the authenticated user's ID
     * 
     * @return int User ID from token or GUEST_USER_ID if not authenticated
     */
    protected function getUserId(): int
    {
        return $this->currentUser?->sub ?? GUEST_USER_ID;
    }

    /**
     * @brief Check if the current request is authenticated
     * 
     * @return bool True if user is logged in, false otherwise
     */
    protected function isLoggedIn(): bool
    {
        return $this->getUserId() !== GUEST_USER_ID;
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
    protected function handleTokenRefresh(string $refreshToken): string
    {
        $jwtService = new JWTService(db: $this->db);
        $payload = $jwtService->validateRefreshToken($refreshToken);

        if (!$payload) {
            throw new Exception('Invalid refresh token');
        }

        // Generate new access token with fresh user data
        return $jwtService->generateAccessToken(user_id: $payload['sub']);
    }
}
