-- set DB version
UPDATE version
SET version = 'v5.9.0';

-- Add new field type `page-keyword` 
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-page-keyword', '9');
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'page_keyword', get_field_type_id('select-page-keyword'), '0');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('validate'), get_field_id('page_keyword'), '', 'Select a page that will be redirected after a successful validation');

-- allow clear on select
-- add field allow_clear to style select
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'allow_clear', get_field_type_id('checkbox'), '0');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('select'), get_field_id('allow_clear'), 0, 'If checked the select value can be cleared once set');