-- set DB version
UPDATE version
SET version = 'v6.2.0';

 -- add keyword and param columns to table user_activity
 CALL add_table_column('user_activity', 'keyword', "VARCHAR(100) DEFAULT NULL");
 CALL add_table_column('user_activity', 'params', "VARCHAR(1000) DEFAULT NULL");
 CALL add_table_column('user_activity', 'mobile', "BOOLEAN DEFAULT NULL");

 