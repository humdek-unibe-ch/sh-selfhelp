# AGENTS.md

Print at start of chat "PLUGINS RULES" so that I know they are loaded

## Scope

This file applies to plugins under `server/plugins`. Most plugin folders are separate nested Git repositories and are ignored by the parent repo, so check `git status` inside the plugin you are changing, not only at the root.

## Plugin Shape

Use the existing plugin layout unless there is a strong reason not to:

- `server/service/globals.php` for constants and small global helper functions.
- `server/component` for hooks, models, module components, and shared plugin PHP classes.
- `server/component/style/<styleName>` for database-registered styles.
- `server/ajax` and `server/callback` only when the plugin exposes those entry points.
- `server/db/vX.Y.Z.sql` for plugin migrations and `server/db/FUN_PRO_VIEWS` for plugin views/procedures.
- `css`, `js`, `schemas`, `docs`, `gulp`, and `server/config` only when the plugin actually needs them.

## Loading And Paths

- Plugin globals are loaded automatically by `Selfhelp::loadPluginGlobals()` from the actual folder name under `server/plugins`.
- Do not echo or perform database work from `globals.php`; define constants and lightweight helper functions only.
- Build paths from `__DIR__` or the real plugin folder. Avoid hard-coded sibling folder names, because a mismatch between plugin name and directory breaks includes after globals have loaded.
- If a plugin uses a user-facing function code that differs from the folder name, keep that as a business constant, not as a filesystem path.

## Database Contract

- Every plugin must register itself in the `plugins` table and keep its version in sync with migrations.
- Migrations must create all fields, styles, lookups, pages, ACL, hooks, tables, and views required by the code. Code must not reference a field name that no plugin migration installs.
- Keep SQL idempotent. Use `INSERT IGNORE`, `CREATE TABLE IF NOT EXISTS`, guarded `UPDATE`s, and helper functions/procedures where available.
- Prefer namespaced table/style/page names that make ownership clear, for example `surveyJS`, `bmzFeedback`, or `bmz_evaluate_motive_<code>`.
- Document required post-migration cache clearing when hooks, styles, pages, or data-table names are added.

## Hooks And Core Extension

- Hook classes extend `BaseHooks` and are registered in SQL.
- For overwrite hooks, always pass through to `$this->execute_private_method($args)` when the hook does not own the current job/type/case.
- Keep hook conditions very specific. Shared core methods such as `UserInput::get_job_type`, `UserInput::get_task_config`, `Task::execute_task`, and `BasePage::getCspRules` affect the whole app.
- Use form actions and scheduled jobs for survey-completion side effects when they need retry/logging/scheduling semantics. Inline completion helpers should be reserved for small synchronous work.

## Components And Styles

- Component class names must match the autoloader convention. A style named `myStyle` should have `MyStyleComponent`, `MyStyleView`, and optionally a model/controller under `server/component/style/myStyle`.
- Component constructors should follow the core pattern: create model, controller if needed, view, then call `parent::__construct($model, $view, $controller)`.
- Styles must be registered through SQL in `styles` and `styles_fields`; views should read configuration with `$model->get_db_field()`.
- Return valid mobile output from `output_content_mobile()` when the style is usable in mobile contexts.

## Survey And Calculation Plugins

- SurveyJS submissions should save through `UserInput::save_data()`, using stable `response_id` updates and the core trigger types.
- Calculations that create derived result tables should also save through `UserInput::save_data()` unless there is a documented reason to bypass the data-table model.
- Keep external-survey migrations separate from calculation plugins. A calculation plugin may depend on SurveyJS being installed, but it should not depend on Qualtrics code unless explicitly documented.
- Preserve participant ownership and lookup semantics. When scheduled jobs run outside a participant session, pass the intended user id and set `own_entries_only` intentionally.

## Tests

Plugin tests are standalone scripts (`php tests/XTest.php`): no bootstrap, no MySQL, no APCu. They must still run the **real** core and plugin code they depend on; a copy in a fake keeps passing when the real code changes (a copied `PageDb::replace_calced_values()` kept the old escaping, a copied registry snapshot hid that versions are compared by hash and config).

- Never copy production logic (core or plugin) into a fake, stub or stand-in. Logic is anything that decides: escaping, interpolation, validation, normalizing, hashing, building descriptors or snapshots, getters over state.
- Load a real core class and bypass only its constructor; stub only the I/O it reaches:
  ```php
  ob_start(); // core files end with stray newlines after their closing tags
  require_once dirname(__DIR__, 4) . '/server/service/PageDb.php';
  ob_end_clean();

  class XCoreDb extends PageDb
  {
      public function __construct() {}                       // no MySQL, no APCu
      public function fetch_user_name() { return 'tester'; } // only the I/O it reaches
  }
  ```
  Define the core constants the code reads with `if (!defined(...)) define(...)`.
- A fake model that extends a core model sets the real properties (e.g. `$this->entry_record`) instead of overriding the core getters that read them.
- To record calls or inject failures, subclass the real class and call `parent::` (e.g. `StubRegistry extends LlmPromptRegistryService` in `LlmPromptHubSaveServiceTest`). For a file-sandbox test, the sandbox file `require_once`s the real file from its real location (`LlmFormControllerTest`).
- Seed rows the way the real code writes them. When a column is derived (hash, config snapshot), let the real code produce it once (e.g. `stampActiveVersions()` in `LlmPromptHubSaveServiceTest`) instead of computing it in the test.
- Only boundaries may be faked: database I/O and the MySQL dialect (`query_db*`, `insert`, `update_by_ids`, `select_by_uid`, `get_lookup_id_by_code`, an in-memory SQLite `pdo`), transactions, cache, `Services` getters, ACL, router, job scheduler, the transaction log, HTTP/LLM providers, `exit`/output, time and randomness. A fake records calls and returns data; it never re-implements what the real method decides.
- When an assertion only held for a stand-in, assert on what the real code does instead (e.g. the model's `save_user_input()` was called), not on the stand-in having run.
- After changing a core or plugin method, search the plugin tests for fakes with the same name (`grep -rn "function <name>(" server/plugins/*/tests`) and make sure they delegate, not copy.
- Examples: `LlmFormUrlParamsTest`, `LlmPromptVersionLinkerTest`, `LlmFormInterviewServiceTest`, `LlmFormControllerTest`, `LlmPromptHubSaveServiceTest`.

## Review Checklist

- The folder name, plugin registration name, constants, include paths, docs, and table prefixes are consistent.
- All fields used by PHP are installed by SQL migrations.
- Hook methods chain for unrelated cases and log failures where scheduled work can fail.
- Dynamic page params and table names are sanitized before use.
- Stored HTML is either escaped or intentionally trusted.
- Cache effects are handled or documented.
- The plugin can be installed into a fresh project from its migrations without relying on manual DB edits.

