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

-- Add new style checkbox
INSERT IGNORE INTO `styles` (`name`, `id_type`, id_group, `description`) VALUES ('checkbox', '1', (select id from styleGroup where `name` = 'Mobile' limit 1), 'Exacute shortcut commands in the mobile app that can open native windows. The functinality is based on [capacitor-native-settings](https://github.com/RaphaelWoude/capacitor-native-settings)');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('checkbox'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('checkbox'), get_field_id('css_mobile'), NULL, 'Allows to assign CSS classes to the root item of the style for the mobile version.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('checkbox'), get_field_id('condition'), NULL, 'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `"@__form_name__#__from_field_name__"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('checkbox'), get_field_id('data_config'), '', 'Define data configuration for fields that are loaded from DB and can be used inside the style with their param names. The name of the field can be used between {{param_name}} to load the required value');