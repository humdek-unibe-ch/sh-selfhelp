-- SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'remember to update the update permissions';
-- udate chat:is_new
-- insert chatRoom
-- insert chatRoom_users
-- delete chatRoom_users

-- allow get on ajax requests
UPDATE `pages` SET `protocol` = 'GET|POST' WHERE `pages`.`id` = 0000000028;
UPDATE `pages` SET `url` = '/request/[a:class]/[a:method]?' WHERE `pages`.`id` = 0000000028;

-- chat changes (Symbol position)
UPDATE `pages` SET `nav_position` = NULL WHERE `pages`.`keyword` = 'contact';

-- allow POST on all experimenter pages and on home
UPDATE `pages` SET `protocol` = 'GET|POST' WHERE `pages`.`id` = 0000000002;
UPDATE `pages` SET `protocol` = 'GET|POST' WHERE `pages`.`id_type` = 0000000003;

-- change group name
UPDATE `groups` SET `name` = 'therapist' WHERE `groups`.`id` = 0000000002;

-- chat changes for new messages indicator
ALTER TABLE `chat` ADD `is_new` TINYINT(0) NOT NULL DEFAULT '1' AFTER `timestamp`;

-- chat changes for chat groups
UPDATE `pages` SET `url` = '/kontakt/[i:gid]?/[i:uid]?' WHERE `pages`.`keyword` = 'contact';

CREATE TABLE `chatRoom` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` LONGTEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `chatRoom`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `chatRoom`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

INSERT INTO `chatRoom` (`id`, `name`, `description`) VALUES (NULL, 'root', 'The main room where every user is part of');

CREATE TABLE `chatRoom_users` (
  `id_chatRoom` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_users` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `chatRoom_users`
  ADD PRIMARY KEY (`id_chatRoom`,`id_users`),
  ADD KEY `id_chatRoom` (`id_chatRoom`),
  ADD KEY `id_users` (`id_users`);
ALTER TABLE `chatRoom_users`
  ADD CONSTRAINT `chatRoom_users_fk_id_chatRoom` FOREIGN KEY (`id_chatRoom`) REFERENCES `chatRoom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chatRoom_users_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `chat` ADD `id_rcv_grp` INT UNSIGNED ZEROFILL NULL DEFAULT NULL AFTER `id_rcv`, ADD INDEX (`id_rcv_grp`);
ALTER TABLE `chat` ADD CONSTRAINT `fk_chat_id_rcv_grp` FOREIGN KEY (`id_rcv_grp`) REFERENCES `chatRoom`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE `styles_fields` SET `id_fields` = '0000000090' WHERE `styles_fields`.`id_styles` = 0000000010 AND `styles_fields`.`id_fields` = 0000000008;
UPDATE `sections_fields_translation` SET `id_fields` = '0000000090' WHERE `sections_fields_translation`.`id_sections` = 0000000025 AND `sections_fields_translation`.`id_fields` = 0000000008 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001; UPDATE `sections_fields_translation` SET `id_fields` = '0000000090' WHERE `sections_fields_translation`.`id_sections` = 0000000025 AND `sections_fields_translation`.`id_fields` = 0000000008 AND `sections_fields_translation`.`id_languages` = 0000000003 AND `sections_fields_translation`.`id_genders` = 0000000001;

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_lobby', '0000000001', '1');
SET @id_field_label_lobby = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000010', @id_field_label_lobby, 'Lobby');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_new', '0000000001', '1');
SET @id_field_label_new = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000010', @id_field_label_new, 'New Messages');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES ('0000000025', @id_field_label_lobby, '0000000002', '0000000001', 'Lobby'), ('0000000025', @id_field_label_lobby, '0000000003', '0000000001', 'Lobby'), ('0000000025', @id_field_label_new, '0000000002', '0000000001', 'Neue Nachrichten'), ('0000000025', @id_field_label_new, '0000000003', '0000000001', 'New Messages');

-- chat admin select
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'chatAdminSelect', '/admin/chat/[i:rid]?', 'GET', '0000000002', NULL, '0000000009', '0', '35', NULL, '0000000001');
SET @id_page_chat = LAST_INSERT_ID();
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_chat, '0000000008', '0000000001', 'Chat Rooms');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_chat, '1', '0', '0', '0');
-- chat admin insert
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'chatAdminInsert', '/admin/chat_insert/', 'GET|POST|PUT', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page_chat_insert = LAST_INSERT_ID();
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_chat_insert, '0000000008', '0000000001', 'Create Chat Room');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_chat_insert, '1', '1', '0', '0');
-- chat admin delete
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'chatAdminDelete', '/admin/chat_delete/[i:rid]', 'GET|POST|DELETE', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page_chat_delete = LAST_INSERT_ID();
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_chat_delete, '0000000008', '0000000001', 'Delete Chat Room');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_chat_delete, '1', '0', '0', '1');
-- chat admin update
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'chatAdminUpdate', '/admin/chat_update/[i:rid]/[add_user|rm_user:mode]/[i:did]?', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page_chat_update = LAST_INSERT_ID();
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_chat_update, '0000000008', '0000000001', 'Administrate Chat Room');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_chat_update, '1', '0', '1', '0');

-- add debug field to conditional container
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'debug', '0000000003', '0');
SET @id_field_debug = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000042', @id_field_debug, 0);
