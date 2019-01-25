INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'debug', '0000000003', '0');
SET @id_field_debug = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000042', @id_field_debug, 0);
