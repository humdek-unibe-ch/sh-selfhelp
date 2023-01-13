-- set DB version
UPDATE version
SET version = 'v5.10.0';

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE lookup_code = 'web')
WHERE keyword = 'sh_globals';

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE lookup_code = 'web')
WHERE keyword = 'sh_modules';

SET @id_globals_page = (SELECT id FROM pages WHERE keyword = 'sh_globals');
SET @id_modules_page = (SELECT id FROM pages WHERE keyword = 'sh_modules');

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE lookup_code = 'web')
WHERE parent = @id_globals_page OR parent = @id_modules_page;

-- add page type global_values
INSERT IGNORE INTO `pageType` (`name`) VALUES ('sh_global_css');

SET @id_page_globals = (SELECT id FROM pages WHERE keyword = 'sh_globals');
-- add translation page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'sh_global_css', '/admin/global_css', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), NULL, @id_page_globals, 0, 5, NULL, (SELECT id FROM pageType WHERE `name` = 'sh_global_css' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'web'));
SET @id_page_values = (SELECT id FROM pages WHERE keyword = 'sh_global_css');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_values, '1', '0', '1', '0');

-- add new fieldType css
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'css', '15');

-- add new filed `custom_css` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'custom_css', get_field_type_id('css'), '0');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_global_css' LIMIT 0,1), get_field_id('custom_css'), NULL, 'Custom CSS raw code');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_global_css' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000001', 'Custom CSS');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000002', 'Custom CSS');


