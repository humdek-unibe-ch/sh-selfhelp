-- add field checkbox with name submit_and_send_email in form
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'submit_and_send_email', get_field_type_id('checkbox'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('form'), get_field_id('submit_and_send_email'), 0, 'Selecting submit and send email will add additional button to the form. If the user click on that button the data inut will ne send on his/her email.');

-- add field checkbox with name submit_and_send_email in formUserInput
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('submit_and_send_email'), 0, 'Selecting submit and send email will add additional button to the form. If the user click on that button the data inut will ne send on his/her email.');

-- add field text with name submit_and_send_label in form
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'submit_and_send_label', get_field_type_id('text'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('form'), get_field_id('submit_and_send_label'), '', 'The label on the submit and send button');

-- add field text with name submit_and_send_label in formUserInput
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('submit_and_send_label'), '', 'The label on the submit and send button');

-- add field markdown with email_subject in formUserInput
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_subject', get_field_type_id('markdown'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('email_subject'), '', 'The email subject that will be send. It could be dynamically configured. [More information](https://selfhelp.psy.unibe.ch/demo/style/454)');

-- add field markdown with name email_body in formUserInput
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_body', get_field_type_id('markdown'), '1');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('formUserInput'), get_field_id('email_body'), '', 'The email boy that will be send. It could be dynamically configured. [More information](https://selfhelp.psy.unibe.ch/demo/style/454)');

-- add field data_config in formUserInput
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('formUserInput'), get_field_id('data_config'), '', 
'In this ***JSON*** field we can configure a data retrieve params from the DB, either `static` or `dynamic` data. Example: 
 ```
 [
	{
		"type": "static|dynamic",
		"table": "table_name | #url_param1",
        "retrieve": "first | last | all",
		"fields": [
			{
				"field_name": "name | #url_param2",
				"field_holder": "@field_1",
				"not_found_text": "my field was not found"				
			}
		]
	}
]
```
If the page supports parameters, then the parameter can be accessed with `#` and the name of the paramer. Example `#url_param_name`. 

In order to inlcude the retrieved data in the markdown field, include the `field_holder` that wa defined in the markdown text.

We can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;)

[More information](https://selfhelp.psy.unibe.ch/demo/style/454)');