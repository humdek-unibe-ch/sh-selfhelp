-- add qualtricsSurveyTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('qualtricsSurveyTypes', 'anonymous', 'Anonymous', 'Anonymous survey. No code or user is used.');

-- add qualtricsProjectActionAdditionalFunction
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('qualtricsProjectActionAdditionalFunction', 'bmz_evaluate_motive', '[BMZ] Evaluate motive', 'Function that will evaluate the motive and genrate PDF file as a feedback');

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
END as survey_reminder_name
FROM qualtricsActions st 
INNER JOIN qualtricsProjects p ON (st.id_qualtricsProjects = p.id)
INNER JOIN qualtricsSurveys s ON (st.id_qualtricsSurveys = s.id)
INNER JOIN lookups typ ON (typ.id = s.id_qualtricsSurveyTypes)
INNER JOIN lookups trig ON (trig.id = st.id_qualtricsProjectActionTriggerTypes)
INNER JOIN lookups action_type ON (action_type.id = st.id_qualtricsActionScheduleTypes)
LEFT JOIN qualtricsSurveys s_reminder ON (st.id_qualtricsSurveys_reminder = s_reminder.id)
LEFT JOIN qualtricsActions_groups sg on (sg.id_qualtricsActions = st.id)
LEFT JOIN `groups` g on (sg.id_groups = g.id)
LEFT JOIN qualtricsActions_functions f on (f.id_qualtricsActions = st.id)
LEFT JOIN lookups l on (f.id_lookups = l.id)
GROUP BY st.id, st.name, st.id_qualtricsProjects, p.name,
st.id_qualtricsSurveys, s.name, s.id_qualtricsSurveyTypes, typ.lookup_value, 
id_qualtricsProjectActionTriggerTypes, trig.lookup_value;

DROP VIEW IF EXISTS view_qualtricsSurveys;
CREATE VIEW view_qualtricsSurveys
AS
SELECT s.*, typ.lookup_value as survey_type, typ.lookup_code as survey_type_code
FROM qualtricsSurveys s 
INNER JOIN lookups typ ON (typ.id = s.id_qualtricsSurveyTypes);

-- add json field named 'data_config' in style markdown
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'data_config', get_field_type_id('json'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('markdown'), get_field_id('data_config'), '', 
'In this ***JSON*** field we can configure a data retrieve params from the DB, either `static` or `dynamic` data. Example: 
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

We can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;)');

-- change markdown style from view to component
update styles
set id_type = '0000000002'
WHERE name = 'markdown';

-- procedure get_form_data_for_user_with_filter
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
		SET @sql = CONCAT('select user_id, form_name, edit_time, user_name, user_code, ', @sql, ' , removed as deleted from view_user_input
		where form_id = ', form_id_param, ' and user_id = ', user_id_param,
		' group by user_id, form_name, user_name, edit_time, user_code, removed HAVING 1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

-- add export_pdf field in style container
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'export_pdf', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('container'), get_field_id('export_pdf'), 0, 
'If `export_pdf` is checked, the container has an export button in the top righ corner. All children in the container can be exported to a PDF file.

add class `skipPDF` to the `css` field in an element which should not be exported inthe PDF file

add class `pdfStartNewPage` to the `css` field in an element which should be on a new page

add class `pdfStartNewPageAfter` to the `css` field in an element which should insert a new page after it is loaded on the page
');

-- Add new style search
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('search', '2', (select id from styleGroup where `name` = 'Input' limit 1), 'Add search input box. Used for pages that accept additional paramter. On click the text is assigned in the url and it can be used as a parameter');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('search'), get_field_id('label'), '', 'Label for the button');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('search'), get_field_id('placeholder'), '', 'Placeholder for the input field');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'prefix', get_field_type_id('text'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('search'), get_field_id('prefix'), '', 'Add prefix to the search text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'suffix', get_field_type_id('text'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('search'), get_field_id('suffix'), '', 'Add suffix to the search text');

-- add field data_config in graph style
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('graph'), get_field_id('data_config'), '', 
'In this ***JSON*** field we can configure a data retrieve params from the DB, either `static` or `dynamic` data. Example: 
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

We can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;)');

-- add column config in qualtricsSurvey, it keeps a JSON configuration
ALTER TABLE qualtricsSurveys
ADD COLUMN config LONGTEXT;

DROP VIEW IF EXISTS view_qualtricsSurveys;
CREATE VIEW view_qualtricsSurveys
AS
SELECT s.*, typ.lookup_value as survey_type, typ.lookup_code as survey_type_code
FROM qualtricsSurveys s 
INNER JOIN lookups typ ON (typ.id = s.id_qualtricsSurveyTypes);