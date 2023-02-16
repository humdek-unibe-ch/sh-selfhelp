-- set DB version
UPDATE version
SET version = 'v5.11.0';

-- add field value_gender
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'value_gender', get_field_type_id('text'), '');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('validate'), get_field_id('value_gender'), '', 'Set the default value for the gender. Once it is set, the field will be hidden on validation. `1` - `male`, `2` - `female`, `3` - `divers`');
-- add field value_name
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'value_name', get_field_type_id('text'), '');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('validate'), get_field_id('value_name'), '', 'Set the default value for the user name. Once it is set, the field will be hidden on validation.');

-- add field `email_activate_email_address`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_activate_email_address', get_field_type_id('text'), '0');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_activate_email_address'), NULL, 'Set the email address which will be used to send activation emails.');

-- add field `email_delete_profile_email_address`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_delete_profile_email_address', get_field_type_id('text'), '0');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_delete_profile_email_address'), NULL, 'Set the email address which will be used to send confirmation emails when the users delete their profile');

-- add field `email_delete_profile_subject`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_delete_profile_subject', get_field_type_id('markdown'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_delete_profile_subject'), NULL, 'Subject text for the email confirmation which is sent when a user profile is deleted');

-- add field `email_delete_profile`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_delete_profile', get_field_type_id('markdown'), '1');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_delete_profile'), NULL, 'Email text which is sent when a user profile is deleted');

-- add field `email_delete_profile_email_address_notification_copy`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_delete_profile_email_address_notification_copy', get_field_type_id('text'), '0');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_delete_profile_email_address_notification_copy'), NULL, 'Set an email address that will be notified that a user acount was deleted');


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
        CONCAT('ALTER TABLE ', param_table, ' DROP COLUMN ', param_column, ' ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;

-- add new filed `internal` from type checkbox
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'internal', get_field_type_id('checkbox'), '0');
-- add field `internal` to styles `formUserInput`, `formUserInputLog` and `formUserInputRecord`
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `hidden`, `help`) VALUES (get_style_id('formUserInput'), get_field_id('internal'), 0, 1, 'If checked the data will not be shown in the data view');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `hidden`, `help`) VALUES (get_style_id('formUserInputLog'), get_field_id('internal'), 0, 1, 'If checked the data will not be shown in the data view');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `hidden`, `help`) VALUES (get_style_id('formUserInputRecord'), get_field_id('internal'), 0, 1, 'If checked the data will not be shown in the data view');

DROP VIEW IF EXISTS view_form;
CREATE VIEW view_form
AS
SELECT DISTINCT cast(form.id AS UNSIGNED) form_id, sft_if.content AS form_name, IFNULL(sft_intern.content, 0) AS internal
FROM user_input ui
LEFT JOIN sections form  ON (ui.id_section_form = form.id)
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_intern ON sft_intern.id_sections = ui.id_section_form AND sft_intern.id_fields = get_field_id('internal');

DROP VIEW IF EXISTS view_data_tables;
CREATE VIEW view_data_tables
AS
SELECT 'dynamic' AS `type`, form_id AS id, form_name AS orig_name, concat(form_name, '_dynamic') AS `table_name`, CONCAT(form_id,"-","dynamic") AS form_id_plus_type, internal
FROM view_form

UNION

SELECT 'static' AS `type`, id AS id, `name` AS orig_name, CONCAT(`name`, '_static') AS `table_name`, CONCAT(FLOOR(id),"-","static") AS form_id_plus_type, 0  AS internal
FROM uploadTables;

DELIMITER //
DROP PROCEDURE IF EXISTS add_foreign_key //
CREATE PROCEDURE add_foreign_key(param_table VARCHAR(100), fk_name VARCHAR(100), fk_column VARCHAR(100), fk_references VARCHAR(200))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) > 0,
        "SELECT 'The foreign key already exists in the table'",
        CONCAT('ALTER TABLE ', param_table, ' ADD CONSTRAINT ', fk_name, ' FOREIGN KEY (', fk_column, ') REFERENCES ', fk_references, ' ON DELETE CASCADE ON UPDATE CASCADE;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS drop_foreign_key //
CREATE PROCEDURE drop_foreign_key(param_table VARCHAR(100), fk_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) = 0,
        "SELECT 'Foreign key does not exist'",
        CONCAT('ALTER TABLE ', param_table, ' DROP FOREIGN KEY ', fk_name, ' ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;

DROP VIEW IF EXISTS view_form;
CREATE VIEW view_form
AS
SELECT DISTINCT cast(form.id AS UNSIGNED) form_id, sft_if.content AS form_name, IFNULL(sft_intern.content, 0) AS internal
FROM user_input ui
LEFT JOIN sections form  ON (ui.id_section_form = form.id)
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_intern ON sft_intern.id_sections = ui.id_section_form AND sft_intern.id_fields = (SELECT id
FROM `fields`
WHERE `name` = 'internal');
