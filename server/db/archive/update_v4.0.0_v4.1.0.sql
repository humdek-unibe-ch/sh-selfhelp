-- set DB version
UPDATE version
SET version = 'v4.1.0';

-- add pageAccessTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'mobile', 'Mobile', 'The page will be loaded only for mobile apps');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'web', 'Web', 'The page will be loaded only for the website');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'mobile_and_web', 'Mobile and web', 'The page will be loaded for web and mobile');

ALTER TABLE `pages`
ADD COLUMN `id_pageAccessTypes` int(10) UNSIGNED ZEROFILL,
ADD CONSTRAINT `pages_fk_id_pacgeAccessTypes` FOREIGN KEY (`id_pageAccessTypes`) REFERENCES `lookups` (`id`);

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE type_code = 'pageAccessTypes' AND lookup_code = 'mobile_and_web');

-- Add new style entryList
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('entryList', '2', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Wrap other styles that later visualize list of entries (inserted via `formUserInput`).');

-- Add new field type `select-formName` and field `formName` in style entryList
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-formName', '8');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'formName', get_field_type_id('select-formName'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('entryList'), get_field_id('formName'), '', 'Select a form name which will be linked to the style');

-- add field children to style entryList
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryList'), get_field_id('children'), 0, 'Children that can be added to the style. It is used to design how the entry in the list will looks like.');
-- add field css to style entryList
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryList'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');

-- Add new style entryRecord
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('entryRecord', '2', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Wrap other styles that later visualize a record from the entry list');
-- Add field `formName` in style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('entryRecord'), get_field_id('formName'), '', 'Select a form name which will be linked to the style');
-- add field children to style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecord'), get_field_id('children'), 0, 'Children that can be added to the style. It is used to design how the entry will looks like.');
-- add field css to style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecord'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');

-- Crate new style group
INSERT INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES (NULL, 'Mobile', 'Styles that are only used by the mobile application', '79');

-- Add new style calendar
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('calendar', '1', (select id from styleGroup where `name` = 'Mobile' limit 1), 'Calendar style');
-- add field css to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');
-- add field config to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('config'), NULL, 'Define the configuration of the calendar. Refer to the documentation of [Ionic2-Calendar](https://github.com/twinssbc/Ionic2-Calendar) for more information');
-- add field title to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('title'), NULL, 'Title of the calendar component');
-- add new field label_month
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_month', get_field_type_id('text'), '1');
-- add new field label_week
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_week', get_field_type_id('text'), '1');
-- add new field label_day
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_day', get_field_type_id('text'), '1');
-- add field label_month to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('label_month'), NULL, 'Label for the month button');
-- add field label_week to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('label_week'), NULL, 'Label for the week button');
-- add field label_day to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('label_day'), NULL, 'Label for the day button');

-- Add new field `icon` in style tabs
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'icon', get_field_type_id('text'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('tab'), get_field_id('icon'), '', 'Show icon; For web font awsome icons are used; For mobile ionicicons are used.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_address', get_field_type_id('text'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('email_address'), '@email_user', 'Use `@email_user` to retrive automaticaly the user email. Emails are separated by the MAIL_SEPARATOR. It is `;`');

-- Add new field `own_entries_only` in style entryList
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'own_entries_only', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('entryList'), get_field_id('own_entries_only'), '1', 'If selected the entry list will load only the records entered by the user.');

-- Add new field `own_entries_only` in style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('entryRecord'), get_field_id('own_entries_only'), '1', 'If selected the entry list will load only the records entered by the user.');

-- Add new field `own_entries_only` in style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('own_entries_only'), '1', 'If selected the entry list will load only the records entered by the user.');

-- enable field Ajax for formUserInput
update styles_fields
set disabled = 0
where id_fields = get_field_id('ajax') and id_styles = get_style_id('formUserInput');

-- Add new field type `select-platform` and field `platform` in style conditionalCointainer
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-platform', '8');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'platform', get_field_type_id('select-platform'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('conditionalContainer'), get_field_id('platform'), 'mobile_and_web', 'Select for which platform the conditional container will be loaded');

-- Add field 'gender_divers' in style valdiate
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'gender_divers', get_field_type_id('text'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('validate'), get_field_id('gender_divers'), 'divers', 'The label next to the divers radio button option.');

DROP VIEW IF EXISTS view_cmsPreferences;
CREATE VIEW view_cmsPreferences
AS
SELECT p.callback_api_key, p.default_language_id, l.language as default_language, l.locale, p.fcm_api_key, p.fcm_sender_id
FROM cmsPreferences p
LEFT JOIN languages l ON (l.id = p.default_language_id)
WHERE p.id = 1;
drop view if exists view_fields;
create view view_fields
as
select cast(f.id as unsigned) as field_id, f.name as field_name, f.display, cast(ft.id as unsigned) as field_type_id, ft.name as field_type, ft.position
from fields f
left join fieldType ft on (f.id_type = ft.id);
drop view if exists view_styles;
create view view_styles
as
select cast(s.id as unsigned) as style_id, s.name as style_name, s.description as style_description,
cast(st.id as unsigned) as style_type_id, st.name as style_type, cast(sg.id as unsigned) as style_group_id,
sg.name as style_group, sg.description as style_group_description, sg.position as style_group_position
from styles s
left join styleType st on (s.id_type = st.id)
left join styleGroup sg on (s.id_group = sg.id);
drop view if exists view_style_fields;
create view view_style_fields
as
select s.style_id, s.style_name, s.style_type, s.style_group, f.field_id, f.field_name, f.field_type, f.display, f.position, 
sf.default_value, sf.help
from view_styles s
left join styles_fields sf on (s.style_id = sf.id_styles)
left join view_fields f on (f.field_id = sf.id_fields);
drop view if exists view_user_input;
create view view_user_input
as
select cast(ui.id as unsigned) as id, cast(u.id as unsigned) as user_id, u.name as user_name, vc.code as user_code, cast(form.id as unsigned) form_id, sft_if.content as form_name, cast(field.id as unsigned) as field_id, 
sft_in.content as field_name, ui.value, record.id as record_id, ui.edit_time, ui.removed
from user_input ui
left join users u on (ui.id_users = u.id)
left join validation_codes vc on (ui.id_users = vc.id_users)
left join sections field on (ui.id_sections = field.id)
left join sections form  on (ui.id_section_form = form.id)
left join user_input_record record  on (ui.id_user_input_record = record.id)
LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57;
DELIMITER //
DROP FUNCTION IF EXISTS get_field_type_id //

CREATE FUNCTION get_field_type_id(field_type varchar(100)) RETURNS INT
BEGIN 
	DECLARE field_type_id INT;    
	SELECT id INTO field_type_id
	FROM fieldType
	WHERE name = field_type COLLATE utf8_unicode_ci;
    RETURN field_type_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_field_id //

CREATE FUNCTION get_field_id(field varchar(100)) RETURNS INT
BEGIN 
	DECLARE field_id INT;    
	SELECT id INTO field_id
	FROM fields
	WHERE name = field COLLATE utf8_unicode_ci;
    RETURN field_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_style_id //

CREATE FUNCTION get_style_id(style varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_id INT;    
	SELECT id INTO style_id
	FROM styles
	WHERE name = style COLLATE utf8_unicode_ci;
    RETURN style_id;
END
//

DELIMITER ;
DELIMITER //
DROP FUNCTION IF EXISTS get_style_group_id //

CREATE FUNCTION get_style_group_id(style_group varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_group_id INT;    
	SELECT id INTO style_group_id
	FROM styleGroup
	WHERE name = style_group COLLATE utf8_unicode_ci;
    RETURN style_group_id;
END
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data //

CREATE PROCEDURE get_form_data( form_id_param INT )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when field_name = "',
		  field_name,
		  '" then value end) as `',
		  replace(field_name, ' ', ''), '`'
		)
	  ) INTO @sql
	from view_user_input
    where form_id = form_id_param;
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select user_id, form_name, edit_time, user_code, ', @sql, ' , removed as deleted from view_user_input
		where form_id = ', form_id_param,
		' group by user_id, form_name, edit_time, user_code, removed');

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_for_user //

CREATE PROCEDURE get_form_data_for_user( form_id_param INT, user_id_param INT )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when field_name = "',
		  field_name,
		  '" then value end) as `',
		  replace(field_name, ' ', ''), '`'
		)
	  ) INTO @sql
	from view_user_input
    where form_id = form_id_param;
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select user_id, form_name, edit_time, user_name, user_code, ', @sql, ' , removed as deleted from view_user_input
		where form_id = ', form_id_param, ' and user_id = ', user_id_param,
		' group by user_id, form_name, user_name, edit_time, user_code, removed');

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;
drop view if exists view_uploadTables;
create view view_uploadTables
as
select t.id as table_id, r.id as row_id, r.timestamp as entry_date, col.id as col_id, t.name as table_name, col.name as col_name, cell.value as value, t.timestamp
from uploadTables t
inner join uploadRows r on (t.id = r.id_uploadTables)
inner join uploadCells cell on (cell.id_uploadRows = r.id)
inner join uploadCols col on (col.id = cell.id_uploadCols);
drop view if exists view_form;
create view view_form
as
select distinct cast(form.id as unsigned) form_id, sft_if.content as form_name
from user_input ui
left join sections form  on (ui.id_section_form = form.id)
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57;
drop view if exists view_data_tables;
create view view_data_tables
as
select 'dynamic' as type, form_id as id, form_name as orig_name, concat(form_name, '_dynamic') as table_name
from view_form

union

select 'static' as type, id as id, name as orig_name, concat(name, '_static') as table_name
from uploadTables;
DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable_with_filter //

CREATE PROCEDURE get_uploadTable_with_filter( table_id_param INT, filter_param VARCHAR(1000) )
BEGIN
    SET @@group_concat_max_len = 32000;
    SET @sql = NULL;
    SELECT
    GROUP_CONCAT(DISTINCT
        CONCAT(
            'max(case when col_name = "',
                col_name,
                '" then value end) as `',
            replace(col_name, ' ', ''), '`'
        )
    ) INTO @sql
    FROM view_uploadTables
    WHERE table_id = table_id_param;

    IF (@sql is null) THEN
        SELECT table_name from view_uploadTables where 1=2;
    ELSE
        BEGIN
            SET @sql = CONCAT('select table_name, timestamp, row_id, entry_date, ', @sql, ' from view_uploadTables t
                where table_id = ', table_id_param,
                ' group by table_name, timestamp, row_id HAVING 1 ', filter_param);
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
DELIMITER //

DROP PROCEDURE IF EXISTS get_uploadTable //

CREATE PROCEDURE get_uploadTable( table_id_param INT )
BEGIN
    CALL get_uploadTable_with_filter(table_id_param, '');
END
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS proc_register_module //

CREATE PROCEDURE proc_register_module( 
	p_module_name VARCHAR(500), 
    p_page_name VARCHAR(100), 
    p_enabled INT )
-- send module name, page name and enabled disabled;
-- if module does not exists, it will be created, then the page will be added to the module if it exists. First we check if the page exist, if it doesnt exist we throw error. 
-- enabled is assigned to the module.
BEGIN
	SET @page_id = NULL;
    SET @module_id = NULL;
    SET @result = '';
	SELECT id INTO @page_id FROM pages WHERE keyword = p_page_name COLLATE utf8_unicode_ci;
    
    IF (@page_id IS NULL) THEN
		SET @result = CONCAT('Page name ', p_page_name, ' does not exist;');
	ELSE

		SELECT id INTO @module_id FROM modules WHERE module_name = p_module_name COLLATE utf8_unicode_ci;
		IF (@module_id IS NULL) THEN
			INSERT INTO modules (module_name, enabled) VALUES (p_module_name, p_enabled); 
			SET @module_id = LAST_INSERT_ID();
            SET @result = CONCAT(@result, 'Module ', p_module_name, ' was created!;');            
		ELSE
			UPDATE modules SET enabled = p_enabled WHERE id = @module_id;
            SET @result = CONCAT(@result, 'The status enabled of Module ', p_module_name, ' was was changed to ', p_enabled, ';');
            
		END IF;
        INSERT INTO modules_pages (id_modules, id_pages) VALUES (@module_id, @page_id); 
		SET @result = CONCAT(@result, 'Page ', p_page_name, ' was added to module ', p_module_name);
        
	END IF;
    
    SELECT @result AS result;

END
//

DELIMITER ;
DROP VIEW IF EXISTS view_qualtricsSurveys;
CREATE VIEW view_qualtricsSurveys
AS
SELECT s.*, typ.lookup_value as survey_type, typ.lookup_code as survey_type_code
FROM qualtricsSurveys s 
INNER JOIN lookups typ ON (typ.id = s.id_qualtricsSurveyTypes);
DROP VIEW IF EXISTS view_acl_groups_pages_modules;
CREATE VIEW view_acl_groups_pages_modules
AS
SELECT acl.id_groups, acl.id_pages, 
CASE
	WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
	ELSE acl.acl_select
END AS acl_select, 
acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
FROM acl_groups acl
INNER JOIN pages p ON (acl.id_pages = p.id or (p.id_type = 4 and acl.id_pages = null)) -- add all open pages although that there is no specific ACL
LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
LEFT JOIN modules m ON (m.id = mp.id_modules)
GROUP BY acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
DROP VIEW IF EXISTS view_acl_users_pages_modules;
CREATE VIEW view_acl_users_pages_modules
AS
SELECT acl.id_users, acl.id_pages, 
CASE
	WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
	ELSE acl.acl_select
END AS acl_select, 
acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
FROM acl_users acl
INNER JOIN pages p ON (acl.id_pages = p.id)
LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
LEFT JOIN modules m ON (m.id = mp.id_modules)
GROUP BY acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
DROP VIEW IF EXISTS view_qualtricsActions;
CREATE VIEW view_qualtricsActions
AS
SELECT st.id as id, st.name as action_name, st.id_qualtricsProjects as project_id, p.name as project_name, p.qualtrics_api, s.participant_variable, p.api_mailing_group_id,
st.id_qualtricsSurveys as survey_id, s.qualtrics_survey_id, s.name as survey_name, s.id_qualtricsSurveyTypes, s.group_variable, typ.lookup_value as survey_type, typ.lookup_code as survey_type_code,
id_qualtricsProjectActionTriggerTypes, trig.lookup_value as trigger_type, trig.lookup_code as trigger_type_code,
GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS `groups`, 
GROUP_CONCAT(DISTINCT g.id*1 SEPARATOR ', ') AS id_groups, 
GROUP_CONCAT(DISTINCT l.lookup_value SEPARATOR '; ') AS functions,
GROUP_CONCAT(DISTINCT l.lookup_code SEPARATOR ';') AS functions_code,
GROUP_CONCAT(DISTINCT l.id SEPARATOR '; ') AS id_functions,
schedule_info, st.id_qualtricsActionScheduleTypes, action_type.lookup_code as action_schedule_type_code, action_type.lookup_value as action_schedule_type, id_qualtricsSurveys_reminder, 
CASE 
	WHEN action_type.lookup_value = 'Reminder' THEN s_reminder.name 
    ELSE NULL
END as survey_reminder_name, st.id_qualtricsActions
FROM qualtricsActions st 
INNER JOIN qualtricsProjects p ON (st.id_qualtricsProjects = p.id)
INNER JOIN qualtricsSurveys s ON (st.id_qualtricsSurveys = s.id)
INNER JOIN lookups typ ON (typ.id = s.id_qualtricsSurveyTypes)
INNER JOIN lookups trig ON (trig.id = st.id_qualtricsProjectActionTriggerTypes)
INNER JOIN lookups action_type ON (action_type.id = st.id_qualtricsActionScheduleTypes)
LEFT JOIN qualtricsSurveys s_reminder ON (st.id_qualtricsSurveys_reminder = s_reminder.id)
LEFT JOIN qualtricsActions_groups sg on (sg.id_qualtricsActions = st.id)
LEFT JOIN groups g on (sg.id_groups = g.id)
LEFT JOIN qualtricsActions_functions f on (f.id_qualtricsActions = st.id)
LEFT JOIN lookups l on (f.id_lookups = l.id)
GROUP BY st.id, st.name, st.id_qualtricsProjects, p.name,
st.id_qualtricsSurveys, s.name, s.id_qualtricsSurveyTypes, typ.lookup_value, 
id_qualtricsProjectActionTriggerTypes, trig.lookup_value;
DROP VIEW IF EXISTS view_transactions;
CREATE VIEW view_transactions
AS
SELECT t.id, t.transaction_time, t.id_transactionTypes, tran_type.lookup_value AS transaction_type,
id_transactionBy, tran_by.lookup_value AS transaction_by, id_users, u.name AS user_name,
table_name, id_table_name, REPLACE(JSON_EXTRACT(transaction_log, '$.verbal_log'), '"', '') AS transaction_verbal_log
FROM transactions t
INNER JOIN lookups tran_type ON (tran_type.id = t.id_transactionTypes)
INNER JOIN lookups tran_by ON (tran_by.id = t.id_transactionBy)
LEFT JOIN users u ON (u.id = t.id_users);
DROP VIEW IF EXISTS view_users;
CREATE VIEW view_users
AS
SELECT u.id, u.email, u.name, u.last_login, us.name AS status,
us.description, u.blocked, vc.code,
GROUP_CONCAT(DISTINCT g.id*1 SEPARATOR ', ') AS groups_ids,
GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS `groups`,
GROUP_CONCAT(DISTINCT ch.name SEPARATOR '; ') AS chat_rooms_names
FROM users AS u
LEFT JOIN userStatus AS us ON us.id = u.id_status
LEFT JOIN users_groups AS ug ON ug.id_users = u.id
LEFT JOIN groups g ON g.id = ug.id_groups
LEFT JOIN chatRoom_users chu ON u.id = chu.id_users
LEFT JOIN chatRoom ch ON ch.id = chu.id_chatRoom
LEFT JOIN validation_codes vc ON u.id = vc.id_users
WHERE u.intern <> 1 AND u.id_status > 0
GROUP BY u.id, u.email, u.name, u.last_login, us.name, us.description, u.blocked, vc.code
ORDER BY u.email;
DROP VIEW IF EXISTS view_acl_users_in_groups_pages_modules;
CREATE VIEW view_acl_users_in_groups_pages_modules
AS
SELECT ug.id_users, acl.id_pages, MAX(IFNULL(acl.acl_select, 0)) as acl_select, MAX(IFNULL(acl.acl_insert, 0)) as acl_insert,
MAX(IFNULL(acl.acl_update, 0)) as acl_update, MAX(IFNULL(acl.acl_delete, 0)) as acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
FROM users u
INNER JOIN users_groups AS ug ON (ug.id_users = u.id)
INNER  JOIN acl_groups acl ON (acl.id_groups = ug.id_groups)
INNER JOIN pages p ON (acl.id_pages = p.id)
LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
LEFT JOIN modules m ON (m.id = mp.id_modules)
GROUP BY ug.id_users, acl.id_pages, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
DROP VIEW IF EXISTS view_acl_users_union;
CREATE VIEW view_acl_users_union
AS
SELECT *
FROM view_acl_users_in_groups_pages_modules

UNION 

SELECT *
FROM view_acl_users_pages_modules;
DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_for_user_with_filter //

CREATE PROCEDURE get_form_data_for_user_with_filter( form_id_param INT, user_id_param INT, filter_param VARCHAR(1000) )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when field_name = "',
		  field_name,
		  '" then value end) as `',
		  replace(field_name, ' ', ''), '`'
		)
	  ) INTO @sql
	from view_user_input
    where form_id = form_id_param;
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select user_id, form_name, max(edit_time) as edit_time, record_id, user_name, user_code, ', @sql, ' , removed as deleted from view_user_input
		where form_id = ', form_id_param, ' and user_id = ', user_id_param,
		' group by user_id, form_name, user_name, record_id, user_code, removed HAVING 1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_group_acl //

CREATE PROCEDURE get_group_acl( param_group_id INT, param_page_id INT ) # when page_id is -1 then all pages
BEGIN

    SELECT acl.id_groups, acl.id_pages, 
	CASE
		WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
		ELSE acl.acl_select
	END AS acl_select, 
	acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
	FROM acl_groups acl
	INNER JOIN pages p ON (acl.id_pages = p.id or (p.id_type = 4 and acl.id_pages = null)) -- add all open pages although that there is no specific ACL
	LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
	LEFT JOIN modules m ON (m.id = mp.id_modules)
    WHERE acl.id_groups = param_group_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type
    HAVING enabled = 1;
    
END
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_navigation //

CREATE PROCEDURE get_navigation( param_locale VARCHAR(10) ) # when page_id is -1 then all pages
BEGIN

    SELECT Json_arrayagg(Json_object(keyword, (SELECT 
						 Json_object('id_navigation_section' 
						 , 
						 p.id_navigation_section, 'title', 
						 pft.content, 'children', (SELECT 
						 Json_arrayagg( 
						 Json_object(keyword, (SELECT 
												 Json_object('id_navigation_section' 
												 , 
												 p2.id_navigation_section, 'title', 
												 pft2.content, 'children', NULL)))) 
						 AS items 
												 FROM   pages AS p2 
												 LEFT JOIN pages_fields_translation 
														   AS pft2 
												 ON pft2.id_pages = p2.id 
												 LEFT JOIN languages AS l2 
												 ON l2.id = pft2.id_languages 
												 LEFT JOIN fields AS f2 
												 ON f2.id = pft2.id_fields 
												 WHERE  p2.parent = p.id 
												 AND ( l.locale = param_locale 
												 OR l.locale = 'all' ) 
												 AND f2.NAME = 'label' 
												 AND p2.nav_position IS NOT NULL 
												 ORDER  BY p2.nav_position ASC))))) AS 
		   pages 
	FROM   pages AS p 
		   LEFT JOIN pages_fields_translation AS pft 
				  ON pft.id_pages = p.id 
		   LEFT JOIN languages AS l 
				  ON l.id = pft.id_languages 
		   LEFT JOIN fields AS f 
				  ON f.id = pft.id_fields 
	WHERE  p.nav_position IS NOT NULL 
		   AND ( l.locale = param_locale 
				  OR l.locale = 'all' ) 
		   AND f.NAME = 'label' 
		   AND p.parent IS NULL 
ORDER  BY p.nav_position DESC;
    
END
//

DELIMITER ;
DELIMITER //

DROP PROCEDURE IF EXISTS get_user_acl //

CREATE PROCEDURE get_user_acl( param_user_id INT, param_page_id INT ) # when page_id is -1 then all pages
BEGIN

    SELECT ug.id_users, acl.id_pages, MAX(IFNULL(acl.acl_select, 0)) as acl_select, MAX(IFNULL(acl.acl_insert, 0)) as acl_insert,
	MAX(IFNULL(acl.acl_update, 0)) as acl_update, MAX(IFNULL(acl.acl_delete, 0)) as acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
	FROM users u
	INNER JOIN users_groups AS ug ON (ug.id_users = u.id)
	INNER  JOIN acl_groups acl ON (acl.id_groups = ug.id_groups)
	INNER JOIN pages p ON (acl.id_pages = p.id)
	LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
	LEFT JOIN modules m ON (m.id = mp.id_modules)
	WHERE ug.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY ug.id_users, acl.id_pages, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type
    HAVING enabled = 1
    
    UNION 
    
    SELECT acl.id_users, acl.id_pages, 
	CASE
		WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
		ELSE acl.acl_select
	END AS acl_select, 
	acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
	FROM acl_users acl
	INNER JOIN pages p ON (acl.id_pages = p.id)
	LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
	LEFT JOIN modules m ON (m.id = mp.id_modules)
    WHERE acl.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type
    HAVING enabled = 1;
    
END
//

DELIMITER ;
DROP VIEW IF EXISTS view_users;
CREATE VIEW view_users
AS
SELECT u.id, u.email, u.name, 
IFNULL(CONCAT(u.last_login, ' (', DATEDIFF(NOW(), u.last_login), ' days ago)'), 'never') AS last_login, 
us.name AS status,
us.description, u.blocked, ifnull(vc.code, '-') AS code,
GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS `groups`,
(SELECT COUNT(*) AS activity FROM user_activity WHERE user_activity.id_users = u.id) AS user_activity,
(SELECT COUNT(DISTINCT url) FROM user_activity WHERE user_activity.id_users = u.id AND id_type = 1) as ac,
u.intern
FROM users AS u
LEFT JOIN userStatus AS us ON us.id = u.id_status
LEFT JOIN users_groups AS ug ON ug.id_users = u.id
LEFT JOIN groups g ON g.id = ug.id_groups
LEFT JOIN validation_codes vc ON u.id = vc.id_users
WHERE u.intern <> 1 AND u.id_status > 0
GROUP BY u.id, u.email, u.name, u.last_login, us.name, us.description, u.blocked, vc.code, user_activity
ORDER BY u.email;
DROP VIEW IF EXISTS view_sections_fields;
CREATE VIEW view_sections_fields
AS
SELECT
   s.id AS id_sections,
   s.name AS section_name,
   sft.content,
   s.id_styles,
   fields.style_name,
   field_id AS id_fields,
   field_name,
   l.locale,
   g.name AS gender 
FROM sections s 
LEFT JOIN view_style_fields fields ON (fields.style_id = s.id_styles) 
LEFT JOIN sections_fields_translation sft ON (sft.id_sections = s.id AND sft.id_fields = fields.field_id) 
LEFT JOIN languages l ON (sft.id_languages = l.id) 
LEFT JOIN genders g ON (sft.id_genders = g.id);
DROP VIEW IF EXISTS view_scheduledJobs;
CREATE VIEW view_scheduledJobs
AS
SELECT sj.id AS id, l_status.lookup_code AS status_code, l_status.lookup_value AS status, l_types.lookup_code AS type_code, l_types.lookup_value AS type, sj.config,
sj.date_create, date_to_be_executed, date_executed, description, 
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.recipient_emails
    WHEN l_types.lookup_code = 'notification' THEN (SELECT GROUP_CONCAT(DISTINCT u.name SEPARATOR '; ') FROM scheduledJobs_users sj_u INNER JOIN users u on (u.id = sj_u.id_users) WHERE id_scheduledJobs = sj.id)
    WHEN l_types.lookup_code = 'task' THEN (SELECT GROUP_CONCAT(DISTINCT u.name SEPARATOR '; ') FROM scheduledJobs_users sj_u INNER JOIN users u on (u.id = sj_u.id_users) WHERE id_scheduledJobs = sj.id)
    ELSE ""
END AS recipient,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.subject
    WHEN l_types.lookup_code = 'notification' THEN n.subject
    ELSE ""
END AS title,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.body
    WHEN l_types.lookup_code = 'notification' THEN n.body
    ELSE ""
END AS message,
sj_mq.id_mailQueue,
id_jobTypes,
id_jobStatus
FROM scheduledJobs sj
INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus)
INNER JOIN lookups l_types ON (l_types.id = sj.id_jobTypes)
LEFT JOIN scheduledJobs_mailQueue sj_mq on (sj_mq.id_scheduledJobs = sj.id)
LEFT JOIN mailQueue mq on (mq.id = sj_mq.id_mailQueue)
LEFT JOIN scheduledJobs_notifications sj_n on (sj_n.id_scheduledJobs = sj.id)
LEFT JOIN notifications n on (n.id = sj_n.id_notifications);
DROP VIEW IF EXISTS view_scheduledJobs_transactions;
CREATE VIEW view_scheduledJobs_transactions
AS
SELECT sj.id, sj.date_create, date_to_be_executed, date_executed, t.id AS transaction_id, transaction_time, 
transaction_type, transaction_by, user_name, transaction_verbal_log
FROM scheduledJobs sj
INNER JOIN view_transactions t ON (t.table_name = 'scheduledJobs' AND t.id_table_name = sj.id)
ORDER BY sj.id ASC, t.id ASC;
DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT sj.id AS id, from_email, from_name,
status_code, status, type_code, type, 
sj.date_create, date_to_be_executed, date_executed,
reply_to, recipient_emails, cc_emails, bcc_emails, subject, body, is_html, mq.id as id_mailQueue, id_jobTypes,
id_jobStatus
FROM mailQueue mq
INNER JOIN scheduledJobs_mailQueue sj_mq ON (sj_mq.id_mailQueue = mq.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_mq.id_scheduledJobs);
DROP VIEW IF EXISTS view_qualtricsReminders;
CREATE VIEW view_qualtricsReminders
AS
SELECT u.id as user_id, u.email, u.name AS user_name, code, sj.id AS id_scheduledJobs,
sj.status_code as status_code, sj.status AS status, r.id_qualtricsSurveys AS id_qualtricsSurveys, s.qualtrics_survey_id,
qa.id_qualtricsActions,
	(SELECT sess.date_to_be_executed 
	FROM scheduledJobs sess 
    INNER JOIN scheduledJobs_qualtricsActions sj_qa2 on (sj_qa2.id_scheduledJobs = sess.id)
	INNER JOIN qualtricsActions qa2 ON (qa2.id = sj_qa2.id_qualtricsActions)
    WHERE qa2.id = qa.id_qualtricsActions 
    ORDER BY sess.date_to_be_executed DESC
    LIMIT 0, 1) AS session_start_date,
(SELECT CAST(JSON_EXTRACT(qa2.schedule_info, '$.valid') AS UNSIGNED)
FROM qualtricsActions qa2
WHERE qa2.id = qa.id_qualtricsActions) AS valid,
(SELECT sess.date_to_be_executed 
	FROM scheduledJobs sess 
    INNER JOIN scheduledJobs_qualtricsActions sj_qa2 on (sj_qa2.id_scheduledJobs = sess.id)
	INNER JOIN qualtricsActions qa2 ON (qa2.id = sj_qa2.id_qualtricsActions)
    WHERE qa2.id = qa.id_qualtricsActions 
    ORDER BY sess.date_to_be_executed DESC
    LIMIT 0, 1) + INTERVAL (SELECT CAST(JSON_EXTRACT(qa2.schedule_info, '$.valid') AS UNSIGNED)
FROM qualtricsActions qa2
WHERE qa2.id = qa.id_qualtricsActions) MINUTE AS valid_till
FROM qualtricsReminders r
INNER JOIN view_users u ON (u.id = r.id_users)
INNER JOIN qualtricsSurveys s ON (s.id = r.id_qualtricsSurveys)
LEFT JOIN view_scheduledJobs sj ON (sj.id = r.id_scheduledJobs) 
LEFT JOIN scheduledJobs_qualtricsActions sj_qa on (sj_qa.id_scheduledJobs = sj.id)
LEFT JOIN qualtricsActions qa ON (qa.id = sj_qa.id_qualtricsActions);
DROP VIEW IF EXISTS view_notifications;
CREATE VIEW view_notifications
AS
SELECT sj.id AS id,
status_code, status, type_code, type, 
sj.date_create, date_to_be_executed, date_executed,
recipient, subject, body, url, id_notifications, id_jobTypes,
id_jobStatus
FROM notifications n
INNER JOIN scheduledJobs_notifications sj_n ON (sj_n.id_notifications = n.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_n.id_scheduledJobs);
DROP VIEW IF EXISTS view_tasks;
CREATE VIEW view_tasks
AS
SELECT sj.id AS id,
status_code, status, type_code, type, 
sj.date_create, date_to_be_executed, date_executed,
recipient, t.config, id_tasks, id_jobTypes, id_jobStatus, description
FROM tasks t
INNER JOIN scheduledJobs_tasks sj_t ON (sj_t.id_tasks = t.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_t.id_scheduledJobs);
DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_with_filter //

CREATE PROCEDURE get_form_data_with_filter( form_id_param INT, filter_param VARCHAR(1000) )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when field_name = "',
		  field_name,
		  '" then value end) as `',
		  replace(field_name, ' ', ''), '`'
		)
	  ) INTO @sql
	from view_user_input
    where form_id = form_id_param;
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select user_id, form_name, max(edit_time) as edit_time, record_id, user_name, user_code, ', @sql, ' , removed as deleted from view_user_input
		where form_id = ', form_id_param, ' group by user_id, form_name, user_name, record_id, user_code, removed HAVING 1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;
