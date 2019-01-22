SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'remember to update the update permission for the chat field is_new';

-- chat changes
UPDATE `pages` SET `nav_position` = NULL WHERE `pages`.`id` = 0000000029;

ALTER TABLE `chat` ADD `is_new` TINYINT(0) NOT NULL DEFAULT '1' AFTER `timestamp`;

INSERT INTO `pageType` (`id`, `name`) VALUES (NULL, 'chat');
SET @id_page_type_chat = LAST_INSERT_ID();
UPDATE `pages` SET `id_type` = @id_page_type_chat WHERE `pages`.`id` = 0000000029;

-- allow POST on all experimenter pages and on home
UPDATE `pages` SET `protocol` = 'GET|POST' WHERE `pages`.`id` = 0000000002;
UPDATE `pages` SET `protocol` = 'GET|POST' WHERE `pages`.`id_type` = 0000000003;

-- change group name
UPDATE `groups` SET `name` = 'therapist' WHERE `groups`.`id` = 0000000002;
