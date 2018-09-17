SET @db_name = "selfhelpDemo"; /* Don't use underscore '_' in the db name as this causes problems with permissions. */
SET @user_name = "selfhelp_demo";

DROP PROCEDURE IF EXISTS grant_proc;

DELIMITER $$
CREATE PROCEDURE grant_proc(varGrant VARCHAR(128), varDb VARCHAR(32), varTable VARCHAR(32), varUser VARCHAR(32))
BEGIN
    SET @query = CONCAT("GRANT ", varGrant, " ON ", varDb , ".", varTable, " TO '", varUser, "'@'localhost'");
    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$
DELIMITER ;

CALL grant_proc("USAGE", "*", "*", @user_name);
CALL grant_proc("SELECT", @db_name, "*", @user_name);
CALL grant_proc("INSERT, DELETE", @db_name, "sections", @user_name);
CALL grant_proc("INSERT, DELETE", @db_name, "groups", @user_name);
CALL grant_proc("INSERT, UPDATE (email, blocked, token, id_genders, name, password), DELETE", @db_name, "users", @user_name);
CALL grant_proc("INSERT", @db_name, "user_activity", @user_name);
CALL grant_proc("INSERT", @db_name, "user_input", @user_name);
CALL grant_proc("INSERT, DELETE", @db_name, "users_groups", @user_name);
CALL grant_proc("INSERT, UPDATE (acl_select, acl_delete, acl_update, acl_insert)", @db_name, "acl_users", @user_name);
CALL grant_proc("INSERT, UPDATE (acl_select, acl_delete, acl_update, acl_insert)", @db_name, "acl_groups", @user_name);
CALL grant_proc("INSERT, UPDATE (position), DELETE", @db_name, "pages_sections", @user_name);
CALL grant_proc("INSERT, UPDATE (content)", @db_name, "sections_fields_translation", @user_name);
CALL grant_proc("INSERT, UPDATE (content)", @db_name, "pages_fields_translation", @user_name);
CALL grant_proc("INSERT, UPDATE (position), DELETE", @db_name, "sections_hierarchy", @user_name);
CALL grant_proc("INSERT, UPDATE (nav_position), DELETE", @db_name, "pages", @user_name);
CALL grant_proc("INSERT, UPDATE (position), DELETE", @db_name, "sections_navigation", @user_name);
CALL grant_proc("INSERT", @db_name, "chat", @user_name);

DROP PROCEDURE IF EXISTS grant_proc;
