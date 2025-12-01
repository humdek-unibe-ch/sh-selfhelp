# SelfHelp System Concept Documentation

## Overview

SelfHelp is a comprehensive vanilla PHP Content Management System (CMS) designed for building dynamic web applications and experiments. The system combines PHP templates with vanilla JavaScript to create a flexible, component-based architecture that supports both web and mobile applications.

## Table of Contents

1. [Architecture Overview](architecture.md)
2. [Database Structure and Versioning](database.md)
3. [Component System (MVC)](components.md)
4. [Routing and Page Management](routing.md)
5. [Frontend and JavaScript](frontend.md)
6. [Configuration and Deployment](configuration.md)
7. [Caching and Performance](caching.md)
8. [Security and Authentication](security.md)
9. [Development Workflow](development.md)
10. [API Reference](api-reference.md)

## Key Characteristics

- **Vanilla PHP**: No frameworks, pure PHP with custom architecture
- **Component-based**: Modular MVC architecture for UI components
- **Database-driven**: Dynamic content loaded from MySQL database
- **Version-controlled**: Semantic versioning with database migrations
- **Mobile-ready**: JSON API for mobile applications
- **Plugin system**: Extensible through plugins
- **Multi-language**: Built-in internationalization support

## Technology Stack

- **Backend**: PHP 8.2+ (8.3 recommended)
- **Database**: MySQL 8.0+
- **Frontend**: Vanilla JavaScript, Bootstrap 4.6, jQuery
- **Caching**: APCu (Alternative PHP Cache User)
- **Build Tools**: Gulp for asset minification
- **Debugging**: Clockwork for performance monitoring

## Getting Started

### Prerequisites

- PHP 8.2 or higher (8.3 recommended)
- MySQL 8.0 or higher
- Apache/Nginx web server
- APCu extension for caching
- Composer (for dependencies)

### Installation

1. Clone the repository
2. Configure database settings in `server/service/globals_untracked.php`
3. Run database migrations in order from `server/db/update_scripts/`
4. Set up web server configuration
5. Install dependencies: `composer install` (if applicable)
6. Run gulp build: `cd gulp && npm install && gulp`

### Development

The system follows a component-based architecture where each UI element is a separate component with Model-View-Controller structure. Components are stored in `server/component/style/` and follow a specific naming convention.

Database changes are managed through migration scripts in `server/db/update_scripts/` with semantic versioning (major.minor.patch).

### Contributing

When adding new features:
1. Create database migration scripts if schema changes are needed
2. Follow the component architecture pattern
3. Update changelog with appropriate version bump
4. Test both web and mobile interfaces
5. Ensure proper error handling and logging

## Version History

See [CHANGELOG.md](../CHANGELOG.md) for detailed version history and breaking changes.

## License

Mozilla Public License 2.0
