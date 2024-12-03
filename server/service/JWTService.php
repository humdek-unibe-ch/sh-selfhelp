<?php
require_once __DIR__ . '/../service/ext/firebase-php/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * @class JWTService
 * @brief Service class for handling JWT (JSON Web Token) operations
 * 
 * This class provides functionality for generating and validating JWT tokens
 * used in API authentication. It handles both access tokens and refresh tokens,
 * incorporating user session data into the token payload.
 */
class JWTService
{
    /** @var string Secret key used for JWT token signing */
    private string $jwt_secret;

    /** @var PageDb Database instance for user data operations */
    private PageDb $db;

    /**
     * @brief Constructor for JWTService
     * 
     * @param PageDb $db Database instance for user operations
     */
    public function __construct(PageDb $db)
    {
        $this->db = $db;
        $this->jwt_secret = JWT_SECRET;
    }

    /**
     * @brief Generate an access token for a user
     * 
     * Creates a JWT access token containing user session data and authentication claims.
     * The token includes essential user information previously stored in PHP sessions.
     * 
     * @param array $user User data array containing id, gender, language preferences
     * @return string Encoded JWT access token
     */
    public function generate_access_token(int $user_id): string
    {
        $payload = [
            'sub' => $user_id,
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + ACCESS_TOKEN_EXPIRATION
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

    /**
     * @brief Generate a refresh token for a user
     * 
     * Creates a JWT refresh token containing user authentication claims.
     * The token is stored in the database for later verification.
     * 
     * @param array $user User data array containing id
     * @return string Encoded JWT refresh token
     */
    public function generate_refresh_token(int $user_id): string
    {
        $payload = [
            'sub' => $user_id,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + REFRESH_TOKEN_EXPIRATION
        ];

        $token = JWT::encode($payload, $this->jwt_secret, 'HS256');

        // Store refresh token in database
        $this->store_refresh_token($user_id, $token);

        return $token;
    }

    /**
     * @brief Store a refresh token in the database
     * 
     * Stores a hash of the refresh token in the database for later verification.
     * 
     * @param int $userId User ID associated with the refresh token
     * @param string $token Refresh token to store
     */
    private function store_refresh_token(int $user_id, string $token): void
    {
        // Store hash of the token instead of the token itself
        $token_hash = hash('sha256', $token);
        $expires_at = date('Y-m-d H:i:s', time() + REFRESH_TOKEN_EXPIRATION);

        $this->db->insert('refreshTokens', [
            'id_users' => $user_id,
            'token_hash' => $token_hash,
            'expires_at' => $expires_at
        ]);
    }

    /**
     * @brief Validate and decode a JWT token
     * 
     * Attempts to validate and decode a JWT token using the service's secret key.
     * Handles validation of token signature and expiration.
     * 
     * @param string $token JWT token to validate
     * @return \stdClass|null Decoded token payload if valid, null if invalid
     */
    public function validate_token(string $token): ?\stdClass
    {
        try {
            $key = new Key($this->jwt_secret, 'HS256');
            return JWT::decode($token, $key);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @brief Validate a refresh token
     * 
     * Verifies a refresh token by checking its type and existence in the database.
     * 
     * @param string $token Refresh token to validate
     * @return array|null Decoded token payload if valid, null if invalid
     */
    public function validate_refresh_token(string $token): ?array
    {
        $decoded = $this->validate_token($token);
        if (!$decoded || $decoded->type !== 'refresh') {
            return null;
        }

        // Verify token exists in database
        $token_hash = hash('sha256', $token);
        $result = $this->db->query_db_first(
            "SELECT * FROM refreshTokens 
             WHERE token_hash = :token_hash 
             AND expires_at > NOW()",
            [':token_hash' => $token_hash]
        );

        return $result ? (array)$decoded : null;
    }

    /**
     * @brief Revoke a refresh token
     * 
     * Removes a refresh token from the database, effectively revoking it.
     * 
     * @param string $token Refresh token to revoke
     */
    public function revoke_refresh_token(string $token): void
    {
        $token_hash = hash('sha256', $token);
        $this->db->query_db(
            "DELETE FROM refreshTokens WHERE token_hash = :token_hash",
            [':token_hash' => $token_hash]
        );
    }

    /**
     * @brief Revoke all tokens for a user
     * 
     * Removes all refresh tokens associated with a user from the database.
     * 
     * @param int $userId User ID for which to revoke all tokens
     */
    public function revoke_all_user_tokens(int $user_id): void
    {
        $this->db->execute_db(
            "DELETE FROM refreshTokens WHERE id_users = :user_id",
            [':user_id' => $user_id]
        );
    }

}

