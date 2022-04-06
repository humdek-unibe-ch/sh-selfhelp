-- set DB version
UPDATE version
SET version = 'v5.0.0';

-- add filed condtion to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('condition'), 'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `"@__form_name__#__from_field_name__"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.' 
FROM view_style_fields
WHERE field_name = 'css' and style_name <> 'conditionalContainer';

-- add filed jquery_builder_json to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('jquery_builder_json'), 'This field contains the JSON structure for the jquery builder. The field shoudl be hidden' 
FROM view_style_fields
WHERE field_name = 'css' and style_name <> 'conditionalContainer';

-- add filed debug to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('debug'), 'If *checked*, debug messages will be rendered to the screen. These might help to understand the result of a condition evaluation. **Make sure that this field is *unchecked* once the page is productive**.' 
FROM view_style_fields
WHERE field_name = 'css' and style_name <> 'conditionalContainer' and style_name <> 'autocomplete';

-- add keyword ajax_get_lookups
INSERT INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'ajax_get_lookups', '/request/[AjaxDataSource:class]/[get_lookups:method]', 'GET|POST', '0000000005', NULL, NULL, '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_data = LAST_INSERT_ID();
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) 
VALUES ('0000000001', @id_page_data, '1', '0', '0', '0');

-- delete field platoform
DELETE FROM fields
WHERE id = get_field_id('platform');

-- delete filedType select-platform
DELETE FROM fieldType
WHERE id = get_field_type_id('select-platform');

-- add UI preferences to the profile page 
INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000012, 'profile-ui-preferences-card', NULL);
SET @id_section_pnc = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnc, 0000000022, 0000000002, 0000000001, 'UI Vorlieben'),
(@id_section_pnc, 0000000022, 0000000003, 0000000001, 'UI Preferences'),
(@id_section_pnc, 0000000023, 0000000001, 0000000001, 'mb-3 mt-3'),
(@id_section_pnc, 0000000028, 0000000001, 0000000001, 'light'),
(@id_section_pnc, 0000000046, 0000000001, 0000000001, '1'),
(@id_section_pnc, 0000000047, 0000000001, 0000000001, '0'),
(@id_section_pnc, 0000000048, 0000000001, 0000000001, ''),
(@id_section_pnc, 0000000091, 0000000001, 0000000001, '{"and":[{"==":[true,"$admin"]}]}'),
(@id_section_pnc, 00000000180, 0000000001, 0000000001, '{"condition":"AND","rules":[{"id":"user_group","field":"user_group","type":"string","input":"select","operator":"in","value":["admin"]}],"valid":true}');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (get_style_id('formUserInputRecord'), 'profile-preferences-ui-formUserInputRecord', NULL);
SET @id_section_pnf = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnf, 0000000008, 0000000002, 0000000001, 'Ändern'),
(@id_section_pnf, 0000000008, 0000000003, 0000000001, 'Change'),
(@id_section_pnf, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pnf, 0000000028, 0000000001, 0000000001, 'primary'),
(@id_section_pnf, 0000000057, 0000000001, 0000000001, 'ui-preferences'),
(@id_section_pnf, 0000000087, 0000000001, 0000000001, '0'),
(@id_section_pnf, 0000000035, 0000000002, 0000000001, 'Die Einstellungen für Vorlieben wurden erfolgreich gespeichert'),
(@id_section_pnf, 0000000035, 0000000003, 0000000001, 'The preferences settings were successfully saved');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'profile-ui-preferences-old-ui', NULL);
SET @id_section_pnci = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnci, 0000000008, 0000000002, 0000000001, 'Enable old UI'),
(@id_section_pnci, 0000000008, 0000000003, 0000000001, 'Enable old UI'),
(@id_section_pnci, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pnci, 0000000054, 0000000001, 0000000001, 'checkbox'),
(@id_section_pnci, 0000000055, 0000000002, 0000000001, ''),
(@id_section_pnci, 0000000055, 0000000003, 0000000001, ''),
(@id_section_pnci, 0000000056, 0000000001, 0000000001, '1'),
(@id_section_pnci, 0000000057, 0000000001, 0000000001, 'old_ui'),
(@id_section_pnci, 0000000058, 0000000001, 0000000001, '0');

INSERT INTO `sections_hierarchy` (`parent`, `child`, `position`) VALUES
((SELECT id FROM sections WHERE name = "profile-col1-div"), @id_section_pnc, 0),
(@id_section_pnc, @id_section_pnf, 0),
(@id_section_pnf, @id_section_pnci, 0);

DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable_with_filter //

CREATE PROCEDURE get_uploadTable_with_filter( table_id_param INT, filter_param VARCHAR(1000) )
BEGIN
    SET @@group_concat_max_len = 32000;
    SET @sql = NULL;
    SELECT
    GROUP_CONCAT(DISTINCT
        CONCAT(
            'max(case when col.name = "',
                col.name,
                '" then value end) as `',
            replace(col.name, ' ', ''), '`'
        )
    ) INTO @sql
    FROM  uploadTables t
	INNER JOIN uploadRows r on (t.id = r.id_uploadTables)
	INNER JOIN uploadCells cell on (cell.id_uploadRows = r.id)
	INNER JOIN uploadCols col on (col.id = cell.id_uploadCols)
    WHERE t.id = table_id_param;

    IF (@sql is null) THEN
        SELECT table_name from view_uploadTables where 1=2;
    ELSE
        BEGIN
            SET @sql = CONCAT('select t.name as table_name, t.timestamp as timestamp, r.id as record_id, r.timestamp as entry_date, ', @sql, 
                ' from uploadTables t
					inner join uploadRows r on (t.id = r.id_uploadTables)
					inner join uploadCells cell on (cell.id_uploadRows = r.id)
					inner join uploadCols col on (col.id = cell.id_uploadCols)
					where t.id = ', table_id_param,
					' group by t.name, t.timestamp, r.id HAVING 1 ', filter_param);
			IF LOCATE('id_users', @sql) THEN
				-- get user_name if there is id_users column
				SET @sql = CONCAT('select v.*, u.name as user_name from (', @sql, ')  as v left join users u on (v.id_users = u.id)');
			END IF;

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END
//

DELIMITER ;
