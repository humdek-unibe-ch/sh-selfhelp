# Configuration and Deployment

## Overview

SelfHelp configuration is managed through PHP files and environment variables. The system supports multiple environments (development, staging, production) with different configuration profiles.

## Configuration Files

### Core Configuration Files

#### globals_untracked.default.php

Default configuration template:

```php
<?php
// Database Configuration
define('DBSERVER', 'localhost');
define('DBNAME', 'selfhelp');
define('DBUSER', 'selfhelp_user');
define('DBPW', 'secure_password');

// Application Settings
define('PROJECT_NAME', 'selfhelp');
define('DEBUG', true);
define('CLOCKWORK_PROFILE', true);
define('LANGUAGE', 2); // 2 = German

// Security Settings
define('JWT_SECRET', 'your-jwt-secret-key');
define('CALLBACK_KEY', 'your-callback-secret');
define('ACCESS_TOKEN_EXPIRATION', 3600); // 1 hour
define('REFRESH_TOKEN_EXPIRATION', 2592000); // 30 days

// Path Configuration
define('BASE_PATH', '/selfhelp');

// Feature Flags
define('CORS', true);
define('SHOW_PHP_INFO', false);
define('REDIRECT_ON_LOGIN', true);
```

#### globals_untracked.php

Environment-specific overrides (gitignored):

```php
<?php
// Production overrides
define('DEBUG', false);
define('CLOCKWORK_PROFILE', false);
define('DBSERVER', 'production-db-server');
define('DBPW', 'production-password');
define('JWT_SECRET', 'production-jwt-secret');
```

### Plugin Configuration

Plugins can have their own configuration:

```
server/plugins/{plugin_name}/
└── server/service/
    └── globals.php
```

## Environment Management

### Development Environment

```php
// globals_untracked.php (development)
define('DEBUG', true);
define('CLOCKWORK_PROFILE', true);
define('DBNAME', 'selfhelp_dev');
define('DBUSER', 'dev_user');
define('DBPW', 'dev_password');
```

### Production Environment

```php
// globals_untracked.php (production)
define('DEBUG', false);
define('CLOCKWORK_PROFILE', false);
define('DBNAME', 'selfhelp_prod');
define('DBUSER', 'prod_user');
define('DBPW', 'prod_password');
define('SESSION_TIMEOUT', 3600); // 1 hour
```

### Staging Environment

```php
// globals_untracked.php (staging)
define('DEBUG', false);
define('CLOCKWORK_PROFILE', true); // For monitoring
define('DBNAME', 'selfhelp_staging');
define('DBUSER', 'staging_user');
define('DBPW', 'staging_password');
```

## Web Server Configuration

### Apache Configuration

#### Virtual Host Configuration

```apache
<VirtualHost *:80>
    ServerName selfhelp.example.com
    DocumentRoot /var/www/html/selfhelp

    # Enable URL rewriting
    <Directory /var/www/html/selfhelp>
        AllowOverride All
        Require all granted
    </Directory>

    # Protect sensitive directories
    <Directory /var/www/html/selfhelp/server>
        Require all denied
    </Directory>

    # Asset access
    <Directory /var/www/html/selfhelp/assets>
        <Files ~ "\.(php|sql)$">
            Require all denied
        </Files>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/selfhelp_error.log
    CustomLog ${APACHE_LOG_DIR}/selfhelp_access.log combined
</VirtualHost>
```

#### .htaccess Configuration

```apache
# Redirect to HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove trailing slashes
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/$ /$1 [R=301,L]

# Handle PHP files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set Referrer-Policy strict-origin-when-cross-origin
    Header always set Content-Security-Policy "default-src 'self'"
</IfModule>

# Compress assets
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name selfhelp.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name selfhelp.example.com;

    root /var/www/html/selfhelp;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/selfhelp.crt;
    ssl_certificate_key /etc/ssl/private/selfhelp.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header Referrer-Policy strict-origin-when-cross-origin;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    # Protect sensitive directories
    location ~ ^/(server|config|logs) {
        deny all;
        return 404;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## PHP Configuration

### PHP-FPM Configuration

```ini
; php.ini
max_execution_time = 300
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20

; Session configuration
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = Lax

; Error reporting
display_errors = Off
log_errors = On
error_log = /var/log/php/selfhelp.log

; Extensions
extension = pdo_mysql
extension = apcu
extension = mbstring
extension = curl
extension = gd
extension = zip
```

### Opcache Configuration

```ini
; Opcache settings for production
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 7963
opcache.revalidate_freq = 0
opcache.validate_timestamps = 0
opcache.preload = /var/www/html/selfhelp/preload.php
```

## Database Setup

### MySQL Configuration

```ini
# my.cnf
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 1
max_connections = 200
query_cache_size = 256M
query_cache_type = 1

# Character set
character_set_server = utf8mb4
collation_server = utf8mb4_unicode_ci

# Logging
general_log = 0
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### Database User Setup

```sql
-- Create database
CREATE DATABASE selfhelp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'selfhelp'@'localhost' IDENTIFIED BY 'secure_password';

-- Grant permissions
GRANT ALL PRIVILEGES ON selfhelp.* TO 'selfhelp'@'localhost';
GRANT RELOAD ON *.* TO 'selfhelp'@'localhost'; -- For FLUSH PRIVILEGES
FLUSH PRIVILEGES;
```

## Caching Configuration

### APCu Configuration

```ini
# APCu settings
apcu.enabled = 1
apcu.shm_size = 256M
apcu.ttl = 7200
apcu.enable_cli = 1
```

### Cache Key Strategy

```php
// Cache key generation
define('CACHE_PREFIX', PROJECT_NAME . '_');
define('CACHE_TTL', 3600); // 1 hour

function generate_cache_key($type, $id, $params = []) {
    $key = CACHE_PREFIX . $type . '_' . $id;
    if (!empty($params)) {
        $key .= '_' . md5(serialize($params));
    }
    return $key;
}
```

## Security Configuration

### File Permissions

```bash
# Set proper permissions
chown -R www-data:www-data /var/www/html/selfhelp
find /var/www/html/selfhelp -type f -exec chmod 644 {} \;
find /var/www/html/selfhelp -type d -exec chmod 755 {} \;

# Secure sensitive files
chmod 600 /var/www/html/selfhelp/server/service/globals_untracked.php
chmod 600 /var/www/html/selfhelp/server/db/*.sql

# Make assets writable for uploads
chmod 775 /var/www/html/selfhelp/assets
chmod 775 /var/www/html/selfhelp/static
```

### Firewall Configuration

```bash
# UFW configuration
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw allow 3306/tcp  # MySQL (restrict to specific IPs if possible)
ufw --force enable
```

### SSL/TLS Configuration

```bash
# Let's Encrypt SSL certificate
certbot --nginx -d selfhelp.example.com

# Verify SSL configuration
openssl s_client -connect selfhelp.example.com:443 -servername selfhelp.example.com
```

## Monitoring and Logging

### Log Configuration

```bash
# Create log directories
mkdir -p /var/log/selfhelp
chown www-data:www-data /var/log/selfhelp

# PHP error logging
echo "error_log = /var/log/selfhelp/php_errors.log" >> /etc/php/8.1/fpm/php.ini

# Application logging
echo "log_errors = On" >> /etc/php/8.1/fpm/php.ini
echo "error_reporting = E_ALL & ~E_DEPRECATED" >> /etc/php/8.1/fpm/php.ini
```

### Monitoring Setup

```bash
# Install monitoring tools
apt install htop iotop sysstat

# Set up log rotation
cat > /etc/logrotate.d/selfhelp << EOF
/var/log/selfhelp/*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    create 644 www-data www-data
    postrotate
        systemctl reload nginx
    endscript
}
EOF
```

## Deployment Process

### Automated Deployment

```bash
#!/bin/bash
# deploy.sh

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}Starting deployment...${NC}"

# Backup database
mysqldump -u selfhelp -p selfhelp > backup_$(date +%Y%m%d_%H%M%S).sql

# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run database migrations
php migrate.php

# Build assets
cd gulp
npm install
gulp

# Clear caches
php clear_cache.php

# Set permissions
chmod 755 /var/www/html/selfhelp
chown -R www-data:www-data /var/www/html/selfhelp

# Restart services
systemctl reload nginx
systemctl reload php8.1-fpm

echo -e "${GREEN}Deployment completed successfully!${NC}"
```

### Rollback Procedure

```bash
#!/bin/bash
# rollback.sh

echo "Starting rollback..."

# Restore database backup
mysql -u selfhelp -p selfhelp < backup_file.sql

# Revert code changes
git reset --hard HEAD~1
git push origin main --force

# Clear caches
php clear_cache.php

# Restart services
systemctl reload nginx
systemctl reload php8.1-fpm

echo "Rollback completed."
```

## Performance Tuning

### MySQL Optimization

```sql
-- MySQL performance tuning
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL innodb_log_file_size = 268435456;     -- 256MB
SET GLOBAL query_cache_size = 268435456;         -- 256MB
SET GLOBAL max_connections = 200;

-- Analyze slow queries
SELECT sql_text, exec_count, avg_timer_wait/1000000000 avg_sec
FROM performance_schema.events_statements_summary_by_digest
ORDER BY avg_timer_wait DESC LIMIT 10;
```

### PHP Optimization

```ini
# PHP performance settings
realpath_cache_size = 4096k
realpath_cache_ttl = 600

# Opcache tuning
opcache.max_accelerated_files = 7963
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.fast_shutdown = 1
```

### APCu Monitoring

```php
// APCu cache statistics
$cache_info = apcu_cache_info();
$mem_info = apcu_sma_info();

echo "Cache hits: " . $cache_info['num_hits'] . "\n";
echo "Cache misses: " . $cache_info['num_misses'] . "\n";
echo "Memory used: " . ($mem_info['seg_size'] - $mem_info['avail_mem']) . " bytes\n";
echo "Memory available: " . $mem_info['avail_mem'] . " bytes\n";
```

## Backup Strategy

### Database Backup

```bash
#!/bin/bash
# backup_db.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/selfhelp"
DB_NAME="selfhelp"
DB_USER="selfhelp"

mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/${DB_NAME}_${DATE}.sql

# Compress
gzip $BACKUP_DIR/${DB_NAME}_${DATE}.sql

# Keep only last 30 backups
cd $BACKUP_DIR
ls -t *.sql.gz | tail -n +31 | xargs -r rm

echo "Database backup completed: ${DB_NAME}_${DATE}.sql.gz"
```

### File Backup

```bash
#!/bin/bash
# backup_files.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/selfhelp/files"
SOURCE_DIR="/var/www/html/selfhelp"

mkdir -p $BACKUP_DIR

# Backup uploaded files and user data
tar -czf $BACKUP_DIR/files_${DATE}.tar.gz \
    -C $SOURCE_DIR \
    assets/ \
    static/ \
    data/

# Keep only last 30 backups
cd $BACKUP_DIR
ls -t *.tar.gz | tail -n +31 | xargs -r rm

echo "File backup completed: files_${DATE}.tar.gz"
```

## Troubleshooting

### Common Issues

#### Database Connection Issues

```bash
# Check MySQL service
systemctl status mysql

# Test database connection
mysql -u selfhelp -p -e "SELECT 1;"

# Check MySQL error logs
tail -f /var/log/mysql/error.log
```

#### PHP Errors

```bash
# Check PHP-FPM status
systemctl status php8.1-fpm

# Check PHP error logs
tail -f /var/log/selfhelp/php_errors.log

# Test PHP configuration
php -c /etc/php/8.1/fpm/php.ini -l
```

#### Permission Issues

```bash
# Check file ownership
ls -la /var/www/html/selfhelp/

# Check SELinux/AppArmor
ausearch -m avc -ts recent | grep selfhelp

# Fix permissions
chown -R www-data:www-data /var/www/html/selfhelp
find /var/www/html/selfhelp -type d -exec chmod 755 {} \;
find /var/www/html/selfhelp -type f -exec chmod 644 {} \;
```

#### Performance Issues

```bash
# Check system resources
top
iotop
free -h

# Check MySQL slow queries
tail -f /var/log/mysql/slow.log

# Profile PHP performance
php -d xdebug.profiler_enable=1 index.php
```
