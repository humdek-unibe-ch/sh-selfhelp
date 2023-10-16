-- set DB version
UPDATE version
SET version = 'v6.5.0';

-- add min and max fields to the textarea
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('textarea'), get_field_id('min'), 0, 'This number will determine the minimum character size required for your input. The input will need to have at least this many characters to be valid');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('textarea'), get_field_id('max'), 2000, 'This number will determine the maximum character size allowed for your input. The input should not exceed this character limit to be valid.');