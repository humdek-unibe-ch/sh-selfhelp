SET @id_page_exportData = (SELECT `id` FROM `pages` WHERE `keyword` = 'exportData');
UPDATE `pages` SET `url` = '/admin/export/[user_input|user_activity|validation_codes:selector]/[all|used|open:option]?' WHERE `pages`.`id` = @id_page_exportData;
