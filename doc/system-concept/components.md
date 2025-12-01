# Component System (MVC Architecture)

## Overview

SelfHelp implements a component-based MVC architecture where each UI element is a self-contained component with Model, View, and optional Controller classes. This modular approach enables code reusability, maintainability, and easy extension.

## Component Structure

### Directory Layout
```
server/component/
├── BaseComponent.php          # Abstract base class
├── BaseController.php         # Controller base class
├── BaseModel.php             # Model base class
├── BaseView.php              # View base class
├── BaseHooks.php             # Plugin hooks
└── style/                    # Individual components
    ├── input/
    │   ├── InputComponent.php
    │   ├── InputModel.php
    │   ├── InputView.php
    │   └── InputController.php (optional)
    ├── form/
    │   ├── FormComponent.php
    │   ├── FormModel.php
    │   └── FormView.php
    └── ...
```

### Naming Convention

Components follow strict naming conventions:

- **Directory**: lowercase, hyphen-separated (e.g., `user-input`)
- **Class files**: `ComponentName.php` (e.g., `UserInputComponent.php`)
- **Class names**: `ComponentName` + `Component/Model/View/Controller`

## Base Classes

### BaseComponent

The foundation class that defines the component lifecycle:

```php
abstract class BaseComponent
{
    protected $model;
    protected $view;
    protected $controller;

    public function __construct($model, $view, $controller = null) {
        $this->model = $model;
        $this->view = $view;
        $this->controller = $controller;
    }

    // Core methods
    public function output_content() {
        // Condition checking
        if (!$this->model->get_condition_result()['result']) {
            return; // Skip rendering if conditions not met
        }

        // CMS editing mode vs normal mode
        if ($this->is_cms_editing_mode()) {
            $this->view->output_style_for_cms();
        } else {
            $this->view->output_content();
        }
    }

    public function output_content_mobile() {
        // JSON output for mobile apps
        return $this->view->output_content_mobile();
    }
}
```

### BaseModel

Handles data logic and component configuration:

```php
abstract class BaseModel
{
    protected $services;
    protected $params;
    protected $id_page;

    public function __construct($services, $id, $params, $id_page) {
        $this->services = $services;
        $this->id = $id;
        $this->params = $params;
        $this->id_page = $id_page;
    }

    // Data loading and processing
    public function get_data() {
        // Component-specific data retrieval
    }

    // Condition evaluation
    public function get_condition_result() {
        // Evaluate display conditions
    }

    // Permission checking
    public function has_access() {
        // Check user permissions
    }
}
```

### BaseView

Manages HTML rendering and asset loading:

```php
abstract class BaseView
{
    protected $model;
    protected $id;

    public function __construct($model, $id) {
        $this->model = $model;
        $this->id = $id;
    }

    // Main rendering method
    public function output_content() {
        // Generate HTML output
    }

    // Mobile JSON output
    public function output_content_mobile() {
        // Return structured data for mobile
    }

    // Asset management
    public function get_css_includes() {
        // Return array of CSS files
    }

    public function get_js_includes() {
        // Return array of JS files
    }
}
```

## Component Lifecycle

### 1. Instantiation
```php
// Page loading process
$page = new SectionPage($services, $page_keyword, $params);
$page->load_sections(); // Loads section configurations from DB

// Component creation
foreach ($section->get_components() as $component_config) {
    $component_class = $component_config['style_name'] . 'Component';
    $component = new $component_class(
        $services,
        $component_config['id'],
        $params,
        $id_page,
        $entry_record
    );
}
```

### 2. Data Loading (Model)
```php
public function __construct($services, $id, $params, $id_page) {
    parent::__construct($services, $id, $params, $id_page);

    // Load component configuration from database
    $this->config = $this->load_config();

    // Load data based on component type
    $this->data = $this->load_data();

    // Evaluate display conditions
    $this->condition_result = $this->evaluate_conditions();
}
```

### 3. Rendering (View)
```php
public function output_content() {
    // Check conditions
    if (!$this->model->get_condition_result()['result']) {
        return;
    }

    // Load template data
    $template_data = $this->prepare_template_data();

    // Render HTML
    include 'templates/component_template.php';
}
```

### 4. Interaction (Controller - Optional)
```php
public function handle_submission() {
    // Process form data
    $form_data = $this->sanitize_input($_POST);

    // Validate data
    if ($this->validate($form_data)) {
        // Save data
        $this->model->save($form_data);

        // Trigger actions
        $this->trigger_actions($form_data);
    }
}
```

## Component Types

### Form Components

Handle user input and data collection:

- **input**: Single input fields (text, email, date, etc.)
- **textarea**: Multi-line text input
- **select**: Dropdown selections
- **checkbox**: Boolean inputs
- **radio**: Single-choice options

### Display Components

Present information to users:

- **text**: Static text content
- **image**: Image display with responsive features
- **markdown**: Rich text with Markdown support
- **table**: Data tables with sorting/filtering

### Layout Components

Structure page layout:

- **div**: Generic container with styling
- **card**: Bootstrap card layout
- **tabs**: Tabbed content organization
- **accordion**: Collapsible content sections

### Interactive Components

Advanced user interactions:

- **form**: Form containers with submission handling
- **button**: Action buttons with various styles
- **link**: Navigation links with routing
- **modal**: Popup dialogs

## Configuration System

### Database-Driven Configuration

Components are configured through database tables:

```sql
-- sections_fields table structure
CREATE TABLE sections_fields (
    id INT PRIMARY KEY,
    id_sections INT,
    style_name VARCHAR(255),
    field_name VARCHAR(255),
    field_value TEXT,
    sort_order INT,
    FOREIGN KEY (id_sections) REFERENCES sections(id)
);
```

### Field Types

Components support various field types:

- **text**: Simple text values
- **textarea**: Multi-line text
- **select**: Dropdown with predefined options
- **checkbox**: Boolean values
- **json**: Complex structured data
- **markdown**: Rich text content

### Dynamic Configuration

Field values can be dynamic:

```php
// Static value
$field_value = "Hello World";

// Dynamic interpolation
$field_value = "{{user_name}}"; // Replaced at runtime

// Database field reference
$field_value = "{{INTERNAL.users.name}}"; // From data source

// Complex expressions
$field_value = "{{current_date}} - {{user_created}}";
```

## Data Sources and Interpolation

### Data Source Types

- **INTERNAL**: Form data within the application
- **EXTERNAL**: Data from external systems (surveys, APIs)
- **SESSION**: User session data
- **GLOBAL**: System-wide variables

### Variable Interpolation

```php
// Simple variable
$text = "Welcome {{user_name}}!";

// Nested object access
$email = "{{user.email}}";

// Array indexing
$item = "{{items.0.name}}";

// Conditional rendering
$content = "{{#if user_logged_in}}Welcome back!{{/if}}";
```

## Component Communication

### Parent-Child Relationships

Components can contain other components:

```php
// Container component
class CardComponent extends BaseComponent {
    public function get_children() {
        return [
            'header' => new TextComponent(/*...*/),
            'body' => new MarkdownComponent(/*...*/),
            'footer' => new ButtonComponent(/*...*/)
        ];
    }
}
```

### Event System

Components can trigger and respond to events:

```php
// Trigger event
$this->services->get_hooks()->trigger('form_submitted', $form_data);

// Listen for events
$this->services->get_hooks()->add_hook('form_submitted', function($data) {
    // Handle event
});
```

## Mobile Support

### Responsive Design

Components adapt to different screen sizes:

```php
public function output_content_mobile() {
    // Return structured data instead of HTML
    return [
        'type' => 'input',
        'value' => $this->model->get_value(),
        'config' => $this->model->get_config()
    ];
}
```

### Mobile-Specific Features

- Touch-friendly interactions
- Native mobile component mapping
- Offline data synchronization
- Push notification integration

## Performance Optimization

### Lazy Loading

Components load data only when needed:

```php
protected $data_loaded = false;

public function get_data() {
    if (!$this->data_loaded) {
        $this->data = $this->load_data_from_database();
        $this->data_loaded = true;
    }
    return $this->data;
}
```

### Caching

Component output can be cached:

```php
public function get_cache_key() {
    return 'component_' . $this->id . '_' . md5(serialize($this->params));
}

public function get_cached_output() {
    $cache = $this->services->get_cache();
    return $cache->get($this->get_cache_key());
}
```

## Development Guidelines

### Creating New Components

1. **Define the component purpose** and interface
2. **Create the directory structure** in `server/component/style/`
3. **Implement the Model class** extending `BaseModel`
4. **Implement the View class** extending `BaseView`
5. **Create the Component class** extending `BaseComponent`
6. **Add database configuration** support
7. **Implement mobile output** if applicable
8. **Add unit tests** and documentation

### Component Best Practices

- **Single Responsibility**: Each component should do one thing well
- **Configuration-Driven**: Avoid hardcoding, use database configuration
- **Mobile-First**: Design for mobile, enhance for desktop
- **Accessible**: Follow WCAG guidelines
- **Performance**: Implement caching and lazy loading
- **Testable**: Write unit tests for business logic

### Code Style

- Follow PSR-4 autoloading standards
- Use meaningful variable and method names
- Add PHPDoc comments for all public methods
- Handle errors gracefully with proper logging
- Use dependency injection instead of global state
