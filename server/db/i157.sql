SET @id_page_exportData = (SELECT `id` FROM `pages` WHERE `keyword` = 'exportData');
UPDATE `pages` SET `url` = '/admin/export/[user_input|user_activity|validation_codes:selector]/[all|used|open:option]?' WHERE `pages`.`id` = @id_page_exportData;

ALTER TABLE `validation_codes` CHANGE `timestamp` `consumed` DATETIME on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `validation_codes` ADD `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `id_users`;
