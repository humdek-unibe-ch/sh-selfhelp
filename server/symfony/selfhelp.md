# Adding API Routes to the Database

This guide explains how to add API routes to the database for dynamic loading in the SH-Selfhelp Symfony backend. This approach allows you to manage routes without editing YAML or PHP files, enabling runtime updates and flexible route management.

## Overview

Dynamic API routes are stored in the `api_routes` database table and loaded by the custom loader (`ApiRouteLoader`). Each route entry defines the HTTP path, controller, method(s), and optional requirements (e.g., parameter regexes). The entity representing a route is `App\Entity\ApiRoute`.

**Key columns:**
- `name`: The unique route name (e.g., `content_page`)
- `path`: The URL path (e.g., `/pages/{page_keyword}`)
- `controller`: The controller and method to handle the route (e.g., `App\\Controller\\ContentController::getPage`)
- `methods`: HTTP methods as a comma-separated string (e.g., `GET`, `POST`)
- `requirements`: (Optional) JSON string for parameter requirements (e.g., `{ "page_keyword": "[A-Za-z0-9_-]+" }`)

## How to Add a Route

### 1. Using Doctrine Fixtures (Recommended for Dev/Initial Setup)

Edit `src/DataFixtures/ApiRouteFixtures.php` and add your route to the `$routesData` array:

```php
// Example entry in ApiRouteFixtures.php
[
    'name' => 'content_page',
    'path' => '/pages/{page_keyword}',
    'controller' => 'App\\Controller\\ContentController::getPage',
    'methods' => 'GET',
    'requirements' => '{"page_keyword": "[A-Za-z0-9_-]+"}'
],
```

**Steps:**
1. Add your route entry to `$routesData` in `ApiRouteFixtures.php`.
2. Run:
   ```sh
   php bin/console doctrine:fixtures:load
   ```
   This will populate the `api_routes` table with your new route.

### 2. Manually Inserting into the Database

You can insert routes directly using SQL. Example:

```sql
INSERT INTO api_routes (name, path, controller, methods, requirements)
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

### 3. Using the Import Command

If you have a CSV or external source of routes, you can write a script or use the included command:

```sh
php bin/console app:import-api-routes
```

This command (`ImportApiRoutesCommand`) will read from the `api_routes` table and import (update) the Symfony route collection. You can customize this command for bulk imports.

## Example Route Entry

| name                | path                                 | controller                                         | methods | requirements                              |
|---------------------|--------------------------------------|----------------------------------------------------|---------|--------------------------------------------|
| content_page        | /pages/{page_keyword}                | App\\Controller\\ContentController::getPage        | GET     | {"page_keyword": "[A-Za-z0-9_-]+"}       |
| admin_page_sections | /admin/pages/{page_keyword}/sections | App\\Controller\\AdminController::getPageSections | GET     | {"page_keyword": "[A-Za-z0-9_-]+"}       |

## Parameter Requirements

- The `requirements` field is a JSON object where keys are parameter names and values are regex patterns.
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

For more advanced usage, see `src/Entity/ApiRoute.php`, `src/Routing/ApiRouteLoader.php`, and `src/Command/ImportApiRoutesCommand.php`.
