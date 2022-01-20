-- set DB version
UPDATE version
SET version = 'v4.6.0';

-- add new field filter
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'filter', get_field_type_id('text'), '0');

-- add field filter to style entryList
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryList'), get_field_id('filter'), NULL, 'Filter the data source; Use SQL syntax');

-- add field filter to style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecord'), get_field_id('filter'), NULL, 'Filter the data source; Use SQL syntax');