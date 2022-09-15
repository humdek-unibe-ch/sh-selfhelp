-- set DB version
UPDATE version
SET version = 'v5.3.0';

-- add translation page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'selfhelpTranslations', '/admin/selfhelp_translations', 'GET|POST', '0000000002', NULL, '0000000009', '0', '12', NULL, '0000000001', (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page = (SELECT id FROM pages WHERE keyword = 'selfhelpTranslations');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('title'), '0000000001', 'Translations');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('title'), '0000000002', 'Translations');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');