-- Add internal style autocomplete
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'autocomplete', '0000000001', '0000000001', 'Provides a text input field which executes an AJAX request on typing.\r\nA AJAX request class and method must be defined for this to work.');
SET @id_style = LAST_INSERT_ID();
-- Add new fields used for style autocomplete
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'name_value_field', @id_field_type, '0');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'callback_class', @id_field_type, '0');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'callback_method', @id_field_type, '0');
-- Assign fields to style autocomplete
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name_value_field');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the hidden autocomplete value input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'placeholder');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The placeholder text to be displayed in the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_required');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'True if the field is required, false otherwise.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The label to be displayed above the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'callback_class');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the class to be instantiated in the AJAX request.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'callback_method');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the method to be called on the class instance as defined in `callback_class`.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'debug');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'If set to true, debug information is shown in an alert box.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The default value to be set in the hidden autocomplete value input field.');

-- Add new field type and field
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'data-source', '15');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'data-source');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'data-source', @id_field_type, '0');
