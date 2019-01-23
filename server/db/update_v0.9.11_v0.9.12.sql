SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'remember to update the update permission for the chat field is_new';

-- chat changes (Symbol position)
INSERT INTO `pageType` (`id`, `name`) VALUES (NULL, 'chat');
SET @id_page_type_chat = LAST_INSERT_ID();
UPDATE `pages` SET `nav_position` = NULL WHERE `pages`.`keyword` = 'contact';
UPDATE `pages` SET `id_type` = @id_page_type_chat WHERE `pages`.`keyword` = 'contact';

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
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `chatRoom`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `chatRoom`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

INSERT INTO `chatRoom` (`id`, `name`) VALUES (NULL, 'root');

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
