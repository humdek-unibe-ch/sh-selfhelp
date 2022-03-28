-- set DB version
UPDATE version
SET version = 'v5.0.0';

-- add filed condtion to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('condition'), 'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `"@__form_name__#__from_field_name__"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.' 
FROM view_style_fields
WHERE field_name = 'css' and style_name <> 'conditionalContainer';

-- add filed jquery_builder_json to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('jquery_builder_json'), 'This field contains the JSON structure for the jquery builder. The field shoudl be hidden' 
FROM view_style_fields
WHERE field_name = 'css' and style_name <> 'conditionalContainer';

-- add filed debug to all styles that have css field
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`)
SELECT style_id, get_field_id('debug'), 'If *checked*, debug messages will be rendered to the screen. These might help to understand the result of a condition evaluation. **Make sure that this field is *unchecked* once the page is productive**.' 
FROM view_style_fields
WHERE field_name = 'css' and style_name <> 'conditionalContainer' and style_name <> 'autocomplete';

-- add keyword ajax_get_lookups
INSERT INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'ajax_get_lookups', '/request/[AjaxDataSource:class]/[get_lookups:method]', 'GET|POST', '0000000005', NULL, NULL, '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_data = LAST_INSERT_ID();
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) 
VALUES ('0000000001', @id_page_data, '1', '0', '0', '0');