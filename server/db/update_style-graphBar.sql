-- add graphBar style
SET @id_group = (SELECT `id` FROM `styleGroup` WHERE `name` = 'Graph');
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'graphBar', '0000000002', @id_group, 'Create a bar diagram from user input data or imported static data.');
SET @id_style = LAST_INSERT_ID();

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'data-source');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The source of the data to be used to render a pie diagram.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the table column or form field to use to render a pie diagram.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'layout');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the layout of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'config');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the configuration of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'labels');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Defines a label for each distinct data value. Use a JSON object where the key corresponds to the data value and the value to the label, e.g.\n\n```\n{\n  "value_1": "Label 1",\n  "value_2": "Label 2"\n}\n```');
