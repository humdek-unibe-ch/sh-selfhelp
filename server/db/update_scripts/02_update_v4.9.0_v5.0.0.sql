-- set DB version
UPDATE version
SET version = 'v5.0.0';

-- add field condtion to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('condition'), 'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `"@__form_name__#__from_field_name__"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.' 
FROM view_style_fields
WHERE field_name = 'css' and style_name <> 'conditionalContainer';

-- add field jquery_builder_json to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('jquery_builder_json'), 'This field contains the JSON structure for the jquery builder. The field should be hidden' 
FROM view_style_fields
WHERE field_name = 'css' and style_name <> 'conditionalContainer';

-- add field debug to all styles that have css field
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
(@id_section_pnci, 0000000056, 0000000001, 0000000001, '0'),
(@id_section_pnci, 0000000057, 0000000001, 0000000001, 'old_ui'),
(@id_section_pnci, 0000000058, 0000000001, 0000000001, '1');

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

-- change field submit_and_send_email that does not depend on the locale
UPDATE fields
SET display = 0
WHERE id = get_field_id('submit_and_send_email');

-- create table pageType_fields
CREATE TABLE `pageType_fields` (
  `id_pageType` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Indexes for table `pageType_fields`
ALTER TABLE `pageType_fields`
  ADD PRIMARY KEY (`id_pageType`,`id_fields`),
  ADD KEY `id_pageType` (`id_pageType`),
  ADD KEY `id_fields` (`id_fields`);

-- Constraints for table `pageType_fields`
ALTER TABLE `pageType_fields`
  ADD CONSTRAINT `fk_pageType_fields_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pageType_fields_id_pageType` FOREIGN KEY (`id_pageType`) REFERENCES `pageType` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add page type maintenance (it is unique and only one page should be from this type)
INSERT INTO `pageType` (`id`, `name`) VALUES (NULL, 'maintenance');

-- add page maintenance
INSERT INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'maintenance', '/maintenance', 'GET|POST', '0000000001', NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'maintenance'));

SET @id_page_data = LAST_INSERT_ID();

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) 
VALUES ('0000000001', @id_page_data, '1', '0', '1', '0');

-- get the content from home page and move it to the new maintance page
UPDATE pages_fields_translation
SET id_pages = @id_page_data
WHERE id_fields IN (get_field_id('maintenance'), get_field_id('maintenance_date'), get_field_id('maintenance_time'));

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
SELECT (SELECT id FROM pageType WHERE `name` = 'maintenance'), id_fields, default_value, `help`
FROM pages_fields
WHERE id_fields IN (get_field_id('maintenance'), get_field_id('maintenance_date'), get_field_id('maintenance_time'));

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
SELECT (SELECT id FROM pageType WHERE `name` = 'intern'), id_fields, default_value, `help`
FROM pages_fields
WHERE id_fields = (get_field_id('description'));

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
SELECT (SELECT id FROM pageType WHERE `name` = 'core'), id_fields, default_value, `help`
FROM pages_fields
WHERE id_fields = (get_field_id('description'));

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
SELECT (SELECT id FROM pageType WHERE `name` = 'experiment'), id_fields, default_value, `help`
FROM pages_fields WHERE id_fields = (get_field_id('description'));

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
SELECT (SELECT id FROM pageType WHERE `name` = 'open'), id_fields, default_value, `help`
FROM pages_fields
WHERE id_fields = (get_field_id('description'));

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
VALUES ((SELECT id FROM pageType WHERE `name` = 'open'), get_field_id('title'), '', 'The title of the page. This field is used as\n - HTML title of the page\n - Menu name in the header');

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
VALUES ((SELECT id FROM pageType WHERE `name` = 'intern'), get_field_id('title'), '', 'The title of the page. This field is used as\n - HTML title of the page\n - Menu name in the header');

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
VALUES ((SELECT id FROM pageType WHERE `name` = 'core'), get_field_id('title'), '', 'The title of the page. This field is used as\n - HTML title of the page\n - Menu name in the header');

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
VALUES ((SELECT id FROM pageType WHERE `name` = 'experiment'), get_field_id('title'), '', 'The title of the page. This field is used as\n - HTML title of the page\n - Menu name in the header');

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
VALUES ((SELECT id FROM pageType WHERE `name` = 'open'), get_field_id('icon'), '', 'The icon which will be used for menus. For mobile icons use prefix `mobile-`');

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
VALUES ((SELECT id FROM pageType WHERE `name` = 'intern'), get_field_id('icon'), '', 'The icon which will be used for menus. For mobile icons use prefix `mobile-`');

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
VALUES ((SELECT id FROM pageType WHERE `name` = 'core'), get_field_id('icon'), '', 'The icon which will be used for menus. For mobile icons use prefix `mobile-`');

INSERT INTO pageType_fields (id_pageType, id_fields, default_value, `help`)
VALUES ((SELECT id FROM pageType WHERE `name` = 'experiment'), get_field_id('icon'), '', 'The icon which will be used for menus. For mobile icons use prefix `mobile-`');

UPDATE pages_fields_translation
SET id_fields = get_field_id('icon')
WHERE id_fields = get_field_id('type_input');

UPDATE pages_fields_translation
SET id_fields = get_field_id('title')
WHERE id_fields = get_field_id('label');

DELIMITER //
DROP FUNCTION IF EXISTS get_page_fields_helper //

CREATE FUNCTION get_page_fields_helper(page_id INT, language_id INT, default_language_id INT) RETURNS TEXT
-- page_id -1 returns all pages
BEGIN 
	SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'MAX(CASE WHEN f.`name` = "',
		  f.`name`,
		  '" THEN IF(IFNULL((SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',language_id,' LIMIT 0,1), "") = "", (SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',default_language_id,' LIMIT 0,1),(SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND pft.id_fields = f.id AND pft.id_languages = ',language_id,' LIMIT 0,1))  end) as `',
		  replace(f.`name`, ' ', ''), '`'
		)
	  ) INTO @sql
	FROM  pages AS p
	LEFT JOIN pageType_fields AS ptf ON ptf.id_pageType = p.id_type 
	LEFT JOIN fields AS f ON f.id = ptf.id_fields
    WHERE p.id = page_id OR page_id = -1;
	
    RETURN @sql;
END
//

DELIMITER //

DROP PROCEDURE IF EXISTS get_page_fields //

CREATE PROCEDURE get_page_fields( page_id INT, language_id INT, default_language_id INT, filter_param VARCHAR(1000), order_param VARCHAR(1000))
BEGIN  
	-- page_id -1 returns all pages
    SET @@group_concat_max_len = 32000;
	SELECT get_page_fields_helper(page_id, language_id, default_language_id) INTO @sql;	
	
    IF (@sql is null) THEN	
        SELECT * FROM pages WHERE 1=2;
    ELSE 
		BEGIN
		SET @sql = CONCAT(
			'select p.id, p.keyword, p.url, p.protocol, p.id_actions, "select" AS access_level, p.id_navigation_section, p.parent, p.is_headless, p.nav_position, p.footer_position, p.id_type, p.id_pageAccessTypes, a.name AS `action`, ', 
			@sql, 
			'FROM pages p
            LEFT JOIN actions AS a ON a.id = p.id_actions
			LEFT JOIN pageType_fields AS ptf ON ptf.id_pageType = p.id_type 
			LEFT JOIN fields AS f ON f.id = ptf.id_fields
			WHERE (p.id = ', page_id, ' OR -1 = ', page_id, ')
            GROUP BY p.id, p.keyword, p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position, p.footer_position, p.id_type, p.id_pageAccessTypes, a.name HAVING 1 ', filter_param
        );
        
        IF (order_param <> '') THEN	        
			SET @sql = concat(
				'SELECT * FROM (',
				@sql,
				') AS t ', order_param
			);
		END IF;

		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

-- set the language to be 2 not 1 for admin pages. If other language is needed it can be added. Now it falls back to the default language
UPDATE pages_fields_translation
SET id_languages = 2
WHERE id_fields = 22 AND id_languages = 1;

-- make section name not-unique
ALTER TABLE sections
DROP INDEX `name`;

-- Add new field type `condition` 
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'condition', '6');

-- Add new style `conditionBuilder`
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('conditionBuilder', '1', (select id from styleGroup where `name` = 'intern' limit 1), 'Internal style used for the condition field');

-- make field `condition` from type `condition`
UPDATE `fields`
SET id_type = (SELECT id FROM fieldType WHERE `name` = 'condition' LIMIT 0,1)
WHERE `name` = 'condition';

--  hide all fields `jquery_builder_json`
UPDATE styles_fields
SET hidden = 1
WHERE id_fields = get_field_id('jquery_builder_json');

-- Add new field type `data-config` 
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'data-config', '7');

-- Add new style `dataConfigBuilder`
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('dataConfigBuilder', '1', (select id from styleGroup where `name` = 'intern' limit 1), 'Internal style used for the `data-config` field');

-- make field `data_config` from type `data-config`
UPDATE `fields`
SET id_type = (SELECT id FROM fieldType WHERE `name` = 'data-config' LIMIT 0,1)
WHERE `name` = 'data_config';

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
            SET @sql = CONCAT('select t.name as table_name, t.timestamp as timestamp, r.id as record_id, r.timestamp as entry_date, ', IF(@sql LIKE '%id_users%', @sql, CONCAT(@sql,', -1 AS id_users')), 
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

-- Add new style `actionConfigBuilder`
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('actionConfigBuilder', '1', (select id from styleGroup where `name` = 'intern' limit 1), 'Internal style used for the configuration of an action');

-- Add new style `qualtricsSurveyConfigBuilder`
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('qualtricsSurveyConfigBuilder', '1', (select id from styleGroup where `name` = 'intern' limit 1), 'Internal style used for the configuration of an Qulatrics Survey');

-- add field data_config to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('data_config'), 'In this ***JSON*** field we can configure a data retrieve params from the DB, either `static` or `dynamic` data. Example: 
  ```
  [
 	{
 		"type": "static|dynamic",
 		"table": "table_name | #url_param1",
		"retrieve": "first | last | all",
 		"fields": [
 			{
 				"field_name": "name | #url_param2",
 				"field_holder": "@field_1",
 				"not_found_text": "my field was not found"				
 			}
 		]
 	}
 ]
 ```
 If the page supports parameters, then the parameter can be accessed with `#` and the name of the paramer. Example `#url_param_name`. 
 
 In order to inlcude the retrieved data in the markdown field, include the `field_holder` that wa defined in the markdown text.
 
 We can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;)' 
FROM view_style_fields
WHERE field_name = 'css' AND style_id NOT IN (SELECT style_id
FROM view_style_fields
WHERE field_name = 'data_config');

-- deprecate style `conditionFailed`
UPDATE styles
SET id_group = 1
WHERE name = "conditionFailed";

-- make style name unique
ALTER TABLE styles ADD UNIQUE KEY `styles_name` (`name`);

-- reduce the name size so it can be a unique key
ALTER TABLE plugins MODIFY COLUMN name VARCHAR(100);

-- make plugins name unique
ALTER TABLE plugins ADD UNIQUE KEY `plugins_name` (`name`);

-- make fieldType name unique
ALTER TABLE fieldType ADD UNIQUE KEY `fieldType_name` (`name`);

-- make fields name unique
ALTER TABLE fields ADD UNIQUE KEY `fields_name` (`name`);

-- make styleGroup name unique
ALTER TABLE styleGroup ADD UNIQUE KEY `styleGroup_name` (`name`);

-- if the style book exists add the entry into the plugin table
INSERT IGNORE INTO plugins (name, version) 
SELECT 'book', 'v1.0.0'
FROM styles
WHERE name = 'book';

-- if the style mermaidForm exists add the entry into the plugin table
INSERT IGNORE INTO plugins (name, version) 
SELECT 'mermaidForm', 'v1.1.0'
FROM styles
WHERE name = 'mermaidForm';

-- if the style graph exists add the entry into the plugin table
INSERT IGNORE INTO plugins (name, version) 
SELECT 'plotly-graphs', 'v1.0.0'
FROM styles
WHERE name = 'graph';

-- deprecate style `trigger`. From now on the new plugins autoloads
UPDATE styles
SET id_group = 1
WHERE name = "trigger";

-- if the style messageBoard exists add the entry into the plugin table
INSERT IGNORE INTO plugins (name, version) 
SELECT 'messageBoard', 'v1.0.0'
FROM styles
WHERE name = 'messageBoard';

-- if the style calendar exists add the entry into the plugin table
INSERT IGNORE INTO plugins (name, version) 
SELECT 'calendar', 'v1.0.0'
FROM styles
WHERE name = 'calendar';

-- deprecate style `quiz`. It could be done with conditions. If needed can be moved and expaned as a separate plugin
UPDATE styles
SET id_group = 1
WHERE name = "quiz";

