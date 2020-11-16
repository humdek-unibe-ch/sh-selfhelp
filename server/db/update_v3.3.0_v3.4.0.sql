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
INNER JOIN sections_fields_translation sft ON (sft.id_sections = s.id) 
INNER JOIN view_style_fields fields ON (fields.style_id = s.id_styles) 
INNER JOIN languages l ON (sft.id_languages = l.id) 
INNER JOIN genders g ON (sft.id_genders = g.id);

-- add keyword cmsImport
INSERT INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'cmsImport', '/admin/cms_import/[page|section:type]', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');

SET @id_page_data = LAST_INSERT_ID();

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) 
VALUES ('0000000001', @id_page_data, '1', '0', '0', '0');
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES (@id_page_data, '0000000008', '0000000001', 'CMS Import');