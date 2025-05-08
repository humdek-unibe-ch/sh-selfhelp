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
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('email_2fa_subject'), '0000000002', '{{@project}} - verification code');

-- add field `email_2fa`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_2fa', get_field_type_id('markdown'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_2fa'), NULL, 'Body text for the email which is sent when a user tries to login with 2fa enabled. `{{2fa_code}}` is used as a keyword where the code will be replaced in the email text.');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('email_2fa'), '0000000002', 'Hi {{@user}},

To complete your login, please use the following verification code:

**{{@2fa_code}}**

This code will expire in 10 minutes.

If you did not attempt to sign in, you can safely ignore this message or contact our support team.

---

*This is an automated message. Please do not reply to it.*
');

-- add 2fa page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'two-factor-authentication', '/two-factor-authentication', 'GET|POST', '0000000003', NULL, NULL, '1', NULL, NULL, '0000000002', (SELECT id FROM lookups WHERE type_code = "pageAccessTypes" AND lookup_code = "mobile_and_web"));
SET @id_page = (SELECT id FROM pages WHERE keyword = 'two-factor-authentication');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('label'), '0000000002', 'Two-Factor Authentication');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('title'), '0000000002', 'Two-Factor Authentication');

-- add the guest user to the page `two-factor-authentication`
INSERT IGNORE INTO `acl_users` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES (0000000001, @id_page, 1, 0, 0, 0);
-- add the admin group to the page `two-factor-authentication` to update and select
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- add twoFactorAuth style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) 
VALUES (NULL, 'twoFactorAuth', (SELECT id FROM styleType WHERE `name` = 'component' LIMIT 1), (SELECT id FROM styleGroup WHERE `name` = 'Admin' LIMIT 1), 'Provides a form for two-factor authentication where users can enter their verification code.');
-- add field `2fa_label_expiration` 
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_expiration_2fa', get_field_type_id('markdown-inline'), '1');
-- add twoFactorAuth style fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`) 
VALUES (get_style_id('twoFactorAuth'), get_field_id('label_expiration_2fa'), 'Code expires in', 'The text that appears before the timer showing how much time is left before the verification code expires.', 0, 0);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`) 
VALUES (get_style_id('twoFactorAuth'), get_field_id('alert_fail'), 'Invalid verification code. Please try again.', 'The alert text that appears when the user enters invalid code.', 0, 0);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`) 
VALUES (get_style_id('twoFactorAuth'), get_field_id('label'), 'Two-Factor Authentication', 'The main heading displayed at the top of the two-factor authentication form.', 0, 0);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`) 
VALUES (get_style_id('twoFactorAuth'), get_field_id('text_md'), 'Please enter the 6-digit code sent to your email', 'The instruction text shown to users explaining what they need to do to complete the two-factor authentication process.', 0, 0);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`) 
VALUES (get_style_id('twoFactorAuth'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.', 0, 0);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`) 
VALUES (get_style_id('twoFactorAuth'), get_field_id('css_mobile'), NULL, 'Allows to assign mobile CSS classes to the root item of the style.', 0, 0);


-- set column `requires_2fa` to `1` for group `admin`
UPDATE `groups`
SET requires_2fa = 1
WHERE `name` = 'admin';

-- add `stefan.kodzhabashev@unibe.ch` as admin
INSERT IGNORE INTO users (email, `name`, id_status, `password`, id_languages, id_genders) VALUES ('stefan.kodzhabashev@unibe.ch','Stefan Kodzhabashev', (SELECT id from userStatus WHERE `name` = 'active' LIMIT 0,1), '$2y$10$PKWjEEoCoTrr8SkKU9EkU.p.AC7qCZeFoEEcVPi3mrOXKGOBXn4vq', 2,1);
INSERT IGNORE INTO validation_codes (`code`, id_users) VALUES ('admin_stefan', (SELECT id FROM users WHERE email = 'stefan.kodzhabashev@unibe.ch'));
INSERT IGNORE INTO users_groups (id_users, id_groups) VALUES ((SELECT id FROM users WHERE email = 'stefan.kodzhabashev@unibe.ch'), (SELECT id FROM `groups` WHERE `name` = 'admin' LIMIT 0,1));

-- add `simon.maurer@unibe.ch` as admin
INSERT IGNORE INTO users (email, `name`, id_status) VALUES ('simon.maurer@unibe.ch','Simon Maurer', (SELECT id from userStatus WHERE `name` = 'invited' LIMIT 0,1));
INSERT IGNORE INTO validation_codes (`code`, id_users) VALUES ('admin_simon', (SELECT id FROM users WHERE email = 'simon.maurer@unibe.ch'));
INSERT IGNORE INTO users_groups (id_users, id_groups) VALUES ((SELECT id FROM users WHERE email = 'simon.maurer@unibe.ch'), (SELECT id FROM `groups` WHERE `name` = 'admin' LIMIT 0,1));

-- add `walter.siegenthaler@unibe.ch` as admin
INSERT IGNORE INTO users (email, `name`, id_status) VALUES ('walter.siegenthaler@unibe.ch','Walter Siegenthaler', (SELECT id from userStatus WHERE `name` = 'invited' LIMIT 0,1));
INSERT IGNORE INTO validation_codes (`code`, id_users) VALUES ('admin_walter', (SELECT id FROM users WHERE email = 'walter.siegenthaler@unibe.ch'));
INSERT IGNORE INTO users_groups (id_users, id_groups) VALUES ((SELECT id FROM users WHERE email = 'walter.siegenthaler@unibe.ch'), (SELECT id FROM `groups` WHERE `name` = 'admin' LIMIT 0,1));

-- add `samuel.stucky@unibe.ch` as admin
INSERT IGNORE INTO users (email, `name`, id_status) VALUES ('samuel.stucky@unibe.ch','Samuel Stucky@unibe', (SELECT id from userStatus WHERE `name` = 'invited' LIMIT 0,1));
INSERT IGNORE INTO validation_codes (`code`, id_users) VALUES ('admin_samuel', (SELECT id FROM users WHERE email = 'samuel.stucky@unibe.ch'));
INSERT IGNORE INTO users_groups (id_users, id_groups) VALUES ((SELECT id FROM users WHERE email = 'samuel.stucky@unibe.ch'), (SELECT id FROM `groups` WHERE `name` = 'admin' LIMIT 0,1));

-- add two-factio-authenitcation section
INSERT IGNORE INTO `sections` (`id_styles`, `name`, `owner`) VALUES (get_style_id('twoFactorAuth'), 'twoFactorAuth-twoFactorAuth', NULL);
INSERT IGNORE INTO `sections` (`id_styles`, `name`, `owner`) VALUES (get_style_id('twoFactorAuth'), 'twoFactorAuth-twoFactorAuth', NULL);
INSERT IGNORE INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
((SELECT id FROM sections WHERE `name` = 'twoFactorAuth-twoFactorAuth'), get_field_id('label_expiration_2fa'), 0000000002, 0000000001, 'Code expires in'),
((SELECT id FROM sections WHERE `name` = 'twoFactorAuth-twoFactorAuth'), get_field_id('alert_fail'), 0000000002, 0000000001, 'Invalid verification code. Please try again.'),
((SELECT id FROM sections WHERE `name` = 'twoFactorAuth-twoFactorAuth'), get_field_id('label'), 0000000002, 0000000001, 'Two-Factor Authentication'),
((SELECT id FROM sections WHERE `name` = 'twoFactorAuth-twoFactorAuth'), get_field_id('text_md'), 0000000002, 0000000001, 'Please enter the 6-digit code sent to your email');

INSERT IGNORE INTO `pages_sections` (`id_pages`, `id_sections`, `position`) VALUES
((SELECT id FROM pages WHERE `keyword` = "two-factor-authentication"), (SELECT id FROM sections WHERE `name` = 'twoFactorAuth-twoFactorAuth'), 0);