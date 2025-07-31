# Deployment Process

## 🚀 Overview

The SelfHelp Symfony Backend follows a structured deployment process that emphasizes database version management, zero-downtime deployments, and comprehensive validation. The deployment strategy is built around the version management system where database changes trigger major version increments.

## 🏗️ Deployment Architecture

```mermaid
graph TD
    A[Development] --> B[Version Check]
    B --> C{Database Changes?}
    C -->|Yes| D[Major Version<br/>7.5.1 → 7.6.0]
    C -->|No| E[Minor Version<br/>7.5.1 → 7.5.2]
    
    D --> F[Create Migration Script]
    E --> G[Code Deployment]
    
    F --> H[Test Migration]
    G --> I[Run Tests]
    
    H --> J[Staging Deployment]
    I --> J
    
    J --> K[Staging Validation]
    K --> L[Production Deployment]
    L --> M[Post-Deployment Validation]
    
    subgraph "Deployment Environments"
        N[Development]
        O[Staging]
        P[Production]
    end
    
    subgraph "Validation Steps"
        Q[Database Migration]
        R[API Health Check]
        S[Integration Tests]
        T[Performance Tests]
    end
```

## 📋 Version Management Strategy

### Version Increment Rules
| Change Type | Version Impact | Example | Migration Required |
|-------------|----------------|---------|-------------------|
| **Database Schema Changes** | Major | 7.5.1 → 7.6.0 | ✅ Yes |
| **New Tables/Columns** | Major | 7.5.1 → 7.6.0 | ✅ Yes |
| **API Breaking Changes** | Major | 7.5.1 → 7.6.0 | ✅ Yes |
| **New Features (Non-breaking)** | Minor | 7.5.1 → 7.5.2 | ❌ No |
| **Bug Fixes** | Minor | 7.5.1 → 7.5.2 | ❌ No |
| **Code Refactoring** | Minor | 7.5.1 → 7.5.2 | ❌ No |

### Version Tracking
```sql
-- Database version is tracked in the version table
CREATE TABLE `version` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Current version check
SELECT version FROM version ORDER BY id DESC LIMIT 1;
```

## 🗄️ Database Migration Process

### Migration Script Structure
```sql
-- File: db/update_scripts/40_update_v8.0.0_v8.1.0.sql

-- 1. ALWAYS UPDATE VERSION FIRST
UPDATE version SET version = 'v8.1.0';

-- 2. DOCUMENT CHANGES
-- New Feature: User Profile Management System
-- - Added user_profiles table
-- - Added profile_types lookup values
-- - Updated user entity relationships
-- - Added profile management API endpoints

-- 3. SCHEMA CHANGES
CREATE TABLE `user_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_users` int NOT NULL,
  `profile_type` varchar(50) NOT NULL,
  `profile_data` json NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_profile` (`id_users`, `profile_type`),
  FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. ADD INDEXES
CALL add_index('user_profiles', 'idx_profile_type', 'profile_type', FALSE);
CALL add_index('user_profiles', 'idx_created_at', 'created_at', FALSE);

-- 5. INSERT LOOKUP DATA
INSERT INTO `lookups` (`type_code`, `code`, `description`) VALUES
('PROFILE_TYPES', 'PERSONAL', 'Personal user profile'),
('PROFILE_TYPES', 'BUSINESS', 'Business user profile'),
('PROFILE_TYPES', 'PROFESSIONAL', 'Professional user profile');

-- 6. DATA MIGRATIONS (if needed)
-- Migrate existing user data to new structure
INSERT INTO `user_profiles` (`id_users`, `profile_type`, `profile_data`)
SELECT id, 'PERSONAL', JSON_OBJECT('migrated', true)
FROM `users` 
WHERE id NOT IN (SELECT id_users FROM user_profiles);

-- 7. UPDATE STORED PROCEDURES (if needed)
DELIMITER //
DROP PROCEDURE IF EXISTS get_user_profile //
CREATE PROCEDURE get_user_profile(IN userId INT, IN profileType VARCHAR(50))
BEGIN
    SELECT up.*, u.username, u.email
    FROM user_profiles up
    JOIN users u ON up.id_users = u.id
    WHERE up.id_users = userId 
    AND up.profile_type = profileType;
END //
DELIMITER ;
```

### API Routes Update
```sql
-- File: db/update_scripts/api_routes.sql (append new routes)

-- User Profile Management API Routes
INSERT INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `params`) VALUES
('admin_get_user_profiles', 'v1', '/admin/users/{userId}/profiles', 'App\\Controller\\AdminUserProfileController::getProfiles', 'GET', 
'{"userId": {"in": "path", "required": true, "type": "integer"}}'),

('admin_create_user_profile', 'v1', '/admin/users/{userId}/profiles', 'App\\Controller\\AdminUserProfileController::createProfile', 'POST',
'{"userId": {"in": "path", "required": true, "type": "integer"}, "profile": {"in": "body", "required": true}}'),

('admin_update_user_profile', 'v1', '/admin/users/{userId}/profiles/{profileId}', 'App\\Controller\\AdminUserProfileController::updateProfile', 'PUT',
'{"userId": {"in": "path", "required": true, "type": "integer"}, "profileId": {"in": "path", "required": true, "type": "integer"}, "profile": {"in": "body", "required": true}}'),

('admin_delete_user_profile', 'v1', '/admin/users/{userId}/profiles/{profileId}', 'App\\Controller\\AdminUserProfileController::deleteProfile', 'DELETE',
'{"userId": {"in": "path", "required": true, "type": "integer"}, "profileId": {"in": "path", "required": true, "type": "integer"}}');

-- Associate routes with permissions
INSERT INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT ar.id, p.id 
FROM `api_routes` ar, `permissions` p
WHERE ar.route_name IN ('admin_get_user_profiles', 'admin_create_user_profile', 'admin_update_user_profile', 'admin_delete_user_profile')
AND p.name = 'admin.user.manage';
```

## 🔧 Pre-Deployment Checklist

### Development Phase
- [ ] **Code Review Completed**
  - All code changes reviewed and approved
  - Security implications assessed
  - Performance impact evaluated

- [ ] **Version Determination**
  - Database changes identified
  - Version increment decided (major vs minor)
  - Migration script created (if needed)

- [ ] **Documentation Updated**
  - API documentation updated
  - Database schema documented
  - Deployment notes prepared

- [ ] **Tests Passing**
  - All unit tests passing
  - Integration tests successful
  - API tests validated
  - Performance tests within limits

### Migration Validation
```bash
# Validate migration script syntax
mysql --dry-run < db/update_scripts/40_update_v8.0.0_v8.1.0.sql

# Check for dangerous operations
grep -E "(DROP TABLE|DROP DATABASE|TRUNCATE)" db/update_scripts/40_update_v8.0.0_v8.1.0.sql

# Verify version update is first statement
head -10 db/update_scripts/40_update_v8.0.0_v8.1.0.sql | grep "UPDATE version"
```

### Database Backup
```bash
# Create backup before deployment
mysqldump -u username -p --routines --triggers selfhelp_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup integrity
mysql -u username -p selfhelp_test < backup_$(date +%Y%m%d_%H%M%S).sql
```

## 🏗️ Staging Deployment

### Staging Environment Setup
```bash
# 1. Deploy code to staging
git clone https://github.com/your-org/selfhelp-backend.git /var/www/staging
cd /var/www/staging
git checkout main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Configure environment
cp .env.staging .env
php bin/console cache:clear --env=prod

# 4. Run database migration (if major version)
mysql -u staging_user -p staging_db < db/update_scripts/40_update_v8.0.0_v8.1.0.sql

# 5. Verify version
mysql -u staging_user -p staging_db -e "SELECT version FROM version ORDER BY id DESC LIMIT 1;"
```

### Staging Validation
```bash
# 1. Health check
curl -X GET https://staging.selfhelp.com/cms-api/v1/health

# 2. Database connectivity
php bin/console doctrine:schema:validate --env=prod

# 3. API endpoints test
curl -X GET https://staging.selfhelp.com/cms-api/v1/admin/pages \
  -H "Authorization: Bearer $STAGING_TOKEN"

# 4. Run integration tests against staging
php bin/phpunit --testsuite=Integration --env=staging
```

## 🚀 Production Deployment

### Production Deployment Script
```bash
#!/bin/bash
# deploy-production.sh

set -e  # Exit on any error

BACKUP_DIR="/var/backups/selfhelp"
APP_DIR="/var/www/selfhelp"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "🚀 Starting production deployment at $(date)"

# 1. Create backup
echo "📦 Creating database backup..."
mkdir -p $BACKUP_DIR
mysqldump -u prod_user -p$DB_PASSWORD --routines --triggers selfhelp_production > $BACKUP_DIR/backup_$TIMESTAMP.sql

# 2. Put application in maintenance mode
echo "🔧 Enabling maintenance mode..."
touch $APP_DIR/maintenance.lock

# 3. Deploy new code
echo "📥 Deploying new code..."
cd $APP_DIR
git fetch origin
git checkout main
git pull origin main

# 4. Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 5. Clear caches
echo "🗑️ Clearing caches..."
php bin/console cache:clear --env=prod --no-interaction
php bin/console cache:warmup --env=prod --no-interaction

# 6. Run database migration (if exists)
if [ -f "db/update_scripts/40_update_v8.0.0_v8.1.0.sql" ]; then
    echo "🗄️ Running database migration..."
    mysql -u prod_user -p$DB_PASSWORD selfhelp_production < db/update_scripts/40_update_v8.0.0_v8.1.0.sql
    
    # Verify version
    NEW_VERSION=$(mysql -u prod_user -p$DB_PASSWORD selfhelp_production -e "SELECT version FROM version ORDER BY id DESC LIMIT 1;" -s -N)
    echo "✅ Database updated to version: $NEW_VERSION"
fi

# 7. Run post-deployment validations
echo "🔍 Running post-deployment validations..."

# Check database connectivity
php bin/console doctrine:schema:validate --env=prod

# Health check
HEALTH_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/cms-api/v1/health)
if [ "$HEALTH_STATUS" != "200" ]; then
    echo "❌ Health check failed with status: $HEALTH_STATUS"
    exit 1
fi

# 8. Remove maintenance mode
echo "✅ Disabling maintenance mode..."
rm -f $APP_DIR/maintenance.lock

# 9. Final verification
echo "🔍 Final verification..."
sleep 5  # Wait for services to stabilize

# Test API endpoint
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://api.selfhelp.com/cms-api/v1/admin/pages -H "Authorization: Bearer $PROD_TEST_TOKEN")
if [ "$API_STATUS" != "200" ]; then
    echo "❌ API test failed with status: $API_STATUS"
    echo "🔄 Consider rollback procedures"
    exit 1
fi

echo "🎉 Production deployment completed successfully at $(date)"
echo "📊 Backup created: $BACKUP_DIR/backup_$TIMESTAMP.sql"
echo "🔢 Database version: $NEW_VERSION"
```

### Zero-Downtime Deployment (Advanced)
```bash
#!/bin/bash
# zero-downtime-deploy.sh

# Use blue-green deployment strategy
CURRENT_ENV=$(readlink /var/www/selfhelp-current)
if [[ $CURRENT_ENV == *"blue"* ]]; then
    NEW_ENV="/var/www/selfhelp-green"
    OLD_ENV="/var/www/selfhelp-blue"
else
    NEW_ENV="/var/www/selfhelp-blue"
    OLD_ENV="/var/www/selfhelp-green"
fi

echo "🔄 Deploying to $NEW_ENV"

# 1. Deploy to inactive environment
cd $NEW_ENV
git pull origin main
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod

# 2. Run database migration (shared database)
if [ -f "db/update_scripts/40_update_v8.0.0_v8.1.0.sql" ]; then
    mysql -u prod_user -p$DB_PASSWORD selfhelp_production < db/update_scripts/40_update_v8.0.0_v8.1.0.sql
fi

# 3. Validate new environment
curl -f http://localhost:8080/cms-api/v1/health || exit 1

# 4. Switch traffic (update symlink)
ln -sfn $NEW_ENV /var/www/selfhelp-current

# 5. Reload web server
sudo systemctl reload nginx

echo "✅ Zero-downtime deployment completed"
```

## 🔍 Post-Deployment Validation

### Automated Validation Script
```bash
#!/bin/bash
# post-deployment-validation.sh

echo "🔍 Starting post-deployment validation..."

# 1. Database version check
DB_VERSION=$(mysql -u prod_user -p$DB_PASSWORD selfhelp_production -e "SELECT version FROM version ORDER BY id DESC LIMIT 1;" -s -N)
echo "📊 Current database version: $DB_VERSION"

# 2. API health check
echo "🏥 Checking API health..."
HEALTH_RESPONSE=$(curl -s https://api.selfhelp.com/cms-api/v1/health)
echo "Health response: $HEALTH_RESPONSE"

# 3. Database connectivity
echo "🗄️ Validating database schema..."
cd /var/www/selfhelp
php bin/console doctrine:schema:validate --env=prod

# 4. Critical API endpoints
echo "🔗 Testing critical API endpoints..."

# Test authentication
AUTH_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" https://api.selfhelp.com/cms-api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test_user","password":"test_password"}')
echo "Auth endpoint status: $AUTH_RESPONSE"

# Test admin pages endpoint
PAGES_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" https://api.selfhelp.com/cms-api/v1/admin/pages \
  -H "Authorization: Bearer $PROD_TEST_TOKEN")
echo "Pages endpoint status: $PAGES_RESPONSE"

# 5. Performance check
echo "⚡ Performance validation..."
RESPONSE_TIME=$(curl -s -o /dev/null -w "%{time_total}" https://api.selfhelp.com/cms-api/v1/admin/pages \
  -H "Authorization: Bearer $PROD_TEST_TOKEN")
echo "Response time: ${RESPONSE_TIME}s"

# Validate response time is reasonable (< 2 seconds)
if (( $(echo "$RESPONSE_TIME > 2.0" | bc -l) )); then
    echo "⚠️ Warning: Response time is high (${RESPONSE_TIME}s)"
else
    echo "✅ Response time is acceptable"
fi

# 6. Log check
echo "📋 Checking for errors in logs..."
ERROR_COUNT=$(tail -100 /var/log/selfhelp/prod.log | grep -c "ERROR" || echo "0")
if [ "$ERROR_COUNT" -gt "0" ]; then
    echo "⚠️ Found $ERROR_COUNT errors in recent logs"
    tail -20 /var/log/selfhelp/prod.log | grep "ERROR"
else
    echo "✅ No recent errors in logs"
fi

# 7. Memory and CPU check
echo "💾 System resource check..."
MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.1f", $3/$2 * 100.0}')
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')

echo "Memory usage: ${MEMORY_USAGE}%"
echo "CPU usage: ${CPU_USAGE}%"

if (( $(echo "$MEMORY_USAGE > 90.0" | bc -l) )); then
    echo "⚠️ Warning: High memory usage"
fi

echo "🎉 Post-deployment validation completed"
```

## 🔄 Rollback Procedures

### Emergency Rollback Script
```bash
#!/bin/bash
# emergency-rollback.sh

BACKUP_FILE=$1
if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file>"
    echo "Available backups:"
    ls -la /var/backups/selfhelp/backup_*.sql
    exit 1
fi

echo "🚨 Starting emergency rollback..."

# 1. Put application in maintenance mode
touch /var/www/selfhelp/maintenance.lock

# 2. Restore database
echo "🗄️ Restoring database from $BACKUP_FILE..."
mysql -u prod_user -p$DB_PASSWORD selfhelp_production < $BACKUP_FILE

# 3. Rollback code (if needed)
echo "📥 Rolling back code..."
cd /var/www/selfhelp
git log --oneline -10  # Show recent commits
read -p "Enter commit hash to rollback to: " COMMIT_HASH
git checkout $COMMIT_HASH

# 4. Reinstall dependencies
composer install --no-dev --optimize-autoloader

# 5. Clear caches
php bin/console cache:clear --env=prod

# 6. Remove maintenance mode
rm -f /var/www/selfhelp/maintenance.lock

echo "✅ Emergency rollback completed"
```

### Gradual Rollback (Blue-Green)
```bash
#!/bin/bash
# gradual-rollback.sh

# Switch back to previous environment
CURRENT_ENV=$(readlink /var/www/selfhelp-current)
if [[ $CURRENT_ENV == *"blue"* ]]; then
    ROLLBACK_ENV="/var/www/selfhelp-green"
else
    ROLLBACK_ENV="/var/www/selfhelp-blue"
fi

echo "🔄 Rolling back to $ROLLBACK_ENV"

# Switch symlink
ln -sfn $ROLLBACK_ENV /var/www/selfhelp-current

# Reload web server
sudo systemctl reload nginx

echo "✅ Gradual rollback completed"
```

## 📊 Deployment Monitoring

### Monitoring Script
```bash
#!/bin/bash
# deployment-monitor.sh

WEBHOOK_URL="https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK"

send_notification() {
    local message=$1
    local color=$2
    
    curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"$message\", \"color\":\"$color\"}" \
        $WEBHOOK_URL
}

# Monitor deployment status
while true; do
    if [ -f "/var/www/selfhelp/maintenance.lock" ]; then
        echo "🔧 Application in maintenance mode"
    else
        # Check health
        HEALTH_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://api.selfhelp.com/cms-api/v1/health)
        if [ "$HEALTH_STATUS" == "200" ]; then
            echo "✅ Application healthy"
        else
            echo "❌ Application unhealthy (status: $HEALTH_STATUS)"
            send_notification "🚨 SelfHelp API is unhealthy (status: $HEALTH_STATUS)" "danger"
        fi
    fi
    
    sleep 30
done
```

## 📋 Environment-Specific Configurations

### Development Environment
```bash
# .env.dev
APP_ENV=dev
APP_DEBUG=true
DATABASE_URL="mysql://dev_user:dev_pass@localhost:3306/selfhelp_dev"
```

### Staging Environment
```bash
# .env.staging
APP_ENV=prod
APP_DEBUG=false
DATABASE_URL="mysql://staging_user:staging_pass@staging-db:3306/selfhelp_staging"
```

### Production Environment
```bash
# .env.prod
APP_ENV=prod
APP_DEBUG=false
DATABASE_URL="mysql://prod_user:prod_pass@prod-db:3306/selfhelp_production"
```

## 🔐 Security Considerations

### Deployment Security
- **Encrypted Connections**: All database connections use SSL
- **Environment Variables**: Sensitive data stored in environment variables
- **Access Control**: Deployment scripts require proper authentication
- **Audit Logging**: All deployment activities logged
- **Backup Encryption**: Database backups encrypted at rest

### Production Hardening
```bash
# production-hardening.sh

# 1. Set proper file permissions
chown -R www-data:www-data /var/www/selfhelp
chmod -R 755 /var/www/selfhelp
chmod -R 644 /var/www/selfhelp/config

# 2. Secure sensitive files
chmod 600 /var/www/selfhelp/.env
chmod 600 /var/www/selfhelp/config/jwt/*.pem

# 3. Remove development files
rm -f /var/www/selfhelp/.env.dev
rm -f /var/www/selfhelp/phpunit.xml.dist
rm -rf /var/www/selfhelp/tests

# 4. Configure web server security headers
# (This would be in nginx/apache configuration)
```

## 📈 Deployment Metrics

### Key Performance Indicators
- **Deployment Frequency**: Track how often deployments occur
- **Deployment Duration**: Time from start to completion
- **Success Rate**: Percentage of successful deployments
- **Rollback Rate**: Percentage of deployments requiring rollback
- **Mean Time to Recovery**: Average time to fix failed deployments

### Deployment Dashboard
```bash
# deployment-metrics.sh
echo "📊 Deployment Metrics Dashboard"
echo "================================"

# Count successful deployments this month
SUCCESSFUL_DEPLOYMENTS=$(grep "deployment completed successfully" /var/log/deployments.log | grep "$(date +%Y-%m)" | wc -l)
echo "✅ Successful deployments this month: $SUCCESSFUL_DEPLOYMENTS"

# Count rollbacks this month
ROLLBACKS=$(grep "rollback completed" /var/log/deployments.log | grep "$(date +%Y-%m)" | wc -l)
echo "🔄 Rollbacks this month: $ROLLBACKS"

# Calculate success rate
if [ $SUCCESSFUL_DEPLOYMENTS -gt 0 ]; then
    SUCCESS_RATE=$((100 * SUCCESSFUL_DEPLOYMENTS / (SUCCESSFUL_DEPLOYMENTS + ROLLBACKS)))
    echo "📈 Success rate: ${SUCCESS_RATE}%"
fi

# Average deployment time
AVG_TIME=$(grep "deployment completed" /var/log/deployments.log | grep "$(date +%Y-%m)" | awk '{print $NF}' | awk '{sum+=$1} END {print sum/NR}')
echo "⏱️ Average deployment time: ${AVG_TIME} minutes"
```

---

This completes the comprehensive developer documentation for the SelfHelp Symfony Backend. The documentation covers all aspects from system architecture to deployment processes, providing developers and AI coding agents with the complete knowledge needed to understand, maintain, and extend the system effectively.