-- set DB version
UPDATE version
SET version = 'v7.3.0';

-- add clockwork page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'clockwork', '/admin/clockwork', 'GET|POST', '0000000002', NULL, '0000000009', '0', '900', NULL, '0000000001', (SELECT id FROM lookups WHERE type_code = "pageAccessTypes" AND lookup_code = "web"));

SET @id_page = (SELECT id FROM pages WHERE keyword = 'clockwork');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('label'), '0000000002', 'Clockwork');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('title'), '0000000002', 'Clockwork');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '0', '0');



