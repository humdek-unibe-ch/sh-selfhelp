-- add field checkbox with name submit_and_send_email in form
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'submit_and_send_email', get_field_type_id('checkbox'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('form'), get_field_id('submit_and_send_email'), 0, 'Selecting submit and send email will add additional button to the form. If the user click on that button the data inut will ne send on his/her email.');

-- add field checkbox with name submit_and_send_email in formUserInput
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('submit_and_send_email'), 0, 'Selecting submit and send email will add additional button to the form. If the user click on that button the data inut will ne send on his/her email.');