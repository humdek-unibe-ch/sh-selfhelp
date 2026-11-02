# SelfHelp core reference (legacy vanilla-PHP SelfHelp)

This repo is the **old** SelfHelp: vanilla PHP 8.2+, MySQL 8, Bootstrap 4.6, jQuery, APCu. No framework,
no namespaces, no Symfony. Ignore patterns from the Symfony rewrite (repositories, `ReworkedCacheService`,
PSR-4 namespaces, PHPUnit suites) — they don't exist here.

## Request flow
`index.php` (security headers) → `Selfhelp.php` (`loadPluginGlobals()` requires every
`server/plugins/*/server/service/globals.php`, then creates `Services`) → router (AltoRouter) →
page type (`SectionPage`, `ComponentPage`, `AjaxRequest`, `CallbackRequest`) → style components.

Services: `$services->get_db()` (PageDb), `get_router()`, `get_acl()`, `get_user_input()`,
`get_job_scheduler()`, `get_transaction()`, `get_condition()`, `get_clockwork()`.

## Styles (CMS components)
- `styles.id_type` is `view`, `component` or `navigation`. A `view` style is only `{Name}View.php`
  (core wires `StyleModel` + `SimpleStyleComponent`); a `component` style has `{Name}Component.php`.
- Folder = style name in camelCase; the autoloader in `server/component/BaseComponent.php` derives the
  folder from the class name and also searches every plugin's `server/component/`. A mismatch renders
  `unknownStyle` silently.
- Views extend `StyleView`, read config with `$this->model->get_db_field('name', default)`, render via
  `tpl_*.php` templates next to the view (some plugins use a `tpl/` subfolder).
- To create a style, use the `selfhelp-new-style` skill.

## Data
- Form/user data: `dataTables` → `dataCols` → `dataRows` → `dataCells`, accessed through
  `UserInput::save_data()` / `get_data()`. A form's table name is `sprintf('%010d', section_id)`.
- `formUserInputRecord` (`is_log=0`, one row per user, updated) and `formUserInputLog` (`is_log=1`,
  append-only) share the `FormUserInput*` model/view/controller.
- Enums live in `lookups` (`type_code`, `lookup_code`, `lookup_value`, `lookup_description`);
  read with `$db->get_lookup_id_by_value()` / `get_lookup_id_by_code()`. Constants for codes are in
  `server/service/globals.php`.
- Field interpolation: `PageDb::replace_calced_values($text, $values)` replaces `{{field}}`.

## Frontend
- Core style assets: `server/component/style/<style>/css/*.css` and `js/*.js`, bundled by gulp
  (`cd gulp && gulp`) into `css/ext/styles.min.css` and `js/ext/styles.min.js`. Never hand-edit the bundles.
- AJAX forms: hidden `ajax=1` input; `styles.min.js` intercepts submit. Confirmation dialogs use
  jquery-confirm via a `data-confirmation` JSON attribute on the `<form>`.
- Every style root gets `css`/`css_mobile` classes plus `style-section-{id}` and `selfHelp-locale-{locale}`.

## Key paths
| What | Where |
|---|---|
| Styles | `server/component/style/` |
| Base classes | `server/component/Base*.php`, `server/component/style/Style*.php` |
| Services | `server/service/` |
| Core migrations | `server/db/update_scripts/` |
| DB procedures/functions/views | `server/db/FUN_PRO_VIEWS/` |
| Plugins (each its own git repo) | `server/plugins/sh-shp-*` |

## Git
Don't run `git commit` or `git push` unless the user asks for it (e.g. via `/commit`). Plugins are
separate nested repos ignored by the root repo — run `git status` inside the plugin you changed.
