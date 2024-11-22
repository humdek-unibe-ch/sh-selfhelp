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
    /** @var array|null Current user's decoded JWT payload */
    private $currentUser = null;

    /** @var array|null User data extracted from JWT token */
    private $userData = null;

    /**
     * @brief Authenticate the current API request
     * 
     * Validates the JWT token from the Authorization header if the page requires authentication.
     * Skips authentication for open access pages.
     * 
     * @throws Exception If token is missing or invalid for protected pages
     */
    private function authenticateRequest(): void
    {

        // CREATE EXTENDED DB THAT WILL BE FOR API AND SHOULD USE CACHE. LIKE NOW CHECKING IF THE PAGE IS OPEN ACCES AND IT SHOULD BE ADDED TO TYPES PAGES WHICH SHOULD BE CLEARED ON PAGE CHANGES

        // Check if the current page is open access
        $page = $this->db->query_db_first(
            "SELECT is_open_access FROM pages WHERE keyword = :keyword LIMIT 1",
            [':keyword' => $this->keyword]
        );

        // If page is open access, skip authentication
        if ($page && $page['is_open_access']) {
            return;
        }

        $token = $this->getBearerToken();
        if (!$token) {
            throw new Exception('No token provided');
        }

        $jwtService = new JWTService($this->db);
        $payload = $jwtService->validateToken($token);

        if (!$payload || $payload['type'] !== 'access') {
            throw new Exception('Invalid token');
        }

        // Store user data from token
        $this->currentUser = $payload;
        $this->userData = $payload['user_data'];
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
        return $this->userData['id_user'] ?? GUEST_USER_ID;
    }

    /**
     * @brief Get the user's preferred language
     * 
     * @return string User's language ID or default language if not set
     */
    protected function getUserLanguage(): string
    {
        return $this->userData['user_language'] ?? $this->db->get_default_language();
    }

    /**
     * @brief Get the user's language locale
     * 
     * @return string User's language locale or empty string if not set
     */
    protected function getUserLanguageLocale(): string
    {
        return $this->userData['user_language_locale'] ?? '';
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
}
