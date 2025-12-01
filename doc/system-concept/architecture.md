# SelfHelp Architecture Overview

## System Architecture

SelfHelp follows a traditional MVC (Model-View-Controller) architecture with a component-based approach. The system is built entirely in vanilla PHP without external frameworks, providing full control over the application lifecycle.

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Browser   │────│   index.php     │────│   Selfhelp.php  │
│   Mobile App    │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                                        │
                                                        ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Services      │◄──►│   Router        │◄──►│   Page Types    │
│                 │    │                 │    │                 │
│ • Database      │    │ • URL Routing   │    │ • SectionPage   │
│ • Cache         │    │ • ACL           │    │ • ComponentPage │
│ • UserInput     │    │ • Navigation    │    │ • AjaxRequest  │
│ • JobScheduler  │    │                 │    │ • Callback      │
│ • Transaction   │    └─────────────────┘    └─────────────────┘
│ • Clockwork     │             ▲                        ▲
└─────────────────┘             │                        │
          ▲                     │                        │
          │                     ▼                        │
          │           ┌─────────────────┐                 │
          │           │   Components    │                 │
          │           │   (MVC)         │◄────────────────┘
          │           │                 │
          │           │ • Model         │
          │           │ • View          │
          │           │ • Controller    │
          └──────────►└─────────────────┘
```

## Entry Point Flow

### 1. index.php
- Sets security headers (XSS protection, frame options)
- Includes Selfhelp.php as the main application class
- Handles optional PHP info display for debugging

### 2. Selfhelp.php
- Initializes plugin globals
- Sets up error handling (only in debug mode)
- Enables CORS for mobile development
- Creates Services instance
- Routes between web and mobile calls

### 3. Services Layer
The Services class acts as a dependency injection container:

- **Database (PageDb)**: MySQL connection with extended PDO
- **Router**: URL routing using AltoRouter library
- **ACL**: Access control layer for permissions
- **UserInput**: Handles form data and user interactions
- **JobScheduler**: Background job processing
- **Transaction**: Database transaction management
- **Clockwork**: Performance monitoring and debugging

## Page Types

### SectionPage
- Renders full pages composed of sections
- Loads content from database-driven sections
- Supports both web and mobile output formats
- Handles navigation and user permissions

### ComponentPage
- Renders single components based on page keywords
- Follows naming convention: `keyword` → `KeywordComponent`
- Used for specialized pages (admin panels, forms, etc.)

### AjaxRequest
- Handles AJAX calls for dynamic content loading
- Returns JSON responses
- Supports both class/method and keyword-based routing

### CallbackRequest
- Processes external callbacks (survey completions, API responses)
- Handles background processing tasks
- Supports plugin extensibility

## Component Architecture

Each UI component follows the MVC pattern:

### Model (extends BaseModel)
- Handles data logic and database interactions
- Manages component state and configuration
- Processes conditions and permissions
- Interacts with services layer

### View (extends BaseView)
- Renders HTML output
- Handles mobile vs web output differences
- Manages CSS and JavaScript includes
- Processes templates and data interpolation

### Controller (optional)
- Handles complex business logic
- Manages component lifecycle
- Processes form submissions
- Coordinates between Model and View

## Key Design Patterns

### Service Locator
```php
$services = new Services();
$db = $services->get_db();
$router = $services->get_router();
```

### Factory Pattern
Components are instantiated dynamically based on database configuration:
```php
$componentClass = ucfirst($keyword) . "Component";
$component = new $componentClass($services, $params, $id_page);
```

### Template Method
Base classes define the skeleton of operations, subclasses override specific steps:
```php
abstract class BaseComponent {
    public function output_content() {
        // Common logic
        $this->custom_output(); // Override in subclass
    }
}
```

### Strategy Pattern
Different output strategies for web vs mobile:
```php
public function output_content() { /* Web output */ }
public function output_content_mobile() { /* Mobile JSON */ }
```

## Plugin System

Plugins extend the core functionality:

- Located in `server/plugins/`
- Auto-loaded during initialization
- Can override components and services
- Follow same architectural patterns
- Versioned independently

## Data Flow

1. **Request** → Router matches URL to page configuration
2. **ACL Check** → Services verify user permissions
3. **Page Load** → Page type loads appropriate components
4. **Component Render** → Each component follows MVC pattern
5. **Data Fetch** → Models query database through services
6. **Response** → Views render output (HTML or JSON)

## Performance Optimizations

- **APCu Caching**: Database queries and computed data
- **Lazy Loading**: Components load only when needed
- **Query Optimization**: Efficient database queries with proper indexing
- **Asset Minification**: Gulp builds optimized CSS/JS bundles
- **Clockwork Monitoring**: Performance profiling and debugging

## Security Architecture

- **Input Sanitization**: All user inputs filtered
- **SQL Injection Prevention**: Parameterized queries
- **XSS Protection**: Output escaping and CSP headers
- **CSRF Protection**: Token-based validation
- **Access Control**: Role-based permissions system
- **Session Security**: Secure cookie configuration
