INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'graphSankey', '0000000001', '0000000007', 'Create a [Sankey diagram](https://en.wikipedia.org/wiki/Sankey_diagram) from user input data or imported static data.');
SET @id_style = LAST_INSERT_ID();

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The title of the Sankey diagram.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'form_field_names', 8, 1);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'In order to create a Sankey diagram from a set of user input data two types of information are required:\n 1. the form field names defined here (think of it as the column headers of a table where each row holds the data of one subject)\n 2. the value types defined in `value_types` (the value entered by the subject).\n\nThe Sankey diagram consist of *nodes* and *links*. All possible combinations of form field names (1) and value types (2) define the nodes in a Sankey diagram. The links are computed by accumulating all values of the same type (2) when transitioning from one field name (1) to another.\n\nThis field expects an ordered list (`json` syntax) which specifies the form field names (1) to be used to generate the Sankey diagram. The order is important because two consecutive form field names (1) form a transition. Each list item is an object with the following fields:\n - `key`: the name of the field. Use the syntax `<form_name >#<field_name>` to refer to a specific input field `<field_name>` of a specific form `<form_name>`\n - `label`: A human-readable label which can be displayed on the diagram.\n\nAn Example\n```\n[\n  { "key": "my_form#field1", "label": "Field 1" },\n  { "key": "my_form#field2", "label": "Field 2" }\n]\n```');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'value_types', 8, 1);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'In order to create a Sankey diagram from a set of user input data two types of information are required:\n 1. the form field names defined in `form_field_names` (think of it as the column headers of a table where each row holds the data of one subject)\n 2. the value types defined here (the value entered by the subject).\n\nThe Sankey diagram consist of *nodes* and *links*. All possible combinations of form field names (1) and value types (2) define the nodes in a Sankey diagram. The links are computed by accumulating all values of the same type (2) when transitioning from one field name (1) to another.\n\nThis field expects an ordered list (`json` syntax) which specifies the value types (2) to be used to generate the Sankey diagram. The order is important because it may be used for node placement. Each list item is an object with the following fields:\n - `key`: the value of the value type.\n - `label`: A human-readable label which can be displayed on the diagram.\n - `color`: A hex string definig the color of the node of this type. Use a string of the following from `"#FF0000"`\n\nAn Example\n```\n[\n  { "key": 1, "label": "Value Type 1", "color": "#FF0000" },\n  { "key": 2, "label": "Value Type 2", "color": "#00FF00" }\n]\n```');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'link_color', 1, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the color of the links. There are four options:\n - `source`: use the color of the source node\n - `target`: use the color of the target node\n - a hex string of the from `#FF0000` to define the same color for all links\n - the empty string to use the default translucent gray');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'link_alpha', 1, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the alpha value of the color of the links. There are two options:\n - `sum`: compute the alpha value based on the width of the link\n - any number from 0 to 1: the same alpha value for all links as defined');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'min');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 1, 'The minimal required item count to form a link. In other words: what is the minimal required link width for a link to be displayed');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'has_type_labels', 3, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 0, 'If checked, the labels defined in `value_types` are displayed next to a node with the corresponding type');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'has_field_labels', 3, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 1, 'If checked, the label defined in `form_field_names` is displayed on top of a grouped node column. This field only has an effect if `is_grouped` is enabled.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'is_grouped', 3, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 1, 'If checked, the nodes are positioned as follows:\n - each node with the same form field name is aligned vertically (same x coordinate)\n - within one column nodes are sorted by value types (by their indices as defined in `value_types`');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'raw', 1, 0);
