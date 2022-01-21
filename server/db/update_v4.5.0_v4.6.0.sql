-- set DB version
UPDATE version
SET version = 'v4.6.0';

-- add new field filter
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'filter', get_field_type_id('text'), '0');

-- add field filter to style entryList
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryList'), get_field_id('filter'), NULL, 'Filter the data source; Use SQL syntax');

-- add field filter to style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecord'), get_field_id('filter'), NULL, 'Filter the data source; Use SQL syntax');

-- add new field css_mobile
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'css_mobile', get_field_type_id('text'), '0');

-- add filed css_mobile to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('css_mobile'), "Allows to assign `mobile` CSS classes to the root item of the style." 
FROM view_style_fields
WHERE field_name = 'css';

-- add field children to style link
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('link'), get_field_id('children'), 0, 'Children that can be added to the style. Each child will be loaded as a page');