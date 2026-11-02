---
paths:
  - "server/db/**"
  - "server/plugins/*/server/db/**"
  - "**/*.sql"
  - "server/service/BaseDb.php"
  - "server/service/PageDb.php"
---
# Database rules

For writing a migration step by step, use the `selfhelp-db-migration` skill. Key rules:

## Migrations
- Core: `server/db/update_scripts/NN_update_vA.B.C_vX.Y.Z.sql`, wrapped in `START TRANSACTION; … COMMIT;`
  with `UPDATE version SET version = 'vX.Y.Z';`. Files must end with an empty line — the install script is
  built by concatenating them. Add a CHANGELOG.md entry.
- Plugin: `server/plugins/<plugin>/server/db/vX.Y.Z.sql`; bump `plugins.version` as the **last**
  statement so a failed run never marks the version installed. `v1.0.0.sql` inserts the `plugins` row.
- Idempotent always: `INSERT IGNORE`, `CREATE TABLE IF NOT EXISTS`, guarded `UPDATE`s, and the helper
  procedures instead of raw `ALTER TABLE`. MySQL DDL isn't rolled back by the transaction, so a re-run
  after partial failure must be safe.
- Reference rows by name with `get_style_id()`, `get_field_id()`, `get_field_type_id()`,
  `get_style_group_id()`, `(SELECT id FROM lookups WHERE lookup_code = '...')` — no hard-coded ids.

## Helper procedures (`server/db/FUN_PRO_VIEWS/`)
```sql
CALL add_table_column('users', 'second_last_url', 'VARCHAR(100) DEFAULT NULL AFTER `last_url`');
CALL drop_table_column('table', 'column');
CALL rename_table_column('table', 'old_name', 'new_name');
CALL rename_table('old_table', 'new_table');
CALL add_index('user_activity', 'idx_user_activity_timestamp', 'timestamp');
CALL drop_index('table', 'index_name');
CALL add_unique_key('styles', 'styles_name', 'name');
CALL add_foreign_key('table', 'fk_name', 'fk_column', 'ref_table(id)');
CALL drop_foreign_key('table', 'fk_name');
```
Changing a view/procedure: edit its file in `FUN_PRO_VIEWS/` **and** put the `DROP … / CREATE …`
in the migration (with `DELIMITER //` for procedures), then regenerate `fun_pro_views.sql`
(`copy /b *.sql fun_pro_views.sql` on Windows).

## PHP access (`BaseDb` / `PageDb`)
- `query_db($sql, $args)`, `query_db_first($sql, $args)`, `execute_update_db($sql, $args)`,
  `insert($table, $entries)`, `insert_mult()`, `update_by_ids($table, $entries, $ids)`,
  `remove_by_ids()`, `select_by_uid()`, `begin_transaction()` / `commit()` / `rollback()`.
- Always bind dynamic values as `:named` parameters. Table/column names can't be bound — whitelist them.
- Multi-step writes: wrap in `begin_transaction()` … `commit()`, `rollback()` on failure
  (rollback also clears the APCu cache).
- Audit: `$services->get_transaction()->add_transaction(transactionTypes_insert, transactionBy_by_user,
  $user_id, $table, $entry_id, $log_row, $verbal_log)` — type/by constants are in `server/service/globals.php`.

## Data tables
Don't write into `dataTables`/`dataRows`/`dataCols`/`dataCells` by hand; use `UserInput::save_data()`
(with `updateBasedOn` for upserts) and `get_data()`. The `get_dataTable_with_filter` procedure and
`view_dataTables_data` are the read-side helpers.
