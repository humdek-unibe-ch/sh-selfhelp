SET @id_group = (SELECT `id` FROM `styleGroup` WHERE `name` = 'Admin');
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'styleSignature', '0000000002', @id_group, 'Allows to render the meta data of a style.');
SET @id_style = LAST_INSERT_ID();

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the style for which the meta data will be rendered.');
