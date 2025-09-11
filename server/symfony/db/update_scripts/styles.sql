-- ===========================================
-- STYLES.SQL - MANTINE COMPONENTS DEFINITION
-- ===========================================
-- EXECUTION ORDER:
-- 1. Create 'mantine' style group
-- 2. Create styles
-- 3. Create new fields (if needed)
-- 4. Link fields to styles in styles_fields table
-- ===========================================

-- Create 'mantine' style group for Mantine-specific components
INSERT IGNORE INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES (NULL, 'mantine', 'Mantine UI components for modern web interfaces', 10);

-- ===========================================
-- CHECKBOX STYLE
-- ===========================================

-- Add checkbox style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'checkbox',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Checkbox input component with Mantine styling',
    0
);

-- Create new fields for checkbox if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_checkbox_checked', get_field_type_id('checkbox'), 0, null);
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_checkbox_indeterminate', get_field_type_id('checkbox'), 0, null);

-- Link fields to checkbox style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('label'), NULL, 'If this field is set, a this text will be rendered next to the checkbox.', 0, 0, 'Label');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('is_required'), '0', 'If enabled the form can only be submitted if the checkbox is `checked`', 0, 0, 'Required');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('name'), NULL, 'The name of the input form field. This name must be unique within a form.', 0, 0, 'Name');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('value'), '0', 'The value of the input', 0, 0, 'Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('disabled'), '0', 'If `disabled` prop is set Checkbox will be disabled. For more information check https://mantine.dev/core/checkbox', 0, 0, 'Disabled');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('locked_after_submit'), '0', 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.', 0, 0, 'Locked After Submit');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('toggle_switch'), '0', 'If enabled and the `type` of the input is `checkbox`, the input will be presented as a `toggle switch`', 0, 0, 'Toggle Switch');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('checkbox_value'), '1', 'What value will be saved when the control is checked.', 0, 0, 'Checked Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Checkbox will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/checkbox', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('mantine_size'), 'sm', 'Sets the size of the checkbox. For more information check https://mantine.dev/core/checkbox', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('mantine_radius'), 'sm', 'Sets the border radius of the checkbox. For more information check https://mantine.dev/core/checkbox', 0, 0, 'Radius');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('mantine_checkbox_checked'), '0', 'If `checked` prop is set, checkbox will be in checked state. For more information check https://mantine.dev/core/checkbox', 0, 0, 'Checked');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('checkbox'), get_field_id('mantine_checkbox_indeterminate'), '0', 'If `indeterminate` prop is set, checkbox will be in indeterminate state. For more information check https://mantine.dev/core/checkbox', 0, 0, 'Indeterminate');


-- ===========================================
-- INPUT STYLE
-- ===========================================

-- Add input style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'input',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Input component with Mantine styling',
    0
);

-- Link fields to input style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('label'), NULL, 'If this field is set, a this text will be rendered next to the input.', 0, 0, 'Label');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('is_required'), '0', 'If enabled the form can only be submitted if the input has a value', 0, 0, 'Required');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('name'), NULL, 'The name of the input form field. This name must be unique within a form.', 0, 0, 'Name');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('value'), NULL, 'The value of the input', 0, 0, 'Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('placeholder'), NULL, 'The placeholder text shown when the input is empty', 0, 0, 'Placeholder');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('disabled'), '0', 'If `disabled` prop is set Input will be disabled. For more information check https://mantine.dev/core/input', 0, 0, 'Disabled');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('locked_after_submit'), '0', 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.', 0, 0, 'Locked After Submit');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Input will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/input', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('mantine_size'), 'sm', 'Sets the size of the input. For more information check https://mantine.dev/core/input', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('mantine_radius'), 'sm', 'Sets the border radius of the input. For more information check https://mantine.dev/core/input', 0, 0, 'Radius');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('mantine_variant'), 'default', 'Sets the variant of the input. For more information check https://mantine.dev/core/input', 0, 0, 'Variant');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('mantine_left_icon'), NULL, 'Sets the left icon of the input. For more information check https://mantine.dev/core/input', 0, 0, 'Left Icon');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('input'), get_field_id('mantine_right_icon'), NULL, 'Sets the right icon of the input. For more information check https://mantine.dev/core/input', 0, 0, 'Right Icon');

-- ===========================================
-- RADIO STYLE
-- ===========================================

-- Add radio style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'radio',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Radio input component with Mantine styling',
    0
);

-- Link fields to radio style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('label'), NULL, 'If this field is set, a this text will be rendered next to the radio.', 0, 0, 'Label');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('is_required'), '0', 'If enabled the form can only be submitted if the radio is checked', 0, 0, 'Required');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('name'), NULL, 'The name of the input form field. This name must be unique within a form.', 0, 0, 'Name');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('value'), '0', 'The value of the input', 0, 0, 'Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('disabled'), '0', 'If `disabled` prop is set Radio will be disabled. For more information check https://mantine.dev/core/radio', 0, 0, 'Disabled');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('locked_after_submit'), '0', 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.', 0, 0, 'Locked After Submit');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Radio will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/radio', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('mantine_size'), 'sm', 'Sets the size of the radio. For more information check https://mantine.dev/core/radio', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('mantine_color'), 'blue', 'Sets the color of the radio. For more information check https://mantine.dev/core/radio', 0, 0, 'Color');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('radio'), get_field_id('mantine_radio_options'), NULL, 'JSON array of radio options. Each option should have value and label properties.', 0, 0, 'Radio Options');

-- ===========================================
-- SLIDER STYLE
-- ===========================================

-- Add slider style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'slider',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Slider input component with Mantine styling',
    0
);

-- Link fields to slider style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('label'), NULL, 'If this field is set, a this text will be rendered next to the slider.', 0, 0, 'Label');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('is_required'), '0', 'If enabled the form can only be submitted if the slider has a value', 0, 0, 'Required');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('name'), NULL, 'The name of the input form field. This name must be unique within a form.', 0, 0, 'Name');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('value'), '0', 'The value of the slider', 0, 0, 'Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('disabled'), '0', 'If `disabled` prop is set Slider will be disabled. For more information check https://mantine.dev/core/slider', 0, 0, 'Disabled');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('locked_after_submit'), '0', 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.', 0, 0, 'Locked After Submit');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Slider will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/slider', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('mantine_size'), 'md', 'Sets the size of the slider. For more information check https://mantine.dev/core/slider', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('mantine_color'), 'blue', 'Sets the color of the slider. For more information check https://mantine.dev/core/slider', 0, 0, 'Color');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('mantine_numeric_min'), '0', 'Minimum value of the slider', 0, 0, 'Min Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('mantine_numeric_max'), '100', 'Maximum value of the slider', 0, 0, 'Max Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('slider'), get_field_id('mantine_numeric_step'), '1', 'Step value of the slider', 0, 0, 'Step');

-- ===========================================
-- TEXTAREA STYLE
-- ===========================================

-- Add textarea style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'textarea',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Textarea component with Mantine styling',
    0
);

-- Create textarea-specific fields if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_textarea_rows', get_field_type_id('select'), 0, '{"creatable": true, "searchable": false, "clearable": false, "options":[
{"value":"3","text":"3"},
{"value":"4","text":"4"},
{"value":"5","text":"5"},
{"value":"6","text":"6"},
{"value":"8","text":"8"},
{"value":"10","text":"10"}
]}');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_textarea_resize', get_field_type_id('segment'), 0, '{"options":[
{"value":"none","text":"None"},
{"value":"vertical","text":"Vertical"},
{"value":"horizontal","text":"Horizontal"},
{"value":"both","text":"Both"}
]}');

-- Link fields to textarea style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('label'), NULL, 'If this field is set, a this text will be rendered next to the textarea.', 0, 0, 'Label');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('is_required'), '0', 'If enabled the form can only be submitted if the textarea has a value', 0, 0, 'Required');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('name'), NULL, 'The name of the input form field. This name must be unique within a form.', 0, 0, 'Name');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('value'), NULL, 'The value of the textarea', 0, 0, 'Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('placeholder'), NULL, 'The placeholder text shown when the textarea is empty', 0, 0, 'Placeholder');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('disabled'), '0', 'If `disabled` prop is set Textarea will be disabled. For more information check https://mantine.dev/core/textarea', 0, 0, 'Disabled');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('locked_after_submit'), '0', 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.', 0, 0, 'Locked After Submit');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Textarea will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/textarea', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('mantine_size'), 'sm', 'Sets the size of the textarea. For more information check https://mantine.dev/core/textarea', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('mantine_radius'), 'sm', 'Sets the border radius of the textarea. For more information check https://mantine.dev/core/textarea', 0, 0, 'Radius');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('mantine_variant'), 'default', 'Sets the variant of the textarea. For more information check https://mantine.dev/core/textarea', 0, 0, 'Variant');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('mantine_textarea_rows'), '4', 'Sets the number of visible text lines for the textarea control. For more information check https://mantine.dev/core/textarea', 0, 0, 'Rows');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('textarea'), get_field_id('mantine_textarea_resize'), 'vertical', 'Sets the resize behavior of the textarea. For more information check https://mantine.dev/core/textarea', 0, 0, 'Resize');

-- ===========================================
-- SELECT STYLE
-- ===========================================

-- Add select style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'select',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Select component with Mantine styling',
    0
);

-- Create select-specific fields if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_select_searchable', get_field_type_id('checkbox'), 0, null);
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_select_clearable', get_field_type_id('checkbox'), 0, null);

-- Link fields to select style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('label'), NULL, 'If this field is set, a this text will be rendered next to the select.', 0, 0, 'Label');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('is_required'), '0', 'If enabled the form can only be submitted if the select has a value', 0, 0, 'Required');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('name'), NULL, 'The name of the input form field. This name must be unique within a form.', 0, 0, 'Name');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('value'), NULL, 'The value of the select', 0, 0, 'Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('placeholder'), NULL, 'The placeholder text shown when the select is empty', 0, 0, 'Placeholder');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('disabled'), '0', 'If `disabled` prop is set Select will be disabled. For more information check https://mantine.dev/core/select', 0, 0, 'Disabled');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('locked_after_submit'), '0', 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.', 0, 0, 'Locked After Submit');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Select will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/select', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('mantine_size'), 'sm', 'Sets the size of the select. For more information check https://mantine.dev/core/select', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('mantine_radius'), 'sm', 'Sets the border radius of the select. For more information check https://mantine.dev/core/select', 0, 0, 'Radius');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('mantine_select_searchable'), '0', 'If `searchable` prop is set, user can filter options by typing. For more information check https://mantine.dev/core/select', 0, 0, 'Searchable');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('mantine_select_clearable'), '0', 'If `clearable` prop is set, user can clear selected value. For more information check https://mantine.dev/core/select', 0, 0, 'Clearable');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('select'), get_field_id('mantine_multi_select_data'), NULL, 'JSON array of select options. Each option should have value and label properties.', 0, 0, 'Select Options');

-- ===========================================
-- ALERT STYLE
-- ===========================================

-- Add alert style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'alert',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Alert component with Mantine styling',
    1
);

-- Create alert-specific fields if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_alert_title', get_field_type_id('text'), 1, '{"placeholder": "Alert title"}');
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_alert_with_close_button', get_field_type_id('checkbox'), 0, null);

-- Link fields to alert style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('value'), NULL, 'The content/message of the alert', 0, 0, 'Message');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Alert will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/alert', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('mantine_variant'), 'light', 'Sets the variant of the alert. For more information check https://mantine.dev/core/alert', 0, 0, 'Variant');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('mantine_color'), 'blue', 'Sets the color of the alert. For more information check https://mantine.dev/core/alert', 0, 0, 'Color');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('mantine_size'), 'md', 'Sets the size of the alert. For more information check https://mantine.dev/core/alert', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('mantine_radius'), 'sm', 'Sets the border radius of the alert. For more information check https://mantine.dev/core/alert', 0, 0, 'Radius');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('mantine_alert_title'), NULL, 'Sets the title of the alert. For more information check https://mantine.dev/core/alert', 0, 0, 'Title');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('mantine_alert_with_close_button'), '0', 'If `withCloseButton` prop is set, close button will be rendered. For more information check https://mantine.dev/core/alert', 0, 0, 'With Close Button');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('alert'), get_field_id('mantine_left_icon'), NULL, 'Sets the left icon of the alert. For more information check https://mantine.dev/core/alert', 0, 0, 'Icon');

-- ===========================================
-- PROGRESS STYLE
-- ===========================================

-- Add progress style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'progress',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Progress component with Mantine styling',
    0
);

-- Create progress-specific fields if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_progress_animated', get_field_type_id('checkbox'), 0, null);
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_progress_striped', get_field_type_id('checkbox'), 0, null);

-- Link fields to progress style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('progress'), get_field_id('value'), '0', 'The value of the progress (0-100)', 0, 0, 'Value');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('progress'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Progress will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/progress', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('progress'), get_field_id('mantine_color'), 'blue', 'Sets the color of the progress. For more information check https://mantine.dev/core/progress', 0, 0, 'Color');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('progress'), get_field_id('mantine_size'), 'md', 'Sets the size of the progress. For more information check https://mantine.dev/core/progress', 0, 0, 'Size');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('progress'), get_field_id('mantine_radius'), 'sm', 'Sets the border radius of the progress. For more information check https://mantine.dev/core/progress', 0, 0, 'Radius');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('progress'), get_field_id('mantine_progress_animated'), '0', 'If `animated` prop is set, progress bar will be animated. For more information check https://mantine.dev/core/progress', 0, 0, 'Animated');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('progress'), get_field_id('mantine_progress_striped'), '0', 'If `striped` prop is set, progress bar will have stripes. For more information check https://mantine.dev/core/progress', 0, 0, 'Striped');

-- ===========================================
-- CARD STYLE
-- ===========================================

-- Add card style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'card',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Card container component with Mantine styling',
    1
);

-- Create card-specific fields if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_card_shadow', get_field_type_id('select'), 0, '{"searchable": false, "clearable": true, "options":[
{"value":"xs","text":"Extra Small"},
{"value":"sm","text":"Small"},
{"value":"md","text":"Medium"},
{"value":"lg","text":"Large"},
{"value":"xl","text":"Extra Large"}
]}');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_card_padding', get_field_type_id('select'), 0, '{"searchable": false, "clearable": true, "options":[
{"value":"xs","text":"Extra Small"},
{"value":"sm","text":"Small"},
{"value":"md","text":"Medium"},
{"value":"lg","text":"Large"},
{"value":"xl","text":"Extra Large"}
]}');

-- Add card-segment style for child components
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'card-segment',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Card segment component for organizing card content',
    1
);

-- Link fields to card style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('card'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Card will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/card', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('card'), get_field_id('mantine_card_shadow'), 'sm', 'Sets the shadow of the card. For more information check https://mantine.dev/core/card', 0, 0, 'Shadow');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('card'), get_field_id('mantine_card_padding'), 'md', 'Sets the padding of the card. For more information check https://mantine.dev/core/card', 0, 0, 'Padding');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('card'), get_field_id('mantine_radius'), 'sm', 'Sets the border radius of the card. For more information check https://mantine.dev/core/card', 0, 0, 'Radius');

-- ===========================================
-- IMAGE STYLE
-- ===========================================

-- Add image style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'image',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'Image component with Mantine styling',
    0
);

-- Create image-specific fields if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_image_src', get_field_type_id('text'), 1, '{"placeholder": "Image URL or path"}');
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_image_alt', get_field_type_id('text'), 1, '{"placeholder": "Alt text for accessibility"}');
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_image_fit', get_field_type_id('segment'), 0, '{"options":[
{"value":"contain","text":"Contain"},
{"value":"cover","text":"Cover"},
{"value":"fill","text":"Fill"},
{"value":"none","text":"None"},
{"value":"scale-down","text":"Scale Down"}
]}');

-- Link fields to image style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('image'), get_field_id('mantine_image_src'), NULL, 'The source URL of the image. For more information check https://mantine.dev/core/image', 0, 0, 'Source');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('image'), get_field_id('mantine_image_alt'), NULL, 'Alt text for the image for accessibility. For more information check https://mantine.dev/core/image', 0, 0, 'Alt Text');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('image'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set Image will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/image', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('image'), get_field_id('mantine_width'), NULL, 'Sets the width of the image. For more information check https://mantine.dev/core/image', 0, 0, 'Width');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('image'), get_field_id('mantine_height'), NULL, 'Sets the height of the image. For more information check https://mantine.dev/core/image', 0, 0, 'Height');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('image'), get_field_id('mantine_image_fit'), 'contain', 'Sets how the image should be resized to fit its container. For more information check https://mantine.dev/core/image', 0, 0, 'Object Fit');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('image'), get_field_id('mantine_radius'), 'sm', 'Sets the border radius of the image. For more information check https://mantine.dev/core/image', 0, 0, 'Radius');

-- ===========================================
-- LIST STYLE
-- ===========================================

-- Add list style
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'list',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'List component with Mantine styling',
    1
);

-- Create list-specific fields if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_list_type', get_field_type_id('segment'), 0, '{"options":[
{"value":"unordered","text":"Unordered"},
{"value":"ordered","text":"Ordered"}
]}');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_list_spacing', get_field_type_id('select'), 0, '{"searchable": false, "clearable": true, "options":[
{"value":"xs","text":"Extra Small"},
{"value":"sm","text":"Small"},
{"value":"md","text":"Medium"},
{"value":"lg","text":"Large"},
{"value":"xl","text":"Extra Large"}
]}');

-- Add list-item style for child components
INSERT IGNORE INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`, `can_have_children`) VALUES (
    NULL,
    'list-item',
    (SELECT id FROM lookups WHERE type_code = 'styleType' AND lookup_code = 'component' LIMIT 1),
    get_style_group_id('mantine'),
    'List item component for list content',
    1
);

-- Create list-item-specific fields if they don't exist
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`, `config`) VALUES (NULL, 'mantine_list_item_icon', get_field_type_id('select-icon'), 0, null);

-- Link fields to list style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('list'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set List will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/list', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('list'), get_field_id('mantine_list_type'), 'unordered', 'Sets the type of the list. For more information check https://mantine.dev/core/list', 0, 0, 'List Type');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('list'), get_field_id('mantine_list_spacing'), 'md', 'Sets the spacing between list items. For more information check https://mantine.dev/core/list', 0, 0, 'Spacing');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('list'), get_field_id('mantine_size'), 'md', 'Sets the size of the list. For more information check https://mantine.dev/core/list', 0, 0, 'Size');

-- Link fields to list-item style
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('list-item'), get_field_id('use_mantine_style'), '1', 'If `useMantineStyle` prop is set List.Item will use the Mantine style, otherwise it will be a clear element which can be styled with CSS and Tailwind CSS classes. For more information check https://mantine.dev/core/list', 0, 0, 'Use Mantine Style');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`, `disabled`, `hidden`, `title`)
VALUES (get_style_id('list-item'), get_field_id('mantine_list_item_icon'), NULL, 'Sets the icon for the list item. For more information check https://mantine.dev/core/list', 0, 0, 'Icon');

-- ===========================================
-- RELATIONSHIP DEFINITIONS
-- ===========================================

-- Define parent-child relationships for hierarchical components

-- Card can contain Card-Segment
INSERT IGNORE INTO styles_allowed_relationships (id_parent_style, id_child_style)
SELECT s1.id, s2.id FROM styles s1, styles s2
WHERE s1.name = 'card' AND s2.name = 'card-segment';

-- List can contain List-Item
INSERT IGNORE INTO styles_allowed_relationships (id_parent_style, id_child_style)
SELECT s1.id, s2.id FROM styles s1, styles s2
WHERE s1.name = 'list' AND s2.name = 'list-item';

