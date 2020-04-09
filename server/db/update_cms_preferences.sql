-- add cms prefeences page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'cmsPreferences', '/admin/cms_preferences', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', '1000', NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'CMS Preferecnes');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- add cms prefeences update page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'cmsPreferencesUpdate', '/admin/cms_preferences_update', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', null, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'CMS Preferecnes Update');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- add insert language page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'language', '/admin/language/[i:lid]?', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Create Language');

-- local and language should be unique
ALTER TABLE languages ADD UNIQUE (locale);
ALTER TABLE languages ADD UNIQUE (language);

-- add table cms_preferences
CREATE TABLE `cmsPreferences` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `callback_api_key` varchar(500),
  `default_language_id` int(10) UNSIGNED ZEROFILL,
  CONSTRAINT fk_cmsPreferences_language FOREIGN KEY (default_language_id) REFERENCES languages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add default row for cms_preferences
-- set default language German
INSERT INTO cmsPreferences(callback_api_key, default_language_id)
VALUES (NULL, 2); 

