-- set DB version
UPDATE version
SET version = 'v6.11.0';

UPDATE libraries
SET `name` = '[Altorouter](https://github.com/dannyvankooten/AltoRouter)', comments = '[License Details](https://github.com/dannyvankooten/AltoRouter?tab=MIT-1-ov-file#readme)'
WHERE `name` = '[Altorouter](http://altorouter.com/)';

-- add field `url_param`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'url_param', get_field_type_id('text'), '0');
-- add `scope` field to style `entryRecord`
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('entryRecord'), get_field_id('url_param'), 'record_id', 'The name of the url parameter that will be taken from the url. This parameter is used to filter the form based on the`record_id` and return one entry.');

-- add `scope` field to style `entryRecord`
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('entryRecord'), get_field_id('scope'), '', 'If the variable `scope` is defined, it serves as a prefix for naming the variables');

-- add `scope` field to style `entryList`
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('entryList'), get_field_id('scope'), '', 'If the variable `scope` is defined, it serves as a prefix for naming the variables');

-- add `scope` field to style `loop`
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('loop'), get_field_id('scope'), '', 'If the variable `scope` is defined, it serves as a prefix for naming the variables');

-- set gender to admin
UPDATE users
SET id_genders = 1
WHERE email = 'admin';

-- Add new style tag
INSERT IGNORE INTO `styles` (`name`, `id_type`, `id_group`, `description`) VALUES ('tag', '1', (SELECT id FROM styleGroup WHERE `name` = 'Wrapper' LIMIT 1), 'Tag wrapper style used to wrap other styles between the selected tag.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('tag'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('tag'), get_field_id('css_mobile'), NULL, 'Allows to assign CSS classes to the root item of the style for the mobile version.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('tag'), get_field_id('condition'), NULL, 'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `"@__form_name__#__from_field_name__"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('tag'), get_field_id('data_config'), '', 'Define data configuration for fields that are loaded from DB and can be used inside the style with their param names. The name of the field can be used between {{param_name}} to load the required value');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('tag'), get_field_id('children'), 0, 'Children that can be added to the style. Each child will be loaded inside the tag');