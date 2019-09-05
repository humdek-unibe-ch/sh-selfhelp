SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'navigationNested');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'text_md');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Use this field to add custom CSS classes to the root navigation page container.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'css_nav');
UPDATE `styles_fields` SET `help` = 'Use this field to add custom CSS classes to the navigation menu of a navigation page.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_page = (SELECT `id` FROM `pages` WHERE `keyword` = 'home');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'maintenance_date');
UPDATE `pages_fields` SET `help` = 'If set (together with the field `maintenance_time`), an alert message is shown at the top of the page displaying to content as defined in the field `maintenance` (where the key `@data` is replaced by this field).' WHERE `pages_fields`.`id_pages` = @id_page AND `pages_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'maintenance_time');
UPDATE `pages_fields` SET `help` = 'If set (together with the field `maintenance_date`), an alert message is shown at the top of the page displaying to content as defined in the field `maintenance` (where the key `@time` is replaced by this field).' WHERE `pages_fields`.`id_pages` = @id_page AND `pages_fields`.`id_fields` = @id_field;
