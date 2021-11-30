-- set DB version
UPDATE version
SET version = 'v4.3.0';

UPDATE styles
SET id_group = 1
WHERE name = "formUserInput";

 -- add column hidden in table styles_fields
 ALTER TABLE styles_fields 
 ADD COLUMN hidden INT DEFAULT 0;

-- add style formUserInputLog
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('formUserInputLog', '2', (select id from styleGroup where `name` = 'Form' limit 1), ' stores the data from all child input fields into the database. All data is entered as a log');

-- add fields to formUserInputLog, copy them from formUserInput
INSERT INTO styles_fields (id_styles, id_fields, default_value, help)
SELECT get_style_id('formUserInputLog'), id_fields, default_value, help
FROM styles_fields
WHERE id_styles = get_style_id('formUserInput') and id_fields <> get_field_id('is_log');

-- add field `is_log` to style `formUserInputLog`
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `hidden`, `help`) VALUES (get_style_id('formUserInputLog'), get_field_id('is_log'), 1, 1,'This fiels allows to control how the data is saved in the database:
 - `disabled`: The submission of data will always overwrite prior submissions of the same user. This means that the user will be able to continously update the data that was submitted here. Any input field that is used within this form will always show the current value stored in the database (if nothing has been submitted as of yet, the input field will be empty or set to a default).
 - `enabled`: Each submission will create a new entry in the database. Once entered, an entry cannot be removed or modified. Any input field within this form will always be empty or set to a default value (nothing will be read from the database).');
 
-- add style formUserInpuRecord
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('formUserInputRecord', '2', (select id from styleGroup where `name` = 'Form' limit 1), ' stores the data from all child input fields into the database. All data is entered as as a single row and it can be edited');

-- add fields to formUserInputLog, copy them from formUserInput
INSERT INTO styles_fields (id_styles, id_fields, default_value, help)
SELECT get_style_id('formUserInputRecord'), id_fields, default_value, help
FROM styles_fields
WHERE id_styles = get_style_id('formUserInput') and id_fields <> get_field_id('is_log');

-- add field `is_log` to style `formUserInputLog`
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `hidden`, `help`) VALUES (get_style_id('formUserInputRecord'), get_field_id('is_log'), 0, 1,'This fiels allows to control how the data is saved in the database:
 - `disabled`: The submission of data will always overwrite prior submissions of the same user. This means that the user will be able to continously update the data that was submitted here. Any input field that is used within this form will always show the current value stored in the database (if nothing has been submitted as of yet, the input field will be empty or set to a default).
 - `enabled`: Each submission will create a new entry in the database. Once entered, an entry cannot be removed or modified. Any input field within this form will always be empty or set to a default value (nothing will be read from the database).');
 
 -- add Forms Actions module page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'moduleFormsActions', '/admin/formsActions', 'GET|POST', '0000000002', NULL, '0000000009', '0', '91', NULL, '0000000001', (SELECT id FROM lookups WHERE type_code = "pageAccessTypes" AND lookup_code = "mobile_and_web"));
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Module Forms Actions');
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000054', '0000000001', '');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- add form action insert/update/select/delete
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'moduleFormsAction', '/admin/formsActions/[select|update|insert|delete:mode]?/[i:sid]?', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE type_code = "pageAccessTypes" AND lookup_code = "mobile_and_web"));
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Form Action');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '1', '1', '1');

-- register moduleFormsActions with page
call proc_register_module('moduleFormsActions', 'moduleFormsActions', 1);
call proc_register_module('moduleFormsActions', 'moduleFormsAction', 1);

-- add table formActions
CREATE TABLE `formActions` (
	`id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,	
	`id_forms` int(10) UNSIGNED ZEROFILL NOT NULL, 
	`name` varchar(200) NOT NULL,    
    `id_formProjectActionTriggerTypes` int(10 ) UNSIGNED ZEROFILL NOT NULL,
    `id_formActionScheduleTypes` int(10 ) UNSIGNED ZEROFILL NOT NULL,
    `id_forms_reminder` int(10) UNSIGNED ZEROFILL, 
    `schedule_info` text	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `formActions`
ADD CONSTRAINT `formActions_fk_id_forms` FOREIGN KEY (`id_forms`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `formActions_fk_id_form_reminder` FOREIGN KEY (`id_forms_reminder`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `formActions_fk_id_formActionScheduleTypes` FOREIGN KEY (`id_formActionScheduleTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `formActions_fk_id_lookups_formProjectActionTriggerType` FOREIGN KEY (`id_formProjectActionTriggerTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add table qformActions_groups
CREATE TABLE `formActions_groups` (	
	`id_formActions` int(10) UNSIGNED ZEROFILL NOT NULL,
	`id_groups` int(10) UNSIGNED ZEROFILL NOT NULL	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `formActions_groups`
ADD PRIMARY KEY (`id_formActions`,`id_groups`),
ADD KEY `id_formActions` (`id_formActions`),
ADD KEY `id_groups` (`id_groups`);

ALTER TABLE `formActions_groups`
ADD CONSTRAINT `formActions_groups_fk_id_formActions` FOREIGN KEY (`id_formActions`) REFERENCES `formActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `formActions_groups_fk_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add fields id_formActions to table formActions. It is used for linking reminders actions to notifications for forms with sessions and block shceduling
ALTER TABLE formActions
ADD COLUMN `id_formActions` INT(10) UNSIGNED ZEROFILL;

-- add table scheduledJobs_formActions
CREATE TABLE `scheduledJobs_formActions` (
  `id_scheduledJobs` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_formActions` INT(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY(id_scheduledJobs, id_formActions)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `scheduledJobs_formActions`
ADD CONSTRAINT `scheduledJobs_formActions_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `scheduledJobs_formActions_fk_iid_formActions` FOREIGN KEY (`id_formActions`) REFERENCES `formActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add table formActionsReminders
CREATE TABLE `formActionsReminders` (	
	`id_forms` int(10) UNSIGNED ZEROFILL NOT NULL, 
    `id_users` int(10) UNSIGNED ZEROFILL NOT NULL, 
    `id_scheduledJobs` int(10) UNSIGNED ZEROFILL NOT NULL	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `formActionsReminders`
  ADD PRIMARY KEY (`id_forms`,`id_users`, `id_scheduledJobs`);

ALTER TABLE `formActionsReminders`
ADD CONSTRAINT `formActionsReminders_fk_id_forms` FOREIGN KEY (`id_forms`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `formActionsReminders_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `formActionsReminders_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mailAttachments`
MODIFY `attachment_name` VARCHAR(1000); 

ALTER TABLE `mailAttachments` 
ALTER `template_path` SET DEFAULT '';

-- add field jquery_builder_json
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'jquery_builder_json', get_field_type_id('json'), '0');
-- add json field 'jquery_builder_json' in style conditionalContainer
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('conditionalContainer'), get_field_id('jquery_builder_json'), '', 'This field contains the JSON structure for the jquery builder. The field shoudl be hidden');

-- add json field 'data_config' in style conditionalContainer
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('conditionalContainer'), get_field_id('data_config'), '', 
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

In order to inlcude the retrieved data in the input `value`, include the `field_holder` that wa defined in the markdown text.

We can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;);

`It is used for prefil of the default value`');

-- Add new style conditionFailed
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('conditionFailed', '1', (select id from styleGroup where `name` = 'Wrapper' limit 1), '**This style should be used as a child in `conditionalContainer`.** Wrap other styles and later show them if the condtion is not met in a condtional container.');

-- add field children to style conditionFailed
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('conditionFailed'), get_field_id('children'), 0, 'Children that can be added to the style. Each child will be loaded as a page');
-- add field css to style conditionFailed
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('conditionFailed'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');

-- add exec_time column in user_activity

ALTER TABLE user_activity
ADD COLUMN exec_time DECIMAL(10,8);