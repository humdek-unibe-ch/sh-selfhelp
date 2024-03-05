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