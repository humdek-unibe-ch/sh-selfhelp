---
name: selfhelp-db-migration
description: Write a database migration for the legacy vanilla-PHP SelfHelp (sh-selfhelp) or one of its plugins (server/plugins/sh-shp-*) — new or changed tables/columns/indexes/foreign keys, lookups, pages, ACL, hooks, fields, views or stored procedures, plus the version bump. Use whenever a change needs SQL in server/db/update_scripts or a plugin's server/db/vX.Y.Z.sql, when the user says "migration", "update script", "schema change", "add a column/table/lookup/page/hook", or when PHP code starts using something the database doesn't have yet. For registering a new style, the selfhelp-new-style skill covers the style part.
---

# Writing a SelfHelp migration

In SelfHelp the database is part of the application contract: pages, routes, styles, fields, hooks,
lookups, ACL and plugin versions all live in rows. A migration is how a code change reaches every
installation, so it must run cleanly on any install that's at the previous version — and survive being
re-run, because MySQL DDL is not rolled back by the surrounding transaction.

## 1. Decide where it goes

**Core** — `server/db/update_scripts/`. Look at the newest file
(`ls server/db/update_scripts | sort -V | tail -2`).
Ask the user whether to append to it (only if that version is unreleased) or create the next one:
`NN+1_update_v<prev>_v<new>.sql`. Pick the new version by semver: new tables/features → minor,
fix-only → patch.

**Plugin** — `server/plugins/<plugin>/server/db/vX.Y.Z.sql`, the next version after the newest file there.
A plugin's first migration is `v1.0.0.sql` and registers the plugin
(`INSERT IGNORE INTO plugins (name, version) VALUES ('<plugin>', 'v1.0.0');`).
Plugin name = the value in its `plugins` row (e.g. `llm` for `sh-shp-llm`) — read it from `v1.0.0.sql`.

## 2. Skeleton

Core:
```sql
START TRANSACTION;

UPDATE version
SET version = 'vX.Y.Z';

-- ---------------------------------------------------------------------------
-- <Topic>: what this block does and why (one or two lines)
-- ---------------------------------------------------------------------------
...

COMMIT;

```
Plugin:
```sql
-- =====================================================
-- <Plugin> plugin vX.Y.Z - <summary>
-- Requires vA.B.C. <Breaking notes.> Clear the SelfHelp cache afterwards.
-- =====================================================

START TRANSACTION;
...
-- Bump the plugin version LAST so a failed statement never marks this version installed.
UPDATE plugins SET version = 'vX.Y.Z' WHERE `name` = '<plugin>';

COMMIT;
```
Keep a trailing empty line: core install scripts are built by concatenating the files.

## 3. Building blocks (all idempotent)

**Tables**
```sql
CREATE TABLE IF NOT EXISTS `myPluginItems` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_users` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_myPluginItems_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
Ids are `INT(10) UNSIGNED ZEROFILL` across the schema — FKs must match exactly or MySQL refuses them.
Prefix plugin tables with the plugin name so ownership is obvious.

**Changing existing tables** — use the helper procedures (they check first), never bare `ALTER TABLE`:
```sql
CALL add_table_column('users', 'second_last_url', 'VARCHAR(100) DEFAULT NULL AFTER `last_url`');
CALL rename_table_column('table', 'old', 'new');
CALL drop_table_column('table', 'column');
CALL add_index('user_activity', 'idx_user_activity_timestamp', 'timestamp');
CALL add_unique_key('styles', 'styles_name', 'name');
CALL add_foreign_key('table', 'fk_table_col', 'col', 'users(id)');
CALL drop_foreign_key('table', 'fk_name');   CALL drop_index('table', 'idx');   CALL rename_table('a', 'b');
```

**Lookups**
```sql
INSERT IGNORE INTO `lookups` (`type_code`, `lookup_code`, `lookup_value`, `lookup_description`)
VALUES ('myPluginStatus', 'active', 'Active', 'Item is active');
```
Add a matching `define('...')` in globals.php if PHP refers to the code.

**Hooks** — see `server/plugins/CLAUDE.md` for semantics:
```sql
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`)
VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return'), '<plugin>-<what>',
        '<description>', 'TargetClass', 'targetMethod', '<Plugin>Hooks', 'methodName');
```

**Pages** — copy the `INSERT IGNORE INTO pages (...)` + `pages_fields_translation` pattern from an existing
migration (e.g. `sh-shp-api/server/db/v1.0.0.sql`), using `SET @page_keyword = '...'` and
`(SELECT id FROM pages WHERE keyword = @page_keyword)`; add ACL rows for the groups that need access
(look at how the same migration grants `acl_groups`).

**Fields / styles** — see the `selfhelp-new-style` skill. Reuse existing field names, create new ones with
`get_field_type_id('<type>')`, link via `styles_fields` with a useful `help`.

**Views / procedures** — the source lives in `server/db/FUN_PRO_VIEWS/<NN>_<name>.sql`. Update that file and
put the same `DROP VIEW IF EXISTS …; CREATE VIEW …` (or `DROP PROCEDURE IF EXISTS` + `DELIMITER //` block)
into the migration; then regenerate `FUN_PRO_VIEWS/fun_pro_views.sql` (`copy /b *.sql fun_pro_views.sql` on
Windows, `cat *.sql > fun_pro_views.sql` on Linux). Plugins keep theirs in `server/db/FUN_PRO_VIEWS/` too.

**Data updates** — guard them so a re-run is a no-op (`WHERE col IS NULL`, `WHERE NOT EXISTS (...)`).

Always reference ids via `get_style_id()`, `get_field_id()`, `get_field_type_id()`, `get_style_group_id()`
or subselects by name/code — never literal ids.

## 4. Check before finishing
1. Every table, column, field, lookup, hook and page the PHP now uses is created by this migration (grep the
   new names in the code and in the SQL).
2. Running the script twice would not fail or duplicate rows.
3. Core: version bumped at the top and CHANGELOG.md has the entry. Plugin: version bump is the last statement,
   CHANGELOG/README of the plugin repo updated (it's its own git repo).
4. Tell the user: run the script (`mysql -u <user> -p <db> < <file>`), then clear the SelfHelp cache
   (Admin → Cache → clear all) when styles, fields, hooks, lookups or pages changed. Mention any breaking
   change and whether code must be deployed before or after the migration.
