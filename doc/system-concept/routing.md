# Routing and Page Management

## Overview

SelfHelp implements a flexible routing system that maps URLs to page configurations stored in the database. The routing system supports both static and dynamic routes, with built-in access control and multi-format output (HTML/JSON).

## Router Architecture

### Core Components

- **AltoRouter**: Third-party routing library for URL pattern matching
- **Router Extension**: SelfHelp-specific routing logic and helpers
- **Database Integration**: Routes loaded dynamically from database
- **Access Control**: Integrated ACL checking per route

### Route Definition

Routes are stored in the `pages` table:

```sql
CREATE TABLE pages (
    id INT PRIMARY KEY,
    keyword VARCHAR(255) UNIQUE,
    url VARCHAR(255),
    protocol VARCHAR(50), -- GET, POST, etc.
    id_actions INT, -- Route handler type
    id_type INT -- Page type (internal, experiment, open)
);
```

## Route Types

### Page Actions

Routes are categorized by their `id_actions`:

- **1 (sections)**: Full page rendering with sections
- **2 (component)**: Single component rendering
- **3 (backend)**: Administrative/backend pages
- **4 (ajax)**: AJAX endpoints returning JSON

### Page Types

Pages are classified by access level:

- **INTERNAL (1)**: Authenticated users only
- **EXPERIMENT (2)**: Experiment participants (special permissions)
- **OPEN (3)**: Public access, no authentication required

## URL Patterns

### Static Routes

Simple, fixed URL patterns:

```
/home
/login
/admin/users
```

### Dynamic Routes

Parameterized URLs with placeholders:

```
/user/{id}
/experiment/{experiment_id}/survey/{survey_id}
/admin/data/{table_id}
```

### Route Parameters

Parameters are extracted from URLs:

```php
// URL: /user/123
// Route: /user/{id}
// Result: ['id' => '123']

// URL: /survey/456/question/789
// Route: /survey/{survey_id}/question/{question_id}
// Result: ['survey_id' => '456', 'question_id' => '789']
```

## Route Matching Process

### 1. URL Parsing

```php
public function match() {
    // Parse current request URI
    $request_uri = $_SERVER['REQUEST_URI'];

    // Remove base path
    $path = str_replace(BASE_PATH, '', $request_uri);

    // Find matching route
    return $this->alto_router->match($path, $_SERVER['REQUEST_METHOD']);
}
```

### 2. Database Lookup

```php
private function init_router_routes() {
    $sql = "SELECT p.protocol, p.url, a.name AS action, p.keyword
            FROM pages AS p
            LEFT JOIN actions AS a ON a.id = p.id_actions
            WHERE protocol IS NOT NULL";

    $pages = $this->db->query_db($sql);

    foreach($pages as $page) {
        $this->map($page['protocol'], $page['url'], $page['action'], $page['keyword']);
    }
}
```

### 3. Access Control

```php
public function check_access($route) {
    // Check page type permissions
    $page_type = $this->get_page_type($route['name']);

    switch($page_type) {
        case OPEN_PAGE_ID:
            return true; // Public access
        case EXPERIMENT_PAGE_ID:
            return $this->is_experimenter_page($route['name']);
        default:
            return $this->services->get_acl()->has_access($route['name']);
    }
}
```

## Route Handlers

### SectionPage Handler

Renders full pages composed of sections:

```php
if ($router->route['target'] == "sections") {
    $page = new SectionPage($services, $router->route['name'], $router->route['params']);
    $page->output(); // HTML output
}
```

### ComponentPage Handler

Renders single components:

```php
if ($router->route['target'] == "component") {
    $page = new ComponentPage($services, $router->route['name'], $router->route['params']);
    $page->output();
}
```

### AJAX Handler

Returns JSON responses:

```php
if ($router->route['target'] == "ajax") {
    $ajax = new AjaxRequest($services, $router->route['params']['class'], $router->route['params']['method']);
    $ajax->print_json();
}
```

## URL Generation

### Basic URL Generation

```php
// Generate URL for named route
$url = $router->generate('home'); // /home
$url = $router->generate('user_profile', ['id' => 123]); // /user/123
```

### Advanced URL Helpers

#### Link URLs
```php
// Direct route name
$router->get_link_url('login'); // /login

// Special keywords
$router->get_url('#back'); // Previous page
$router->get_url('#self'); // Current page
$router->get_url('#last_user_page'); // Last visited page
```

#### Section Links
```php
// Link to section within page
$router->get_url('#page_name/section_name'); // /page#section-123

// Link to section by ID
$router->get_url('#page_name/123'); // /page#section-123
```

#### Asset URLs
```php
// Asset files
$router->get_url('%image.jpg'); // /assets/image.jpg

// Base path URLs
$router->get_url('|api/data'); // /selfhelp/api/data
```

## Page Lifecycle

### Web Request Flow

1. **Route Matching**: URL matched against database routes
2. **Access Check**: User permissions verified
3. **Page Creation**: Appropriate page class instantiated
4. **Content Loading**: Sections/components loaded from database
5. **Rendering**: HTML output generated
6. **Activity Logging**: User activity recorded

### Mobile Request Flow

1. **Route Matching**: Same as web requests
2. **Access Check**: User permissions verified
3. **Page Creation**: SectionPage with mobile flag
4. **Content Loading**: Sections loaded with mobile context
5. **JSON Output**: Structured data returned
6. **Activity Logging**: Mobile activity recorded

## Dynamic Page Creation

### Database-Driven Pages

Pages are created through the CMS interface:

```php
// New page creation
INSERT INTO pages (keyword, url, protocol, id_actions, id_type)
VALUES ('new_page', '/new-page', 'GET', 1, 1);
```

### Section Management

Sections are attached to pages:

```php
INSERT INTO sections (id_pages, name, sort_order)
VALUES (1, 'header', 1);

INSERT INTO sections (id_pages, name, sort_order)
VALUES (1, 'content', 2);
```

### Component Attachment

Components are attached to sections:

```php
INSERT INTO sections_fields (id_sections, style_name, field_name, field_value)
VALUES (1, 'text', 'content', 'Welcome to our site!');
```

## Access Control Integration

### Role-Based Access

Routes integrate with the ACL system:

```php
// Check page access
$has_access = $services->get_acl()->check_page_access($page_keyword, $user_id);

// Check section access
$has_access = $services->get_acl()->check_section_access($section_id, $user_id);
```

### Group Permissions

Users belong to groups with different permission levels:

```php
// Admin group (full access)
$user_groups = ['admin', 'experimenter'];

// Subject group (limited access)
$user_groups = ['subject'];
```

## Error Handling

### 404 Pages

Unmatched routes show custom 404 pages:

```php
if (!$router->route) {
    $page = new SectionPage($services, 'missing', array());
    $page->output();
}
```

### Access Denied

Unauthorized access shows appropriate error pages:

```php
if (!$page->has_access()) {
    $page = new SectionPage($services, 'no_access', array());
    $page->output();
}
```

## Performance Optimization

### Route Caching

Routes are cached to avoid database queries:

```php
private function get_cached_routes() {
    $cache_key = 'routes_' . PROJECT_NAME;
    $routes = $this->cache->get($cache_key);

    if (!$routes) {
        $routes = $this->load_routes_from_db();
        $this->cache->set($cache_key, $routes, 3600); // 1 hour
    }

    return $routes;
}
```

### URL Resolution Caching

Generated URLs are cached for performance:

```php
public function get_cached_url($route_name, $params = []) {
    $cache_key = 'url_' . $route_name . '_' . md5(serialize($params));
    $url = $this->cache->get($cache_key);

    if (!$url) {
        $url = $this->generate($route_name, $params);
        $this->cache->set($cache_key, $url, 1800); // 30 minutes
    }

    return $url;
}
```

## Development Guidelines

### Adding New Routes

1. **Define the route** in the database or CMS
2. **Create the page handler** (SectionPage, ComponentPage, etc.)
3. **Implement access control** rules
4. **Test URL generation** and parameter handling
5. **Document the route** purpose and parameters

### Route Best Practices

- **RESTful URLs**: Use consistent URL patterns
- **Descriptive keywords**: Use meaningful page keywords
- **Parameter validation**: Validate route parameters
- **Access control**: Define clear permission requirements
- **Documentation**: Document route purposes and parameters

### URL Structure Guidelines

```
Good: /experiment/{id}/survey/{survey_id}
Bad:  /exp?id={id}&survey={survey_id}

Good: /admin/users/{user_id}/edit
Bad:  /admin/edit_user.php?id={user_id}
```

## Mobile-Specific Routing

### Mobile Detection

Routes automatically detect mobile requests:

```php
if (isset($_POST['mobile']) && $_POST['mobile']) {
    $this->mobile_call($services);
} else {
    $this->web_call($services);
}
```

### Mobile URL Handling

Mobile apps may require different URL structures:

```php
// Web URL: /survey/123
// Mobile API: /mobile/survey/123 (with mobile=1 parameter)
```

### CORS Configuration

Mobile development requires CORS headers:

```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Credentials: true");
```

## Testing Routes

### Route Testing Checklist

- [ ] URL matches expected pattern
- [ ] Parameters extracted correctly
- [ ] Access control works for different user types
- [ ] Page renders without errors
- [ ] Mobile output format correct
- [ ] Caching works properly
- [ ] Error pages display appropriately

### Debugging Routes

```php
// Debug route matching
$route = $router->match();
var_dump($route);

// Debug URL generation
$url = $router->generate('page_name', ['param' => 'value']);
echo $url;

// Debug access control
$has_access = $services->get_acl()->check_page_access('page_keyword');
var_dump($has_access);
```
