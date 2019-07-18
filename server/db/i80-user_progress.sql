INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'userProgress', '0000000001', '0000000009', 'A progress bar to indicate the overall experiment progress of a user.');
SET @id_style_userProgress = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_userProgress, (SELECT `id` FROM `fields` WHERE `name` = 'type'), NULL, '.Use the type to change the appearance of individual progress bars');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_userProgress, (SELECT `id` FROM `fields` WHERE `name` = 'is_striped'), NULL, 'iIf set apply a stripe via CSS gradient over the progress bar’s background color.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_userProgress, (SELECT `id` FROM `fields` WHERE `name` = 'has_label'), NULL, 'If set display the progress in numbers ontop of the progress bar.');
