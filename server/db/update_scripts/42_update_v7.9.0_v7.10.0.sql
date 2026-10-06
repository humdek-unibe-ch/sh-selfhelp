-- set DB version
UPDATE version
SET version = 'v7.10.0';

-- ---------------------------------------------------------------------------
-- Style: languagePicker
--
-- Offers the same choice the footer does, as a component that can be placed on
-- any page. A headless page has no footer, so without this a participant on
-- such a page cannot change language at all - which matters most on the first
-- page of a study, where the language is chosen once and then left alone. It is
-- equally useful on login and register, which are reached before a user has a
-- profile carrying a preference.
--
-- The component ships its behaviour as a js include rather than an inline
-- script: the page CSP pins a script hash, which voids `unsafe-inline` and
-- blocks every inline <script> tag (see BasePage::getCspRules()), so an inline
-- handler never runs.
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO `styles` (`name`, `id_type`, `id_group`, `description`)
VALUES (
    'languagePicker',
    (SELECT id FROM styleType WHERE name = 'component'),
    (SELECT id FROM styleGroup WHERE name = 'Form'),
    'Lets the visitor change the interface language. Renders every language configured in the CMS, so it needs no per-page configuration. Useful on headless pages and before login, where the footer picker is not available.'
);

-- `display_style` picks how the options are drawn. A select suits a footer or a
-- dense form; buttons suit a landing page where the choice is the point.
--
-- Fixed choices, so `lookups` and a dropdown rather than free text, like
-- `pageAccessTypes`. Stores the `lookup_code`, not the row id: the view
-- compares it against the literal "select".
INSERT IGNORE INTO `lookups` (`type_code`, `lookup_code`, `lookup_value`, `lookup_description`)
VALUES
    ('languagePickerDisplayStyles', 'buttons', 'Buttons', 'One button per language.'),
    ('languagePickerDisplayStyles', 'select', 'Dropdown', 'A single dropdown listing every language.');

INSERT IGNORE INTO `fieldType` (`name`, `position`) VALUES ('select-language-picker-display-style', 8);

INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`)
VALUES ('display_style', get_field_type_id('select-language-picker-display-style'), 0);

-- An install that already ran this script has the field as free text.
UPDATE `fields` SET `id_type` = get_field_type_id('select-language-picker-display-style')
 WHERE `name` = 'display_style';

-- `redirect_at_select` names the page to open once a language is chosen. Empty
-- reloads the current page, which is what the footer does.
INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`)
VALUES ('redirect_at_select', get_field_type_id('select-page-keyword'), 0);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (
    get_style_id('languagePicker'), get_field_id('display_style'), 'buttons',
    'How the languages are rendered: `buttons` (default) draws one button per language, `select` draws a dropdown. Buttons suit a page where choosing the language is the only thing to do; a select suits a footer or a form.'
);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (
    get_style_id('languagePicker'), get_field_id('redirect_at_select'), '',
    'Page to open once a language is chosen. Leave empty to reload the current page, which is what the footer picker does. Set it when the picker is a step of its own, so choosing a language also moves the visitor on.'
);

-- `highlight_selected` marks the language already in force. Useful where the
-- picker is a setting being changed, misleading where it is a first-time
-- choice: nobody has chosen yet, so it only highlights the session default.
INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`)
VALUES ('highlight_selected', get_field_type_id('checkbox'), 0);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (
    get_style_id('languagePicker'), get_field_id('highlight_selected'), '1',
    'If enabled (the default) the language currently in force is marked, so a visitor can see which one is active. Disable it where the picker is a first-time choice rather than a setting: before anyone has chosen, the mark only shows the session default, which reads as a selection the visitor did not make.'
);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (
    get_style_id('languagePicker'), get_field_id('label'), '',
    'Text shown beside the options, for example "Sprache / Language". Leave empty to render no label.'
);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (
    get_style_id('languagePicker'), get_field_id('css'), NULL,
    'Allows to assign CSS classes to the root item of the style.'
);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (
    get_style_id('languagePicker'), get_field_id('css_mobile'), NULL,
    'Allows to assign CSS classes to the root item of the style for the mobile version.'
);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (
    get_style_id('languagePicker'), get_field_id('condition'), NULL,
    'The field `condition` allows to specify a condition. Only if a condition resolves to true is the style rendered.'
);
