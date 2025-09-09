-- Remove not needed field type `type` from `button` style
DELETE FROM styles_fields
WHERE id_fields = get_field_id('type') and id_styles = get_style_id('button');

-- strucutre of the config field:
-- export interface IFieldConfig {
--     // Core select functionality
--     multiSelect?: boolean;
--     creatable?: boolean;
--     separator?: string;
--     clearable?: boolean;
--     searchable?: boolean;
--     allowDeselect?: boolean;

--     // Display and behavior
--     placeholder?: string;
--     nothingFoundMessage?: string;
--     description?: string;
--     error?: string;
--     required?: boolean;
--     withAsterisk?: boolean;
--     disabled?: boolean;

--     // Dropdown configuration
--     limit?: number;
--     maxDropdownHeight?: number;
--     hidePickedOptions?: boolean;
--     maxValues?: number;

--     // Styling and layout
--     checkIconPosition?: 'left' | 'right';
--     leftSection?: ReactNode;
--     rightSection?: ReactNode;

--     // Data and options
--     options?: Array<{
--         value: string;
--         text: string;
--         disabled?: boolean;
--         [key: string]: any;
--     }>;
--     apiUrl?: string;

-- }
CALL add_table_column('fields', 'config', 'JSON DEFAULT NULL');

-- Add new field type `select`
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select', '1');

-- Add new field `mantine-variant` from type `select` based on the mantine button variant
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_variant', get_field_type_id('select'), 0, '{"searchable" : false, "clearable" : false, "options":[{
"value":"filled",
"text":"Filled"
},
{
"value":"light",
"text":"Light"
},
{
"value":"outline",
"text":"Outline"
},
{
"value":"subtle",
"text":"Subtle"
},
{
"value":"default",
"text":"Default"
},
{
"value":"transparent",
"text":"Transparent"
},
{
"value":"white",
"text":"White"
}]}');

-- Add new field `mantine-variant` to `button` style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_variant'), 'filled', 'Select variant for the button. For more information check https://mantine.dev/core/button', 0, 0, 'Variant');


-- Add new field type `color-picker`
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'color-picker', '2');


-- Add field `mantine-color-picker` from type `color-picker`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_color', get_field_type_id('color-picker'), 0, '{
  "options": [
    { "value": "gray", "text": "Gray" },
    { "value": "red", "text": "Red" },        
    { "value": "grape", "text": "Grape" },
    { "value": "violet", "text": "Violet" },        
    { "value": "blue", "text": "Blue" },    
    { "value": "cyan", "text": "Cyan" },    
    { "value": "green", "text": "Green" },    
    { "value": "lime", "text": "Lime" },
    { "value": "yellow", "text": "Yellow" },
    { "value": "orange", "text": "Orange" }    
  ]
}
');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_color'), 'blue', 'Select color for the button. For more information check https://mantine.dev/core/button', 0, 0, 'Color');


-- Add new field type `slider`
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'slider', '3');

-- Add field `mantine-slider-size` from type `slider`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_slider_size', get_field_type_id('slider'), 0, '
{
	"options": [
		{
			"value": "xs",
			"text": "xs"
		},
		{
			"value": "sm",
			"text": "sm"
		},
		{
			"value": "md",
			"text": "md"
		},
		{
			"value": "lg",
			"text": "lg"
		},
		{
			"value": "xl",
			"text": "xl"
		}
	]
}
');

-- Add field `mantine-slider-size` from type `slider`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_slider_radius', get_field_type_id('slider'), 0, '
{
	"options": [
		{
			"value": "xs",
			"text": "xs"
		},
		{
			"value": "sm",
			"text": "sm"
		},
		{
			"value": "md",
			"text": "md"
		},
		{
			"value": "lg",
			"text": "lg"
		},
		{
			"value": "xl",
			"text": "xl"
		}
	]
}
');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_slider_size'), 'sm', 'Select slider size for the button. For more information check https://mantine.dev/core/button', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_slider_radius'), 'sm', 'Select slider size for the button. For more information check https://mantine.dev/core/button', 0, 0, 'Radius');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_fullwidth', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_fullwidth'), '0', 'If `fullWidth`	 prop is set Button will take 100% of parent width', 0, 0, 'Full Width');

INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-icon', '4');

-- Add new field type `segment`
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'segment', '5');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_btn_left_icon', get_field_type_id('select-icon'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_btn_left_icon'), NULL, '`leftSection` and `rightSection` allow adding icons or any other element to the left and right side of the button. When a section is added, padding on the corresponding side is reduced.
Note that `leftSection` and `rightSection` are flipped in RTL mode (`leftSection` is displayed on the right and `rightSection` is displayed on the left).', 0, 0, 'Left Icon');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_btn_right_icon', get_field_type_id('select-icon'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_btn_right_icon'), NULL, '`leftSection` and `rightSection` allow adding icons or any other element to the left and right side of the button. When a section is added, padding on the corresponding side is reduced.
Note that `leftSection` and `rightSection` are flipped in RTL mode (`leftSection` is displayed on the right and `rightSection` is displayed on the left).', 0, 0, 'Right Icon');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_compact', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_compact'), '0', 'If `compact` prop is set Button will be smaller. Button supports xs – xl and compact-xs – compact-xl sizes. compact sizes have the same font-size as xs – xl but reduced padding and height.', 0, 0, 'Compact');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_auto_contrast', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine_auto_contrast'), '0', 'If `autoContrast` prop is set Button will automatically adjust the contrast of the button to the background color. For more information check https://mantine.dev/core/button', 0, 0, 'Auto Contrast');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'is_link', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('is_link'), '0', 'If `isLink` prop is set Button will be a link. For more information check https://mantine.dev/core/button', 0, 0, 'Is Link');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('disabled'), '0', 'If `disabled` prop is set Button will be disabled. For more information check https://mantine.dev/core/button', 0, 0, 'Disabled');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('open_in_new_tab'), '0', 'If `openInNewTab` prop is set Button will open in a new tab. For more information check https://mantine.dev/core/button', 0, 0, 'Open in New Tab');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'use_mantine_style', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Button will use the Mantine style, otherwise it will be a clear element whcih can be styled with CSS and tailwind CSS classes. For more information check https://mantine.dev/core/button', 0, 0, 'Use Mantine Style');

UPDATE fieldType
SET position = 0
WHERE `name` = 'checkbox';

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('page_keyword'), '#', 'Select a page keyword to link to. For more information check https://mantine.dev/core/button', 0, 0, 'URL');

DELETE FROM styles_fields
WHERE id_fields = get_field_id('url') and id_styles = get_style_id('button');

-- Create 'mantine' style group for Mantine-specific components
INSERT IGNORE INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES (NULL, 'mantine', 'Mantine UI components for modern web interfaces', 10);

-- Add new style 'center' based on Mantine Center component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'center',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Center component for centering content',
    1
);

-- Add field for inline property (checkbox)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_center_inline', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('center'), get_field_id('mantine_center_inline'), '0', 'If `inline` prop is set, Center will use inline-flex instead of flex display. For more information check https://mantine.dev/core/center', 0, 0, 'Inline');

-- Add generic width field (reusable across components)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_width', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": true, "options":[
{"value":"25%","text":"25%"},
{"value":"50%","text":"50%"},
{"value":"75%","text":"75%"},
{"value":"100%","text":"100%"},
{"value":"auto","text":"Auto"},
{"value":"fit-content","text":"Fit Content"},
{"value":"max-content","text":"Max Content"},
{"value":"min-content","text":"Min Content"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('center'), get_field_id('mantine_width'), NULL, 'Sets the width of the Center component. Common values include percentages, auto, or content-based sizing. For more information check https://mantine.dev/core/center', 0, 0, 'Width');

-- Add generic height field (reusable across components)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_height', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": true, "options":[
{"value":"25%","text":"25%"},
{"value":"50%","text":"50%"},
{"value":"75%","text":"75%"},
{"value":"100%","text":"100%"},
{"value":"auto","text":"Auto"},
{"value":"fit-content","text":"Fit Content"},
{"value":"max-content","text":"Max Content"},
{"value":"min-content","text":"Min Content"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('center'), get_field_id('mantine_height'), NULL, 'Sets the height of the Center component. Common values include percentages, auto, or content-based sizing. For more information check https://mantine.dev/core/center', 0, 0, 'Height');

-- Add generic minimum width field (reusable across components)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_miw', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": true, "options":[
{"value":"0","text":"0"},
{"value":"25%","text":"25%"},
{"value":"50%","text":"50%"},
{"value":"100%","text":"100%"},
{"value":"200px","text":"200px"},
{"value":"300px","text":"300px"},
{"value":"400px","text":"400px"},
{"value":"500px","text":"500px"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('center'), get_field_id('mantine_miw'), NULL, 'Sets the minimum width of the Center component. For more information check https://mantine.dev/core/center', 0, 0, 'Min Width');

-- Add generic minimum height field (reusable across components)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_mih', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": true, "options":[
{"value":"0","text":"0"},
{"value":"25%","text":"25%"},
{"value":"50%","text":"50%"},
{"value":"100%","text":"100%"},
{"value":"200px","text":"200px"},
{"value":"300px","text":"300px"},
{"value":"400px","text":"400px"},
{"value":"500px","text":"500px"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('center'), get_field_id('mantine_mih'), NULL, 'Sets the minimum height of the Center component. For more information check https://mantine.dev/core/center', 0, 0, 'Min Height');

-- Add generic maximum width field (reusable across components)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_maw', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": true, "options":[
{"value":"25%","text":"25%"},
{"value":"50%","text":"50%"},
{"value":"75%","text":"75%"},
{"value":"100%","text":"100%"},
{"value":"200px","text":"200px"},
{"value":"300px","text":"300px"},
{"value":"400px","text":"400px"},
{"value":"500px","text":"500px"},
{"value":"600px","text":"600px"},
{"value":"800px","text":"800px"},
{"value":"1000px","text":"1000px"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('center'), get_field_id('mantine_maw'), NULL, 'Sets the maximum width of the Center component. For more information check https://mantine.dev/core/center', 0, 0, 'Max Width');

-- Add generic maximum height field (reusable across components)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_mah', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": true, "options":[
{"value":"25%","text":"25%"},
{"value":"50%","text":"50%"},
{"value":"75%","text":"75%"},
{"value":"100%","text":"100%"},
{"value":"200px","text":"200px"},
{"value":"300px","text":"300px"},
{"value":"400px","text":"400px"},
{"value":"500px","text":"500px"},
{"value":"600px","text":"600px"},
{"value":"800px","text":"800px"},
{"value":"1000px","text":"1000px"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('center'), get_field_id('mantine_mah'), NULL, 'Sets the maximum height of the Center component. For more information check https://mantine.dev/core/center', 0, 0, 'Max Height');


-- Add new style 'container' based on Mantine Container component (core props only)
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'container',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Container component for responsive layout containers',
    1
);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('container'), get_field_id('mantine_size'), NULL, 'Sets the maximum width of the Container component. Choose from predefined responsive breakpoints or enter custom pixel values. For more information check https://mantine.dev/core/container', 0, 0, 'Size');

-- Add fluid property field (core Mantine prop)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_fluid', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('container'), get_field_id('mantine_fluid'), '0', 'If `fluid` prop is set Container will take 100% of parent width, ignoring size prop. For more information check https://mantine.dev/core/container', 0, 0, 'Fluid');

-- Add horizontal padding field (core Mantine prop)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_px', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": true, "options":[
{"value":"xs","text":"Extra Small"},
{"value":"sm","text":"Small"},
{"value":"md","text":"Medium"},
{"value":"lg","text":"Large"},
{"value":"xl","text":"Extra Large"},
{"value":"0","text":"None"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('container'), get_field_id('mantine_px'), NULL, 'Sets the horizontal padding of the Container component. Choose from predefined sizes or enter custom values. For more information check https://mantine.dev/core/container', 0, 0, 'Horizontal Padding');

-- Add vertical padding field (core Mantine prop)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_py', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": true, "options":[
{"value":"xs","text":"Extra Small"},
{"value":"sm","text":"Small"},
{"value":"md","text":"Medium"},
{"value":"lg","text":"Large"},
{"value":"xl","text":"Extra Large"},
{"value":"0","text":"None"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('container'), get_field_id('mantine_py'), NULL, 'Sets the vertical padding of the Container component. Choose from predefined sizes or enter custom values. For more information check https://mantine.dev/core/container', 0, 0, 'Vertical Padding');

-- NOTE: use_mantine_style field is already a generic field created for the button style
-- and can be reused across ALL components (both Mantine and non-Mantine components)
-- It provides the option to use Mantine styling or fall back to custom CSS/Tailwind classes

-- Add use_mantine_style field for Container (reuse existing generic field)
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('container'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Container will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/container', 0, 0, 'Use Mantine Style');

-- Add generic gap field (reusable across components) - use slider
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_gap', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "0", "text": "None"},
		{"value": "xs", "text": "xs"},
		{"value": "sm", "text": "sm"},
		{"value": "md", "text": "md"},
		{"value": "lg", "text": "lg"},
		{"value": "xl", "text": "xl"}
	]
}');

-- Add generic justify field (reusable across components)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_justify', get_field_type_id('select'), 0, '{"searchable": false, "clearable": true, "options":[
{"value":"flex-start","text":"Start"},
{"value":"center","text":"Center"},
{"value":"flex-end","text":"End"},
{"value":"space-between","text":"Space Between"},
{"value":"space-around","text":"Space Around"},
{"value":"space-evenly","text":"Space Evenly"}
]}');

-- Add generic align field (reusable across components)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_align', get_field_type_id('select'), 0, '{"searchable": false, "clearable": true, "options":[
{"value":"flex-start","text":"Start"},
{"value":"center","text":"Center"},
{"value":"flex-end","text":"End"},
{"value":"stretch","text":"Stretch"},
{"value":"baseline","text":"Baseline"}
]}');

-- Add generic direction field (reusable across components) - use segment type
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_direction', get_field_type_id('segment'), 0, '{"options":[
{"value":"row","text":"Row"},
{"value":"column","text":"Column"},
{"value":"row-reverse","text":"Row Reverse"},
{"value":"column-reverse","text":"Column Reverse"}
]}');

-- Add generic wrap field (reusable across components) - use segment type
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_wrap', get_field_type_id('segment'), 0, '{"options":[
{"value":"wrap","text":"Wrap"},
{"value":"nowrap","text":"No Wrap"},
{"value":"wrap-reverse","text":"Wrap Reverse"}
]}');

-- Add generic spacing field (reusable across components) - use slider
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_spacing', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "0", "text": "None"},
		{"value": "xs", "text": "xs"},
		{"value": "sm", "text": "sm"},
		{"value": "md", "text": "md"},
		{"value": "lg", "text": "lg"},
		{"value": "xl", "text": "xl"}
	]
}');

-- Add generic breakpoints field (reusable across components) - use slider
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_breakpoints', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "xs", "text": "xs"},
		{"value": "sm", "text": "sm"},
		{"value": "md", "text": "md"},
		{"value": "lg", "text": "lg"},
		{"value": "xl", "text": "xl"}
	]
}');

-- Add generic columns field (reusable across components) - slider from 1 to 6
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_cols', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "1", "text": "1"},
		{"value": "2", "text": "2"},
		{"value": "3", "text": "3"},
		{"value": "4", "text": "4"},
		{"value": "5", "text": "5"},
		{"value": "6", "text": "6"}
	]
}');

-- ===========================================
-- FLEX COMPONENT
-- ===========================================

-- Add new style 'flex' based on Mantine Flex component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'flex',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Flex component for flexible layouts',
    1
);

-- Add Flex-specific fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('flex'), get_field_id('mantine_gap'), 'md', 'Sets the gap between flex items. For more information check https://mantine.dev/core/flex', 0, 0, 'Gap');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('flex'), get_field_id('mantine_justify'), NULL, 'Sets the justify-content property. For more information check https://mantine.dev/core/flex', 0, 0, 'Justify');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('flex'), get_field_id('mantine_align'), NULL, 'Sets the align-items property. For more information check https://mantine.dev/core/flex', 0, 0, 'Align Items');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('flex'), get_field_id('mantine_direction'), 'row', 'Sets the flex-direction property. For more information check https://mantine.dev/core/flex', 0, 0, 'Direction');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('flex'), get_field_id('mantine_wrap'), 'nowrap', 'Sets the flex-wrap property. For more information check https://mantine.dev/core/flex', 0, 0, 'Wrap');

-- Reuse existing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('flex'), get_field_id('mantine_width'), NULL, 'Sets the width of the Flex component. For more information check https://mantine.dev/core/flex', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('flex'), get_field_id('mantine_height'), NULL, 'Sets the height of the Flex component. For more information check https://mantine.dev/core/flex', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('flex'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Flex will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/flex', 0, 1, 'Use Mantine Style');

-- ===========================================
-- GROUP COMPONENT
-- ===========================================

-- Add new style 'group' based on Mantine Group component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'group',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Group component for horizontal layouts',
    1
);

-- Add Group-specific fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('group'), get_field_id('mantine_gap'), 'md', 'Sets the gap between group items. For more information check https://mantine.dev/core/group', 0, 0, 'Gap');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('group'), get_field_id('mantine_justify'), NULL, 'Sets the justify-content property. For more information check https://mantine.dev/core/group', 0, 0, 'Justify');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('group'), get_field_id('mantine_align'), NULL, 'Sets the align-items property. For more information check https://mantine.dev/core/group', 0, 0, 'Align Items');

-- Add Group-specific fields - use segment type
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_group_wrap', get_field_type_id('segment'), 0, '{"options":[
{"value":"0","text":"No Wrap"},
{"value":"1","text":"Wrap"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('group'), get_field_id('mantine_group_wrap'), '0', 'If `wrap` prop is set Group will wrap items to the next line when there is not enough space. For more information check https://mantine.dev/core/group', 0, 0, 'Wrap');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_group_grow', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('group'), get_field_id('mantine_group_grow'), '0', 'If `grow` prop is set Group will take all available space. For more information check https://mantine.dev/core/group', 0, 0, 'Grow');

-- Reuse existing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('group'), get_field_id('mantine_width'), NULL, 'Sets the width of the Group component. For more information check https://mantine.dev/core/group', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('group'), get_field_id('mantine_height'), NULL, 'Sets the height of the Group component. For more information check https://mantine.dev/core/group', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('group'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Group will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/group', 0, 1, 'Use Mantine Style');

-- ===========================================
-- SIMPLEGRID COMPONENT
-- ===========================================

-- Add new style 'simpleGrid' based on Mantine SimpleGrid component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'simpleGrid',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine SimpleGrid component for responsive grid layouts',
    1
);

-- Add SimpleGrid-specific fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('simpleGrid'), get_field_id('mantine_cols'), '3', 'Sets the number of columns in the grid (1-6). For more information check https://mantine.dev/core/simple-grid', 0, 0, 'Columns');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('simpleGrid'), get_field_id('mantine_spacing'), 'md', 'Sets the spacing between grid items. For more information check https://mantine.dev/core/simple-grid', 0, 0, 'Spacing');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('simpleGrid'), get_field_id('mantine_breakpoints'), NULL, 'Sets responsive breakpoints for different screen sizes. For more information check https://mantine.dev/core/simple-grid', 0, 0, 'Breakpoints');

-- Add vertical spacing field (reusable across components) - use slider
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_vertical_spacing', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "0", "text": "None"},
		{"value": "xs", "text": "xs"},
		{"value": "sm", "text": "sm"},
		{"value": "md", "text": "md"},
		{"value": "lg", "text": "lg"},
		{"value": "xl", "text": "xl"}
	]
}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('simpleGrid'), get_field_id('mantine_vertical_spacing'), NULL, 'Sets the vertical spacing between grid items. For more information check https://mantine.dev/core/simple-grid', 0, 0, 'Vertical Spacing');

-- Reuse existing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('simpleGrid'), get_field_id('mantine_width'), NULL, 'Sets the width of the SimpleGrid component. For more information check https://mantine.dev/core/simple-grid', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('simpleGrid'), get_field_id('mantine_height'), NULL, 'Sets the height of the SimpleGrid component. For more information check https://mantine.dev/core/simple-grid', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('simpleGrid'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set SimpleGrid will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/simple-grid', 0, 1, 'Use Mantine Style');

-- ===========================================
-- SPACE COMPONENT
-- ===========================================

-- Add new style 'space' based on Mantine Space component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'space',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Space component for adding spacing',
    0
);

-- Add Space-specific fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('space'), get_field_id('mantine_slider_size'), 'md', 'Sets the size of the space. For more information check https://mantine.dev/core/space', 0, 0, 'Size');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_space_h', get_field_type_id('segment'), 0, '{"options":[
{"value":"0","text":"Vertical"},
{"value":"1","text":"Horizontal"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('space'), get_field_id('mantine_space_h'), '0', 'If `h` prop is set Space will add horizontal spacing, otherwise it adds vertical spacing. For more information check https://mantine.dev/core/space', 0, 0, 'Direction');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('space'), get_field_id('mantine_width'), NULL, 'Sets the width of the Space component. For more information check https://mantine.dev/core/space', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('space'), get_field_id('mantine_height'), NULL, 'Sets the height of the Space component. For more information check https://mantine.dev/core/space', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('space'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Space will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/space', 0, 1, 'Use Mantine Style');

-- ===========================================
-- STACK COMPONENT
-- ===========================================

-- Add new style 'stack' based on Mantine Stack component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'stack',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Stack component for vertical layouts',
    1
);

-- Add Stack-specific fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('stack'), get_field_id('mantine_gap'), 'md', 'Sets the gap between stack items. For more information check https://mantine.dev/core/stack', 0, 0, 'Gap');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('stack'), get_field_id('mantine_justify'), NULL, 'Sets the justify-content property. For more information check https://mantine.dev/core/stack', 0, 0, 'Justify');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('stack'), get_field_id('mantine_align'), NULL, 'Sets the align-items property. For more information check https://mantine.dev/core/stack', 0, 0, 'Align Items');

-- Reuse existing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('stack'), get_field_id('mantine_width'), NULL, 'Sets the width of the Stack component. For more information check https://mantine.dev/core/stack', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('stack'), get_field_id('mantine_height'), NULL, 'Sets the height of the Stack component. For more information check https://mantine.dev/core/stack', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('stack'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Stack will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/stack', 0, 1, 'Use Mantine Style');

-- ===========================================
-- GRID COMPONENT
-- ===========================================

-- Add new style 'grid' based on Mantine Grid component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'grid',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Grid component for responsive 12 columns grid system',
    1
);

-- Add Grid-specific fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid'), get_field_id('mantine_cols'), '12', 'Sets the total number of columns in the grid (default 12). For more information check https://mantine.dev/core/grid', 0, 0, 'Columns');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid'), get_field_id('mantine_gap'), 'md', 'Sets the gutter (spacing) between grid columns. For more information check https://mantine.dev/core/grid', 0, 0, 'Gutter');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid'), get_field_id('mantine_justify'), NULL, 'Sets the justify-content CSS property for the grid. For more information check https://mantine.dev/core/grid', 0, 0, 'Justify');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid'), get_field_id('mantine_align'), NULL, 'Sets the align-items CSS property for the grid. For more information check https://mantine.dev/core/grid', 0, 0, 'Align');

-- Add grid-specific field for overflow
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_grid_overflow', get_field_type_id('segment'), 0, '{"options":[
{"value":"visible","text":"Visible"},
{"value":"hidden","text":"Hidden"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid'), get_field_id('mantine_grid_overflow'), 'visible', 'Sets the overflow CSS property for the grid container. For more information check https://mantine.dev/core/grid', 0, 0, 'Overflow');

-- Reuse existing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid'), get_field_id('mantine_width'), NULL, 'Sets the width of the Grid component. For more information check https://mantine.dev/core/grid', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid'), get_field_id('mantine_height'), NULL, 'Sets the height of the Grid component. For more information check https://mantine.dev/core/grid', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Grid will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/grid', 0, 1, 'Use Mantine Style');

-- ===========================================
-- GRID-COLUMN COMPONENT
-- ===========================================

-- Add new style 'grid-column' based on Mantine Grid.Col component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'grid-column',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Grid.Col component for grid column with span, offset, and order controls',
    1
);

-- Add Grid.Col-specific fields
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_grid_span', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "1", "text": "1"},
		{"value": "2", "text": "2"},
		{"value": "3", "text": "3"},
		{"value": "4", "text": "4"},
		{"value": "5", "text": "5"},
		{"value": "6", "text": "6"},
		{"value": "7", "text": "7"},
		{"value": "8", "text": "8"},
		{"value": "9", "text": "9"},
		{"value": "10", "text": "10"},
		{"value": "11", "text": "11"},
		{"value": "12", "text": "12"},
		{"value": "auto", "text": "Auto"},
		{"value": "content", "text": "Content"}
	]
}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid-column'), get_field_id('mantine_grid_span'), '1', 'Sets the span (width) of the column. Number from 1-12 or "auto"/"content". For more information check https://mantine.dev/core/grid', 0, 0, 'Span');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_grid_offset', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "0", "text": "0"},
		{"value": "1", "text": "1"},
		{"value": "2", "text": "2"},
		{"value": "3", "text": "3"},
		{"value": "4", "text": "4"},
		{"value": "5", "text": "5"},
		{"value": "6", "text": "6"},
		{"value": "7", "text": "7"},
		{"value": "8", "text": "8"},
		{"value": "9", "text": "9"},
		{"value": "10", "text": "10"},
		{"value": "11", "text": "11"}
	]
}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid-column'), get_field_id('mantine_grid_offset'), '0', 'Sets the offset (left margin) of the column. Number from 0-11. For more information check https://mantine.dev/core/grid', 0, 0, 'Offset');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_grid_order', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "1", "text": "1"},
		{"value": "2", "text": "2"},
		{"value": "3", "text": "3"},
		{"value": "4", "text": "4"},
		{"value": "5", "text": "5"},
		{"value": "6", "text": "6"},
		{"value": "7", "text": "7"},
		{"value": "8", "text": "8"},
		{"value": "9", "text": "9"},
		{"value": "10", "text": "10"},
		{"value": "11", "text": "11"},
		{"value": "12", "text": "12"}
	]
}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid-column'), get_field_id('mantine_grid_order'), NULL, 'Sets the order of the column for reordering. Number from 1-12. For more information check https://mantine.dev/core/grid', 0, 0, 'Order');

-- Add grid-column specific field for grow
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_grid_grow', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid-column'), get_field_id('mantine_grid_grow'), '0', 'If `grow` prop is set, column will grow to fill the remaining space in the row. For more information check https://mantine.dev/core/grid', 0, 0, 'Grow');

-- Reuse existing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid-column'), get_field_id('mantine_width'), NULL, 'Sets the width of the Grid.Col component. For more information check https://mantine.dev/core/grid', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid-column'), get_field_id('mantine_height'), NULL, 'Sets the height of the Grid.Col component. For more information check https://mantine.dev/core/grid', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('grid-column'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Grid.Col will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/grid', 0, 1, 'Use Mantine Style');

-- ===========================================
-- TABS COMPONENT
-- ===========================================

-- Add new style 'tabs' based on Mantine Tabs component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'tabs',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Tabs component for switching between different views',
    1
);

-- Add Tabs-specific fields
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_tabs_variant', get_field_type_id('select'), 0, '{"searchable": false, "clearable": false, "options":[
{"value":"default","text":"Default"},
{"value":"outline","text":"Outline"},
{"value":"pills","text":"Pills"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tabs'), get_field_id('mantine_tabs_variant'), 'default', 'Sets the variant of the tabs. For more information check https://mantine.dev/core/tabs', 0, 0, 'Variant');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_tabs_orientation', get_field_type_id('segment'), 0, '{"options":[
{"value":"horizontal","text":"Horizontal"},
{"value":"vertical","text":"Vertical"}
]}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tabs'), get_field_id('mantine_tabs_orientation'), 'horizontal', 'Sets the orientation of the tabs. For more information check https://mantine.dev/core/tabs', 0, 0, 'Orientation');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_tabs_radius', get_field_type_id('slider'), 0, '{
	"options": [
		{"value": "xs", "text": "xs"},
		{"value": "sm", "text": "sm"},
		{"value": "md", "text": "md"},
		{"value": "lg", "text": "lg"},
		{"value": "xl", "text": "xl"}
	]
}');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tabs'), get_field_id('mantine_tabs_radius'), 'sm', 'Sets the border radius of the tabs. For more information check https://mantine.dev/core/tabs', 0, 0, 'Radius');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tabs'), get_field_id('mantine_color'), 'blue', 'Sets the color of the tabs. For more information check https://mantine.dev/core/tabs', 0, 0, 'Color');

-- Reuse existing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tabs'), get_field_id('mantine_width'), NULL, 'Sets the width of the Tabs component. For more information check https://mantine.dev/core/tabs', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tabs'), get_field_id('mantine_height'), NULL, 'Sets the height of the Tabs component. For more information check https://mantine.dev/core/tabs', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tabs'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Tabs will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/tabs', 0, 1, 'Use Mantine Style');

-- ===========================================
-- TAB COMPONENT
-- ===========================================

-- Add new style 'tab' based on Mantine Tabs.Tab component
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'tab',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Mantine Tabs.Tab component for individual tab items within a tabs component. Can contain child components for tab panel content.',
    1
);

-- Add content field for tab label (using existing content field)
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tab'), get_field_id('label'), NULL, 'Sets the label/content of the tab that will be displayed to users. For more information check https://mantine.dev/core/tabs', 0, 0, 'Label');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_tab_left_section', get_field_type_id('select-icon'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tab'), get_field_id('mantine_tab_left_section'), NULL, 'Sets the left section (icon) of the tab. For more information check https://mantine.dev/core/tabs', 0, 0, 'Left Icon');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_tab_right_section', get_field_type_id('select-icon'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tab'), get_field_id('mantine_tab_right_section'), NULL, 'Sets the right section (icon) of the tab. For more information check https://mantine.dev/core/tabs', 0, 0, 'Right Icon');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_tab_disabled', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tab'), get_field_id('mantine_tab_disabled'), '0', 'If `disabled` prop is set, tab will be disabled. For more information check https://mantine.dev/core/tabs', 0, 0, 'Disabled');

-- Reuse existing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tab'), get_field_id('mantine_width'), NULL, 'Sets the width of the Tabs.Tab component. For more information check https://mantine.dev/core/tabs', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tab'), get_field_id('mantine_height'), NULL, 'Sets the height of the Tabs.Tab component. For more information check https://mantine.dev/core/tabs', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('tab'), get_field_id('use_mantine_style'), 1, 'If `useMantineStyle` prop is set Tabs.Tab will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/tabs', 0, 1, 'Use Mantine Style');

-- ===========================================
-- STYLE RELATIONSHIPS
-- ===========================================

-- Define that grid-column can ONLY be added inside grid
INSERT IGNORE INTO styles_allowed_relationships (id_parent_style, id_child_style)
SELECT s1.id, s2.id FROM styles s1, styles s2
WHERE s1.name = 'grid' AND s2.name = 'grid-column';

-- Define that tab can ONLY be added inside tabs
INSERT IGNORE INTO styles_allowed_relationships (id_parent_style, id_child_style)
SELECT s1.id, s2.id FROM styles s1, styles s2
WHERE s1.name = 'tabs' AND s2.name = 'tab';

