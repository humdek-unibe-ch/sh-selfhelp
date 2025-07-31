# Database Design

## 🗄️ Database Architecture Overview

The SelfHelp Symfony Backend uses a sophisticated MySQL database design that supports dynamic routing, fine-grained permissions, content management, and comprehensive audit trails.

## 📊 Database Schema Overview

```mermaid
erDiagram
    %% Core Authentication & Authorization
    User ||--o{ UsersGroup : belongs_to
    UsersGroup }o--|| Group : represents
    Group ||--o{ UserGroupsPermission : has
    UserGroupsPermission }o--|| Permission : grants
    
    %% API Routes & Permissions
    ApiRoute ||--o{ ApiRoutePermission : requires
    ApiRoutePermission }o--|| Permission : grants
    
    %% CMS Content Structure
    Page ||--o{ PagesSection : contains
    PagesSection }o--|| Section : has
    Section ||--o{ SectionsField : contains
    SectionsField }o--|| Field : has
    Section }o--|| Style : styled_by
    
    %% Fine-grained Access Control
    Page ||--o{ AclUser : user_acl
    Page ||--o{ AclGroup : group_acl
    AclUser }o--|| User : for_user
    AclGroup }o--|| Group : for_group
    
    %% Multi-language Support
    Field ||--o{ FieldsTranslation : translations
    FieldsTranslation }o--|| Language : in_language
    Page ||--o{ PagesFieldsTranslation : page_translations
    PagesFieldsTranslation }o--|| Language : in_language
    
    %% System Components
    User ||--o{ Transaction : performed_by
    ScheduledJob ||--o{ ScheduledJobsUser : assigned_to
    ScheduledJobsUser }o--|| User : for_user
    Asset }o--|| Lookup : asset_type
```

## 🔧 Core Table Groups

### 1. Authentication & Authorization Tables

#### `users` - User Accounts
```sql
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9F85E0677` (`username`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `groups` - User Groups
```sql
CREATE TABLE `groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F06D39705E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### `permissions` - System Permissions
```sql
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2DEDCC6F5E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Junction Tables
- **`users_groups`**: Links users to groups (many-to-many)
- **`user_groups_permissions`**: Links groups to permissions (many-to-many)

### 2. Dynamic Routing Tables

#### `api_routes` - Dynamic Route Definitions
```sql
CREATE TABLE `api_routes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `route_name` varchar(100) NOT NULL,
  `version` varchar(10) NOT NULL DEFAULT 'v1',
  `path` varchar(255) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `methods` varchar(50) NOT NULL,
  `requirements` json DEFAULT NULL,
  `params` json DEFAULT NULL COMMENT 'Expected parameters: name → {in: body|query, required: bool}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_route_name_version` (`route_name`,`version`),
  UNIQUE KEY `uniq_version_path_methods` (`version`,`path`,`methods`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Key Features:**
- **Dynamic Loading**: Routes loaded from database at runtime
- **Versioning Support**: Multiple API versions per route
- **Parameter Documentation**: JSON schema for expected parameters
- **Method Specification**: HTTP methods (GET, POST, PUT, DELETE)

#### `api_routes_permissions` - Route Permission Requirements
```sql
CREATE TABLE `api_routes_permissions` (
  `id_api_routes` int NOT NULL,
  `id_permissions` int NOT NULL,
  PRIMARY KEY (`id_api_routes`,`id_permissions`),
  FOREIGN KEY (`id_api_routes`) REFERENCES `api_routes` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_permissions`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Content Management System Tables

#### `pages` - CMS Pages
```sql
CREATE TABLE `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `protocol` varchar(10) DEFAULT 'https',
  `parent` int DEFAULT NULL,
  `id_type` int NOT NULL,
  `id_pageAccessTypes` int DEFAULT NULL,
  `is_headless` tinyint(1) NOT NULL DEFAULT '0',
  `nav_position` int DEFAULT NULL,
  `footer_position` int DEFAULT NULL,
  `is_open_access` tinyint(1) DEFAULT '0',
  `is_system` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2074E575A17F5E88` (`keyword`),
  FOREIGN KEY (`parent`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_type`) REFERENCES `pageTypes` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_pageAccessTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### `sections` - Content Sections
```sql
CREATE TABLE `sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_styles` int NOT NULL,
  `parent` int DEFAULT NULL,
  `position` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_styles`) REFERENCES `styles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### `fields` - Content Fields
```sql
CREATE TABLE `fields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `id_fieldTypes` int NOT NULL,
  `default_value` longtext,
  `help` varchar(1000) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_fieldTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### Junction Tables for CMS
- **`pages_sections`**: Links pages to sections with position
- **`sections_fields`**: Links sections to fields with position
- **`sections_navigation`**: Navigation-specific section relationships

### 4. Access Control Lists (ACL) Tables

#### `acl_users` - User-Level Page Permissions
```sql
CREATE TABLE `acl_users` (
  `id_users` int NOT NULL,
  `id_pages` int NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_users`,`id_pages`),
  FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### `acl_groups` - Group-Level Page Permissions
```sql
CREATE TABLE `acl_groups` (
  `id_groups` int NOT NULL,
  `id_pages` int NOT NULL,
  `acl_select` tinyint(1) NOT NULL DEFAULT '1',
  `acl_insert` tinyint(1) NOT NULL DEFAULT '0',
  `acl_update` tinyint(1) NOT NULL DEFAULT '0',
  `acl_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_groups`,`id_pages`),
  FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

**ACL Permission Types:**
- **`acl_select`**: Read access (view page/content)
- **`acl_insert`**: Create access (add new content)
- **`acl_update`**: Update access (modify existing content)
- **`acl_delete`**: Delete access (remove content)

### 5. Multi-language Support Tables

#### `languages` - Supported Languages
```sql
CREATE TABLE `languages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `language` varchar(100) NOT NULL,
  `locale` varchar(10) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5D237014D4DB71B5` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### `fieldsTranslations` - Field Content Translations
```sql
CREATE TABLE `fieldsTranslations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_fields` int NOT NULL,
  `id_languages` int NOT NULL,
  `content` longtext,
  `meta` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_field_language` (`id_fields`,`id_languages`),
  FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

### 6. System Tables

#### `transactions` - Audit Trail
```sql
CREATE TABLE `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_users` int DEFAULT NULL,
  `id_transactionTypes` int DEFAULT NULL,
  `id_transactionBy` int DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `id_table_name` int DEFAULT NULL,
  `transaction_log` longtext,
  `transaction_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_transactionTypes`) REFERENCES `lookups` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_transactionBy`) REFERENCES `lookups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### `scheduledJobs` - Background Tasks
```sql
CREATE TABLE `scheduledJobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(1000) DEFAULT NULL,
  `date_create` datetime NOT NULL,
  `date_to_be_executed` datetime DEFAULT NULL,
  `date_executed` datetime DEFAULT NULL,
  `config` varchar(1000) DEFAULT NULL,
  `id_jobStatus` int NOT NULL,
  `id_jobTypes` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_jobStatus`) REFERENCES `lookups` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_jobTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### `version` - Database Version Tracking
```sql
CREATE TABLE `version` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

### 7. Lookup Tables System

#### `lookups` - Dynamic Lookup Values
```sql
CREATE TABLE `lookups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_code` varchar(100) NOT NULL,
  `code` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_type_code` (`type_code`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

**Common Lookup Types:**
- `TRANSACTION_TYPES`: insert, update, delete
- `TRANSACTION_BY`: user, system, admin
- `JOB_TYPES`: email, notification, task
- `JOB_STATUS`: pending, running, completed, failed
- `FIELD_TYPES`: text, textarea, select, checkbox
- `ASSET_TYPES`: image, document, css, js

## 🔄 Stored Procedures

### ACL Permission Check Procedure
```sql
DELIMITER //
CREATE PROCEDURE get_user_acl(IN userId INT, IN pageId INT)
BEGIN
    SELECT 
        COALESCE(MAX(au.acl_select), MAX(ag.acl_select), 0) as acl_select,
        COALESCE(MAX(au.acl_insert), MAX(ag.acl_insert), 0) as acl_insert,
        COALESCE(MAX(au.acl_update), MAX(ag.acl_update), 0) as acl_update,
        COALESCE(MAX(au.acl_delete), MAX(ag.acl_delete), 0) as acl_delete
    FROM users u
    LEFT JOIN acl_users au ON u.id = au.id_users AND au.id_pages = pageId
    LEFT JOIN users_groups ug ON u.id = ug.id_users
    LEFT JOIN acl_groups ag ON ug.id_groups = ag.id_groups AND ag.id_pages = pageId
    WHERE u.id = userId;
END //
DELIMITER ;
```

**Purpose**: Efficiently calculates user permissions for a specific page by combining user-specific and group-based ACL rules.

### Index Management Procedure
```sql
DELIMITER //
CREATE PROCEDURE add_index(
    param_table VARCHAR(100), 
    param_index_name VARCHAR(100), 
    param_index_column VARCHAR(1000),
    param_is_unique BOOLEAN
)
BEGIN	
    SET @sqlstmt = (SELECT IF(
        (SELECT COUNT(*) FROM information_schema.STATISTICS 
         WHERE `table_schema` = DATABASE()
         AND `table_name` = param_table
         AND `index_name` = param_index_name) > 0,
        "SELECT 'The index already exists in the table'",
        CONCAT(
            IF(param_is_unique, "CREATE UNIQUE INDEX ", "CREATE INDEX "),
            param_index_name, " ON ", param_table, " (", param_index_column, ")"
        )
    ));
    PREPARE stmt FROM @sqlstmt;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;
```

**Purpose**: Safely adds database indexes only if they don't already exist, used in migration scripts.

## 📊 Database Relationships & Constraints

### Foreign Key Relationships
```mermaid
graph TD
    A[users] --> B[users_groups]
    C[groups] --> B
    C --> D[user_groups_permissions]
    E[permissions] --> D
    E --> F[api_routes_permissions]
    G[api_routes] --> F
    
    H[pages] --> I[pages_sections]
    J[sections] --> I
    J --> K[sections_fields]
    L[fields] --> K
    
    A --> M[acl_users]
    H --> M
    C --> N[acl_groups]
    H --> N
    
    A --> O[transactions]
    P[lookups] --> O
```

### Cascade Delete Rules
- **User deletion**: Cascades to `users_groups`, `acl_users`, sets NULL in `transactions`
- **Group deletion**: Cascades to `users_groups`, `user_groups_permissions`, `acl_groups`
- **Page deletion**: Cascades to `pages_sections`, `acl_users`, `acl_groups`
- **Section deletion**: Cascades to `sections_fields`, child sections
- **API route deletion**: Cascades to `api_routes_permissions`

## 🔍 Indexing Strategy

### Primary Indexes
- All tables have auto-incrementing primary keys
- Unique constraints on business keys (username, email, keyword, locale)

### Performance Indexes
```sql
-- User lookup optimization
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);

-- ACL performance
CREATE INDEX idx_acl_users_user ON acl_users(id_users);
CREATE INDEX idx_acl_users_page ON acl_users(id_pages);
CREATE INDEX idx_acl_groups_group ON acl_groups(id_groups);
CREATE INDEX idx_acl_groups_page ON acl_groups(id_pages);

-- CMS navigation
CREATE INDEX idx_pages_parent ON pages(parent);
CREATE INDEX idx_pages_nav_position ON pages(nav_position);
CREATE INDEX idx_sections_parent ON sections(parent);
CREATE INDEX idx_sections_position ON sections(position);

-- API routing
CREATE INDEX idx_api_routes_version ON api_routes(version);
CREATE INDEX idx_api_routes_path ON api_routes(path);

-- Transaction queries
CREATE INDEX idx_transactions_user ON transactions(id_users);
CREATE INDEX idx_transactions_table ON transactions(table_name, id_table_name);
CREATE INDEX idx_transactions_time ON transactions(transaction_time);
```

## 🔧 Entity-Database Mapping

### Doctrine Entity Rules
Based on the codebase analysis, entities must follow these patterns:

#### ✅ Correct Association Mapping
```php
#[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', onDelete: 'CASCADE')]
private ?User $user = null;

public function setUser(?User $user): static
{
    $this->user = $user;
    return $this;
}
```

#### ❌ Incorrect Primitive Mapping
```php
// Don't use primitive foreign keys
private ?int $idUsers = null;
public function setIdUsers(?int $idUsers): self { }
```

### Entity Synchronization
- All entities must sync with `db/structure_db.sql`
- Column names in entities match database column names
- Proper ORM attributes for relationships
- Generate complete getters and setters
- Add "ENTITY RULE" comment when designing

## 📈 Performance Considerations

### Query Optimization
- Use stored procedures for complex ACL calculations
- Implement proper indexing for frequent queries
- Use eager loading for related entities
- Cache lookup table values

### Connection Management
- Connection pooling for high concurrency
- Read replicas for reporting queries
- Transaction isolation for data consistency

### Storage Optimization
- JSON columns for flexible configuration data
- LONGTEXT for large content fields
- Proper charset (utf8mb4) for international content
- Engine selection (InnoDB for transactions)

## 🔒 Security Considerations

### Data Protection
- Password hashing with BCrypt
- Sensitive data encryption where needed
- Audit trail for all changes
- Secure token storage

### Access Control
- Multi-layer permission system
- Fine-grained ACL for pages
- Role-based access control
- Permission inheritance through groups

### Data Integrity
- Foreign key constraints
- Check constraints where applicable
- Transaction wrapping for complex operations
- Backup and recovery procedures

---

**Next**: [API Design Patterns](./05-api-patterns.md)