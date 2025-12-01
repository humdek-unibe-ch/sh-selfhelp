# Security and Authentication

## Overview

SelfHelp implements a comprehensive security framework with role-based access control, secure authentication mechanisms, and protection against common web vulnerabilities. The system supports both traditional web authentication and mobile API access.

## Authentication System

### User Authentication

#### Login Process

```php
class Login {
    public function authenticate($username, $password) {
        // Validate input
        if (!$this->validate_credentials($username, $password)) {
            $this->log_failed_attempt($username);
            return false;
        }

        // Check account status
        $user = $this->get_user_by_credentials($username);
        if (!$user || !$user['active']) {
            return false;
        }

        // Create session
        $this->create_user_session($user);

        // Log successful login
        $this->log_successful_login($user['id']);

        return true;
    }
}
```

#### Session Management

```php
class Login {
    private function create_user_session($user) {
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['user_language'] = $user['language'];
        $_SESSION['default_language_id'] = LANGUAGE;
        $_SESSION['last_activity'] = time();

        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    public function check_session_timeout() {
        $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600;
        if (isset($_SESSION['last_activity']) &&
            (time() - $_SESSION['last_activity'] > $timeout)) {
            $this->logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
}
```

### Two-Factor Authentication (2FA)

#### 2FA Implementation

```php
class TwoFactorAuth {
    public function enable_2fa($user_id) {
        // Generate secret key
        $secret = $this->generate_secret();

        // Store in database
        $this->db->insert('users_2fa_codes', [
            'id_users' => $user_id,
            'secret' => $this->encrypt_secret($secret),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $secret;
    }

    public function verify_2fa_code($user_id, $code) {
        $secret_data = $this->get_user_2fa_secret($user_id);
        if (!$secret_data) {
            return false;
        }

        $secret = $this->decrypt_secret($secret_data['secret']);
        return $this->verify_totp($secret, $code);
    }
}
```

#### TOTP Verification

```php
private function verify_totp($secret, $code) {
    $window = 1; // Allow 30 seconds clock skew
    $current_time = time();

    for ($i = -$window; $i <= $window; $i++) {
        $time = $current_time + ($i * 30);
        $generated_code = $this->generate_totp($secret, $time);
        if ($generated_code === $code) {
            return true;
        }
    }

    return false;
}
```

## Access Control

### Role-Based Access Control (RBAC)

#### Group-Based Permissions

```php
class Acl {
    public function check_page_access($page_keyword, $user_id) {
        // Get user's groups
        $user_groups = $this->get_user_groups($user_id);

        // Check if any group has access to the page
        foreach ($user_groups as $group_id) {
            if ($this->group_has_page_access($group_id, $page_keyword)) {
                return true;
            }
        }

        return false;
    }

    public function check_section_access($section_id, $user_id) {
        $user_groups = $this->get_user_groups($user_id);

        foreach ($user_groups as $group_id) {
            if ($this->group_has_section_access($group_id, $section_id)) {
                return true;
            }
        }

        return false;
    }
}
```

#### Permission Levels

```sql
-- Page access types
CREATE TABLE pages (
    id INT PRIMARY KEY,
    id_type INT -- 1=INTERNAL, 2=EXPERIMENT, 3=OPEN, 4=CORE
);

-- Group permissions
CREATE TABLE acl_groups_pages (
    id_groups INT,
    id_pages INT,
    can_select BOOLEAN DEFAULT 1,
    can_insert BOOLEAN DEFAULT 0,
    can_update BOOLEAN DEFAULT 0,
    can_delete BOOLEAN DEFAULT 0
);
```

### Database-Level Security

#### Row-Level Security

```sql
-- Users can only see their own data
CREATE VIEW user_input_secure AS
SELECT * FROM user_input
WHERE id_users = @current_user_id;

-- Groups restrict data access
CREATE VIEW restricted_user_data AS
SELECT u.* FROM users u
INNER JOIN user_groups ug ON u.id = ug.id_users
WHERE ug.id_groups IN (
    SELECT id_groups FROM user_groups
    WHERE id_users = @current_user_id
);
```

#### Prepared Statements

```php
class PageDb {
    public function query_db($sql, $params = array()) {
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters to prevent SQL injection
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $this->get_param_type($value));
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function get_param_type($value) {
        if (is_int($value)) return PDO::PARAM_INT;
        if (is_bool($value)) return PDO::PARAM_BOOL;
        return PDO::PARAM_STR;
    }
}
```

## Input Validation and Sanitization

### Input Filtering

```php
class InputValidator {
    public function sanitize_string($input) {
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);

        // Convert special characters to HTML entities
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validate_integer($input, $min = null, $max = null) {
        if (!is_numeric($input)) {
            return false;
        }

        $num = (int)$input;

        if ($min !== null && $num < $min) {
            return false;
        }

        if ($max !== null && $num > $max) {
            return false;
        }

        return true;
    }
}
```

### File Upload Security

```php
class FileUpload {
    private $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    private $max_file_size = 5242880; // 5MB

    public function validate_upload($file) {
        // Check file size
        if ($file['size'] > $this->max_file_size) {
            throw new Exception('File too large');
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowed_extensions)) {
            throw new Exception('Invalid file type');
        }

        // Check MIME type
        $mime_type = mime_content_type($file['tmp_name']);
        if (!$this->is_allowed_mime_type($mime_type)) {
            throw new Exception('Invalid MIME type');
        }

        // Scan for malware (if enabled)
        if ($this->virus_scan_enabled && !$this->scan_file($file['tmp_name'])) {
            throw new Exception('File failed security scan');
        }

        return true;
    }

    public function secure_filename($filename) {
        // Remove path traversal attempts
        $filename = basename($filename);

        // Replace dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        return $filename;
    }
}
```

## XSS Protection

### Content Security Policy (CSP)

```php
class SecurityHeaders {
    public function set_csp_headers() {
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://code.jquery.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ];

        header("Content-Security-Policy: " . implode('; ', $csp));
    }

    public function set_security_headers() {
        // Prevent clickjacking
        header("X-Frame-Options: DENY");

        // Prevent MIME type sniffing
        header("X-Content-Type-Options: nosniff");

        // Enable XSS protection
        header("X-XSS-Protection: 1; mode=block");

        // Referrer policy
        header("Referrer-Policy: strict-origin-when-cross-origin");

        // HSTS for HTTPS
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}
```

### Output Escaping

```php
class ViewHelper {
    public function escape_html($content) {
        return htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function escape_js($content) {
        return json_encode($content, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public function escape_css($content) {
        // Basic CSS escaping
        return str_replace(['<', '>', '"', "'", '(', ')', ';'], '', $content);
    }
}
```

## CSRF Protection

### Token-Based Protection

```php
class CsrfProtection {
    public function generate_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validate_token($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function insert_token_into_form($html) {
        $token = $this->generate_token();
        $hidden_field = '<input type="hidden" name="csrf_token" value="' . $this->escape_html($token) . '">';

        // Insert before closing form tag
        return str_replace('</form>', $hidden_field . '</form>', $html);
    }
}
```

### AJAX CSRF Protection

```javascript
// Add CSRF token to all AJAX requests
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('X-CSRF-Token', getCsrfToken());
    }
});

function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content') ||
           $('input[name="csrf_token"]').val();
}
```

## Password Security

### Password Hashing

```php
class PasswordSecurity {
    public function hash_password($password) {
        // Use Argon2ID if available (PHP 7.3+), otherwise bcrypt
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536, // 64MB
                'time_cost' => 4,
                'threads' => 3
            ]);
        } else {
            return password_hash($password, PASSWORD_DEFAULT);
        }
    }

    public function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }

    public function needs_rehash($hash) {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
```

### Password Policies

```php
class PasswordValidator {
    public function validate_password_strength($password) {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return $errors;
    }

    public function check_password_history($user_id, $password) {
        // Check if password was used recently
        $recent_passwords = $this->get_recent_passwords($user_id, 5);

        foreach ($recent_passwords as $old_hash) {
            if (password_verify($password, $old_hash)) {
                return false; // Password reuse not allowed
            }
        }

        return true;
    }
}
```

## API Security

### JWT Token Management

```php
class JwtManager {
    private $secret_key;
    private $algorithm = 'HS256';

    public function generate_token($user_data, $expiration = null) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $header_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        $payload = $user_data;
        $payload['iat'] = time();
        $payload['exp'] = $expiration ?: time() + ACCESS_TOKEN_EXPIRATION;
        $payload_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = hash_hmac('sha256', $header_encoded . "." . $payload_encoded, $this->secret_key, true);
        $signature_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }

    public function validate_token($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        // Verify signature
        $expected_signature = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $expected_signature_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expected_signature));

        if (!hash_equals($signature, $expected_signature_encoded)) {
            return false;
        }

        // Check expiration
        $payload_data = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        if ($payload_data['exp'] < time()) {
            return false;
        }

        return $payload_data;
    }
}
```

### Mobile API Security

```php
class MobileApi {
    public function authenticate_request() {
        $headers = getallheaders();

        // Check for Bearer token
        if (!isset($headers['Authorization'])) {
            return $this->unauthorized_response();
        }

        $auth_header = $headers['Authorization'];
        if (!preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return $this->unauthorized_response();
        }

        $token = $matches[1];
        $jwt_manager = new JwtManager();

        $user_data = $jwt_manager->validate_token($token);
        if (!$user_data) {
            return $this->unauthorized_response();
        }

        // Set user context for request
        $this->set_user_context($user_data);

        return true;
    }

    private function unauthorized_response() {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['error' => 'Invalid or missing authentication token']);
        exit;
    }
}
```

## Audit Logging

### Security Event Logging

```php
class SecurityLogger {
    public function log_security_event($event_type, $user_id, $details = []) {
        $log_entry = [
            'event_type' => $event_type,
            'user_id' => $user_id,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'details' => json_encode($details)
        ];

        $this->db->insert('security_logs', $log_entry);

        // Alert on critical events
        if (in_array($event_type, ['failed_login_brute_force', 'suspicious_activity'])) {
            $this->send_security_alert($event_type, $details);
        }
    }

    public function log_failed_login($username, $reason) {
        $this->log_security_event('failed_login', null, [
            'username' => $username,
            'reason' => $reason,
            'attempt_count' => $this->get_failed_attempt_count($username)
        ]);
    }
}
```

### Transaction Auditing

```php
class TransactionLogger {
    public function log_transaction($operation, $table, $record_id, $user_id, $details = null) {
        $transaction_data = [
            'operation' => $operation, // INSERT, UPDATE, DELETE, SELECT
            'table_name' => $table,
            'record_id' => $record_id,
            'user_id' => $user_id,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'details' => $details ? json_encode($details) : null
        ];

        $this->db->insert('transactions', $transaction_data);
    }

    public function log_data_modification($operation, $table, $record_id, $old_data = null, $new_data = null) {
        $details = [];

        if ($old_data) {
            $details['old_data'] = $old_data;
        }

        if ($new_data) {
            $details['new_data'] = $new_data;
        }

        $this->log_transaction($operation, $table, $record_id,
                             $_SESSION['id_user'] ?? null, $details);
    }
}
```

## Incident Response

### Security Monitoring

```php
class SecurityMonitor {
    public function detect_brute_force($username, $ip_address) {
        $time_window = 900; // 15 minutes
        $max_attempts = 5;

        $attempts = $this->db->query_db(
            "SELECT COUNT(*) as count FROM security_logs
             WHERE event_type = 'failed_login'
             AND details LIKE ?
             AND timestamp > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            ['%"username":"' . $username . '"%', $time_window / 60]
        );

        if ($attempts[0]['count'] >= $max_attempts) {
            $this->block_ip_address($ip_address, $time_window);
            $this->alert_administrators('brute_force_detected', [
                'username' => $username,
                'ip_address' => $ip_address,
                'attempts' => $attempts[0]['count']
            ]);
        }
    }

    public function detect_suspicious_activity($user_id, $activity_type) {
        // Implement anomaly detection logic
        $recent_activity = $this->get_recent_user_activity($user_id, 3600); // Last hour

        if ($this->is_anomalous_activity($recent_activity, $activity_type)) {
            $this->log_security_event('suspicious_activity', $user_id, [
                'activity_type' => $activity_type,
                'recent_activity' => $recent_activity
            ]);
        }
    }
}
```

### Automated Responses

```php
class AutomatedSecurity {
    public function block_ip_address($ip_address, $duration_seconds) {
        $this->db->insert('blocked_ips', [
            'ip_address' => $ip_address,
            'blocked_until' => date('Y-m-d H:i:s', time() + $duration_seconds),
            'reason' => 'automated_security_response'
        ]);
    }

    public function temporary_account_lock($user_id, $duration_minutes = 30) {
        $lock_until = date('Y-m-d H:i:s', time() + ($duration_minutes * 60));

        $this->db->update_by_ids('users', [
            'locked_until' => $lock_until,
            'failed_login_attempts' => 0
        ], ['id' => $user_id]);

        $this->log_security_event('account_locked', $user_id, [
            'duration_minutes' => $duration_minutes,
            'reason' => 'security_policy'
        ]);
    }

    public function require_password_reset($user_id) {
        $this->db->update_by_ids('users', [
            'password_reset_required' => 1,
            'password_reset_token' => $this->generate_reset_token()
        ], ['id' => $user_id]);

        // Send password reset email
        $this->send_password_reset_email($user_id);
    }
}
```
