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
    private function authenticateRequest(): void
    {
        $token = $this->getBearerToken();
        if (!$token) {
            throw new Exception('No token provided');
        }

        $jwtService = new JWTService(db: $this->db);
        $payload = $jwtService->validateToken(token: $token);

        // Ensure only access tokens are used for API authentication
        // Refresh tokens should only be used with the token refresh endpoint
        if (!$payload || $payload->type !== 'access') {
            throw new Exception('Invalid token');
        }

        // Store user data from token
        $this->currentUser = $payload;
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
        return $this->currentUser?->user_data?->id_user ?? GUEST_USER_ID;
    }

    /**
     * @brief Get the user's preferred language
     * 
     * @return string User's language ID or default language if not set
     */
    protected function getUserLanguage(): string
    {
        return $this->currentUser?->user_data?->user_language ?? $this->db->get_default_language();
    }

    /**
     * @brief Get the user's language locale
     * 
     * @return string User's language locale or empty string if not set
     */
    protected function getUserLanguageLocale(): string
    {
        return $this->currentUser?->user_data?->user_language_locale ?? '';
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
     * @return array Array containing new access token and refresh token
     * @throws Exception If refresh token is invalid
     */
    protected function handleTokenRefresh(string $refreshToken): array
    {
        $jwtService = new JWTService(db: $this->db);
        $payload = $jwtService->validateRefreshToken($refreshToken);

        if (!$payload) {
            throw new Exception('Invalid refresh token');
        }

        // Generate new tokens
        $user = ['id' => $payload->sub] + (array)$payload->user_data;
        $accessToken = $jwtService->generateAccessToken($user);
        $newRefreshToken = $jwtService->generateRefreshToken($user);

        // Revoke the old refresh token
        $jwtService->revokeRefreshToken($refreshToken);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken
        ];
    }
}
