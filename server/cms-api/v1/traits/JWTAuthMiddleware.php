<?php
trait JWTAuthMiddleware {
    private function authenticateRequest(): void {

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

        // Otherwise, proceed with token authentication
        $token = $this->getBearerToken();
        if (!$token) {
            throw new Exception('No token provided');
        }

        $jwtService = new JWTService($this->db);
        $payload = $jwtService->validateToken($token);
        
        if (!$payload || $payload['type'] !== 'access') {
            throw new Exception('Invalid token');
        }

        $this->currentUser = $payload;
    }

    private function getBearerToken(): ?string {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }
}