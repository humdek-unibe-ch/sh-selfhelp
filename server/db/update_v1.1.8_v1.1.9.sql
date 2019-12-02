-- add custom group update page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'groupUpdateCustom', '/admin/group_update_custom/[i:gid]', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page_group_custom_update = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_group_custom_update, '0000000008', '0000000001', 'Custom Group Update');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_group_custom_update, '1', '0', '1', '0');

-- the name field in groups should be unique. It will prevent duplicates #213
ALTER TABLE groups ADD UNIQUE (name);

-- add callback request page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'callback', '/callback/[v:class]/[v:method]?', 'GET|POST', '0000000001', NULL, NULL, '0', NULL, NULL, '0000000001');

-- add group to the validation codes. If there is agroup it is assinged to the user once the user is activated.
ALTER TABLE validation_codes
ADD COLUMN id_groups INT(10) UNSIGNED ZEROFILL NULL,
ADD CONSTRAINT validation_codes_fk_id_groups FOREIGN KEY (id_groups)  REFERENCES groups(id) ON DELETE CASCADE;	
