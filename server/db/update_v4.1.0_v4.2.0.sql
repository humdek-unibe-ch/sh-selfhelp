-- set DB version
UPDATE version
SET version = 'v4.2.0';

-- Add new style Book
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('book', '1', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Wrap other styles and later show them as pages. It is based on `turn.js`');

-- add field children to style book
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('book'), get_field_id('children'), 0, 'Children that can be added to the style. Each child will be loaded as a page');
-- add field css to style book
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('book'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');
-- add field config to style book
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('book'), get_field_id('config'), NULL, 'Define the configuration of the book. Refer to the documentation of [turn.js](http://www.turnjs.com/turnjs4-api-docs.pdf) for more information');


-- Add new style refContainer
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('refContainer', '1', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Wrap other styles that later can be used in different place. It can be used for creating resusable blocks.');

-- add field children to style refContainer
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('refContainer'), get_field_id('children'), 0, 'Children that can be added to the style and later used in multiple places');

-- executed in wesir

-- add field locked_after_submit
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'locked_after_submit', get_field_type_id('checkbox'), '0');
-- add field locked_after_submit to style input
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('input'), get_field_id('locked_after_submit'), NULL, 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.');

-- add field locked_after_submit to style radio
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('radio'), get_field_id('locked_after_submit'), NULL, 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.');
-- add field locked_after_submit to style select
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('select'), get_field_id('locked_after_submit'), NULL, 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.');