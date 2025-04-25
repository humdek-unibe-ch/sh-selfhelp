-- set DB version
UPDATE version
SET version = 'v7.5.0';

-- add column `requires_2fa` to talble `groups`
CALL add_table_column('`groups`', 'requires_2fa', 'TINYINT(1) NOT NULL DEFAULT 0');
