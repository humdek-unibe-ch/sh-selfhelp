-- set DB version
UPDATE version
SET version = 'v4.2.0';

-- Add new style Book
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('book', '1', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Wrap other children and later show them as pages. It is based on `turn.js`');

-- add field children to style book
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('book'), get_field_id('children'), 0, 'Children that can be added to the style. Each child will be loaded as a page');
-- add field css to style book
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('book'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');
-- add field config to style book
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('book'), get_field_id('config'), NULL, 'Define the configuration of the book. Refer to the documentation of [turn.js](http://www.turnjs.com/turnjs4-api-docs.pdf) for more information');

-- executed in wesir

