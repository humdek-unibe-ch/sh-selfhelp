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
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine-variant', get_field_type_id('select'), 0, '{"searchable" : false, "clearable" : false, "options":[{
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
VALUES (get_style_id('button'), get_field_id('mantine-variant'), 'filled', 'Select variant for the button. For more information check https://mantine.dev/core/button', 0, 0, 'Variant');


-- Add new field type `color-picker`
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'color-picker', '2');


-- Add field `mantine-color-picker` from type `color-picker`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine-color', get_field_type_id('color-picker'), 0, '{
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
VALUES (get_style_id('button'), get_field_id('mantine-color'), 'blue', 'Select color for the button. For more information check https://mantine.dev/core/button', 0, 0, 'Color');


-- Add new field type `slider`
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'slider', '3');

-- Add field `mantine-slider-size` from type `slider`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine-slider-size', get_field_type_id('slider'), 0, '
{
	"options": [
		{
			"value": "xs",
			"text": "XS"
		},
		{
			"value": "sm",
			"text": "SM"
		},
		{
			"value": "md",
			"text": "MD"
		},
		{
			"value": "lg",
			"text": "LG"
		},
		{
			"value": "xl",
			"text": "XL"
		}
	]
}
');

-- Add field `mantine-slider-size` from type `slider`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine-slider-radius', get_field_type_id('slider'), 0, '
{
	"options": [
		{
			"value": "xs",
			"text": "XS"
		},
		{
			"value": "sm",
			"text": "SM"
		},
		{
			"value": "md",
			"text": "MD"
		},
		{
			"value": "lg",
			"text": "LG"
		},
		{
			"value": "xl",
			"text": "XL"
		}
	]
}
');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine-slider-size'), 'sm', 'Select slider size for the button. For more information check https://mantine.dev/core/button', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine-slider-radius'), 'sm', 'Select slider size for the button. For more information check https://mantine.dev/core/button', 0, 0, 'Radius');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine-fullwidth', get_field_type_id('checkbox'), 0, null);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`) 
VALUES (get_style_id('button'), get_field_id('mantine-fullwidth'), '0', 'If `fullWidth`	 prop is set Button will take 100% of parent width', 0, 0, 'Full Width');
