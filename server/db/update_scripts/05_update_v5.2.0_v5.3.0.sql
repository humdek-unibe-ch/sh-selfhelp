-- set DB version
UPDATE version
SET version = 'v5.3.0';

-- Add new style `formulaParser`
INSERT IGNORE INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('formulaParser', '2', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Style used to parse formulas');
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'formula', get_field_type_id('json'), '0');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('formulaParser'), get_field_id('formula'), NULL, 'JSON file with the formula definition');