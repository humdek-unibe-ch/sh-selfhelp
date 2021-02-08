-- Add style messageBoard
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'messageBoard', '0000000002', '0000000002', 'Shows a board of messages which can be rated and commented with pre-defined messages.');
SET @id_style = LAST_INSERT_ID();
-- Assign fields to style autocomplete
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'css');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Allows to assign CSS classes to the root item of the style. E.g use the bootsrap class [`card-columns`](!https://getbootstrap.com/docs/4.6/components/card/#card-columns) to arrange the messages in a grid.');
