-- Add style messageBoard
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'messageBoard', '0000000002', '0000000002', 'Shows a board of messages which can be rated and commented with pre-defined messages.');
SET @id_style = LAST_INSERT_ID();
-- Add new fields used for style messageBoard
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'form_name', @id_field_type, '0');
-- Assign fields to style messageBoard
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'css');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Allows to assign CSS classes to the root item of the style. E.g use the bootsrap class [`card-columns`](!https://getbootstrap.com/docs/4.6/components/card/#card-columns) to arrange the messages in a grid.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The title of a message.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'text_md');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The messgae to be displayed nex to the score badge.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'form_name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the form under which the score is stored.');
