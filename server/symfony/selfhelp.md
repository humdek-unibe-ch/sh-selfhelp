# Adding API Routes to the Database

This guide explains how to add API routes to the database for dynamic loading in the SH-Selfhelp Symfony backend. **All routes are now dynamically loaded from the database.** You do not need to edit YAML, PHP, or use fixtures/commands for route registration. To add or modify an API route, simply insert or update the relevant entry in the `api_routes` table.

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

### Action
- `id`: int (PK)
- `name`: string (unique)

### ApiRoute
- `id`: int (PK)
- `route_name`: string (unique)
- `path`: string
- `controller`: string
- `methods`: string
- `requirements`: json/array (nullable)
- `params`: json/array (nullable)
- `version`: string (default 'v1')

### Lookup
- `id`: int (PK)
- `name`: string (unique)
- `lookup_code`: string
- `lookup_value`: string
- `lookup_type`: string

### Page
- `id`: int (PK)
- `name`: string (unique)
- `keyword`: string (unique)
- `id_type`: int (FK to PageType)
- `id_navigation_section`: int (nullable)
- `parent`: int (nullable)
- `is_headless`: bool
- `nav_position`: int
- `footer_position`: int
- `is_system`: bool

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

### Entity and Loader
- The `ApiRoute` entity has a `version` property.
- The loader queries only the routes for the requested version.

### Best Practices
- Always specify the correct version when adding or updating routes.
- Keep controller code organized by version for clarity and maintainability.
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
