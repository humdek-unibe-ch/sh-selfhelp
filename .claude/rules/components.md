---
paths:
  - "server/component/**"
  - "server/plugins/*/server/component/**"
---
# Component / style rules

To add a style, use the `selfhelp-new-style` skill. When editing existing components:

- Constructor wiring: model → controller only `if(!$model->is_cms_page())` → view →
  `parent::__construct($model, $view, $controller)`.
- Models extend `StyleModel` and own all DB/service access; views only read `get_db_field()` values and
  model getters; controllers extend `BaseController`, act on `$_POST`, and report through
  `$this->success/$this->fail` + `success_msgs`/`error_msgs`.
- Keep `output_content_mobile()` working: start from `parent::output_content_mobile()` and add values.
- A field read in PHP must be installed by a migration (`styles_fields` link) — add the SQL in the same
  change. Same for lookups, hooks, pages.
- Explicit `require_once __DIR__ . ...` includes, MPL header on new files, no namespaces.
- Run `php -l` on every changed PHP file.
