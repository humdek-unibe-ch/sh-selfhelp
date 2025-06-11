# SH-Selfhelp Symfony Backend Documentation

## Project Overview

The SH-Selfhelp backend is built on Symfony 7.2 with PHP 8.3 and Doctrine ORM 3.3, following PSR-4 standards. It provides a comprehensive API for content management system (CMS) settings and user management.

## Architecture

### Directory Structure

```
server/symfony/
├── bin/                      # Symfony console and other executables
├── config/                   # Configuration files
├── public/                   # Web server document root
├── src/                      # Application source code
│   ├── Controller/           # Controllers
│   │   ├── Api/              # API controllers
│   │   │   ├── ApiVersionResolver.php
│   │   │   └── V1/           # API v1 controllers
│   │   │       ├── Admin/    # Admin controllers
│   │   │       ├── Auth/     # Authentication controllers
│   │   │       └── Frontend/ # Frontend controllers
│   ├── Entity/               # Doctrine entities
│   ├── EventListener/        # Event listeners
│   ├── Exception/            # Custom exceptions
│   ├── Repository/           # Doctrine repositories
│   ├── Routing/              # Custom routing components
│   ├── Security/             # Security components
│   ├── Service/              # Service layer
│   │   ├── ACL/              # Access control services
│   │   ├── Auth/             # Authentication services
│   │   ├── CMS/              # CMS services
│   │   │   ├── Admin/        # Admin-specific services
│   │   │   └── Frontend/     # Frontend-specific services
│   │   └── Core/             # Core services
│   └── Util/                 # Utility classes
├── tests/                    # Test suite
└── var/                      # Cache, logs, and other runtime files
```

## Database Structure

The database follows a relational model with entities perfectly synchronized using Doctrine ORM. Key tables include:

### Core Tables

- **pages**: Stores page information with fields like `keyword`, `url`, `protocol`, `is_headless`, `is_open_access`, `is_system`
- **sections**: Defines sections that can be placed on pages
- **fields**: Defines content fields that can be used in pages and sections
- **pages_fields_translation**: Stores multilingual content for page fields
- **sections_fields_translation**: Stores multilingual content for section fields

### Access Control Tables

- **users**: User accounts and authentication information
- **groups**: User groups for role-based access control
- **acl_users**: User-specific page permissions
- **acl_groups**: Group-specific page permissions

### API and System Tables

- **api_routes**: Dynamic API route definitions
- **api_request_logs**: Logs of API requests
- **transactions**: Audit trail of system changes

## Entity Structure

Entities are defined using Doctrine ORM annotations and follow a consistent pattern:

### Entity Rules

1. All entities must have proper ORM attributes
2. Relationships between entities must be clearly defined
3. Getter and setter methods must be implemented for all properties
4. Many-to-One relationships should be properly mapped with JoinColumn annotations

### Example Entity: Page

```php
#[ORM\Entity]
#[ORM\Table(name: 'pages')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'keyword', type: 'string', length: 100, unique: true)]
    private ?string $keyword = null;

    // Relationships
    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_pageAccessTypes', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Lookup $pageAccessType = null;

    // Getters and setters
    public function getId(): ?int { return $this->id; }
    
    public function getKeyword(): ?string { return $this->keyword; }
    public function setKeyword(string $keyword): static
    {
        $this->keyword = $keyword;
        return $this;
    }
    
    // Relationship getters/setters
    public function getPageAccessType(): ?Lookup { return $this->pageAccessType; }
    public function setPageAccessType(?Lookup $pageAccessType): static
    {
        $this->pageAccessType = $pageAccessType;
        return $this;
    }
}
```

## API Structure

### API Versioning

The API supports versioning to maintain backward compatibility while evolving. Versions can be specified in two ways:

1. **URL Path**: `/cms-api/v1/...`
2. **Accept Header**: `Accept: application/vnd.selfhelp.v1+json`

### Dynamic Route Registration

Routes are stored in the database (`api_routes` table) rather than in configuration files, allowing for dynamic route management:

```sql
INSERT INTO `api_routes` (`route_name`,`version`,`path`,`controller`,`methods`,`requirements`,`params`) VALUES
('pages','v1','/pages','App\\Controller\\Api\\V1\\Frontend\\PageController::getPages','GET',NULL,NULL);
```

### Response Format

All API responses follow a consistent structure:

```json
{
    "status": {
        "success": true,
        "code": 200,
        "message": "Success"
    },
    "data": {
        // Response data
    }
}
```

Error responses:

```json
{
    "status": {
        "success": false,
        "code": 400,
        "message": "Error message"
    },
    "data": null
}
```

## Service Layer

The service layer follows a domain-driven design approach:

### Core Services

- **ApiResponseFormatter**: Standardizes API response formatting
- **TransactionService**: Handles transaction logging
- **UserContextService**: Manages current user context

### CMS Services

- **AdminPageService**: Handles admin-specific page operations
- **PageService**: Manages frontend page operations

### Authentication and Authorization

- **ACLService**: Manages access control
- **JWTService**: Handles JWT token operations
- **LoginService**: Manages user authentication

## Recent Database Changes (v8.0.0)

Key changes in version 8.0.0:

1. Added `is_open_access` column to `pages` table to support pages accessible without authentication
2. Added `is_system` column to `pages` table to protect system pages from deletion
3. Updated the `get_user_acl` stored procedure to include open access pages
4. Added performance logging table `logPerformance` for monitoring
5. Removed deprecated tables and columns

## Best Practices

### Entity Development

1. Always use Doctrine ORM attributes for entity definitions
2. Define clear relationships between entities
3. Implement getter and setter methods for all properties
4. Follow the "ENTITY RULE" pattern for consistency

### API Development

1. Use the dynamic route registration system
2. Follow RESTful API design principles
3. Use the ApiResponseFormatter for consistent responses
4. Implement proper error handling with appropriate HTTP status codes

### Database Operations

1. Use transactions for operations that modify multiple records
2. Log all significant data changes using TransactionService
3. Use parameter binding with explicit types for database queries
4. Follow the principle of least privilege for database access

### Security

1. Implement proper access control checks in all controllers and services
2. Use JWT for API authentication
3. Validate all user input against JSON schemas
4. Log security-related events

## Testing

Tests are organized in the `tests/` directory, mirroring the structure of the `src/` directory:

```
tests/
├── Controller/
│   └── Api/
│       └── V1/
│           ├── Admin/
│           ├── Auth/
│           └── Frontend/
├── Service/
└── Entity/
```

PHPUnit is configured in `phpunit.xml.dist` and tests can be run with:

```bash
php bin/phpunit
```

## Deployment

The application is designed to be deployed in a standard Symfony environment with:

1. PHP 8.3 or higher
2. MySQL/MariaDB database
3. Web server (Apache or Nginx) with proper URL rewriting

## Conclusion

The SH-Selfhelp Symfony backend provides a robust foundation for the CMS system with a clean architecture, comprehensive API, and strong security features. Following the documented best practices will ensure maintainability and extensibility of the codebase.