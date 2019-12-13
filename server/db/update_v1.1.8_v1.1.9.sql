-- add custom group update page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'groupUpdateCustom', '/admin/group_update_custom/[i:gid]', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page_group_custom_update = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_group_custom_update, '0000000008', '0000000001', 'Custom Group Update');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_group_custom_update, '1', '0', '1', '0');

-- the name field in groups should be unique. It will prevent duplicates #213
ALTER TABLE groups ADD UNIQUE (name);

-- add callback request page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'callback', '/callback/[v:class]/[v:method]?', 'GET|POST', '0000000001', NULL, NULL, '0', NULL, NULL, '0000000001');

-- add table codes_groups many to many. Assign a code to a group
CREATE TABLE codes_groups (
	`code` VARCHAR(16) NOT NULL,
    id_groups INT(10) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY (`code`, id_groups),
    CONSTRAINT fk_codes FOREIGN KEY (`code`)  REFERENCES validation_codes(`code`) ON DELETE CASCADE,
    CONSTRAINT fk_id_groups FOREIGN KEY (id_groups)  REFERENCES groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- create table that keeps information about the requested callbacks
drop table callbackLogs;
CREATE TABLE `callbackLogs` (
  `id` INTEGER PRIMARY KEY AUTO_INCREMENT,
  `callback_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `remote_addr` VARCHAR(200),
  `redirect_url` VARCHAR(1000),
  `callback_params` LONGTEXT,
  `status` VARCHAR(200), -- statuscode are defined in the globals.php 
  `callback_output` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
