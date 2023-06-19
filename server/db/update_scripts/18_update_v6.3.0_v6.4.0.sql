-- set DB version
UPDATE version
SET version = 'v6.4.0';

-- add user types
INSERT IGNORE INTO `lookups` (type_code, lookup_code, lookup_value, lookup_description) values ('userTypes', 'user', 'User', 'All default users');

SET @user_type_user_id = (SELECT id FROM lookups WHERE type_code = "userTypes" AND lookup_value = 'user');

SET @user_default_type_id = (SELECT CONCAT('INT(10) UNSIGNED ZEROFILL NOT NULL DEFAULT', ' ', @user_type_user_id));

CALL add_table_column('users', 'user_type_id', @user_default_type_id);

