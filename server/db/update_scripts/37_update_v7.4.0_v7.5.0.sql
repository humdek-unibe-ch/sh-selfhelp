-- set DB version
UPDATE version
SET version = 'v7.5.0';

DELIMITER //

DROP PROCEDURE IF EXISTS add_table_column //
CREATE PROCEDURE add_table_column(
    IN param_table VARCHAR(100), 
    IN param_column VARCHAR(100), 
    IN param_column_type VARCHAR(500)
)
BEGIN
    SET @sqlstmt = (
        SELECT IF(
            (
                SELECT COUNT(*) 
                FROM information_schema.COLUMNS
                WHERE `table_schema` = DATABASE()
                AND `table_name` = param_table
                AND `COLUMN_NAME` = param_column 
            ) > 0,
            "SELECT 'Column already exists in the table'",
            CONCAT('ALTER TABLE `', param_table, '` ADD COLUMN `', param_column, '` ', param_column_type, ';')
        )
    );

    PREPARE st FROM @sqlstmt;
    EXECUTE st;
    DEALLOCATE PREPARE st;
END
//

DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS drop_table_column //
CREATE PROCEDURE drop_table_column(param_table VARCHAR(100), param_column VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*) 
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
			AND `COLUMN_NAME` = param_column 
		) = 0,
        "SELECT 'Column does not exist'",
        CONCAT('ALTER TABLE `', param_table, '` DROP COLUMN `', param_column, '` ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;


-- add column `requires_2fa` to talble `groups`
CALL add_table_column('groups', 'requires_2fa', 'TINYINT(1) NOT NULL DEFAULT 0');

-- create table users_2fa_codes
CREATE TABLE IF NOT EXISTS `users_2fa_codes` (
	`id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
	id_users INT(10) UNSIGNED ZEROFILL NOT NULL,
    `code` VARCHAR(6) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    is_used BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (id_users) REFERENCES users(id) ON DELETE CASCADE
);

SET @id_page = (SELECT id FROM pages WHERE keyword = 'email');

-- add field `email_2fa_subject`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_2fa_subject', get_field_type_id('markdown'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_2fa_subject'), NULL, 'Subject text for the email which is sent when a user tries to login with 2fa enabled');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('email_2fa_subject'), '0000000002', '@project - validation code');

-- add field `email_2fa`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_2fa', get_field_type_id('markdown'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_2fa'), NULL, 'Body text for the email which is sent when a user tries to login with 2fa enabled. `{{2fa_code}}` is used as a keyword where the code will be replaced in the email text.');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('email_2fa'), '0000000002', 'Hi {{user_name}},

To complete your login, please use the following verification code:

**{{2fa_code}}**

This code will expire in 10 minutes.

If you did not attempt to sign in, you can safely ignore this message or contact our support team.

---

*This is an automated message. Please do not reply to it.*
');