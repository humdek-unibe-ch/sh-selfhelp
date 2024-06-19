-- set DB version
UPDATE version
SET version = 'v6.7.0';

-- add field `toggle_switch`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'toggle_switch', get_field_type_id('checkbox'), '0');
-- add `toggle_switch` field to style `input`, when enabled and the type is checkbox, then the input will be loaded as toggle switch
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('input'), get_field_id('toggle_switch'), 0, 'If enabled and the `type` of the input is `checkbox`, the input will be presented as a `toggle switch`');