# SH-Selfhelp Symfony Backend Documentation

## Controller Structure and API Versioning

### Directory Structure
```
src/Controller/
├── Api/                     # API controllers
│   ├── BaseApiController.php # Base controller for all API controllers
│   ├── ApiVersionResolver.php # Resolves API versions
│   └── V1/                  # API v1 controllers
│       ├── Admin/           # Admin controllers
│       │   └── AdminController.php
│       ├── Auth/            # Authentication controllers
│       │   └── AuthController.php
│       └── Frontend/         # Frontend controllers
│           └── ContentController.php
└── [Legacy controllers]     # Legacy controllers (to be migrated)
```

### API Versioning

The API supports versioning to maintain backward compatibility while evolving the API. Versions can be specified in two ways:

1. **URL Path**: `/cms-api/v1/...`
2. **Accept Header**: `Accept: application/vnd.selfhelp.v1+json`

If no version is specified, the default version (v1) is used.

### Controller Hierarchy

- **BaseApiController**: Provides common functionality for all API controllers
- **Version-specific controllers**: Implement endpoints for specific API versions

### Adding a New API Version

To add a new API version:

1. Create a new directory under `src/Controller/Api/` (e.g., `V2/`)
2. Create subdirectories for each controller domain (Admin, Auth, Content, etc.)
3. Create controllers in each subdirectory
4. Update `ApiVersionResolver::AVAILABLE_VERSIONS` to include the new version
5. Update the API routes database table with the new version

## Service Layer Organization

### Directory Structure
```
src/Service/
├── Auth/                     # Authentication related services
│   ├── JWTService.php        # JWT token handling
│   ├── LoginService.php      # Login functionality
│   └── UserContextService.php # Current user context
├── CMS/                      # Content Management System
│   ├── Admin/                # Admin-specific services
│   │   └── AdminPageService.php
│   └── Frontend/             # Frontend-specific services
│       └── PageService.php
├── ACL/                      # Access Control
│   └── ACLService.php        # Permissions and access control
├── Core/                     # Core framework services
│   ├── BaseService.php       # Base service functionality
│   ├── UserContextAwareService.php # User context for services
│   └── ApiResponseFormatter.php # Response formatting
└── Dynamic/                  # Dynamic components
    └── DynamicControllerService.php # Dynamic routing
```

### Key Principles
1. **Domain-Driven Design**: Services are organized by their domain/responsibility
2. **Separation of Concerns**: Clear boundaries between different parts of the system
3. **Discoverability**: Easier to find related services
4. **Maintainability**: Simpler to maintain and extend
5. **Scalability**: New services can be added to the appropriate domain

### Service Categories

#### Auth Services
Services related to authentication, user context, and security.

#### CMS Services
Services for content management, split into Admin (backend management) and Frontend (public-facing content delivery).

#### ACL Services
Services for access control and permissions management.

#### Core Services
Foundational services that provide base functionality for other services.

#### Dynamic Services
Services for dynamic routing and controller handling.

## API Response Structure
All API responses follow a standardized format:

```json
{
    "status": 200,
    "message": "OK",
    "error": null,
    "logged_in": true,
    "meta": {
        "version": "v1",
        "timestamp": "2025-05-19T10:50:41+02:00"
    },
    "data": {} // Your response data here
}
```

## API Versioning and Database-Driven Routing

### API Versioning System

The API supports versioning to maintain backward compatibility while evolving the API. The versioning system consists of several components:

1. **ApiVersionResolver**: Detects API versions from requests
2. **ApiVersionListener**: Integrates version detection into the request flow
3. **ApiRouteLoader**: Loads routes from the database and maps them to versioned controllers
4. **Versioned Controllers**: Implement API endpoints for specific versions

### Version Detection

API versions can be specified in two ways:

1. **URL Path**: `/cms-api/v1/...`
2. **Accept Header**: `Accept: application/vnd.selfhelp.v1+json`

If no version is specified, the default version (v1) is used.

### Database-Driven Routing

All API routes are dynamically loaded from the database. You do not need to edit YAML, PHP, or use fixtures/commands for route registration. To add or modify an API route, simply insert or update the relevant entry in the `api_routes` table.

```sql
CREATE TABLE `api_routes` (
  `id`           INT             NOT NULL AUTO_INCREMENT,
  `route_name`   VARCHAR(100)    NOT NULL,
  `version`      VARCHAR(10)     NOT NULL DEFAULT 'v1',
  `path`         VARCHAR(255)    NOT NULL,
  `controller`   VARCHAR(255)    NOT NULL,
  `methods`      VARCHAR(50)     NOT NULL,
  `requirements` JSON            NULL,
  `params`       JSON            NULL COMMENT 'Expected parameters: name → {in: body|query, required: bool}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_route_name_version` (`route_name`, `version`),
  UNIQUE KEY `uniq_version_path` (`version`, `path`)
);
```

### Controller Mapping

The system automatically maps controllers from the database to the versioned namespace structure:

```
Database controller: App\Controller\AuthController::login
↓
Actual controller: App\Controller\Api\V1\Auth\AuthController::login
```

This mapping is handled by the `ApiRouteLoader::mapControllerToVersionedNamespace()` method.

### Route Structure Best Practices

Routes should follow these best practices:

1. **Group by Domain**: Group routes by their domain (Auth, Content, Admin, etc.)
2. **Use Consistent Naming**: Use consistent route naming conventions
3. **Use RESTful Patterns**: Follow RESTful patterns for resource operations
4. **Version All Routes**: Always specify a version for each route

### Example Route Registration

```sql
INSERT IGNORE INTO `api_routes` (`route_name`,`version`,`path`,`controller`,`methods`,`requirements`,`params`) VALUES
-- Auth routes
('auth_login','v1','/auth/login','App\\Controller\\Api\\V1\\Auth\\AuthController::login','POST',NULL,JSON_OBJECT('user',JSON_OBJECT('in','body','required',true),'password',JSON_OBJECT('in','body','required',true))),

-- Frontend routes
('content_pages','v1','/pages','App\\Controller\\Api\\V1\\Content\\ContentController::getAllPages','GET',NULL,NULL),

-- Admin routes
('admin_get_pages','v1','/admin/pages','App\\Controller\\Api\\V1\\Admin\\AdminController::getPages','GET',NULL,NULL);
```

### Adding a New API Version

To add a new API version (e.g., v2):

1. Create a new directory structure under `src/Controller/Api/` (e.g., `V2/`)
2. Create controllers in the appropriate subdirectories
3. Add routes to the database with the new version
4. Update `ApiVersionResolver::AVAILABLE_VERSIONS` to include the new version

The system will automatically load and map the routes to the correct controllers.

## JWT Key Generation and Configuration

### JWT Firewall and User Provider Configuration

- The `security.yaml` file configures a `cms_api` firewall for `/cms-api` endpoints. This firewall uses the `jwt: ~` authenticator from LexikJWTAuthenticationBundle.
- The user provider is set to use the `App\Entity\User` entity, and the property is `username` (which should match the `username` claim in your JWT token, typically the user's email).
- Example JWT payload:
  ```json
  {
    "iat": 1747236586,
    "exp": 1747240186,
    "roles": ["ROLE_USER"],
    "username": "user@email.com"
  }
  ```
- For `/cms-api` endpoints, always send the JWT as:
  ```
  Authorization: Bearer <token>
  ```
- If `$this->getUser()` in a controller returns `null`, check that:
    - The `cms_api` firewall covers your route (pattern: `^/cms-api`).
    - The provider property matches the JWT claim (e.g., `username`).
    - The JWT is valid and not expired.

### JWT Key Pair Generation for Token Authentication

If you encounter errors like `Unable to create a signed JWT from the given configuration`, you must generate the required PEM keys for JWT authentication.

**Step-by-step instructions:**

1. **Create the JWT key directory:**
   ```bash
   mkdir -p config/jwt
   ```
   (This should be run from the Symfony project root.)

2. **Generate the private key:**
   ```bash
   openssl genrsa -out config/jwt/private.pem 4096
   ```

3. **Generate the public key:**
   ```bash
   openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
   ```

4. **(Optional) Use a passphrase for production:**
   ```bash
   openssl genrsa -aes256 -out config/jwt/private.pem 4096
   openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
   ```
   Then set your passphrase in `.env` as `JWT_PASSPHRASE=your_passphrase`.

5. **Update configuration:**
   Ensure your `lexik_jwt_authentication.yaml` or equivalent config points to these keys and uses the correct passphrase.

**Notes:**
- Both `private.pem` and `public.pem` must be readable by the PHP process.
- These steps are required for initial installation and whenever the keys are missing or need to be rotated.

## Overview

Dynamic API routes are stored in the `api_routes` database table and loaded by the custom loader (`ApiRouteLoader`). Each route entry defines the HTTP path, controller, method(s), and optional requirements (e.g., parameter regexes). The entity representing a route is `App\Entity\ApiRoute`.

**Key columns:**
- `name`: The unique route name (e.g., `content_page`)
- `path`: The URL path (e.g., `/pages/{page_keyword}`)
- `controller`: The controller and method to handle the route (e.g., `App\\Controller\\ContentController::getPage`)
- `methods`: HTTP methods as a comma-separated string (e.g., `GET`, `POST`)
- `requirements`: (Optional) JSON string for parameter requirements (e.g., `{ "page_keyword": "[A-Za-z0-9_-]+" }`)
- `version`: The API version (e.g., `v1`, `v2`)

## ACL Integration (2025-05-15)

## Doctrine Entity Attribute Mapping (2025-05-15)

**This section lists all Doctrine entity attributes in `src/Entity` for onboarding and reference.**

### AclGroup
- `id_groups`: int (PK, FK to Group)
- `id_pages`: int (PK, FK to Page)
- `acl_select`: bool
- `acl_insert`: bool
- `acl_update`: bool
- `acl_delete`: bool

### AclUser
- `id_users`: int (PK, FK to User)
- `id_pages`: int (PK, FK to Page)
- `acl_select`: bool
- `acl_insert`: bool
- `acl_update`: bool
- `acl_delete`: bool

### Action
- `id`: int (PK)
- `name`: string (unique)

### ActivityType
- `id`: int (PK)
- `name`: string

### ApiRoute
- `id`: int (PK)
- `route_name`: string (unique)
- `path`: string
- `controller`: string
- `methods`: string
- `requirements`: json/array (nullable)
- `params`: json/array (nullable)
- `version`: string (default 'v1')

### Asset
- `id`: int (PK)
- `id_assetTypes`: int (FK to Lookup)
- `folder`: string (nullable)
- `file_name`: string (nullable)
- `file_path`: string

### CallbackLog
- `id`: int (PK)
- `callback_date`: datetime
- `remote_addr`: string (nullable)
- `redirect_url`: string (nullable)
- `callback_params`: text (nullable)
- `status`: string (nullable)
- `callback_output`: text (nullable)

### Chat
- `id`: int (PK)
- `id_snd`: int (FK to User)
- `id_rcv`: int (nullable, FK to User)
- `content`: text
- `timestamp`: datetime
- `id_rcv_group`: int (FK to Group)

### ChatRecipiant
- `id_users`: int (PK, FK to User)
- `id_chat`: int (PK, FK to Chat)
- `id_room_users`: int (nullable, FK to ChatRoomUser)
- `is_new`: bool

### ChatRoom
- `id`: int (PK)
- `name`: string
- `description`: string (nullable)
- `created_at`: datetime

### ChatRoomUser
- `id`: int (PK)
- `id_users`: int (FK to User)
- `id_chatRoom`: int (FK to ChatRoom)
- `is_admin`: bool
- `joined_at`: datetime

### CmsPreference
- `id`: int (PK)
- `callback_api_key`: string (nullable)
- `default_language_id`: int (nullable, FK to Language)
- `anonymous_users`: int
- `firebase_config`: string (nullable)

### CodesGroup
- `code`: string (PK, FK to ValidationCode)
- `id_groups`: int (PK, FK to Group)

### DataCell
- `id_dataRows`: int (PK, FK to DataRow)
- `id_dataCols`: int (PK, FK to DataCol)
- `value`: text

### DataCol
- `id`: int (PK)
- `name`: string (nullable)
- `id_dataTables`: int (nullable, FK to DataTable)

### DataRow
- `id`: int (PK)
- `id_dataTables`: int (nullable, FK to DataTable)
- `timestamp`: datetime
- `id_users`: int (nullable, FK to User)
- `id_actionTriggerTypes`: int (nullable, FK to Lookup)

### DataTable
- `id`: int (PK)
- `name`: string
- `timestamp`: datetime
- `displayName`: string (nullable)

### Field
- `id`: int (PK)
- `name`: string
- `id_type`: int (FK to FieldType)
- `display`: bool

### FieldType
- `id`: int (PK)
- `name`: string
- `position`: int

### FormAction
- `id`: int (PK)
- `name`: string
- `id_formProjectActionTriggerTypes`: int
- `config`: text (nullable)
- `id_dataTables`: int (nullable, FK to DataTable)

### Gender
- `id`: int (PK)
- `name`: string

### Group
- `id`: int (PK)
- `name`: string
- `description`: string
- `id_group_types`: int (nullable, FK to Lookup)
- `requires_2fa`: bool

### Hook
- `id`: int (PK)
- `id_hookTypes`: int (FK to Lookup)
- `name`: string (nullable)
- `description`: string (nullable)
- `class`: string
- `function`: string
- `exec_class`: string
- `exec_function`: string
- `priority`: int (nullable)

### Language
- `id`: int (PK)
- `locale`: string
- `language`: string
- `csv_separator`: string

### Library
- `id`: int (PK)
- `name`: string (nullable)
- `version`: string (nullable)
- `license`: string (nullable)
- `comments`: string (nullable)

### LogPerformance
- `id_user_activity`: int (PK, FK to UserActivity)
- `log`: text (nullable)

### Lookup
- `id`: int (PK)
- `type_code`: string
- `lookup_code`: string (nullable)
- `lookup_value`: string (nullable)
- `lookup_description`: string (nullable)

### MailAttachment
- `id`: int (PK)
- `id_mailQueue`: int (FK to MailQueue)
- `attachment_name`: string (nullable)
- `attachment_path`: string
- `attachment_url`: string
- `template_path`: string

### MailQueue
- `id`: int (PK)
- `from_email`: string
- `from_name`: string
- `reply_to`: string
- `recipient_emails`: text
- `cc_emails`: string (nullable)
- `bcc_emails`: string (nullable)
- `subject`: string
- `body`: text
- `is_html`: int (nullable)

### Notification
- `id`: int (PK)
- `subject`: string
- `body`: text
- `url`: string (nullable)

### Page
- `id`: int (PK)
- `keyword`: string (unique)
- `url`: string (nullable)
- `protocol`: string (nullable)
- `id_actions`: int (nullable, FK to Action)
- `id_navigation_section`: int (nullable, FK to Section)
- `parent`: int (nullable, FK to Page)
- `is_headless`: bool
- `nav_position`: int (nullable)
- `footer_position`: int (nullable)
- `id_type`: int (FK to PageType)
- `id_pageAccessTypes`: int (nullable, FK to Lookup)
- `is_open_access`: bool (nullable)
- `is_system`: bool (nullable)

### PageType
- `id`: int (PK)
- `name`: string

### PageTypeField
- `id_pageType`: int (PK, FK to PageType)
- `id_fields`: int (PK, FK to Field)
- `default_value`: string (nullable)
- `help`: text (nullable)

### PagesField
- `id_pages`: int (PK, FK to Page)
- `id_fields`: int (PK, FK to Field)
- `default_value`: string (nullable)
- `help`: text (nullable)

### PagesFieldsTranslation
- `id_pages`: int (PK, FK to Page)
- `id_fields`: int (PK, FK to Field)
- `id_languages`: int (PK, FK to Language)
- `content`: text

### PagesSection
- `id_pages`: int (PK, FK to Page)
- `id_sections`: int (PK, FK to Section)
- `position`: int (nullable)

### Plugin
- `id`: int (PK)
- `name`: string (nullable)
- `version`: string (nullable)

### QualtricsAction
- `id`: int (PK)
- `id_qualtricsProjects`: int (FK to QualtricsProject)
- `id_qualtricsSurveys`: int (FK to QualtricsSurvey)
- `name`: string
- `id_qualtricsProjectActionTriggerTypes`: int (FK to Lookup)
- `id_qualtricsActionScheduleTypes`: int (FK to Lookup)
- `id_qualtricsSurveys_reminder`: int (nullable, FK to QualtricsSurvey)
- `schedule_info`: text (nullable)
- `id_qualtricsActions`: int (nullable, FK to QualtricsAction)

### QualtricsActionsFunction
- `id_qualtricsActions`: int (PK, FK to QualtricsAction)
- `id_lookups`: int (PK, FK to Lookup)

### QualtricsActionsGroup
- `id_qualtricsActions`: int (PK, FK to QualtricsAction)
- `id_groups`: int (PK, FK to Group)

### QualtricsProject
- `id`: int (PK)
- `name`: string
- `description`: string (nullable)
- `qualtrics_api`: string (nullable)
- `api_library_id`: string (nullable)
- `api_mailing_group_id`: string (nullable)
- `created_on`: datetime
- `edited_on`: datetime

### QualtricsReminder
- `id_qualtricsSurveys`: int (PK, FK to QualtricsSurvey)
- `id_users`: int (PK, FK to User)
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)

### QualtricsSurvey
- `id`: int (PK)
- `name`: string
- `description`: string (nullable)
- `qualtrics_survey_id`: string (nullable)
- `id_qualtricsSurveyTypes`: int (FK to Lookup)
- `participant_variable`: string (nullable)
- `group_variable`: int (nullable)
- `created_on`: datetime
- `edited_on`: datetime
- `config`: text (nullable)

### QualtricsSurveysResponse
- `id`: int (PK)
- `id_users`: int (FK to User)
- `id_surveys`: int (FK to QualtricsSurvey)
- `id_qualtricsProjectActionTriggerTypes`: int (FK to Lookup)
- `survey_response_id`: string (nullable)
- `started_on`: datetime
- `edited_on`: datetime
### RefreshToken
- `id`: bigint (PK)
- `id_users`: bigint
- `token_hash`: string
- `expires_at`: datetime
- `created_at`: datetime (nullable)

### ScheduledJob
- `id`: int (PK)
- `id_jobTypes`: int (FK to Lookup)
- `id_jobStatus`: int (FK to Lookup)
- `description`: string (nullable)
- `date_create`: datetime
- `date_to_be_executed`: datetime (nullable)
- `date_executed`: datetime (nullable)
- `config`: string (nullable)

### ScheduledJobsFormAction
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_formActions`: int (PK, FK to FormAction)
- `id_dataRows`: int (nullable, FK to DataRow)

### ScheduledJobsMailQueue
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_mailQueue`: int (PK, FK to MailQueue)

### ScheduledJobsNotification
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_notifications`: int (PK, FK to Notification)

### ScheduledJobsQualtricsAction
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_qualtricsActions`: int (PK, FK to QualtricsAction)

### ScheduledJobsReminder
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_dataTables`: int (PK, FK to DataTable)
- `session_start_date`: datetime (nullable)
- `session_end_date`: datetime (nullable)

### ScheduledJobsTask
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)
- `id_tasks`: int (PK, FK to Task)

### ScheduledJobsUser
- `id_users`: int (PK, FK to User)
- `id_scheduledJobs`: int (PK, FK to ScheduledJob)

### Section
- `id`: int (PK)
- `id_styles`: int (FK to Style)
- `name`: string

### SectionsFieldsTranslation
- `id_sections`: int (PK, FK to Section)
- `id_fields`: int (PK, FK to Field)
- `id_languages`: int (PK, FK to Language)
- `id_genders`: int (PK, FK to Gender)
- `content`: text
- `meta`: string (nullable)

### SectionsHierarchy
- `parent`: int (PK, FK to Section)
- `child`: int (PK, FK to Section)
- `position`: int (nullable)

### SectionsNavigation
- `parent`: int (PK, FK to Section)
- `child`: int (PK, FK to Section)
- `id_pages`: int (FK to Page)
- `position`: int

### Style
- `id`: int (PK)
- `name`: string
- `id_type`: int (FK to Lookup with type_code = 'styleType')
- `id_group`: int (FK to StyleGroup)
- `description`: text (nullable)

### StyleGroup
- `id`: int (PK)
- `name`: string
- `description`: text (nullable)
- `position`: int (nullable)

### StylesField
- `id_styles`: int (PK, FK to Style)
- `id_fields`: int (PK, FK to Field)
- `default_value`: string (nullable)
- `help`: text (nullable)
- `disabled`: bool
- `hidden`: int (nullable)

### Task
- `id`: int (PK)
- `config`: text (nullable)

### Transaction
- `id`: int (PK)
- `transaction_time`: datetime
- `id_transactionTypes`: int (nullable, FK to Lookup)
- `id_transactionBy`: int (nullable, FK to Lookup)
- `id_users`: int (nullable, FK to User)
- `table_name`: string (nullable)
- `id_table_name`: int (nullable)
- `transaction_log`: text (nullable)

### User
- `id`: int (PK)
- `email`: string
- `name`: string (nullable)
- `password`: string (nullable)
- `id_genders`: int (nullable, FK to Gender)
- `blocked`: bool
- `id_status`: int (nullable, FK to UserStatus)
- `intern`: bool
- `token`: string (nullable)
- `id_languages`: int (nullable, FK to Language)
- `is_reminded`: bool
- `last_login`: date (nullable)
- `last_url`: string (nullable)
- `device_id`: string (nullable)
- `device_token`: string (nullable)
- `security_questions`: string (nullable)
- `user_name`: string (nullable)
- `id_userTypes`: int (FK to Lookup)

### UserActivity
- `id`: int (PK)
- `id_users`: int (FK to User)
- `url`: string
- `timestamp`: datetime
- `id_type`: int (FK to ActivityType)
- `exec_time`: decimal (nullable)
- `keyword`: string (nullable)
- `params`: string (nullable)
- `mobile`: bool (nullable)

### Users2faCode
- `id`: int (PK)
- `id_users`: int (FK to User)
- `code`: string
- `created_at`: datetime
- `expires_at`: datetime
- `is_used`: bool

### UsersGroup
- `id_users`: int (PK, FK to User)
- `id_groups`: int (PK, FK to Group)

### UserStatus
- `id`: int (PK)
- `name`: string
- `description`: string

### ValidationCode
- `code`: string (PK)
- `id_users`: int (nullable, FK to User)
- `created`: datetime
- `consumed`: datetime (nullable)

### Version
- `id`: int (PK)
- `version`: string (nullable)

### PageType
- `id`: int (PK)
- `name`: string (unique)

### Chat
- `id`: int (PK)
- `id_snd`: int
- `id_rcv`: int (nullable)
- `content`: text
- `timestamp`: datetime
- `id_rcv_group`: int

### ChatRecipiant
- `id_users`: int (PK)
- `id_chat`: int (PK)
- `id_room_users`: int (nullable)
- `is_new`: bool (default 1)

### CmsPreference
- `id`: int (PK)
- `callback_api_key`: string (nullable, length 500)
- `default_language_id`: int (nullable)
- `anonymous_users`: int (default 0)
- `firebase_config`: string (nullable, length 10000)

### CodesGroup
- `code`: string (PK, length 16)
- `id_groups`: int (PK)

### DataCell
- `id_dataRows`: int (PK)
- `id_dataCols`: int (PK)
- `value`: text

### DataCol
- `id`: int (PK)
- `name`: string (nullable, length 255)
- `id_dataTables`: int (nullable)

### DataRow
- `id`: int (PK)
- `id_dataTables`: int (nullable)
- `timestamp`: datetime
- `id_users`: int (nullable)
- `id_actionTriggerTypes`: int (nullable)

### DataTable
- `id`: int (PK)
- `name`: string (length 100)
- `timestamp`: datetime
- `displayName`: string (nullable, length 1000)

### Field
- `id`: int (PK)
- `name`: string (length 100)
- `id_type`: int
- `display`: bool (default 1)

### FieldType
- `id`: int (PK)
- `name`: string (length 100)
- `position`: int

### FormAction
- `id`: int (PK)
- `name`: string (length 200)
- `id_formProjectActionTriggerTypes`: int
- `config`: text (nullable)
- `id_dataTables`: int (nullable)

### Gender
- `id`: int (PK)
- `name`: string (length 20)

### Group
- `id`: int (PK)
- `name`: string (length 100)
- `description`: string (length 250)
- `id_group_types`: int (nullable)
- `requires_2fa`: bool (default 0)

### Hook
- `id`: int (PK)
- `id_hookTypes`: int
- `name`: string (nullable, length 100)
- `description`: string (nullable, length 1000)
- `class`: string (length 100)
- `function`: string (length 100)
- `exec_class`: string (length 100)
- `exec_function`: string (length 100)
- `priority`: int (default 10)

### Language
- `id`: int (PK)
- `locale`: string (length 5)
- `language`: string (length 100)
- `csv_separator`: string (length 1, default ',')

### Library
- `id`: int (PK)
- `name`: string (nullable, length 250)
- `version`: string (nullable, length 500)
- `license`: string (nullable, length 1000)
- `comments`: string (nullable, length 1000)

### MailAttachment
- `id`: int (PK)
- `id_mailQueue`: int
- `attachment_name`: string (nullable, length 1000)
- `attachment_path`: string (length 1000)
- `attachment_url`: string (length 1000)
- `template_path`: string (length 1000, default '')

### MailQueue
- `id`: int (PK)
- `from_email`: string (length 100)
- `from_name`: string (length 100)
- `reply_to`: string (length 100)
- `recipient_emails`: text
- `cc_emails`: string (nullable, length 1000)
- `bcc_emails`: string (nullable, length 1000)
- `subject`: string (length 1000)
- `body`: text
- `is_html`: bool (default 1)

### Notification
- `id`: int (PK)
- `subject`: string (length 1000)
- `body`: text
- `url`: string (nullable, length 100)

### Plugin
- `id`: int (PK)
- `name`: string (nullable, length 100)
- `version`: string (nullable, length 500)

### QualtricsProject
- `id`: int (PK)
- `name`: string (length 200)
- `description`: string (nullable, length 1000)
- `qualtrics_api`: string (nullable, length 100)
- `api_library_id`: string (nullable, length 100)
- `api_mailing_group_id`: string (nullable, length 100)
- `created_on`: datetime
- `edited_on`: datetime

### QualtricsSurvey
- `id`: int (PK)
- `name`: string (length 200)
- `description`: string (nullable, length 1000)
- `qualtrics_survey_id`: string (nullable, length 100)
- `id_qualtricsSurveyTypes`: int
- `participant_variable`: string (nullable, length 100)
- `group_variable`: int (default 0)
- `created_on`: datetime
- `edited_on`: datetime
- `config`: text (nullable)

### QualtricsSurveysResponse
- `id`: int (PK)
- `id_users`: int
- `id_surveys`: int
- `id_qualtricsProjectActionTriggerTypes`: int
- `survey_response_id`: string (nullable, length 100)
- `started_on`: datetime
- `edited_on`: datetime

### ScheduledJob
- `id`: int (PK)
- `id_jobTypes`: int
- `id_jobStatus`: int
- `description`: string (nullable, length 1000)
- `date_create`: datetime
- `date_to_be_executed`: datetime (nullable)
- `date_executed`: datetime (nullable)
- `config`: string (nullable, length 1000)

### ScheduledJobsFormAction
- `id_scheduledJobs`: int (PK)
- `id_formActions`: int (PK)
- `id_dataRows`: int (nullable)

### ScheduledJobsMailQueue
- `id_scheduledJobs`: int (PK)
- `id_mailQueue`: int (PK)

### ScheduledJobsNotification
- `id_scheduledJobs`: int (PK)
- `id_notifications`: int (PK)

### ScheduledJobsQualtricsAction
- `id_scheduledJobs`: int (PK)
- `id_qualtricsActions`: int (PK)

### RefreshToken
- `id`: bigint (PK)
- `user`: FK to User
- `token_hash`: string
- `expires_at`: datetime
- `created_at`: datetime

### Section
- `id`: int (PK)
- `style`: FK to Style
- `name`: string (unique)

### Style
- `id`: int (PK)
- `name`: string (unique)
- `type`: FK to StyleType
- `group`: FK to StyleGroup
- `description`: text (nullable)

### StyleGroup
- `id`: int (PK)
- `name`: string (unique)
- `description`: text (nullable)
- `position`: int (nullable)

### StyleType
- `id`: int (PK)
- `name`: string

### User
- `id`: int (PK)
- `email`: string (unique)
- `name`: string (nullable)
- `password`: string (nullable)
- `id_genders`: int (nullable)
- `id_languages`: int (nullable)
- `id_status`: int (nullable)
- `blocked`: bool
- `intern`: bool
- `last_login`: datetime (nullable)
- `last_url`: string (nullable)
- `user_name`: string (nullable)
- `is_reminded`: bool
- `token`: string (nullable)
- `twoFactorRequired`: bool
- `id_userTypes`: int (nullable)


### Canonical ACL Source: get_user_acl Stored Procedure

### Global User Context Service
- Use `App\Service\UserContextService` to get the current authenticated user entity anywhere in Symfony.
- Call `$this->userContext->getCurrentUser()` to get the `App\Entity\User|null`.
- This avoids duplicating user-casting logic and is the recommended pattern for all services and controllers.
- Example:
  ```php
  public function __construct(UserContextService $userContext) { ... }
  $user = $this->userContext->getCurrentUser();
  ```

- All access checks in Symfony now use the stored procedure `get_user_acl(user_id, page_id)`.
- The procedure returns columns: acl_select, acl_insert, acl_update, acl_delete, etc.
- The access type (`select`, `insert`, `update`, `delete`) is mapped to the corresponding column.
- The check is: if the column value is `1`, access is granted; otherwise, denied.
- This matches the core logic from the legacy PHP implementation and is now the canonical approach for ACL in this project.
- Example:
  ```php
  // In ACLService
  $sql = 'CALL get_user_acl(:userId, :pageId)';
  $stmt = $connection->prepare($sql);
  $result = $stmt->executeQuery(['userId' => $userId, 'pageId' => $pageId])->fetchAssociative();
  $hasAccess = ((int)$result['acl_select'] === 1); // for 'select' access
  ```
- This is documented in project memory for all contributors.

## Dynamic API Route Management

All API routes are dynamically loaded from the `api_routes` database table. Developers manage routes by inserting/updating records in this table. There is no need to use YAML, PHP, fixtures, or import commands for route registration. The only supported method is direct SQL/database insertion.

### API Versioning

The `api_routes` table now includes a `version` column (e.g., `v1`, `v2`). This allows you to maintain and serve multiple API versions in parallel. Each route record must specify the version it belongs to.

**Example table structure:**
```sql
CREATE TABLE `api_routes` (
  `id`           INT             NOT NULL AUTO_INCREMENT,
  `route_name`   VARCHAR(100)    NOT NULL,
  `version`      VARCHAR(10)     NOT NULL DEFAULT 'v1',
  `path`         VARCHAR(255)    NOT NULL,
  `controller`   VARCHAR(255)    NOT NULL,
  `methods`      VARCHAR(50)     NOT NULL,
  `requirements` JSON            NULL,
  `params`       JSON            NULL COMMENT 'Expected parameters: name → {in: body|query, required: bool}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_route_name_version` (`route_name`, `version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Example insert:**
```sql
INSERT INTO `api_routes` (`route_name`,`version`,`path`,...) VALUES
('auth_login','v1','/auth/login',...),
('auth_login','v2','/auth/login',...);
```

### How to Add a New Version (v2+)
1. **Database**: Insert new routes into `api_routes` with `version = 'v2'` and update `controller`/`path` as needed.
2. **Controllers**: Place v2 controllers in `src/Controller/Api/V2/` (and `Admin/` subfolder for admin routes).
3. **Routing Config**: In `config/routes/selfhelp_api.yaml`, duplicate the v1 blocks, rename to v2, and update prefixes/resources:
    ```yaml
    # ── V2 API ROUTES ──
    selfhelp_api_v2:
        resource: 'api_v2.yaml'
        prefix:   '/cms-api/v2'
    selfhelp_api_v2_controllers:
        resource: ../../src/Controller/Api/V2/
        type: attribute
        prefix: '/cms-api/v2'
    selfhelp_admin_v2_controllers:
        resource: ../../src/Controller/Api/V2/Admin/
        type: attribute
        prefix: '/cms-api/v2/admin'
    ```
4. **Loader**: The loader/repository (`ApiRouteRepository`) should filter by version (e.g., `findAllRoutesByVersion('v2')`).

### Entities

### DataTable, DataRow, DataCol, DataCell Refactor (2024-xx-xx)

#### Summary
The entity relationships for tabular data have been refactored to follow Doctrine ORM and Symfony best practices, as per ENTITY RULE and MEMORY_RULE. This enables robust, maintainable, and navigable associations for all tabular data in the system.

#### Relationships
- **DataTable**
  - `OneToMany` to `DataRow` (dataRows)
  - `OneToMany` to `DataCol` (dataCols)
- **DataRow**
  - `ManyToOne` to `DataTable` (dataTable)
  - `OneToMany` to `DataCell` (dataCells)
- **DataCol**
  - `ManyToOne` to `DataTable` (dataTable)
  - `OneToMany` to `DataCell` (dataCells)
- **DataCell**
  - Composite PK: (`dataRow`, `dataCol`)
  - `ManyToOne` to `DataRow` (dataRow)
  - `ManyToOne` to `DataCol` (dataCol)

#### Implementation Notes
- All integer FK fields were replaced by proper Doctrine ORM relations using PHP 8 attributes.
- Navigation methods (getter/setter/add/remove) were implemented for all entity relationships.
- DataCell now uses a composite primary key of (`dataRow`, `dataCol`) and only one property per relation.
- All changes follow ENTITY RULE and have been tested for bidirectional navigation.

#### Manual Migration Required
After these changes, you must manually run doctrine:migrations:diff and doctrine:migrations:migrate to update the database schema.

### Entity and Loader
- The `ApiRoute` entity has a `version` property.
- The loader queries only the routes for the requested version.

### Best Practices
- Always specify the correct version when adding or updating routes.
- Document route changes and new versions for your team.

## How to Add a Route

All API routes are now dynamically loaded from the `api_routes` table. To add or update a route, insert or update a record in the table. The table now supports POST routes and parameter definitions via the `params` JSON column.

### 2. Manually Inserting into the Database

You can insert routes directly using SQL. Example:

```sql
INSERT INTO api_routes (name, path, controller, methods, requirements, version)
VALUES (
    'admin_page_sections',
    '/admin/pages/{page_keyword}/sections',
    'App\\Controller\\AdminController::getPageSections',
    'GET',
    '{"page_keyword": "[A-Za-z0-9_-]+"}'
);
```

- Double backslashes (`\\`) are required in the `controller` field for PHP namespace escaping.
- The `requirements` field is a JSON object (or NULL if not needed).

## Example Route Entry

| route_name          | path                                 | controller                                         | methods | requirements                              | params                                                                                  |
|---------------------|--------------------------------------|----------------------------------------------------|---------|--------------------------------------------|-----------------------------------------------------------------------------------------|
| auth_login          | /auth/login                          | App\\Controller\\AuthController::POST_login         | POST    | NULL                                       | {"user": {"in": "body", "required": true}, "password": {"in": "body", "required": true}} |
| content_page        | /pages/{page_keyword}                | App\\Controller\\ContentController::getPage        | GET     | {"page_keyword": "[A-Za-z0-9_-]+"}       | NULL                                                                                    |

## Parameter Requirements and POST Parameters

- The `requirements` field is a JSON object where keys are parameter names and values are regex patterns for path variables.
- The `params` field is a JSON object describing expected parameters for POST/PUT requests. For each parameter:
    - `in`: Where to expect the parameter (`body` or `query`).
    - `required`: Whether the parameter is required (`true`/`false`).
- Example for a POST route:
    ```json
    {
      "user": {"in": "body", "required": true},
      "password": {"in": "body", "required": true}
    }
    ```
- If not needed, set to NULL or leave blank.

## How Dynamic Routes Work

1. On cache warmup or server start, the `ApiRouteLoader` loads all entries from `api_routes` and registers them as Symfony routes.
2. Requests matching these paths are dispatched to the specified controller and method.
3. If you update the database, clear the Symfony cache to reload the routes:
   ```sh
   php bin/console cache:clear
   ```

## Best Practices
- Always use unique route names.
- Use double backslashes in the controller field for namespaces.
- Validate your regexes in the `requirements` field.
- After adding or updating routes, clear the cache.
- Use fixtures for version-controlled, repeatable route setups.
- Use the `debug:router` command to verify loaded routes:
  ```sh
  php bin/console debug:router | findstr cms-api
  ```

## Troubleshooting
- If a route does not appear, ensure it is in the database and the cache is cleared.
- If you get duplicate routes, check for static YAML/PHP definitions and remove them.
- Use the `app:import-api-routes` command for bulk updates or imports.

---

For more advanced usage, see `src/Entity/ApiRoute.php`
