# SelfHelp Plugin System

This document describes the SelfHelp plugin system, how plugins are structured, created, loaded, and how the hook system works for extending core functionality.

## Table of Contents

1. [Plugin Overview](#plugin-overview)
2. [Plugin Architecture](#plugin-architecture)
3. [Creating a New Plugin](#creating-a-new-plugin)
4. [Plugin Loading Process](#plugin-loading-process)
5. [Hook System](#hook-system)
6. [Available Plugins](#available-plugins)
7. [Best Practices](#best-practices)

## Plugin Overview

SelfHelp plugins extend the core functionality without modifying the base system. Plugins can:

- Add new components and styles
- Implement API endpoints
- Integrate with external services
- Add custom business logic
- Modify existing behavior through hooks
- Provide scheduled jobs and cron tasks

### Key Features

- **Version-controlled**: Each plugin maintains its own version history
- **Database-driven**: Plugin configuration stored in database
- **Hook-based**: Extensible through the hook system
- **Component-based**: Can add new UI components
- **Service-oriented**: Can register new services

## Plugin Architecture

### Directory Structure

Each plugin follows a standardized directory structure:

```
server/plugins/plugin-name/
├── README.md              # Plugin documentation
├── CHANGELOG.md           # Version history
├── server/                # Server-side code
│   ├── component/         # UI components and hooks
│   │   ├── PluginHooks.php    # Hook implementations
│   │   └── style/         # Custom components
│   ├── service/           # Service classes and globals
│   │   └── globals.php    # Plugin constants and config
│   ├── api/               # API endpoints (optional)
│   ├── callback/          # External callbacks (optional)
│   ├── cronjobs/          # Scheduled jobs (optional)
│   └── db/                # Database migrations
│       └── v1.0.0.sql     # Initial schema
├── css/                   # Frontend styles (optional)
│   └── ext/
├── js/                    # Frontend scripts (optional)
│   └── ext/
├── schemas/               # JSON schemas (optional)
├── examples/              # Usage examples (optional)
└── gulp/                  # Build configuration (optional)
```

### Core Components

#### PluginHooks.php

The main hook class that extends `BaseHooks`:

```php
<?php
class PluginHooks extends BaseHooks
{
    public function __construct($services, $params = array()) {
        parent::__construct($services, $params);
    }

    // Hook implementations
    public function customHookMethod($args) {
        // Plugin-specific logic
    }
}
?>
```

#### globals.php (optional)

Plugin configuration and constants (not all plugins have this file):

```php
<?php
// Plugin constants - examples from actual plugins
define('PAGE_API_SETTINGS', 'apiSettings');
define('PAGE_ACTION_API', 'api');
define('X_API_KEY', 'x-api-key');
define('qualtricsSurveyTypes', 'qualtricsSurveyTypes');
define('QUALTRICS_SETTINGS', 'qualtrics-settings');

// HTTP status codes (from API plugin)
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
// ... many more status codes
?>
```

#### Database Migrations

Version-controlled database changes (examples from real plugins):

```sql
-- File: server/db/v1.0.0.sql
-- Initial plugin schema (from API plugin)

-- Register plugin in plugins table
INSERT IGNORE INTO plugins (`name`, version)
VALUES ('api', 'v1.0.0');

-- Create API-specific tables
CREATE TABLE IF NOT EXISTS `users_api` (
    `id_users` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY,
    `token` VARCHAR(100) UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `users_api_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`)
);

CREATE TABLE IF NOT EXISTS `apiLogs` (
    `id` INTEGER PRIMARY KEY AUTO_INCREMENT,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `id_users` INT(10) UNSIGNED ZEROFILL NOT NULL,
    `remote_addr` VARCHAR(200),
    `target_url` VARCHAR(1000),
    `post_params` LONGTEXT,
    `status` INTEGER,
    `return_response` longtext,
    CONSTRAINT `apiLogs_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`)
);
```

## Creating a New Plugin

### Step 1: Plugin Planning

1. **Define Purpose**: What functionality does the plugin provide?
2. **Identify Components**: What UI components are needed?
3. **Database Requirements**: What tables/views are required?
4. **Hook Points**: Where will the plugin hook into the core system?
5. **External Dependencies**: Any external libraries or services?

### Step 2: Create Directory Structure

```bash
# Create plugin directory
mkdir -p server/plugins/sh-shp-your-plugin

# Create subdirectories
cd server/plugins/sh-shp-your-plugin
mkdir -p server/component server/service server/db css/ext js/ext

# Create required files
touch README.md CHANGELOG.md
touch server/component/YourPluginHooks.php
touch server/service/globals.php
touch server/db/v1.0.0.sql
```

### Step 3: Implement Core Files

#### PluginHooks.php

```php
<?php
require_once __DIR__ . "/../../../../component/BaseHooks.php";

class YourPluginHooks extends BaseHooks
{
    public function __construct($services, $params = array()) {
        parent::__construct($services, $params);
    }

    /**
     * Example hook: Modify component output
     */
    public function modifyComponentOutput($args) {
        $component = $args['component'];
        // Modify component behavior
        return $component;
    }

    /**
     * Example hook: Add custom validation
     */
    public function customValidation($args) {
        $data = $args['data'];
        // Custom validation logic
        return $this->validateData($data);
    }
}
?>
```

#### globals.php (if needed)

```php
<?php
/* Plugin Configuration - based on actual plugin patterns */
define('YOUR_PLUGIN_NAME', 'sh-shp-your-plugin');

/* Page Actions */
define('PAGE_ACTION_YOUR_FEATURE', 'your-feature');

/* Custom Constants - use patterns from existing plugins */
define('YOUR_TRANSACTION_TYPE', 'by_your_plugin');
define('YOUR_LOOKUP_TYPE', 'yourLookupType');
?>
```

#### Database Migration

```sql
-- Plugin initial schema (based on real plugin patterns)
START TRANSACTION;

-- Register plugin
INSERT IGNORE INTO plugins (name, version)
VALUES ('your-plugin', 'v1.0.0');

-- Create your plugin-specific tables (adapt based on your needs)
CREATE TABLE IF NOT EXISTS your_plugin_table (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_users INT(10) UNSIGNED ZEROFILL NOT NULL,
    data_field VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_your_plugin_users FOREIGN KEY (id_users) REFERENCES users(id)
);

-- Register transaction type for audit logging
INSERT IGNORE INTO lookups (type_code, lookup_code, lookup_value, lookup_description)
VALUES ('transactionBy', 'by_your_plugin', 'By Your Plugin', 'Actions performed by your plugin');

COMMIT;
```

### Step 4: Register Hooks

Hooks must be registered in the database. Include hook registration in your migration:

```sql
-- Register hooks for the plugin
INSERT INTO hooks (id_hookTypes, name, description, class, function, exec_class, exec_function)
SELECT
    (SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return'),
    'your-plugin-custom-hook',
    'Custom hook for your plugin',
    'TargetClass',
    'targetMethod',
    'YourPluginHooks',
    'customHookMethod';
```

### Step 5: Add Components (Optional)

Create custom components by extending BaseComponent:

```php
<?php
require_once __DIR__ . "/../../../../component/BaseComponent.php";

class YourComponent extends BaseComponent {
    public function __construct($services, $id, $params, $id_page, $entry_record) {
        $model = new YourModel($services, $id, $params, $id_page, $entry_record);
        $view = new YourView($model, $id);
        parent::__construct($model, $view);
    }
}
?>
```

### Step 6: Add API Endpoints (Optional)

Create API classes extending ApiRequest:

```php
<?php
require_once __DIR__ . "/../api/ApiRequest.php";

class YourApi extends ApiRequest {
    public function GET_your_endpoint($params) {
        $data = $this->getYourData($params);
        $this->set_response($data);
        return $this->return_response();
    }
}
?>
```

### Step 7: Documentation

Create comprehensive documentation:

```markdown
# Your Plugin

## Description
Brief description of what the plugin does.

## Installation
1. Copy plugin to `server/plugins/`
2. Run database migrations
3. Configure settings in `globals.php`

## Configuration
List configuration options and how to set them.

## Usage
Examples of how to use the plugin.

## Hooks
List of hooks provided by the plugin.
```

## Plugin Loading Process

### 1. Global Configuration Loading

During SelfHelp initialization (`Selfhelp.php`), plugins are loaded in this order:

1. **Directory Scan**: System scans `server/plugins/` directory
2. **Global Files**: Loads each plugin's `server/service/globals.php`
3. **Constants Registration**: Plugin constants become available system-wide

```php
// In Selfhelp.php
private function loadPluginGlobals() {
    $plugin_path = __DIR__ . '/server/plugins/' . $dir . '/server/service/';
    if (file_exists($plugin_path . "globals.php")) {
        require_once $plugin_path . "globals.php";
    }
}
```

### 2. Service Initialization

Plugins are loaded through global configuration files only. There is no separate plugin service initialization in the Services class.

### 3. Hook Registration

Hooks are registered through the database and loaded by the Hooks service:

1. **Database Query**: Hooks service queries `hooks` table
2. **Class Loading**: Dynamically loads hook classes
3. **Method Binding**: Binds hook methods using `uopz_set_hook()` or `uopz_set_return()`

### 4. Component Registration

Components are registered through the database `styles` table and loaded dynamically:

```php
// Component instantiation
$component_class = $style_name . 'Component';
$component = new $component_class($services, $id, $params, $id_page, $entry_record);
```

## Hook System

### Hook Types

SelfHelp supports two main hook types:

#### 1. Function Execution Hooks (`hook_on_function_execute`)

Execute when a specific method is called:

```php
// Registered in database
INSERT INTO hooks (id_hookTypes, name, class, function, exec_class, exec_function)
VALUES (
    (SELECT id FROM lookups WHERE lookup_code = 'hook_on_function_execute'),
    'hook-name',
    'TargetClass',
    'targetMethod',
    'HookClass',
    'hookMethod'
);
```

**Execution**: When `TargetClass::targetMethod()` is called, `HookClass::hookMethod()` executes.

#### 2. Return Override Hooks (`hook_overwrite_return`)

Replace the return value of a method:

```php
// Registered in database
INSERT INTO hooks (id_hookTypes, name, class, function, exec_class, exec_function)
VALUES (
    (SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return'),
    'hook-name',
    'TargetClass',
    'targetMethod',
    'HookClass',
    'hookMethod'
);
```

**Execution**: `TargetClass::targetMethod()` returns the result of `HookClass::hookMethod()`.

### Hook Priority

Hooks execute in priority order (defined in database):

```sql
ORDER BY priority;
```

Lower priority numbers execute first. Multiple hooks can modify the same target.

### Hook Parameters

Hooks receive context through parameters:

```php
public function yourHookMethod($args) {
    $hookedClassInstance = $args['hookedClassInstance'];  // The hooked object
    $methodName = $args['methodName'];                    // Method being hooked
    $originalParams = $args['original_parameters'];      // Original method params
    // ... other parameters
}
```

### Reflection-Based Access

Hooks can access private methods and properties using reflection:

```php
// Execute private method
protected function execute_private_method($args) {
    $reflector = new ReflectionObject($args['hookedClassInstance']);
    $method = $reflector->getMethod($args['methodName']);
    $method->setAccessible(true);
    $result = $method->invoke($args['hookedClassInstance'], ...$params);
    $method->setAccessible(false);
    return $result;
}

// Access private property
protected function get_private_property($args) {
    $reflector = new ReflectionObject($args['hookedClassInstance']);
    $property = $reflector->getProperty($args['propertyName']);
    $property->setAccessible(true);
    $value = $property->getValue($args['hookedClassInstance']);
    $property->setAccessible(false);
    return $value;
}
```

### Hook Execution Behavior

**Important**: Hooks do NOT chain together. Each hook completely replaces the return value of the original method. Multiple hooks on the same method will override each other based on priority order.

For `hook_overwrite_return` type hooks:
- Only the highest priority hook (lowest priority number) executes
- That hook completely replaces the original method's return value
- Other hooks registered for the same method are ignored

For `hook_on_function_execute` type hooks:
- All hooks execute when the method is called
- They do not affect the return value
- Used for side effects (logging, notifications, etc.)

Example of how hooks actually work:

```php
// Original method in SomeClass
public function getData() {
    return ['original' => 'data'];
}

// Hook with higher priority (lower number = higher priority)
public function highPriorityHook($args) {
    // This hook will execute and replace the return value
    return ['hooked' => 'data', 'source' => 'high_priority'];
}

// Hook with lower priority (higher number = lower priority)
public function lowPriorityHook($args) {
    // This hook will NOT execute because highPriorityHook has higher priority
    return ['hooked' => 'data', 'source' => 'low_priority'];
}

// Result: getData() returns ['hooked' => 'data', 'source' => 'high_priority']
```

## Available Plugins

### sh-shp-api
**Purpose**: Provides RESTful API endpoints for mobile applications and external integrations.

**Key Features**:
- API key authentication
- CRUD operations on dataTables
- Custom API endpoints
- Request/response logging

**Hooks**: `listen_for_api_request`, API authorization hooks

### sh-shp-auth_external
**Purpose**: External authentication integration (University of Bern).

**Key Features**:
- Single sign-on integration
- User provisioning
- Group synchronization

### sh-shp-chat
**Purpose**: Real-time chat functionality.

**Key Features**:
- WebSocket-based messaging
- User-to-user communication
- Message history
- File attachments

### sh-shp-formula_parser
**Purpose**: Advanced formula parsing and mathematical calculations.

**Key Features**:
- Mathematical expression evaluation
- Custom functions
- Variable substitution
- Error handling

### sh-shp-mobisense
**Purpose**: Mobile sensing data collection and processing.

**Key Features**:
- Scheduled data pulls
- Sensor data processing
- Data visualization
- SSH-based secure connections

### sh-shp-qualtrics
**Purpose**: Qualtrics survey integration.

**Key Features**:
- Survey creation and management
- Response processing
- Automated workflows
- PDF generation

### sh-shp-r_serve
**Purpose**: R statistical computing integration.

**Key Features**:
- R script execution
- Statistical analysis
- Data visualization
- Result processing

### sh-shp-shepherd
**Purpose**: Interactive guided tours.

**Key Features**:
- Step-by-step tutorials
- Contextual help
- User onboarding
- Progress tracking

### sh-shp-studybuddy
**Purpose**: Study management and participant coordination.

**Key Features**:
- Participant management
- Study scheduling
- Progress tracking
- Automated notifications

### sh-shp-survey_js
**Purpose**: SurveyJS integration for dynamic surveys.

**Key Features**:
- Dynamic survey creation
- Multiple question types
- Response validation
- Result analysis

## Plugin Usage and Implementation

This section provides detailed information about how plugins are used in SelfHelp, how they are created, loaded into the system, and how the hook system works.

### How Plugins Are Used

SelfHelp plugins extend core functionality without modifying the base system. Plugins are used for:

- **API Endpoints**: Adding RESTful APIs (e.g., `sh-shp-api`)
- **External Integrations**: Connecting with third-party services (e.g., `sh-shp-qualtrics`, `sh-shp-auth_external`)
- **Enhanced Features**: Adding chat, surveys, statistical computing (e.g., `sh-shp-chat`, `sh-shp-survey_js`, `sh-shp-r_serve`)
- **Business Logic**: Custom workflows and processing (e.g., `sh-shp-formula_parser`, `sh-shp-studybuddy`)
- **User Experience**: Interactive features like guided tours (e.g., `sh-shp-shepherd`)
- **Data Collection**: Mobile sensing and monitoring (e.g., `sh-shp-mobisense`)

### Plugin Loading Process

Plugins are loaded during SelfHelp initialization in `Selfhelp.php`:

1. **Global Configuration Loading** (line 50 in `Selfhelp.php`):
   - System scans `server/plugins/` directory
   - Loads each plugin's `server/service/globals.php` file
   - Plugin constants become available system-wide

```php
private function loadPluginGlobals()
{
    if ($handle = opendir(PLUGIN_SERVER_PATH)) {
        while (false !== ($dir = readdir($handle))) {
            if (filetype(PLUGIN_SERVER_PATH . '/' . $dir) == "dir") {
                $plugin_path = __DIR__ . '/server/plugins/' . $dir . '/server/service/';
                if (file_exists($plugin_path . "globals.php")) {
                    require_once $plugin_path . "globals.php";
                }
            }
        }
    }
}
```

2. **Hook Registration**:
   - Hooks are registered through database queries in the `Hooks` service
   - Two types of hooks are loaded: `hook_on_function_execute` and `hook_overwrite_return`
   - Hooks are cached for performance

3. **Component Registration**:
   - Components are registered through the database `styles` table
   - Loaded dynamically when pages request specific component styles

### Hook System Implementation

SelfHelp uses a sophisticated hook system that allows plugins to modify core behavior without changing source code.

#### Hook Types

##### 1. Function Execution Hooks (`hook_on_function_execute`)
Execute when a specific method is called, but don't modify the return value:

```sql
INSERT INTO hooks (id_hookTypes, name, class, function, exec_class, exec_function)
VALUES (
    (SELECT id FROM lookups WHERE lookup_code = 'hook_on_function_execute'),
    'hook-name',
    'TargetClass',
    'targetMethod',
    'HookClass',
    'hookMethod'
);
```

**Execution**: When `TargetClass::targetMethod()` is called, `HookClass::hookMethod()` executes alongside it.

##### 2. Return Override Hooks (`hook_overwrite_return`)
Replace the return value of a method:

```sql
INSERT INTO hooks (id_hookTypes, name, class, function, exec_class, exec_function)
VALUES (
    (SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return'),
    'hook-name',
    'TargetClass',
    'targetMethod',
    'HookClass',
    'hookMethod'
);
```

**Execution**: `TargetClass::targetMethod()` returns the result of `HookClass::hookMethod()` instead of its original implementation.

#### Hook Priority System

Hooks execute in priority order (defined in database):

```sql
ORDER BY priority;
```

- Lower priority numbers execute first
- For `hook_overwrite_return`: Only the highest priority hook (lowest number) executes
- For `hook_on_function_execute`: All hooks execute in priority order

#### Hook Loading and Execution

Hooks are loaded during `Hooks` service initialization:

```php
public function __construct($services)
{
    $this->db = $services->get_db();
    $this->services = $services;
    $this->schedule_hook_on_function_execute();  // Load execution hooks
    $this->schedule_hook_overwrite_return();     // Load override hooks
}
```

**Execution Hook Loading** (`schedule_hook_on_function_execute`):
```php
foreach ($this->get_hooks(hookTypes_hook_on_function_execute) as $hook) {
    if (class_exists($hook['class']) && method_exists($hook['class'], $hook['function'])) {
        uopz_set_hook($hook['class'], $hook['function'], function () use ($hookService, $hook) {
            foreach ($hookService->get_hook_calls(hookTypes_hook_on_function_execute, $hook['class'], $hook['function']) as $hook_method) {
                if (class_exists($hook_method['exec_class'])) {
                    $hookClassInstance = new $hook_method['exec_class']($hookService->get_services());
                    if (method_exists($hookClassInstance, $hook_method['exec_function'])) {
                        $hookClassInstance->{$hook_method['exec_function']}();
                    }
                }
            }
        });
    }
}
```

**Override Hook Loading** (`schedule_hook_overwrite_return`):
```php
foreach ($this->get_hooks(hookTypes_hook_overwrite_return) as $hook) {
    foreach ($hookService->get_hook_calls(hookTypes_hook_overwrite_return, $hook['class'], $hook['function']) as $hook_method) {
        if (class_exists($hook['class']) && method_exists($hook['class'], $hook['function'])) {
            $new_func = function (...$args) use ($hookService, $hook_method, $class, $func) {
                if (class_exists($hook_method['exec_class'])) {
                    $hookClassInstance = new $hook_method['exec_class']($hookService->get_services());
                    if (method_exists($hookClassInstance, $hook_method['exec_function'])) {
                        // Build parameter array with reflection
                        $reflector = new ReflectionClass($class);
                        $parameters = $reflector->getMethod($func)->getParameters();
                        $argsKeys = array();
                        foreach ($parameters as $key => $parameter) {
                            if (array_key_exists($key, $args)) {
                                $argsKeys[$parameter->name] = $args[$key];
                            }
                        }
                        $argsKeys['hookedClassInstance'] = $this;
                        $argsKeys['methodName'] = $func;

                        $res = $hookClassInstance->{$hook_method['exec_function']}($argsKeys);
                        return $res;
                    }
                }
            };
            uopz_set_return($class, $func, $new_func, true);
        }
    }
}
```

#### Hook Execution Behavior

**Critical Behavior Note**: Hooks do NOT chain together. Each hook completely replaces the return value of the original method. Multiple hooks on the same method will override each other based on priority order.

For `hook_overwrite_return` type hooks:
- Only the highest priority hook (lowest priority number) executes
- That hook completely replaces the original method's return value
- Other hooks registered for the same method are ignored

Example:
```php
// Original method
public function getData() {
    return ['original' => 'data'];
}

// Hook with higher priority (lower number = higher priority)
public function highPriorityHook($args) {
    return ['hooked' => 'data', 'source' => 'high_priority'];
}

// Hook with lower priority (higher number = lower priority)
public function lowPriorityHook($args) {
    return ['hooked' => 'data', 'source' => 'low_priority'];
}

// Result: getData() returns ['hooked' => 'data', 'source' => 'high_priority']
```

#### Hook Parameters

Hooks receive context through parameters:

```php
public function yourHookMethod($args) {
    $hookedClassInstance = $args['hookedClassInstance'];  // The hooked object
    $methodName = $args['methodName'];                    // Method being hooked
    $originalParams = $args['original_parameters'];      // Original method params
    // ... other hook-specific parameters
}
```

Hooks can access private methods and properties using reflection:

```php
// Execute private method
protected function execute_private_method($args) {
    $reflector = new ReflectionObject($args['hookedClassInstance']);
    $method = $reflector->getMethod($args['methodName']);
    $method->setAccessible(true);
    $result = $method->invoke($args['hookedClassInstance'], ...$params);
    $method->setAccessible(false);
    return $result;
}

// Access private property
protected function get_private_property($args) {
    $reflector = new ReflectionObject($args['hookedClassInstance']);
    $property = $reflector->getProperty($args['propertyName']);
    $property->setAccessible(true);
    $value = $property->getValue($args['hookedClassInstance']);
    $property->setAccessible(false);
    return $value;
}
```

## Best Practices

### Plugin Development

1. **Version Control**: Use semantic versioning (MAJOR.MINOR.PATCH)
2. **Database Safety**: Always use transactions for data changes
3. **Error Handling**: Implement proper error handling and logging
4. **Security**: Validate all inputs and escape outputs
5. **Documentation**: Document all hooks, methods, and configuration options

### Hook Implementation

1. **Idempotent Operations**: Hooks should be safe to run multiple times
2. **Performance**: Keep hooks lightweight; avoid expensive operations
3. **Error Resilience**: Handle exceptions gracefully to prevent breaking core functionality
4. **Clear Naming**: Use descriptive names for hooks and methods

### Database Design

1. **Prefix Tables**: Use plugin prefixes to avoid naming conflicts
2. **Foreign Keys**: Define proper relationships and constraints
3. **Indexing**: Add appropriate indexes for performance
4. **Migrations**: Provide rollback scripts for all migrations

### Code Quality

1. **PSR Standards**: Follow PHP PSR coding standards
2. **Documentation**: Use PHPDoc comments for all public methods
3. **Testing**: Include unit tests for critical functionality
4. **Dependencies**: Minimize external dependencies

### Deployment

1. **Version Compatibility**: Specify minimum SelfHelp version requirements
2. **Migration Order**: Ensure migrations can be run in sequence
3. **Rollback Plan**: Provide rollback procedures for failed deployments
4. **Configuration**: Document all configuration requirements

## Troubleshooting

### Common Issues

1. **Hooks Not Firing**: Check database registration and hook type
2. **Plugin Not Loading**: Verify directory structure and globals.php
3. **Database Errors**: Check migration scripts and foreign key constraints
4. **Permission Issues**: Verify ACL settings for plugin pages

### Debug Mode

Enable debug mode to see detailed logging:

```php
define('DEBUG', 1);
```

Check logs in the data/clockwork directory for detailed execution traces.