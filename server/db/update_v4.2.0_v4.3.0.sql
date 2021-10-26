-- set DB version
UPDATE version
SET version = 'v4.3.0';

UPDATE styles
SET id_group = 1
WHERE name = "formUserInput";

 -- add column hidden in table styles_fields
 ALTER TABLE styles_fields 
 ADD COLUMN hidden INT DEFAULT 0;

-- add style formUserInputLog
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('formUserInputLog', '2', (select id from styleGroup where `name` = 'Form' limit 1), ' stores the data from all child input fields into the database. All data is entered as a log');

-- add fields to formUserInputLog, copy them from formUserInput
INSERT INTO styles_fields (id_styles, id_fields, default_value, help)
SELECT get_style_id('formUserInputLog'), id_fields, default_value, help
FROM styles_fields
WHERE id_styles = get_style_id('formUserInput') and id_fields <> get_field_id('is_log');

-- add field `is_log` to style `formUserInputLog`
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `hidden`, `help`) VALUES (get_style_id('formUserInputLog'), get_field_id('is_log'), 1, 1,'This fiels allows to control how the data is saved in the database:
 - `disabled`: The submission of data will always overwrite prior submissions of the same user. This means that the user will be able to continously update the data that was submitted here. Any input field that is used within this form will always show the current value stored in the database (if nothing has been submitted as of yet, the input field will be empty or set to a default).
 - `enabled`: Each submission will create a new entry in the database. Once entered, an entry cannot be removed or modified. Any input field within this form will always be empty or set to a default value (nothing will be read from the database).');
 
-- add style formUserInpuRecord
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('formUserInputRecord', '2', (select id from styleGroup where `name` = 'Form' limit 1), ' stores the data from all child input fields into the database. All data is entered as as a single row and it can be edited');

-- add fields to formUserInputLog, copy them from formUserInput
INSERT INTO styles_fields (id_styles, id_fields, default_value, help)
SELECT get_style_id('formUserInputRecord'), id_fields, default_value, help
FROM styles_fields
WHERE id_styles = get_style_id('formUserInput') and id_fields <> get_field_id('is_log');

-- add field `is_log` to style `formUserInputLog`
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `hidden`, `help`) VALUES (get_style_id('formUserInputRecord'), get_field_id('is_log'), 0, 1,'This fiels allows to control how the data is saved in the database:
 - `disabled`: The submission of data will always overwrite prior submissions of the same user. This means that the user will be able to continously update the data that was submitted here. Any input field that is used within this form will always show the current value stored in the database (if nothing has been submitted as of yet, the input field will be empty or set to a default).
 - `enabled`: Each submission will create a new entry in the database. Once entered, an entry cannot be removed or modified. Any input field within this form will always be empty or set to a default value (nothing will be read from the database).');