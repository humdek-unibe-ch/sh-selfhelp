-- insert new style
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'showUserInput', '0000000002', '0000000002');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'is_log', '0000000003', '0');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_data_time', '0000000001', '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000039', '0000000053', NULL);
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000039', '0000000088', '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000039', '0000000090', NULL);

-- update form styles
UPDATE `styles` SET `name` = 'formUserInput' WHERE `styles`.`id` = 0000000036;
DELETE FROM `styles` WHERE `styles`.`id` = 0000000037;
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'show_data', '0000000003', '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000036', '0000000089', '1');
UPDATE `styleGroup` SET `description` = 'A form is a wrapper for input fields. It allows to send content of the input fields to the server and store the data to the database. Several style are available:\r\n\r\n- `form` provides only the client-side functionality and does not do anything with the submitted data. This is intended to be connected with a custom component (required PHP programming).\r\n- `formUserInput` stores the data from all child input fields into the database and displays the latest set of data in the database as values in the child input field (if `show_data` is checked).\r\n- `showUserInput` allows to display user input data. Use the name of a form to display the corresponding data.' WHERE `styleGroup`.`id` = 0000000002;
