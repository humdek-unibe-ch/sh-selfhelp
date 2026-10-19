START TRANSACTION;

-- Bump version only with a successful migration (same transaction as INSERTs).
UPDATE version
SET version = 'v7.12.0';

-- ---------------------------------------------------------------------------
-- Style: formUserInputLogList
--
-- Combines a log form (children = form layout/inputs) with a built-in list
-- table, create/edit (modal or page), and optional delete. Always behaves as
-- a log form; is_log is forced in PHP and is NOT registered as a CMS field.
-- Deep-link keys are fixed to `record` + `sid` (section id); researchers do not configure them.
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO `styles` (`name`, `id_type`, `id_group`, `description`)
VALUES (
    'formUserInputLogList',
    (SELECT id FROM styleType WHERE name = 'component'),
    (SELECT id FROM styleGroup WHERE name = 'Form'),
    'Log form with built-in list: configure form children as usual, then list/create/edit/delete records in one style. List uses fields_map (translatable). Form can open in a Bootstrap modal or as a full-page form (list hidden). Deep-links use fixed query keys `record` and `sid` (section id) so multiple instances on one page do not collide.'
);

INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`)
VALUES ('show_list', get_field_type_id('checkbox'), 0);

INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`)
VALUES ('show_form_in_modal', get_field_type_id('checkbox'), 0);

INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`)
VALUES ('label_edit', get_field_type_id('text'), 1);

-- Core form fields (same family as formUserInputLog; no is_log)
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('children'), 0, 'The child sections that make up the form body (layout + inputs). Same as formUserInputLog.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('name'), NULL, 'A unique name to identify the form when exporting the collected data.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('label'), 'Submit', 'The label on the submit button.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('type'), 'primary', 'The visual appearance of the submit button as predefined by Bootstrap.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('alert_success'), NULL, 'Text shown after a successful insert, update, or delete. Leave empty to show no success alert.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('alert_fail'), NULL, 'Text shown after a failed insert, update, or delete (and appended after validation errors). Leave empty to use the built-in fallback messages.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('ajax'), '1', 'When checked, the form is submitted via AJAX. The list refreshes from the response without a full browser navigation when possible.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('redirect_at_end'), NULL, 'Optional page keyword to redirect to after submit. Leave empty to stay on the list.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('own_entries_only'), '1', 'If enabled, only records created by the current user are listed, editable, and deletable.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('css'), NULL, 'CSS classes on the root element. For the list DataTable, add any of these flags (copy/paste):\n`dt-searching` — search box\n`dt-bPaginate` — pagination\n`dt-bInfo` — info footer\n`dt-sortable` — sortable columns\nExample: `dt-searching dt-bPaginate dt-bInfo dt-sortable`');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('css_mobile'), NULL, 'CSS classes for the mobile version.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('condition'), NULL, 'Only if a condition resolves to true is the style rendered.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('data_config'), '', 'Optional data_config JSON for dynamic field values inside the form.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('confirmation_title'), '', 'Optional confirmation modal title before submit.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('label_cancel'), '', 'Cancel button label (submit confirmation and/or page-mode cancel).');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('confirmation_continue'), 'OK', 'Continue button label on the submit confirmation modal.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('confirmation_message'), 'Do you want to continue?', 'Message on the submit confirmation modal.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('url_cancel'), '', 'Cancel URL in page form mode. Leave empty to return to the list (strip the `record` query param).');

-- List / routing fields
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('show_list'), '1', 'If enabled (default), the list is shown when no record is selected in page mode. Disable to always show the form only.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('show_form_in_modal'), '1', 'If enabled (default), create/edit opens the form in a Bootstrap modal while the list stays visible. If disabled, create/edit replaces the list with the form only (`?record=`).');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('filter'), '', 'Optional SQL filter appended when loading list data (same idea as entryList). Leave empty to load all allowed records.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('fields_map'), '', 'Map which columns appear in the list. Use the field name as key and the column label as value. Only mapped fields are shown. This field is translatable (`display=1`), so each language can show a different set/labels. Example:\n```\n{\n  "title": "Title",\n  "timestamp": "Entry Date"\n}\n```');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('label_new'), 'New', 'Label of the button that creates a new record.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('label_edit'), 'Edit', 'Label of the edit action on each list row.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('label_delete'), '', 'Label of the delete action. Leave empty to hide delete.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('delete_title'), 'Remove Entry', 'Title of the delete confirmation modal.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('formUserInputLogList'), get_field_id('delete_content'), 'Do you want to remove the entry?', 'Content of the delete confirmation modal.');

COMMIT;
