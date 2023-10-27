-- set DB version
UPDATE version
SET version = 'v5.3.0';

UPDATE actions
SET `name` = 'backend'
WHERE `name` = 'custom';

CALL add_unique_key('pageType','pageType_name','name');

-- add page globals
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'sh_globals', '/admin/globals', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), NULL, NULL, '0', 0, NULL, (SELECT id FROM pageType WHERE `name` = 'core' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_globals = (SELECT id FROM pages WHERE keyword = 'sh_globals');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_globals, '1', '0', '1', '0');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_globals, get_field_id('title'), '0000000001', 'Globals');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_globals, get_field_id('title'), '0000000002', 'Globals');

-- add page type global_values
INSERT IGNORE INTO `pageType` (`name`) VALUES ('global_values');

-- add translation page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'sh_global_values', '/admin/global_values', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), NULL, @id_page_globals, '0', 0, NULL, (SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_values = (SELECT id FROM pages WHERE keyword = 'sh_global_values');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_values, '1', '0', '1', '0');

-- add new filed `selfhelpTranslations` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'global_values', get_field_type_id('json'), '1');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 0,1), get_field_id('global_values'), NULL, 'JSON object where can be defined global translation keys and use the key to load the proper translation based on the selected language. A key is accessed by {{key_name}}, and this will be replaced with the value for the selected language');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000001', 'Values');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000002', 'Values');

-- add page maintance to globasl group
UPDATE pages
SET parent = @id_page_globals, nav_position = 10
WHERE keyword = 'maintenance';

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'maintenance' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'maintenance'), get_field_id('title'), '0000000001', 'Maintenance');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'maintenance'), get_field_id('title'), '0000000002', 'Maintenance');

-- add page modules
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'sh_modules', '/admin/modules', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), NULL, NULL, '0', 10, NULL, (SELECT id FROM pageType WHERE `name` = 'core' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_modules= (SELECT id FROM pages WHERE keyword = 'sh_modules');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_modules, '1', '0', '1', '0');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_modules, get_field_id('title'), '0000000001', 'Modules');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_modules, get_field_id('title'), '0000000002', 'Modules');

-- add all modules to page modules
UPDATE pages
SET parent = @id_page_modules
WHERE keyword IN ('moduleFormsAction','moduleFormsActions','moduleQualtrics','moduleQualtricsProject','moduleQualtricsProjectAction','moduleQualtricsSurvey','moduleQualtricsSync','moduleScheduledJobs','moduleScheduledJobsCompose');

SET @id_page_email = (SELECT id FROM pages WHERE keyword = 'email');

-- add page type global_values
INSERT IGNORE INTO `pageType` (`name`) VALUES ('emails');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_activate'), NULL, 'Activation email text');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_reminder'), NULL, 'Reminder email text');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_subject'), NULL, 'Email subject text');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_activate_subject'), NULL, 'Email activate subject text');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('email_reminder_subject'), NULL, 'Email reminder subject text');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');

UPDATE pages_fields_translation
SET content = 'Email Templates'
WHERE id_pages = @id_page_email AND id_fields = get_field_id('title');

UPDATE `fields`
SET id_type = get_field_type_id('markdown')
WHERE `name` IN ('email_activate','email_reminder','email_subject','email_activate_subject','email_reminder_subject');

UPDATE pages
SET url = '/admin/email', id_actions = (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), nav_position = 20, parent = @id_page_globals, id_type = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 0,1)
WHERE keyword = 'email';

INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'dynamic_json', '15');