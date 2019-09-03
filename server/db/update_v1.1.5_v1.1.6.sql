SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'navigationNested');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'text_md');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Use this field to add custom CSS classes to the root navigation page container.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'css_nav');
UPDATE `styles_fields` SET `help` = 'Use this field to add custom CSS classes to the navigation menu of a navigation page.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
