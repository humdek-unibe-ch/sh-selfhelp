# Frontend and JavaScript

## Overview

SelfHelp uses vanilla JavaScript with jQuery for DOM manipulation and Bootstrap for UI components. The frontend is built using a component-based architecture that mirrors the backend PHP components.

## Technology Stack

- **JavaScript**: ES5+ with Babel transpilation
- **jQuery**: DOM manipulation and AJAX
- **Bootstrap 4**: UI components and responsive design
- **Gulp**: Build system for asset optimization
- **Monaco Editor**: Code editing in CMS
- **Various Libraries**: Chart.js, DataTables, etc.

## Build System

### Gulp Configuration

The frontend assets are processed through Gulp:

```javascript
// gulpfile.js
gulp.task('styles', function () {
    return gulp.src(['../server/component/style/css/*.css',
                     '../server/component/style/**/css/*.css'])
        .pipe(csso())  // Minify CSS
        .pipe(concat('styles.min.css'))
        .pipe(gulp.dest('../css/ext'));
});

gulp.task('scripts', function() {
    return gulp.src(['../server/component/style/js/*.js',
                     '../server/component/style/**/js/*.js'])
        .pipe(babel({ presets: ['@babel/preset-env'] }))  // ES6+ to ES5
        .pipe(terser())  // Minify JS
        .pipe(concat('styles.min.js'))
        .pipe(gulp.dest('../js/ext'));
});
```

### Asset Organization

```
js/ext/                    # External libraries
├── styles.min.js         # Minified component JS
├── jquery.min.js         # jQuery library
├── bootstrap.bundle.min.js # Bootstrap JS
├── datatables.min.js     # DataTables
└── ...

css/ext/                  # External stylesheets
├── styles.min.css        # Minified component CSS
├── bootstrap.min.css     # Bootstrap CSS
└── ...
```

## Component JavaScript

### Component Structure

Each component can have associated JavaScript:

```
server/component/style/{component}/
├── {Component}.php       # PHP classes
├── js/
│   └── {component}.js    # Component-specific JS
└── css/
    └── {component}.css   # Component-specific CSS
```

### JavaScript Loading

Components register their JavaScript files:

```php
// In component view class
public function get_js_includes() {
    return [
        '/js/ext/jquery.min.js',
        '/js/ext/bootstrap.bundle.min.js',
        '/server/component/style/input/js/input.js'
    ];
}
```

### Component Initialization

JavaScript is initialized when components are rendered:

```javascript
// input.js
$(document).ready(function() {
    // Initialize input components
    $('.input-component').each(function() {
        initializeInput($(this));
    });
});

function initializeInput($element) {
    // Component-specific initialization
    var config = $element.data('config');

    // Date picker initialization
    if (config.type === 'date') {
        $element.datepicker({
            format: config.format || 'yyyy-mm-dd'
        });
    }
}
```

## AJAX Communication

### AJAX Request Structure

All AJAX calls follow a consistent pattern:

```javascript
function makeAjaxRequest(action, data, callback) {
    $.ajax({
        url: '/ajax/' + action,
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                callback(response.data);
            } else {
                handleAjaxError(response);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}
```

### Standardized Response Format

AJAX responses follow the envelope pattern:

```json
{
    "status": "success|error",
    "message": "Human-readable message",
    "error": "Error details if applicable",
    "logged_in": true|false,
    "meta": {
        "page": 1,
        "total_pages": 5,
        "per_page": 20
    },
    "data": {
        // Actual response data
    }
}
```

### Error Handling

Consistent error handling across components:

```javascript
function handleAjaxError(response) {
    switch(response.status) {
        case 'error':
            showErrorMessage(response.message);
            break;
        case 'unauthorized':
            redirectToLogin();
            break;
        default:
            showGenericError();
    }
}
```

## Form Handling

### Form Submission

Forms are handled through JavaScript for better UX:

```javascript
$('.component-form').on('submit', function(e) {
    e.preventDefault();

    var $form = $(this);
    var formData = new FormData($form[0]);

    // Add mobile flag if needed
    if (isMobileApp()) {
        formData.append('mobile', '1');
    }

    $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            handleFormResponse(response, $form);
        }
    });
});
```

### Form Validation

Client-side validation before submission:

```javascript
function validateForm($form) {
    var isValid = true;
    var errors = [];

    // Required field validation
    $form.find('[required]').each(function() {
        if (!$(this).val().trim()) {
            isValid = false;
            errors.push($(this).attr('name') + ' is required');
        }
    });

    // Email validation
    $form.find('input[type="email"]').each(function() {
        if ($(this).val() && !isValidEmail($(this).val())) {
            isValid = false;
            errors.push('Invalid email format');
        }
    });

    if (!isValid) {
        showValidationErrors(errors);
    }

    return isValid;
}
```

## Component Interactions

### Dynamic Content Loading

Components can load content dynamically:

```javascript
function loadComponentContent(componentId, params) {
    var url = '/ajax/get_component_content';

    $.post(url, {
        component_id: componentId,
        params: params
    }, function(response) {
        $('#component-' + componentId).html(response.data.html);

        // Re-initialize any new components
        initializeComponents();
    });
}
```

### Component Communication

Components can communicate through events:

```javascript
// Trigger custom event
$(document).trigger('component:updated', {
    componentId: 'user-form',
    action: 'saved'
});

// Listen for events
$(document).on('component:updated', function(e, data) {
    if (data.action === 'saved') {
        updateRelatedComponents(data.componentId);
    }
});
```

## Mobile Support

### Mobile Detection

JavaScript detects mobile context:

```javascript
function isMobileApp() {
    return $('body').hasClass('mobile-app') ||
           window.location.search.indexOf('mobile=1') > -1;
}

function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i
           .test(navigator.userAgent);
}
```

### Mobile-Specific Behavior

Different behavior for mobile vs web:

```javascript
if (isMobileApp()) {
    // Mobile-specific code
    useNativeDatePicker();
    enableTouchGestures();
} else {
    // Web-specific code
    useBootstrapDatePicker();
    enableHoverEffects();
}
```

### Touch Events

Mobile-optimized interactions:

```javascript
// Touch-friendly interactions
$('.touch-button').on('touchstart', function() {
    $(this).addClass('active');
}).on('touchend', function() {
    $(this).removeClass('active');

    // Handle click action
    performAction($(this).data('action'));
});
```

## UI Components

### Bootstrap Integration

Components use Bootstrap classes:

```javascript
function createBootstrapAlert(type, message) {
    return $('<div>')
        .addClass('alert alert-' + type + ' alert-dismissible')
        .html(message +
            '<button type="button" class="close" data-dismiss="alert">' +
            '<span>&times;</span></button>');
}
```

### DataTables Integration

Advanced table functionality:

```javascript
function initializeDataTable($table, options) {
    $table.DataTable({
        paging: options.paging !== false,
        searching: options.searching !== false,
        ordering: options.ordering !== false,
        responsive: true,
        language: getDataTableLanguage()
    });
}
```

### Modal Management

Modal dialogs for user interactions:

```javascript
function showModal(title, content, buttons) {
    var $modal = $('#main-modal');
    $modal.find('.modal-title').text(title);
    $modal.find('.modal-body').html(content);

    // Configure buttons
    var $footer = $modal.find('.modal-footer').empty();
    buttons.forEach(function(button) {
        $('<button>')
            .addClass('btn btn-' + button.class)
            .text(button.text)
            .click(button.click)
            .appendTo($footer);
    });

    $modal.modal('show');
}
```

## Performance Optimization

### Lazy Loading

Components load JavaScript only when needed:

```javascript
function loadScriptAsync(src, callback) {
    var script = document.createElement('script');
    script.src = src;
    script.async = true;
    script.onload = callback;

    document.head.appendChild(script);
}

// Load component JS only when component is visible
function loadComponentJS(componentName) {
    if (!window[componentName + 'Loaded']) {
        loadScriptAsync('/js/components/' + componentName + '.js', function() {
            window[componentName + 'Loaded'] = true;
            initializeComponent(componentName);
        });
    }
}
```

### Debounced Events

Prevent excessive event firing:

```javascript
function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Usage for search input
$('.search-input').on('input', debounce(function() {
    performSearch($(this).val());
}, 300));
```

## CMS Integration

### CMS Editing Mode

Special JavaScript for CMS editing:

```javascript
function enableCMSEditing() {
    $('.cms-editable').each(function() {
        var $element = $(this);
        var componentId = $element.data('component-id');

        // Add edit overlay
        $element.append(
            '<div class="cms-edit-overlay">' +
            '<button class="btn btn-primary cms-edit-btn" ' +
                    'data-component-id="' + componentId + '">' +
                'Edit' +
            '</button></div>'
        );
    });
}
```

### Live Preview

Real-time preview of changes:

```javascript
function updateComponentPreview(componentId, newContent) {
    var $component = $('#component-' + componentId);

    // Update content
    $component.html(newContent);

    // Re-initialize any JavaScript
    initializeComponentsInContainer($component);
}
```

## Development Guidelines

### JavaScript Best Practices

1. **Modular Code**: Organize code into small, focused functions
2. **Event Delegation**: Use event delegation for dynamic content
3. **Progressive Enhancement**: Ensure functionality without JavaScript
4. **Accessibility**: Support keyboard navigation and screen readers
5. **Performance**: Minimize DOM manipulation and use efficient selectors

### Code Organization

```javascript
// Component-specific code
var ComponentName = {
    init: function() {
        this.bindEvents();
        this.initializeState();
    },

    bindEvents: function() {
        $(document).on('click', '.component-selector', this.handleClick);
    },

    handleClick: function(e) {
        // Event handler
    },

    initializeState: function() {
        // Initial setup
    }
};

// Initialize on document ready
$(document).ready(function() {
    ComponentName.init();
});
```

### Testing

Basic testing approach:

```javascript
// Simple test runner
function runTests() {
    test('Form validation works', function() {
        var $form = createTestForm();
        assert(validateForm($form), 'Form should be valid');
    });

    test('AJAX error handling', function() {
        handleAjaxError({ status: 'error', message: 'Test error' });
        assert($('.error-message').is(':visible'), 'Error should be displayed');
    });
}
```

### Debugging

Console logging and debugging tools:

```javascript
// Debug logging
function debugLog(message, data) {
    if (DEBUG_MODE) {
        console.log('[DEBUG]', message, data);
    }
}

// Component state inspection
function inspectComponent($element) {
    console.log('Component data:', $element.data());
    console.log('Component classes:', $element.attr('class'));
    console.log('Component HTML:', $element.html());
}
```
