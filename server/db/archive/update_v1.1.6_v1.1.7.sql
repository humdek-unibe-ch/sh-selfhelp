SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'userProgress');
UPDATE `styles` SET `id_type` = '0000000002' WHERE `styles`.`id` = @id_style;
