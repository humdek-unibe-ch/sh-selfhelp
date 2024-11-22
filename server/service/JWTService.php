<?php
require_once __DIR__ . '/../service/ext/firebase-php/vendor/autoload.php';
use Firebase\JWT\JWT;

class JWTService {
    private string $jwtSecret;
    private PageDb $db;
    private int $accessTokenExpiration = 3600; // 1 hour
    private int $refreshTokenExpiration = 2592000; // 30 days

    public function __construct($db) {
        $this->db = $db;
        $this->jwtSecret = getenv('JWT_SECRET') ?: 'your-secret-key'; // Use environment variable in production
    }

    public function generateAccessToken(array $user): string {
        $payload = [
            'sub' => $user['id'],
            'username' => $user['username'],
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + $this->accessTokenExpiration
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function generateRefreshToken(array $user): string {
        $payload = [
            'sub' => $user['id'],
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + $this->refreshTokenExpiration
        ];

        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');
        
        // Store refresh token in database
        $this->storeRefreshToken($user['id'], $token);
        
        return $token;
    }

    private function storeRefreshToken(int $userId, string $token): void {
        // Store hash of the token instead of the token itself
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + $this->refreshTokenExpiration);

        $this->db->insert('refresh_tokens', [
            'user_id' => $userId,    
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt
        ]);
            
    }

    public function validateToken(string $token): ?array {
        try {
            $decoded = (array)JWT::decode($token, $this->jwtSecret, ['HS256']);
            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    public function validateRefreshToken(string $token): ?array {
        $decoded = $this->validateToken($token);
        if (!$decoded || $decoded['type'] !== 'refresh') {
            return null;
        }

        // Verify token exists in database
        $tokenHash = hash('sha256', $token);
        $result = $this->db->query_db_first(
            "SELECT * FROM refresh_tokens 
             WHERE token_hash = :token_hash 
             AND expires_at > NOW()",
            [':token_hash' => $tokenHash]
        );

        return $result ? $decoded : null;
    }

    public function revokeRefreshToken(string $token): void {
        $tokenHash = hash('sha256', $token);
        $this->db->execute_db(
            "DELETE FROM refresh_tokens WHERE token_hash = :token_hash",
            [':token_hash' => $tokenHash]
        );
    }

    public function revokeAllUserTokens(int $userId): void {
        $this->db->execute_db(
            "DELETE FROM refresh_tokens WHERE user_id = :user_id",
            [':user_id' => $userId]
        );
    }
}