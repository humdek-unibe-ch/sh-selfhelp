-- Add internal style autocomplete
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'autocomplete', '0000000001', '0000000001', 'Provides a text input field which executes an AJAX request on typing.\r\nA AJAX request class and method must be defined for this to work.');
SET @id_style = LAST_INSERT_ID();
-- Add new fields used for style autocomplete
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'name_value_field', @id_field_type, '0');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'callback_class', @id_field_type, '0');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'callback_method', @id_field_type, '0');
-- Assign fields to style autocomplete
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name_value_field');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the hidden autocomplete value input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'placeholder');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The placeholder text to be displayed in the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_required');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'True if the field is required, false otherwise.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The label to be displayed above the autocomplete input field.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'callback_class');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the class to be instantiated in the AJAX request.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'callback_method');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the method to be called on the class instance as defined in `callback_class`.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'debug');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'If set to true, debug information is shown in an alert box.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The default value to be set in the hidden autocomplete value input field.');

-- Add new field type and field
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'data-source', '15');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'data-source');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'data-source', @id_field_type, '0');

-- Alter asset page url
SET @id_page = (SELECT `id` FROM `pages` WHERE `keyword` = 'assetInsert');
UPDATE `pages` SET `url` = '/admin/asset_insert/[css|asset|static:mode]' WHERE `pages`.`id` = @id_page;
SET @id_page = (SELECT `id` FROM `pages` WHERE `keyword` = 'assetDelete');
UPDATE `pages` SET `url` = '/admin/asset_delete/[css|asset|static:mode]/[*:file]' WHERE `pages`.`id` = @id_page;

--
-- Table structure for table `uploadCells`
--

CREATE TABLE `uploadCells` (
  `id_uploadRows` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_uploadCols` int(10) UNSIGNED ZEROFILL NOT NULL,
  `value` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadCols`
--

CREATE TABLE `uploadCols` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `id_uploadTables` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadRows`
--

CREATE TABLE `uploadRows` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_uploadTables` int(10) UNSIGNED ZEROFILL NOT NULL  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadTables`
--

CREATE TABLE `uploadTables` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `uploadCells`
--
ALTER TABLE `uploadCells`
  ADD PRIMARY KEY (`id_uploadRows`,`id_uploadCols`),
  ADD KEY `id_uploadRows` (`id_uploadRows`),
  ADD KEY `id_uploadCols` (`id_uploadCols`);

--
-- Indexes for table `uploadCols`
--
ALTER TABLE `uploadCols`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uploadTables` (`id_uploadTables`);

--
-- Indexes for table `uploadRows`
--
ALTER TABLE `uploadRows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uploadTables` (`id_uploadTables`);

--
-- Indexes for table `uploadTables`
--
ALTER TABLE `uploadTables`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `uploadCols`
--
ALTER TABLE `uploadCols`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `uploadRows`
--
ALTER TABLE `uploadRows`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `uploadTables`
--
ALTER TABLE `uploadTables`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `uploadCells`
--
ALTER TABLE `uploadCells`
  ADD CONSTRAINT `uploadCells_fk_id_uploadCols` FOREIGN KEY (`id_uploadCols`) REFERENCES `uploadCols` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `uploadCells_fk_id_uploadRows` FOREIGN KEY (`id_uploadRows`) REFERENCES `uploadRows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploadCols`
--
ALTER TABLE `uploadCols`
  ADD CONSTRAINT `uploadCols_fk_id_uploadTables` FOREIGN KEY (`id_uploadTables`) REFERENCES `uploadTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploadRows`
--
ALTER TABLE `uploadRows`
  ADD CONSTRAINT `uploadRows_fk_id_uploadTables` FOREIGN KEY (`id_uploadTables`) REFERENCES `uploadTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- important index for faster performance  
CREATE INDEX idx_uploadTables_name_timestamp ON uploadTables (name, timestamp);

-- add style graph and all its fields
INSERT INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES (NULL, 'Graph', 'Graph styles allow to draw graps and diagrams based on static (uploaded assets) or dynamic (user input) data.', 55);
SET @id_group = LAST_INSERT_ID();

INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'graph', '0000000002', @id_group, 'The most general graph style which allows to render a vast variety of graphs but requires extensive configuration. All other graph styles are based on this style.');
SET @id_style = LAST_INSERT_ID();

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'traces', 8, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the data traces to be rendered. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'layout', 8, 1);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the layout of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'config', 8, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the configuration of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The title to be rendered on top of teh graph. This field is here purely for convenience as the title of a graph can also be defined in the field `layout`');

-- add style graphSankey and all its fields
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'graphSankey', '0000000002', @id_group, 'Create a Sankey diagram from user input data or imported static data.');
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

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'data-source');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The source of the data to be used to draw the Sankey diagram.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'single_user', 3, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 1, 'This option only takes effect when using **dynamic** data. If checked, only data from the current logged-in user is used. If unchecked, data form all users is used.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'raw', 1, 0);

-- add filterToggle style
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'filterToggle', '0000000002', '0000000007', 'Create a toggle button which will enable or disable a filter on a set of data.');
SET @id_style = LAST_INSERT_ID();

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'data-source');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The source of the data to be filtered.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name to be rendered on the filter button.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the table column or form field to filter on.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The value of the filter. All data sets of the data source (as specified by `data-source`) where the field (as specified by `name`) holds a value equal to the one indicated here will be selected.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The visual apperance of the button as predefined by bootstrap.');

-- add filterToggleGroup style
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'filterToggleGroup', '0000000002', '0000000007', 'Create a group of toggle buttons which will enable or disable a filter on a set of data. Multiple active buttons are combinde with the logic or function.');
SET @id_style = LAST_INSERT_ID();

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'data-source');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The source of the data to be filtered.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the table column or form field to filter on.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'labels');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The names to be rendered on the filter buttons. Use a JSON array to specify all labels. The labels must correspond to the values as specified in `values`');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'values', 8, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The value of each filter button. All data sets of the data source (as specified by `data-source`) where the field (as specified by `name`) holds a value equal to the one indicated here will be selected. Use a JSON array to specify all values. The values must correspond to the labels as specified in `labels`.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'is_vertical', 3, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 0, 'If checked, the button group is rendered as a vertical stack. If unchecked, the button group is rendered as a vertical list.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_fluid');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 0, 'If checked, the button group is streched to fill 100% of the available width. If unchecked, the button group is stretched to fit all text within each button but never more than available space.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The visual apperance of the buttons as predefined by bootstrap.');

-- add graphPie style
SET @id_group = (SELECT `id` FROM `styleGroup` WHERE `name` = 'Graph');
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'graphPie', '0000000002', @id_group, 'Create a pie diagram from user input data or imported static data.');
SET @id_style = LAST_INSERT_ID();

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'data-source');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The source of the data to be used to render a pie diagram.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the table column or form field to use to render a pie diagram.');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value_types');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Defines the label and color for each distinct data value. Use a JSON array where each item has the following keys:\n - `key`: the data value to which the color and label will be assigned\n - `label`: to the label of the data value\n - `color`: the color of the data value (optional)\n\nAn example:\n```\n[\n  { "key": "value_1", "label", "Label 1", "color": "#ff0000" },\n  { "key": "value_2", "label", "Label 2", "color": "#00ff00" }\n}\n```');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'layout');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the layout of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information');

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'config');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Define the configuration of the graph. Refer to the documentation of [Plotly.js](https://plotly.com/javascript/) for more information');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'hole', 5, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 0, 'Use this to render a donut chart. Use a percentage from 0 to 100 where 0% means no hole and 100% a hole as big as the chart.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'hoverinfo', 1, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Allows to define the information to be rendered in the hover box. Use "none" to disable the hover box. Refer to the [Plotly.js documentation](!https://plotly.com/javascript/reference/#pie-hoverinfo) for more information.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'textinfo', 1, 0);
SET @id_field = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Allows to define the information to be rendered on each pie slice. Use "none" to show no text. Refer to the [Plotly.js documentation](!https://plotly.com/javascript/reference/#pie-textinfo) for more information.');

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

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value_types');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Defines the label and color for each distinct data value. Use a JSON array where each item has the following keys:\n - `key`: the data value to which the color and label will be assigned\n - `label`: to the label of the data value\n - `color`: the color of the data value (optional)\n\nAn example:\n```\n[\n  { "key": "value_1", "label", "Label 1", "color": "#ff0000" },\n  { "key": "value_2", "label", "Label 2", "color": "#00ff00" }\n}\n```');

-- add graphLegend style
SET @id_group = (SELECT `id` FROM `styleGroup` WHERE `name` = 'Graph');
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'graphLegend', '0000000001', @id_group, 'Render colored list of items. This can be used to show one global legend for multiple graphs.');
SET @id_style = LAST_INSERT_ID();

SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value_types');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Defines the label and color for each distinct data value. Use a JSON array where each item has the following keys:\n - `key`: the data value to which the color and label will be assigned\n - `label`: to the label of the data value\n - `color`: the color of the data value\n\nAn example:\n```\n[\n  { "key": "value_1", "label", "Label 1", "color": "#ff0000" },\n  { "key": "value_2", "label", "Label 2", "color": "#00ff00" }\n}\n```');
