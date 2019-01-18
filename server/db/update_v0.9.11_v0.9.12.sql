SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'remember to update the update permission for the chat field is_new';

UPDATE `pages` SET `nav_position` = NULL WHERE `pages`.`id` = 0000000029;

ALTER TABLE `chat` ADD `is_new` TINYINT(0) NOT NULL DEFAULT '1' AFTER `timestamp`;
