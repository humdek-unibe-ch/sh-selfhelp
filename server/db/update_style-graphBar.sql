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
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Defines a label for each disting data value. Use a JSON object where the key corresponds to the data value and the value to the label, e.g.\n\n```\n{\n  "value_1": "Label 1",\n  "value_2": "Label 2"\n}\n```');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'hole', 5, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 0, 'Use this to render a donut chart. Use a percentage from 0 to 100 where 0% means no hole and 100% a hole as big as the chart.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'hoverinfo', 1, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Allows to define the information to be rendered in the hover box. Use "none" to disable the hover box. Refer to the [Plotly.js documentation](!https://plotly.com/javascript/reference/#pie-hoverinfo) for more information.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'textinfo', 1, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Allows to define the information to be rendered on each pie slice. Use "none" to show no text. Refer to the [Plotly.js documentation](!https://plotly.com/javascript/reference/#pie-textinfo) for more information.');
