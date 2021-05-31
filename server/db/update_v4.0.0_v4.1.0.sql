-- set DB version
UPDATE version
SET version = 'v4.1.0';

-- add pageAccessTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'mobile', 'Mobile', 'The page will be loaded only for mobile apps');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'web', 'Web', 'The page will be loaded only for the website');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'mobile_and_web', 'Mobile and web', 'The page will be loaded for web and mobile');

ALTER TABLE `pages`
ADD COLUMN `id_pageAccessTypes` int(10) UNSIGNED ZEROFILL,
ADD CONSTRAINT `pages_fk_id_pacgeAccessTypes` FOREIGN KEY (`id_pageAccessTypes`) REFERENCES `lookups` (`id`);

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE type_code = 'pageAccessTypes' AND lookup_code = 'mobile_and_web');

-- Add new style entryList
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('entryList', '2', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Wrap other styles that later visualize list of entries (inserted via `formUserInput`).');

-- Add new field type `select-formName` and field `formName` in style entryList
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-formName', '8');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'formName', get_field_type_id('select-formName'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('entryList'), get_field_id('formName'), '', 'Select a form name which will be linked to the style');

-- add field children to style entryList
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryList'), get_field_id('children'), 0, 'Children that can be added to the style. It is used to design how the entry in the list will looks like.');
-- add field css to style entryList
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryList'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');

-- Add new style entryRecord
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('entryRecord', '2', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Wrap other styles that later visualize a record from the entry list');
-- Add field `formName` in style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('entryRecord'), get_field_id('formName'), '', 'Select a form name which will be linked to the style');
-- add field children to style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecord'), get_field_id('children'), 0, 'Children that can be added to the style. It is used to design how the entry will looks like.');
-- add field css to style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecord'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');

-- Crate new style group
INSERT INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES (NULL, 'Mobile', 'Styles that are only used by the mobile application', '79');

-- Add new style calendar
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('calendar', '1', (select id from styleGroup where `name` = 'Mobile' limit 1), 'Calendar style');
-- add field css to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');
-- add field config to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('config'), NULL, 'Define the configuration of the calendar. Refer to the documentation of [Ionic2-Calendar](https://github.com/twinssbc/Ionic2-Calendar) for more information');
-- add field title to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('title'), NULL, 'Title of the calendar component');
-- add new field label_month
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_month', get_field_type_id('text'), '1');
-- add new field label_week
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_week', get_field_type_id('text'), '1');
-- add new field label_day
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_day', get_field_type_id('text'), '1');
-- add field label_month to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('label_month'), NULL, 'Label for the month button');
-- add field label_week to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('label_week'), NULL, 'Label for the week button');
-- add field label_day to style calendar
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('calendar'), get_field_id('label_day'), NULL, 'Label for the day button');

-- Add new field `icon` in style tabs
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'icon', get_field_type_id('text'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('tab'), get_field_id('icon'), '', 'Show icon; For web font awsome icons are used; For mobile ionicicons are used.');

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_address', get_field_type_id('text'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('email_address'), '@email_user', 'Use `@email_user` to retrive automaticaly the user email. Emails are separated by the MAIL_SEPARATOR. It is `;`');

-- Add new field `own_entries_only` in style entryList
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'own_entries_only', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('entryList'), get_field_id('own_entries_only'), '1', 'If selected the entry list will load only the records entered by the user.');

-- Add new field `own_entries_only` in style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('entryRecord'), get_field_id('own_entries_only'), '1', 'If selected the entry list will load only the records entered by the user.');

-- Add new field `own_entries_only` in style entryRecord
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('own_entries_only'), '1', 'If selected the entry list will load only the records entered by the user.');

-- enable field Ajax for formUserInput
update styles_fields
set disabled = 0
where id_fields = get_field_id('ajax') and id_styles = get_style_id('formUserInput');

-- Add new field type `select-platform` and field `platform` in style conditionalCointainer
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-platform', '8');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'platform', get_field_type_id('select-platform'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('conditionalContainer'), get_field_id('platform'), 'mobile_and_web', 'Select for which platform the conditional container will be loaded');

-- Add field 'gender_divers' in style valdiate
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'gender_divers', get_field_type_id('text'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('validate'), get_field_id('gender_divers'), 'divers', 'The label next to the divers radio button option.');

# executed on studybuddy

#gen all functions and procs