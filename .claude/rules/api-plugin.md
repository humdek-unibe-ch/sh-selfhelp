---
paths:
  - "server/plugins/sh-shp-api/**"
---
# sh-shp-api (REST API plugin)

Core has no API; it lives in this plugin. `ApiHooks` intercepts routes whose target is `api`
(`PAGE_ACTION_API`) and maps `/api/<class>/<method>/...` to class `Api` + ucfirst(class) and method
`<HTTP_METHOD>_<method>`, e.g. `GET /api/data/table/x` → `ApiData::GET_table($table_name, ...)`.

## Adding an endpoint
1. Add a public method `GET_x` / `POST_x` / `PUT_x` / `DELETE_x` to an existing `server/api/Api*.php`, or a
   new class `ApiFoo extends ApiRequest` with `__construct($services, $response)` calling
   `parent::__construct($services); $this->init_response($response);` (copy `ApiBase.php`).
2. Route params are matched to method parameter **names** via reflection; missing mandatory params return
   `HTTP_NOT_ACCEPTABLE` automatically. Optional params need defaults.
3. Respond with `$this->set_response($data); $this->return_response();`; errors via
   `set_status(HTTP_…)` + `set_error_message()` before `return_response()`.
4. Register the page/route and ACL in a new `server/db/vX.Y.Z.sql` (see the `pages` inserts in
   `v1.0.0.sql`), bump the plugin version last, document the endpoint in `API.md`.

## Auth
Header `x-api-key` (`X_API_KEY`); token → user via `users_api.token`; `authorizeUser()` then checks the
route page with `$acl->has_access()` for the request's mode. Every call is logged to `apiLogs`.
HTTP status constants (`HTTP_OK`, `HTTP_UNAUTHORIZED`, …) are in `server/service/globals.php`.
