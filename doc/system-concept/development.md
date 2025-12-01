# Development Workflow

## Overview

SelfHelp development follows a structured workflow with version-controlled database migrations, component-based development, and automated build processes. This document outlines the development practices and workflows for contributing to the SelfHelp project.

## Development Environment Setup

### Prerequisites

```bash
# Required software
PHP 8.2+ (8.3 recommended)
MySQL 8.0+
Node.js 14+ (for build tools)
Git
Composer (PHP dependency manager)
```

### Initial Setup

```bash
# Clone repository
git clone https://github.com/humdek-unibe-ch/sh-selfhelp.git
cd sh-selfhelp

# Install PHP dependencies
composer install

# Install Node.js dependencies
cd gulp
npm install

# Configure database
cp server/service/globals_untracked.default.php server/service/globals_untracked.php
# Edit globals_untracked.php with your database credentials

# Run database migrations (in order)
mysql -u username -p database < server/db/update_scripts/01_initial.sql
mysql -u username -p database < server/db/update_scripts/02_update_xyz.sql
# ... continue with all migration scripts

# Build frontend assets
gulp
```

### Development Server

```bash
# Start PHP built-in server (for development only)
php -S localhost:8000 -t .

# Or configure Apache/Nginx to serve the project
```

## Component Development

### Creating a New Component

1. **Plan the Component**
   - Define the component's purpose and interface
   - Identify required database fields
   - Plan mobile vs web output differences

2. **Create Directory Structure**
   ```bash
   mkdir -p server/component/style/new_component
   cd server/component/style/new_component
   ```

3. **Implement Component Files**
   ```php
   # NewComponent.php (main component class)
   <?php
   class NewComponent extends BaseComponent {
       public function __construct($services, $id, $params, $id_page, $entry_record) {
           $model = new NewModel($services, $id, $params, $id_page, $entry_record);
           $view = new NewView($model, $id);
           parent::__construct($model, $view);
       }
   }
   ```

   ```php
   # NewModel.php (data logic)
   <?php
   class NewModel extends BaseModel {
       public function get_component_data() {
           // Component-specific data loading
           return $this->load_data();
       }
   }
   ```

   ```php
   # NewView.php (presentation)
   <?php
   class NewView extends BaseView {
       public function output_content() {
           $data = $this->model->get_component_data();
           include 'templates/new_component.php';
       }

       public function get_css_includes() {
           return ['/css/components/new_component.css'];
       }

       public function get_js_includes() {
           return ['/js/components/new_component.js'];
       }
   }
   ```

4. **Add Template and Assets**
   ```html
   <!-- templates/new_component.php -->
   <div class="new-component">
       <h3><?php echo $this->escape_html($data['title']); ?></h3>
       <div class="content">
           <?php echo $data['content']; ?>
       </div>
   </div>
   ```

   ```css
   /* css/new_component.css */
   .new-component {
       border: 1px solid #ddd;
       padding: 1rem;
       margin: 1rem 0;
   }
   ```

   ```javascript
   // js/new_component.js
   $(document).ready(function() {
       $('.new-component').each(function() {
           initializeNewComponent($(this));
       });
   });

   function initializeNewComponent($element) {
       // Component initialization logic
   }
   ```

5. **Register Component in Database**
   ```sql
   -- Add component style definition
   INSERT INTO styles (name, description, style_group_id)
   VALUES ('new_component', 'Description of new component', 1);
   ```

### Component Testing

```php
# Basic component test
class NewComponentTest {
    public function test_component_renders() {
        $services = $this->create_mock_services();
        $component = new NewComponent($services, 1, [], 1, null);

        $output = $component->output_content();
        $this->assertContains('new-component', $output);
    }

    public function test_component_data_loading() {
        $model = new NewModel($this->services, 1, [], 1, null);
        $data = $model->get_component_data();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('title', $data);
    }
}
```

## Database Development

### Creating Database Migrations

1. **Analyze Schema Changes**
   - Identify required table modifications
   - Plan foreign key relationships
   - Consider data migration needs

2. **Create Migration Script**
   ```sql
   -- File: server/db/update_scripts/NN_update_vX.Y.Z_vX.Y.Z+1.sql

   -- Update version number
   UPDATE version SET version = 'vX.Y.Z+1';

   -- Begin transaction
   START TRANSACTION;

   -- Schema changes
   ALTER TABLE existing_table
   ADD COLUMN new_field VARCHAR(255) DEFAULT NULL;

   -- Data migrations (if needed)
   UPDATE existing_table
   SET new_field = 'default_value'
   WHERE new_field IS NULL;

   -- Add constraints
   ALTER TABLE existing_table
   ADD CONSTRAINT fk_table_reference
   FOREIGN KEY (reference_id) REFERENCES reference_table(id);

   -- Commit transaction
   COMMIT;
   ```

3. **Helper Procedures**
   ```sql
   -- Use existing helper procedures for safe operations
   CALL add_table_column('table_name', 'column_name', 'VARCHAR(255) NOT NULL DEFAULT ""');
   CALL drop_table_column('table_name', 'column_name');
   CALL add_foreign_key('table_name', 'column_name', 'reference_table', 'reference_column');
   ```

4. **Test Migration**
   ```bash
   # Test on development database
   mysql -u dev_user -p dev_database < migration_script.sql

   # Verify schema changes
   mysql -u dev_user -p dev_database -e "DESCRIBE table_name;"

   # Check data integrity
   mysql -u dev_user -p dev_database -e "SELECT COUNT(*) FROM table_name WHERE new_field IS NULL;"
   ```

### Database Version Management

```php
class DatabaseVersionManager {
    public function get_current_version() {
        $result = $this->db->query_db_first("SELECT version FROM version");
        return $result['version'];
    }

    public function update_version($new_version) {
        $this->db->update_by_ids('version', ['version' => $new_version], ['id' => 1]);
    }

    public function validate_migration_order() {
        $applied_migrations = $this->get_applied_migrations();
        $available_migrations = $this->get_available_migrations();

        foreach ($available_migrations as $migration) {
            if (!in_array($migration, $applied_migrations)) {
                throw new Exception("Missing migration: $migration");
            }
        }
    }
}
```

## Frontend Development

### Asset Management

1. **Component Assets**
   ```javascript
   // server/component/style/component_name/js/component_name.js
   $(document).ready(function() {
       // Component initialization
       $('.component-name').componentName();
   });

   $.fn.componentName = function(options) {
       return this.each(function() {
           var $element = $(this);
           var settings = $.extend({}, $.fn.componentName.defaults, options);

           // Component logic
       });
   };

   $.fn.componentName.defaults = {
       // Default settings
   };
   ```

2. **Build Process**
   ```bash
   # Development build (with source maps)
   gulp

   # Production build (minified)
   NODE_ENV=production gulp

   # Watch for changes
   gulp watch
   ```

### JavaScript Best Practices

```javascript
// Module pattern
var ComponentName = (function() {
    var privateVariable = 'private';

    function privateMethod() {
        // Private implementation
    }

    return {
        publicMethod: function() {
            privateMethod();
        },

        init: function(options) {
            // Initialization logic
        }
    };
})();

// Usage
ComponentName.init({ option: 'value' });
```

## Version Control

### Git Workflow

```bash
# Feature branch workflow
git checkout -b feature/new-component
git add .
git commit -m "Add new component with database changes

- Add NewComponent class
- Add database migration for new table
- Update frontend assets
- Add component documentation"

# Create pull request
git push origin feature/new-component

# After review and merge
git checkout main
git pull origin main
git branch -d feature/new-component
```

### Commit Message Convention

```
type(scope): description

[optional body]

[optional footer]
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Testing
- `chore`: Maintenance

Examples:
```
feat(components): add new chart component

Add interactive chart component with support for bar, line, and pie charts.
Database migration included for chart data storage.

BREAKING CHANGE: Chart component API has changed
```

## Testing

### Unit Testing

```php
# tests/ComponentTest.php
class ComponentTest extends PHPUnit_Framework_TestCase {
    protected $services;

    protected function setUp() {
        $this->services = $this->createMockServices();
    }

    public function test_component_creation() {
        $component = new TestComponent($this->services, 1, [], 1, null);
        $this->assertInstanceOf('TestComponent', $component);
    }

    public function test_component_output() {
        $component = new TestComponent($this->services, 1, [], 1, null);
        ob_start();
        $component->output_content();
        $output = ob_get_clean();

        $this->assertContains('expected-content', $output);
    }
}
```

### Integration Testing

```php
# tests/IntegrationTest.php
class IntegrationTest extends PHPUnit_Framework_TestCase {
    public function test_full_user_workflow() {
        // Create test user
        $user_id = $this->createTestUser();

        // Simulate login
        $login_result = $this->services->get_login()->authenticate('test@example.com', 'password');
        $this->assertTrue($login_result);

        // Test page access
        $has_access = $this->services->get_acl()->check_page_access('test_page', $user_id);
        $this->assertTrue($has_access);

        // Test component rendering
        $component = new TestComponent($this->services, 1, [], 1, null);
        $this->assertNotEmpty($component->output_content());
    }
}
```

### Automated Testing

```bash
# Run PHP tests
vendor/bin/phpunit tests/

# Run JavaScript tests (if implemented)
npm test

# Code coverage
vendor/bin/phpunit tests/ --coverage-html coverage/

# Continuous integration
# (Configure CI/CD pipeline to run tests on each push)
```

## Code Quality

### PHP Standards

```php
# PSR-4 Autoloading
# Classes in namespace SelfHelp\Component\Style
# Located in server/component/style/

# PSR-2 Code Style
class ClassName
{
    public function methodName()
    {
        if ($condition) {
            // Code here
        }
    }
}
```

### Code Analysis

```bash
# PHP CodeSniffer
vendor/bin/phpcs --standard=PSR2 server/

# PHP Mess Detector
vendor/bin/phpmd server/ text codesize,unusedcode,naming

# PHPStan (static analysis)
vendor/bin/phpstan analyse server/
```

### Pre-commit Hooks

```bash
# .pre-commit-config.yaml (if using pre-commit)
repos:
  - repo: local
    hooks:
      - id: phpcs
        name: PHP CodeSniffer
        entry: vendor/bin/phpcs
        language: system
        files: \.php$

      - id: phpunit
        name: PHPUnit
        entry: vendor/bin/phpunit
        language: system
        files: tests/.*\.php$
```

## Documentation

### Component Documentation

```php
/**
 * Chart Component
 *
 * Displays interactive charts with support for multiple chart types.
 *
 * @package SelfHelp\Component\Style
 * @author Developer Name
 * @version 1.0.0
 *
 * Configuration Fields:
 * - chart_type: Type of chart (bar, line, pie)
 * - data_source: Source of chart data
 * - height: Chart height in pixels
 * - width: Chart width in pixels
 *
 * Example Usage:
 * $component = new ChartComponent($services, $id, $params, $page_id, $entry_record);
 * $component->output_content();
 */
class ChartComponent extends BaseComponent
{
    // Implementation
}
```

### API Documentation

```php
/**
 * Get user data endpoint
 *
 * @api {get} /ajax/get_user_data Get User Data
 * @apiName GetUserData
 * @apiGroup User
 *
 * @apiParam {Number} user_id User's unique ID
 *
 * @apiSuccess {Object} data User data object
 * @apiSuccess {Number} data.id User ID
 * @apiSuccess {String} data.name User name
 *
 * @apiError {String} error Error message
 */
```

## Deployment

### Staging Deployment

```bash
# Deploy to staging
ssh staging-server << 'EOF'
cd /var/www/staging.selfhelp
git pull origin develop
composer install --no-dev
gulp
php migrate.php
systemctl reload nginx
EOF
```

### Production Deployment

```bash
# Production deployment with rollback capability
ssh production-server << 'EOF'
cd /var/www/selfhelp

# Backup current state
cp -r . deploy_backup_$(date +%Y%m%d_%H%M%S)

# Deploy new version
git pull origin main
composer install --no-dev --optimize-autoloader
gulp

# Run migrations
php migrate.php

# Clear caches
php clear_cache.php

# Reload services
systemctl reload php8.1-fpm
systemctl reload nginx

# Health check
curl -f https://selfhelp.example.com/health || exit 1
EOF
```

### Rollback Procedure

```bash
# Rollback script
ssh production-server << 'EOF'
cd /var/www/selfhelp

# Find latest backup
LATEST_BACKUP=$(ls -td deploy_backup_* | head -1)

if [ -d "$LATEST_BACKUP" ]; then
    # Restore from backup
    rm -rf *
    cp -r $LATEST_BACKUP/* .

    # Restore database if needed
    mysql -u prod_user -p prod_db < $LATEST_BACKUP/database_backup.sql

    # Reload services
    systemctl reload php8.1-fpm
    systemctl reload nginx
else
    echo "No backup found for rollback"
    exit 1
fi
EOF
```

## Performance Monitoring

### Application Monitoring

```php
# Enable profiling in development
define('CLOCKWORK_PROFILE', true);

// Clockwork integration
$clockwork = $services->get_clockwork();
$clockwork->start_event('component_render', 'Rendering chart component');

// Component logic here

$clockwork->end_event('component_render');
```

### Database Monitoring

```sql
-- Query performance analysis
SELECT
    sql_text,
    exec_count,
    avg_timer_wait/1000000000 avg_sec,
    sum_timer_wait/1000000000 total_sec
FROM performance_schema.events_statements_summary_by_digest
WHERE sql_text LIKE '%SELECT%'
ORDER BY avg_timer_wait DESC
LIMIT 10;
```

### Cache Monitoring

```php
class CacheMonitor {
    public function get_cache_stats() {
        $info = apcu_cache_info();

        return [
            'hits' => $info['num_hits'],
            'misses' => $info['num_misses'],
            'hit_rate' => $info['num_hits'] / ($info['num_hits'] + $info['num_misses']),
            'memory_usage' => $info['mem_size'],
            'uptime' => time() - $info['start_time']
        ];
    }

    public function log_cache_metrics() {
        $stats = $this->get_cache_stats();
        error_log("Cache stats: " . json_encode($stats));
    }
}
```

## Security Checklist

### Pre-deployment Security Review

- [ ] Input validation implemented
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS protection (output escaping)
- [ ] CSRF protection (tokens)
- [ ] Secure session configuration
- [ ] File upload validation
- [ ] Password security (hashing, policies)
- [ ] Access control verification
- [ ] Audit logging enabled
- [ ] Security headers configured
- [ ] Dependencies updated (no vulnerabilities)

### Code Review Checklist

- [ ] PSR standards followed
- [ ] Unit tests written and passing
- [ ] Documentation updated
- [ ] Database migrations tested
- [ ] Security implications reviewed
- [ ] Performance impact assessed
- [ ] Mobile compatibility verified
- [ ] Accessibility considerations
- [ ] Error handling implemented
