ALTER TABLE `users` ADD `last_url` VARCHAR(100) NULL DEFAULT NULL AFTER `last_login`;

ALTER TABLE `styles_fields` ADD `help` VARCHAR(500) NULL DEFAULT NULL AFTER `default_value`;

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'json', '0000000008', '1');
SET @id_field_json = LAST_INSERT_ID();
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'json', '0000000001', '0000000004', 'allows to describe `baseStyles` with `json` Syntax');
SET @id_style_json = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_json, @id_field_json, NULL, 'The JSON string to specify the (potentially) nested base styles.');

UPDATE `styles_fields` SET `help` = 'The JSON string to specify the (potentially) nested base styles.\r\n\r\nThere are a few things to note:\r\n - the key `baseStyle` must be used to indicate that the assigned object is a *style object*\r\n - the *style object* must contain the key `name` where the value must match a style name\r\n - the *style object* must contain the key `fields` where the value is an object holding all required fields of the style (refer to the <a href=\"https://selfhelp.psy.unibe.ch/demo/styles\" target=\"_blank\">style documentation</a> for more information)' WHERE `styles_fields`.`id_styles` = 0000000045 AND `styles_fields`.`id_fields` = 0000000105;
UPDATE `styles_fields` SET `help` = 'Select for a full width container, spanning the entire width of the viewport.' WHERE `styles_fields`.`id_styles` = 0000000003 AND `styles_fields`.`id_fields` = 0000000029;
UPDATE `styles_fields` SET `help` = 'The HTML heading level (1-6)' WHERE `styles_fields`.`id_styles` = 0000000005 AND `styles_fields`.`id_fields` = 0000000021;
UPDATE `styles_fields` SET `help` = 'Use <a href=\"https://en.wikipedia.org/wiki/Markdown\" target=\"_blank\">markdown</a> syntax here.' WHERE `styles_fields`.`id_styles` = 0000000006 AND `styles_fields`.`id_fields` = 0000000025;
UPDATE `styles_fields` SET `help` = 'Only use <a href=\"https://en.wikipedia.org/wiki/Markdown\" target=\"_blank\">markdown</a> elements that can be displayed inline (e.g. bold, italic, etc).' WHERE `styles_fields`.`id_styles` = 0000000007 AND `styles_fields`.`id_fields` = 0000000026;
UPDATE `styles_fields` SET `help` = 'The text to appear on the button.' WHERE `styles_fields`.`id_styles` = 0000000008 AND `styles_fields`.`id_fields` = 0000000008;
UPDATE `styles_fields` SET `help` = 'Use a full URL or any special characters as defined <a href=\"https://selfhelp.psy.unibe.ch/demo/style/440\" target=\"_blank\">here</a>.' WHERE `styles_fields`.`id_styles` = 0000000008 AND `styles_fields`.`id_fields` = 0000000027;
UPDATE `styles_fields` SET `help` = 'The <a href=\"https://getbootstrap.com/docs/4.1/components/buttons/#examples\" target=\"_blank\">bootstrap type</a> of the button.' WHERE `styles_fields`.`id_styles` = 0000000008 AND `styles_fields`.`id_fields` = 0000000028;
