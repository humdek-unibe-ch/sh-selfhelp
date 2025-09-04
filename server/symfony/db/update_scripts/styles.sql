-- Remove not needed field type `type` from `button` style
DELETE FROM styles_fields
WHERE id_fields = get_field_id('type') and id_styles = get_style_id('button');


CALL add_table_column('fields', 'config', 'JSON DEFAULT NULL');

-- Add new field type `select`
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select', '1');

-- Add new field `mantine_variant` from type `select` based on the mantine button variant
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_variant', get_field_type_id('select'), '1', '{"options":[{
"value":"filled",
"text":"Filled"
},
{
"value":"light",
"text":"Light"
},
{
"value":"outline",
"text":"Outline"
},
{
"value":"subtle",
"text":"Subtle"
},
{
"value":"default",
"text":"Default"
},
{
"value":"transparent",
"text":"Transparent"
},
{
"value":"white",
"text":"White"
}]}');

-- Add new field `mantine_variant` to `button` style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`) 
VALUES (get_style_id('button'), get_field_id('mantine_variant'), 'filled', 'Select variant for the button. For more information check https://mantine.dev/core/button', 0, 0);