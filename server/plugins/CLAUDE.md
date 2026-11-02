@AGENTS.md

# Plugin notes for Claude

This file is loaded for any plugin under `server/plugins/`, also when a plugin repo is opened on its
own (Claude reads CLAUDE.md files in parent folders). Path-scoped rules and skills from the root
`sh-selfhelp/.claude/` are **not** loaded then — for style, migration or plugin-scaffold work, open the
root `sh-selfhelp` folder so the `selfhelp-new-style`, `selfhelp-db-migration` and
`selfhelp-new-plugin` skills are available.

## Hooks — how they really work (server/service/Hooks.php, server/component/BaseHooks.php)
- Registered in SQL; `id_hookTypes` is the lookup `hook_on_function_execute` or `hook_overwrite_return`:
  ```sql
  INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`)
  VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return'), 'myplugin-field-x-edit',
          'Output field x - edit mode', 'CmsView', 'create_field_form_item', 'MyPluginHooks', 'outputFieldXEdit');
  ```
  `name` must be unique; prefix it with the plugin name.
- `hook_on_function_execute` (uopz_set_hook): runs alongside the target for side effects; return value ignored.
- `hook_overwrite_return` (uopz_set_return) **chains**: hooks run ordered by `priority`, the one with the
  highest priority value is the outermost. Each hook receives `$args` (`hookedClassInstance`, `methodName`,
  original parameters, chain context) and must call `$this->execute_private_method($args)` for every case
  it doesn't handle — that runs the next hook down, and finally the original method. Returning without it
  silently disables other plugins' hooks on the same method.
- Helpers in `BaseHooks`: `execute_private_method()`, `execute_parent_method()`, `get_private_property()`,
  `set_private_property()`, `get_param_by_name($args, 'name')`, `get_plugin_db_version()`.
- Hook classes: `server/component/<Plugin>Hooks.php`, `class XHooks extends BaseHooks`,
  `require_once __DIR__ . "/../../../../component/BaseHooks.php";` — the autoloader finds them there.
- Hooks are APCu-cached: after adding/changing hook rows, clear the SelfHelp cache.
