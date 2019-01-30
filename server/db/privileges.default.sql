SET @db_name = "__experiment_name__"; /* Don't use underscore '_' in the db name as this causes problems with permissions. */
SET @user_name = "__experiment_name__";

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
CALL grant_proc("INSERT, UPDATE (email, blocked, token, id_genders, name, password, last_login, is_reminded), DELETE", @db_name, "users", @user_name);
CALL grant_proc("INSERT", @db_name, "user_activity", @user_name);
CALL grant_proc("INSERT, UPDATE (`value`, `edit_time`)", @db_name, "user_input", @user_name);
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
CALL grant_proc("INSERT, UPDATE (is_new)", @db_name, "chatRecipiants", @user_name);
CALL grant_proc("INSERT, DELETE", @db_name, "chatRoom", @user_name);
CALL grant_proc("INSERT, DELETE", @db_name, "chatRoom_users", @user_name);
CALL grant_proc("INSERT, UPDATE (id_users)", @db_name, "validation_codes", @user_name);

SET @user_name_reminder = "selfhelp_reminder";
CALL grant_proc("SELECT", @db_name, "pages_fields_translation", @user_name_reminder);
CALL grant_proc("SELECT", @db_name, "users", @user_name_reminder);
CALL grant_proc("SELECT", @db_name, "pages", @user_name_reminder);
CALL grant_proc("SELECT", @db_name, "fields", @user_name_reminder);

DROP PROCEDURE IF EXISTS grant_proc;
