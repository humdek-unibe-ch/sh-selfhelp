-- set DB version
UPDATE version
SET version = 'v3.7.0';

-- add field format to style input
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'format', get_field_type_id('text'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('input'), get_field_id('format'), '', 'Add format pattern for the [input](https://selfhelp.psy.unibe.ch/demo/style/471)');
