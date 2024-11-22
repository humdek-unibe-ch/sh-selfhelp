<?php
require_once __DIR__ . '/../../BaseApiRequest.php';
require_once __DIR__ . '/../../../service/JWTService.php';

class JWTAuthApi extends BaseApiRequest {
    private JWTService $jwtService;
    private Login $loginService;

    public function __construct() {
        parent::__construct();
        $this->jwtService = new JWTService(db: $this->db);
    }

    public function POST_login(): array {
        $username = $this->getParameter('username');
        $password = $this->getParameter('password');

        // Validate credentials using existing Login service
        $user = $this->loginService->validate_login($username, $password);
        if (!$user) {
            return $this->error_response('Invalid credentials');
        }

        // Generate tokens
        $accessToken = $this->jwtService->generateAccessToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user);

        return $this->success_response([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600,
            'token_type' => 'Bearer'
        ]);
    }

    public function POST_refresh(): array {
        $refreshToken = $this->getParameter('refresh_token');
        if (!$refreshToken) {
            return $this->error_response('Refresh token is required');
        }

        // Validate refresh token
        $decoded = $this->jwtService->validateRefreshToken($refreshToken);
        if (!$decoded) {
            return $this->error_response('Invalid refresh token');
        }

        // Get user data
        $user = $this->loginService->get_user($decoded['sub']);
        if (!$user) {
            return $this->error_response('User not found');
        }

        // Generate new access token
        $accessToken = $this->jwtService->generateAccessToken($user);

        return $this->success_response([
            'access_token' => $accessToken,
            'expires_in' => 3600,
            'token_type' => 'Bearer'
        ]);
    }

    public function POST_logout(): array {
        $refreshToken = $this->getParameter('refresh_token');
        if ($refreshToken) {
            $this->jwtService->revokeRefreshToken($refreshToken);
        }

        return $this->success_response(['message' => 'Successfully logged out']);
    }

    public function POST_logout_all(): array {
        $token = $this->getBearerToken();
        if (!$token) {
            return $this->error_response('No token provided');
        }

        $decoded = $this->jwtService->validateToken($token);
        if (!$decoded) {
            return $this->error_response('Invalid token');
        }

        $this->jwtService->revokeAllUserTokens($decoded['sub']);
        return $this->success_response(['message' => 'Logged out from all devices']);
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