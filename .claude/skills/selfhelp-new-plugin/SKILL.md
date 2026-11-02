---
name: selfhelp-new-plugin
description: Scaffold a new SelfHelp plugin (server/plugins/sh-shp-<name>) for the legacy vanilla-PHP SelfHelp, or add a hook to an existing plugin — folder layout, globals.php, the hooks class, the v1.0.0.sql that registers the plugin, README/CHANGELOG and optional gulp/assets. Use whenever the user wants to create, start, set up or bootstrap a SelfHelp plugin or extension, move a feature out of core into a plugin, or extend/override core behaviour from a plugin via a hook (uopz hook_overwrite_return / hook_on_function_execute).
---

# Creating a SelfHelp plugin

A plugin is a folder `server/plugins/sh-shp-<name>/` that is **its own git repository** (the root repo
ignores `server/plugins/*`). Core discovers it in three ways, and each needs its piece:
- `Selfhelp::loadPluginGlobals()` requires `server/service/globals.php` → constants.
- The autoloader in `server/component/BaseComponent.php` searches `<plugin>/server/component/` and
  `<plugin>/server/component/style/<style>/` → hook classes, styles.
- The database (`plugins`, `hooks`, `styles`, `pages`, `lookups` rows) → everything else. No row, no behaviour.

Read `server/plugins/AGENTS.md` and `server/plugins/CLAUDE.md` first — they hold the plugin contract
and the hook-chaining rule.

## 1. Gather
- **Name**: folder `sh-shp-<name>` (lowercase, `_` allowed, e.g. `sh-shp-formula_parser`); `plugins.name`
  = `<name>`; class prefix = PascalCase (`FormulaParserHooks`). Keep them consistent — AGENTS.md's review
  checklist checks exactly this.
- **What it plugs into**: new styles? hooks into core methods? pages (admin/module page)? AJAX/callback
  endpoints? scheduled jobs/tasks? Only create the folders it needs.
- **Constant prefix** for globals (e.g. `FP_` for formula_parser) to avoid collisions.

## 2. Files

```
sh-shp-<name>/
├── README.md               # what it does, requirements, install (run migrations in order, clear cache)
├── CHANGELOG.md            # "# v1.0.0" + bullet list
├── server/
│   ├── service/globals.php # constants + tiny helpers only — no DB work, no output
│   ├── component/
│   │   ├── <Name>Hooks.php         # only if it hooks core
│   │   └── style/<styleName>/      # only if it adds styles → selfhelp-new-style skill
│   └── db/v1.0.0.sql
├── css/ext/, js/ext/       # built assets, only if needed
└── gulp/                   # only if it has its own build
```
Every PHP file starts with the MPL-2.0 header used across the codebase.

**globals.php**
```php
<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
define('MYP_PLUGIN_NAME', 'myplugin');   // value of plugins.name
// paths: build from __DIR__, never from a hard-coded sibling folder name
define('MYP_PLUGIN_PATH', __DIR__ . '/../..');
?>
```

**Hooks class** — `server/component/<Name>Hooks.php`
```php
<?php
/* MPL header */
?>
<?php
require_once __DIR__ . "/../../../../component/BaseHooks.php";

/**
 * The class to define the hooks for the <name> plugin.
 */
class MyPluginHooks extends BaseHooks
{
    public function __construct($services, $params = array())
    {
        parent::__construct($services, $params);
    }

    /**
     * hook_overwrite_return on CmsView::create_field_form_item:
     * render the custom field type, pass everything else down the chain.
     */
    public function outputFieldMyThingEdit($args)
    {
        $field = $this->get_param_by_name($args, 'field');
        if (!$field || $field['name'] !== 'my_thing') {
            return $this->execute_private_method($args);   // not ours: keep the chain intact
        }
        // ... build and return the replacement (or call execute_private_method() first and
        //     extend its result, as LlmHooks::returnSelectLlmModelField does)
    }
}
?>
```
Keep the "is this ours?" test as narrow as possible — the same method may be hooked by several plugins,
and code like `UserInput::get_job_type`, `Task::execute_task`, `BasePage::getCspRules` affects the whole app.
`hook_on_function_execute` methods are for side effects; their return is ignored.

**v1.0.0.sql**
```sql
-- =====================================================
-- <Name> plugin v1.0.0 - initial install
-- Clear the SelfHelp cache after running.
-- =====================================================
START TRANSACTION;

INSERT IGNORE INTO plugins (`name`, version) VALUES ('myplugin', 'v1.0.0');

-- tables (CREATE TABLE IF NOT EXISTS, prefixed), lookups, fields, styles, pages + acl_groups, hooks:
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`)
VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return'), 'myplugin-field-my-thing-edit',
        'Output my-thing field - edit mode', 'CmsView', 'create_field_form_item', 'MyPluginHooks', 'outputFieldMyThingEdit');

COMMIT;

```
For the SQL building blocks (pages, ACL, lookups, tables, views) use the `selfhelp-db-migration` skill.
Everything the PHP references must be installed here — the plugin must install into a fresh project from its
migrations alone.

## 3. Assets
Plugin CSS/JS isn't part of the core gulp bundle. Ship built files in `css/ext/` and `js/ext/`, include them
from the view's `get_css_includes()/get_js_includes()` with a `?v=` cache-buster (see the
`selfhelp-new-style` skill), and add a `gulp/` build only when there are sources to compile.

## 4. Finish
1. `php -l` every PHP file.
2. Cross-check names: folder, `plugins.name`, constants prefix, class names, hook `exec_class`, include paths.
3. `git init` is the user's call — tell them the plugin is a separate repo and suggest creating it.
4. Tell the user: run `server/db/v1.0.0.sql`, clear the SelfHelp cache (hooks/styles are cached), and how to
   see the plugin working (page, style or hooked behaviour).
