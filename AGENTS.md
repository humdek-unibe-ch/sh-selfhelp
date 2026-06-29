# AGENTS.md

## Project Shape

SelfHelp is a vanilla PHP application with no framework. Requests enter through `index.php`, bootstrap in `Selfhelp.php`, create `Services`, then route to a page, component, AJAX request, callback, or backend action.

The database is part of the application contract. Pages, routes, styles, fields, hooks, data tables, lookups, ACL, and plugin versions are database-driven. Code changes that introduce behavior usually need a matching SQL migration.

## Core Runtime

- `Selfhelp.php` loads plugin globals before creating `Services`.
- `Services` is the service container. Use `$services->get_db()`, `get_user_input()`, `get_router()`, `get_transaction()`, `get_job_scheduler()`, and related getters instead of creating duplicate service objects.
- Core pages live under `server/page`; AJAX handlers under `server/ajax`; callbacks under `server/callback`.
- Components follow the existing MVC pattern: `*Component` wires a model, view, and optional controller; models own data and service access; views render web/mobile output; controllers handle POST actions.
- Style components are database-backed. `StyleModel` loads fields, children, conditions, data config, dynamic values, and entry records.
- Client assets are plain JS/CSS and are included by component/view `get_js_includes()` and `get_css_includes()` methods.

## Data And Forms

- Use `UserInput::save_data()` and `UserInput::get_data()` for the core `dataTables`/`dataRows`/`dataCols`/`dataCells` storage model.
- Survey/form writes should carry `trigger_type` values from the core constants: `started`, `updated`, `finished`, and `deleted`.
- Use `updateBasedOn` when a logical response should update an existing row, commonly by `response_id` or a stable code.
- Do not hand-roll writes into the data table structure unless the core helper cannot support the use case.
- Respect `own_entries_only` carefully. Scheduled jobs and system callbacks often need `false` because they run outside the participant's session.

## Hooks

- Hook classes extend `BaseHooks`.
- Register hooks through SQL in the `hooks` table.
- For `hook_overwrite_return`, the current code supports chained overwrite hooks. Plugin hook methods must call `$this->execute_private_method($args)` whenever they do not fully handle the current case, so other hooks and/or the original method still run.
- Keep hook methods narrow and defensive. Hooks run during bootstrap/runtime and can break unrelated pages if they assume a route, session value, or class is always present.
- Avoid modifying core files for extension behavior when a hook or component registration can do the job.

## Database Migrations

- Root migrations live in `server/db/update_scripts`; plugin migrations live inside each plugin under `server/db`.
- Prefer idempotent SQL: `INSERT IGNORE`, `CREATE TABLE IF NOT EXISTS`, helper procedures such as `add_table_column`, and guarded updates.
- Register new styles by creating fields first, then linking them through `styles_fields`.
- Prefer helper functions like `get_style_id()`, `get_field_id()`, and `get_field_type_id()` over hard-coded ids, except for documented static core ids.
- Update the plugin or app version in the same migration that introduces schema/config changes.
- After migrations that affect pages, styles, fields, hooks, or data-table names, clear the relevant cache or document that CMS cache clearing is required.

## Security And Output

- Keep `server/service/globals_untracked.php`, credentials, local config, generated data, uploads, and built vendor artifacts out of commits unless the repo already tracks that exact type.
- Use parameterized DB calls for dynamic values. Avoid concatenating user input into SQL filters; if a legacy helper requires a filter string, sanitize and constrain values first.
- Escape user-visible plain text with `htmlspecialchars`. Only output stored HTML when the field is intentionally trusted CMS/authored content.
- For file paths and uploads, strip control characters, reject traversal, enforce expected extensions, and verify resolved paths stay under the intended upload root.

## Style And Maintenance

- Follow the existing PHP style: explicit `require_once`, procedural constants in globals, simple arrays, and no namespaces unless a dependency already uses them.
- Keep changes scoped. Do not introduce framework-style abstractions that bypass the database-driven component model.
- Add comments only when they explain non-obvious behavior, hook chaining, cache invalidation, path constraints, or migration assumptions.
- Run `php -l` on changed PHP files when possible. For frontend/plugin asset changes, also verify the page renders and browser console is clean.

