-- set DB version
UPDATE version
SET version = 'v3.4.0';

-- add keyword cmsExport
INSERT INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'cmsExport', '/admin/cms_export/[page|section:type]/[i:id]', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');

SET @id_page_data = LAST_INSERT_ID();

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) 
VALUES ('0000000001', @id_page_data, '1', '0', '0', '0');
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES (@id_page_data, '0000000008', '0000000001', 'CMS Export');
