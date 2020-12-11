-- set DB version
UPDATE version
SET version = 'v3.9.0';

ALTER TABLE `styles_fields` ADD `disabled` BOOLEAN NOT NULL DEFAULT FALSE AFTER `help`;

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'ajax');
SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'formUserInput');
UPDATE `styles_fields` SET `disabled` = '1' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
