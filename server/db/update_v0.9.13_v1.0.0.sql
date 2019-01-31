-- fields for progress bar
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'is_striped', '0000000003', '0');
SET @id_field_is_striped = LAST_INSERT_ID();
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'has_label', '0000000003', '0');
SET @id_field_has_label = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000019', @id_field_is_striped, '1'), ('0000000019', @id_field_has_label, '1');

-- fields for carousel
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'has_crossfade', '0000000003', '0');
SET @id_field_has_crossfade = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) SELECT id AS id_styles, @id_field_has_crossfade AS id_fields, 0 AS default_value FROM styles WHERE styles.name = 'carousel';

-- custom inputs for validation
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000009', '0000000006', NULL);
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000009', '0000000057', NULL);
