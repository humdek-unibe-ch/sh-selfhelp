---
paths:
  - "server/**/*.php"
  - "*.php"
  - "js/**/*.js"
  - "server/**/*.js"
---
# Security rules

What actually exists in this codebase — build on it, don't invent parallel mechanisms:

- **Passwords**: `password_hash($pw, PASSWORD_DEFAULT)` / `password_verify()` (see `server/service/Login.php`).
  Never store, log or echo plain passwords or tokens.
- **Sessions**: configured in `Login.php` (`httponly`, SameSite, secure when HTTPS). Don't add new session
  cookies or change those params casually.
- **Access control**: `$acl->has_access($id_user, $id_page, $mode)` and `has_access_select/insert/update/delete()`
  in `server/service/Acl.php`; page ids via `$db->fetch_page_id_by_keyword()`. Check access server-side
  for every AJAX/callback/API action, not only by hiding UI. Respect `own_entries_only` when reading
  or editing records.
- **SQL**: bind every dynamic value as a `:named` parameter in `query_db*` / `execute_update_db`.
  Identifiers (table/column names, ORDER BY) can't be bound — check them against a whitelist or the
  known schema. If a legacy helper only takes a filter string, sanitize and constrain the values first.
- **Output**: escape plain text with `htmlspecialchars()`; echo raw HTML only for intentionally trusted
  CMS-authored content (markdown fields). JSON for `data-*` attributes: `htmlspecialchars(json_encode(...))`.
- **CSP / headers**: `index.php` sends `X-XSS-Protection` and `X-Frame-Options`; `BasePage::getCspRules()`
  builds the CSP. New external scripts/styles/endpoints need a CSP change — plugins extend it via a narrow
  hook on `BasePage::getCspRules`, never by disabling it. Prefer local assets over CDNs.
- **CSRF**: core has no generic CSRF token. Don't claim it does. State-changing endpoints should require
  POST, check the session user and ACL; plugins that need a token implement it locally
  (e.g. sh-shp-llm's `LlmCsrfService`).
- **CORS**: `Selfhelp::cors()` only answers exact origins from `CORS_ALLOWED_ORIGINS`. Never loosen to
  substring/wildcard matching.
- **Files/uploads**: strip control characters, reject `..`/absolute paths, enforce an extension whitelist,
  generate the stored name server-side, and verify `realpath()` stays inside the intended upload root.
- **Secrets/config**: `server/service/globals_untracked.php`, API keys, uploads and generated data never
  go into commits.
