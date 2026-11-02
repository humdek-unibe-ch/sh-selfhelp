---
name: selfhelp-new-style
description: Create a new SelfHelp CMS style (UI component) in the legacy vanilla-PHP SelfHelp (sh-selfhelp) or in one of its plugins (server/plugins/sh-shp-*). Covers picking view vs component type, the PHP files and naming the autoloader requires, tpl/css/js placement, and the SQL migration that registers the style, its fields and styles_fields. Use this whenever the user wants to add, scaffold, build, port or register a new style, component, widget, block or CMS element in SelfHelp or a SelfHelp plugin, or add new fields to a style — even if they just say "make a new X component" without the word "style".
---

# Creating a new SelfHelp style

A "style" is a CMS building block a researcher places on a page (alert, card, formUserInputLog, llmChat…).
Each one is two halves that must match exactly: **PHP files on disk** and **rows in the database**
(`styles`, `fields`, `styles_fields`). Most bugs come from the halves disagreeing — a misnamed folder,
a field read in PHP that no migration installs — so the workflow below keeps them in lockstep.

This is the legacy SelfHelp (vanilla PHP, no namespaces, no Symfony). Ignore anything that looks like
the newer Symfony rewrite.

## 0. Gather before writing

Work these out from the request; ask only about what is genuinely unclear:

1. **Name** — camelCase, e.g. `progressCard`. Plugin styles get a plugin prefix (`llmChat`, `surveyJS`)
   so ownership is obvious. Check it's free: `ls server/component/style/` (and the plugin's
   `server/component/style/`) and grep the migrations for `'<name>'`.
2. **Core or plugin** — core = `sh-selfhelp/server/component/style/`;
   plugin = `server/plugins/<plugin>/server/component/style/`.
3. **Type** — see step 1.
4. **Group** — one of `Wrapper, Text, Media, Link, List, Form, Input, Admin, Triggers, Mobile`.
5. **Fields** — every configurable value, its field type, default, whether it's translatable,
   and whether the style has children.
6. **Core only — migration target** — ask the user whether to create a new
   `update_scripts/NN_update_vX_vY.sql` or append to the newest script if it isn't released yet.
   Don't guess: appending to a released script means existing installs never run it.

## 1. Pick the style type

`StyleComponent` decides how to build a style from `styles.id_type`:

| styleType | What you write | Wiring |
|---|---|---|
| `view` | only `{Name}View.php` (+ tpl) | core creates `StyleModel` + `SimpleStyleComponent`, which instantiates `{Name}View` |
| `component` | `{Name}Component.php` + `{Name}View.php`, optional `{Name}Model.php` / `{Name}Controller.php` | core calls `new {Name}Component($services, $id, $params, $id_page, $entry_record)` |

Choose **view** when the style only renders its fields/children (alert, div, card, progressBar).
Choose **component** when it needs its own model logic (DB queries, services), a controller handling
POST/AJAX, custom `has_access()`, or reuses another style's model/view (formUserInputLog reuses FormUserInput*).
Prefer view when in doubt — it's less code and there's nothing to wire.

## 2. Naming — the autoloader is unforgiving

`BaseComponent.php` registers the autoloader. For class `ProgressCardView` it strips
`Component|View|Model|Controller|Init`, lcfirst's the rest, and looks for
`server/component/style/progressCard/ProgressCardView.php`, then the same path inside every plugin's
`server/component/`. So:

- Folder = style name exactly, camelCase (`progressCard`, never `progress-card` or `progress_card`).
- Classes = `ucfirst(name)` + suffix: `ProgressCardView`, `ProgressCardComponent`, `ProgressCardModel`, `ProgressCardController`.
- A mismatch doesn't error — the style silently renders as `unknownStyle`. If you see that, check naming first.

## 3. Files

```
<style dir>/progressCard/
├── ProgressCardView.php        # always
├── ProgressCardComponent.php   # component type only
├── ProgressCardModel.php       # only if StyleModel isn't enough
├── ProgressCardController.php  # only if it handles POST/AJAX
├── tpl_progressCard.php        # templates live next to the view, named tpl_*.php
├── css/progressCard.css        # optional
└── js/progressCard.js          # optional
```

Copy the skeletons in `references/php-templates.md`. The essentials they encode:

- Every file starts with the MPL-2.0 header block used throughout the codebase.
- Explicit `require_once` with `__DIR__`-relative paths (core: `__DIR__ . "/../StyleView.php"`;
  plugin styles reach core via `__DIR__ . "/../../../../../../component/..."`).
- The View reads config in its constructor with `$this->model->get_db_field('field', default)`.
  `StyleView`'s constructor already sets `$this->css` (incl. `css`, locale and `style-section-{id}`
  classes), `$this->css_mobile`, `$this->id_section` — don't re-read those.
- `output_content()` just `require`s the tpl. The root element carries
  `id="section-<?php echo $this->id_section; ?>"` and `class="... <?php echo $this->css; ?>"`.
  Containers call `$this->output_children()` where children go.
- `output_content_mobile()`: start from `parent::output_content_mobile()` (which returns all DB fields
  plus css) and add only computed values. Skip overriding it if there are none.
- Escape plain user-visible text with `htmlspecialchars`; only echo raw HTML for intentionally
  trusted CMS content (e.g. markdown fields already parsed by the model).
- Component constructor order: model → controller (only `if(!$model->is_cms_page())`) → view →
  `parent::__construct($model, $view, $controller)`.

### Assets

- **Core**: put files in `css/` and `js/` inside the style folder. Gulp globs
  `server/component/style/**/css/*.css` and `**/js/*.js` into `styles.min.css/js`, so nothing to
  register — but tell the user to run `gulp` in `gulp/`. Never hand-edit the built bundles
  (`css/ext/styles.min.css`, `js/ext/styles.min.js`): the next gulp run overwrites them.
- **Plugin**: the core gulp bundle doesn't see plugin files. Override `get_css_includes()` /
  `get_js_includes()` in the View, pointing at the plugin's built asset with a cache-busting version
  (see the plugin example in `references/php-templates.md`).

## 4. SQL registration

Use `references/sql-templates.md`. Rules that matter:

- **Order**: `styles` row → new `fields` → `styles_fields` links. Use `get_style_id()`, `get_field_id()`,
  `get_field_type_id()` — never hard-coded ids.
- **Reuse fields before creating**: field names are global and shared across styles (`label`, `type`,
  `text_md`, `url`, `is_dismissable`…). Grep the migrations (`grep -rn "'<field>'" server/db/`) — if it
  exists, just link it. Only create a field when the name is new; if a field of the same name exists
  with a different type, choose a different name.
- **`display`**: `1` = content shown to participants and translated per language (labels, texts,
  markdown); `0` = configuration (flags, numbers, JSON, CSS).
- **Field types** (`get_field_type_id`): `text, textarea, markdown, markdown-inline, checkbox, number,
  json, code, email, date, time, color, css, condition, data-config, select-page-keyword, style-list,
  style-bootstrap, select-group, select-formName, password, panel, html-tag…`. Grep `fieldType` in
  the migrations if you need another.
- **Always link the standard set** so the style behaves like every other one in the CMS:
  `css`, `css_mobile`, `condition`, `data_config`, `debug` — plus `children` for containers.
  Older styles got `condition`/`debug` via a bulk migration; new styles must link them explicitly.
- Every link gets a useful `help` text — it's what researchers see in the CMS.
- Idempotent: `INSERT IGNORE` throughout; when re-running should refresh help texts, append
  `ON DUPLICATE KEY UPDATE help = VALUES(help)`.

**Core migration** (`server/db/update_scripts/`): wrapped in `START TRANSACTION; … COMMIT;`, with
`UPDATE version SET version = 'vX.Y.Z';` at the top. New file = next number + `_update_v<prev>_v<new>.sql`.
End the file with an empty line (the install script is built by concatenating all scripts).
Add a CHANGELOG.md entry.

**Plugin migration** (`server/plugins/<plugin>/server/db/vX.Y.Z.sql`): same transaction wrapper;
bump the plugin **last** — `UPDATE plugins SET version = 'vX.Y.Z' WHERE name = '<plugin>';` — so a
failed statement can't mark the version installed. Add a CHANGELOG entry in the plugin repo (it's a
separate git repo: check `git status` inside it).

## 5. Finish

1. `php -l` every new PHP file.
2. Cross-check: every `get_db_field('x')` in the PHP has a `styles_fields` link for this style in SQL,
   and every linked field is used (or intentionally CMS-only). Report any mismatch.
3. Document it: core styles get an entry in `doc/documentation/sections-and-components.md`
   (match the neighbouring entries); plugin styles get a section in the plugin's README.
4. Tell the user the follow-ups: run the migration, clear the SelfHelp cache (Admin → Cache → clear
   all — styles and fields are APCu-cached), run `gulp` if core assets changed, then add the style to
   a page in the CMS to try it.

Keep the summary short: files created, migration written, follow-ups.
