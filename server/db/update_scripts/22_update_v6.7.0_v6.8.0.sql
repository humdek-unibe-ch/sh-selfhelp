-- set DB version
UPDATE version
SET version = 'v6.8.0';

UPDATE styles_fields
SET `help` = 'Links can refer to elements within SelfHelp
Use the following syntax to achieve this:
 - link to back (browser functionality) `#back`
 - link to the last unique visited page `#last_user_page`
 - link to asset `%asset_name`
 - link to page `#page_name`
 - link to anchor on page `#page_name#wrapper_name`
 - link to root_section on a nav_page `#nav_page_name/nav_section_name`
 - link to anchor on root_section on nav_page `#nav_page_name/nav_section_name#wrapper_name`
 
Please use relative paths unless the `url` is an external link.'
WHERE id_styles IN (get_style_id('link'), get_style_id('button')) AND id_fields = get_field_id('url');

UPDATE styles_fields
SET `help` = 'Redirect `url` after the execution
Use the following syntax to achieve this:
 - link to back (browser functionality) `#back`
 - link to the last unique visited page `#last_user_page`
 - link to asset `%asset_name`
 - link to page `#page_name`
 - link to anchor on page `#page_name#wrapper_name`
 - link to root_section on a nav_page `#nav_page_name/nav_section_name`
 - link to anchor on root_section on nav_page `#nav_page_name/nav_section_name#wrapper_name`
 
Please use relative paths unless the `url` is an external link.'
WHERE id_fields = get_field_id('redirect_at_end');

DELETE FROM `styles_fields`
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('toggle_switch');

-- Add new style checkbox
INSERT IGNORE INTO `styles` (`name`, `id_type`, id_group, `description`) VALUES ('checkbox', '2', (select id from styleGroup where `name` = 'Mobile' limit 1), 'Exacute shortcut commands in the mobile app that can open native windows. The functinality is based on [capacitor-native-settings](https://github.com/RaphaelWoude/capacitor-native-settings)');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('checkbox'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('checkbox'), get_field_id('css_mobile'), NULL, 'Allows to assign CSS classes to the root item of the style for the mobile version.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('checkbox'), get_field_id('condition'), NULL, 'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `"@__form_name__#__from_field_name__"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('checkbox'), get_field_id('data_config'), '', 'Define data configuration for fields that are loaded from DB and can be used inside the style with their param names. The name of the field can be used between {{param_name}} to load the required value');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('checkbox'), get_field_id('toggle_switch'), 0, 'If enabled and the `type` of the input is `checkbox`, the input will be presented as a `toggle switch`');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('checkbox'), get_field_id('label'), '', 'Set lable for the checkbox');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('checkbox'), get_field_id('is_required'), 0, 'If enabled the form can only be submitted if the checkbox is `checked`');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('checkbox'), get_field_id('locked_after_submit'), 0, 'If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('checkbox'), get_field_id('name'), '', 'The name of the input form field. This name must be unique within a form.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('checkbox'), get_field_id('value'), '', 'The value of the input');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'checkbox_value', get_field_type_id('text'), 0);
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('checkbox'), get_field_id('checkbox_value'), 1, 'What value will be saved when the control is checked.');

-- Add new field type `color` and fields `color_background` and `color_border` to style div
INSERT IGNORE INTO `fieldType` (`name`, `position`) VALUES ('color', 9);
INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`) VALUES ('color_background', get_field_type_id('color'), '0');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('div'), get_field_id('color_background'), '', 'Set a color that will be used as a `background color` for the `div`');

INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`) VALUES ('color_border', get_field_type_id('color'), '0');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('div'), get_field_id('color_border'), '', 'Set a color that will be used as a `border color` for the `div`. If the color is set, a border will be automatically added.');

INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`) VALUES ('color_text', get_field_type_id('color'), '0');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('div'), get_field_id('color_text'), '', 'Set a color that will be used for the text inside the `div`');

INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`) VALUES ('load_as_table', get_field_type_id('checkbox'), '0');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('entryList'), get_field_id('load_as_table'), 0, 'If enabled, the children are loaded inside a table.');


INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputLog'), get_field_id('confirmation_title'), '', 'Confirmation title for the modal when the button is clicked');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputLog'), get_field_id('label_cancel'), '', 'Cancel button label on the confirmation modal');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputLog'), get_field_id('label_continue'), 'OK', 'Continue button for the modal when the button is clicked');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputLog'), get_field_id('label_message'), 'Do you want to continue?', 'The message shown on the modal');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputLog'), get_field_id('url_cancel'), '', 'The target URL of the cancel button.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputRecord'), get_field_id('confirmation_title'), '', 'Confirmation title for the modal when the button is clicked');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputRecord'), get_field_id('label_cancel'), '', 'Cancel button label on the confirmation modal');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputRecord'), get_field_id('label_continue'), 'OK', 'Continue button for the modal when the button is clicked');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputRecord'), get_field_id('label_message'), 'Do you want to continue?', 'The message shown on the modal');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('formUserInputRecord'), get_field_id('url_cancel'), '', 'The target URL of the cancel button.');

