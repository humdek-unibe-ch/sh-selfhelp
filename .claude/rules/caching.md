---
paths:
  - "server/service/**"
  - "server/plugins/*/server/service/**"
  - "server/component/**/*Model.php"
---
# Caching (APCu)

SelfHelp caching is the small `Cache` class in `server/service/Cache.php`, reached via
`$db->get_cache()` (BaseDb/PageDb hold it as `$this->cache`). There is no generation counter,
category builder or statistics layer — those belong to the Symfony rewrite.

## API
```php
$cache = $this->db->get_cache();
$key = $cache->generate_key($cache::CACHE_TYPE_HOOKS, $cache::CACHE_ALL, [__FUNCTION__, $hook_type]);
$res = $cache->get($key);           // false on miss
if ($res === false) {
    $res = $this->db->query_db($sql, $args);
    $cache->set($key, $res);        // ttl 0 = until cleared
}
```
- Keys: `PROJECT_NAME-<TYPE>-<id>[-<param>-<param>…]`. Put every input that changes the result into
  the extra params, otherwise different queries share a key.
- Types: `CACHE_TYPE_PAGES`, `_SECTIONS`, `_FIELDS`, `_STYLES`, `_HOOKS`, `_USER_INPUT`, `_CONDITION`,
  `_LOOKUPS`; use `CACHE_ALL` as id for "all records" lookups.
- Invalidate with `$db->clear_cache($type, $id)` (prefix match via `APCUIterator`) or `clear_cache()`
  for everything. `BaseDb::rollback()` clears everything.

## Rules
- When code writes something a cached reader returns (pages, sections, fields, styles, hooks, lookups,
  user input), clear that type (and id if known) right after the write commits.
- Don't cache per-user data under a key without the user id.
- Migrations that add/change styles, fields, hooks, lookups or pages need a cache clear afterwards
  (Admin → Cache → clear all) — say so in the migration header and in your summary to the user.
