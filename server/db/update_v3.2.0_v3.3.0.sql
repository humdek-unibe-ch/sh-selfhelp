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

DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data //

CREATE PROCEDURE get_form_data( form_id_param INT )
BEGIN  
    SET @@group_concat_max_len = 32000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when field_name = "',
		  field_name,
		  '" then value end) as `',
		  replace(field_name, ' ', ''), '`'
		)
	  ) INTO @sql
	from view_user_input
    where form_id = form_id_param;
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select user_id, form_name, edit_time, user_code, ', @sql, ' , removed as deleted from view_user_input
		where form_id = ', form_id_param,
		' group by user_id, form_name, edit_time, user_code, removed');

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

-- Add new style version
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('version', '1', (select id from styleGroup where `name` = 'Admin' limit 1), 'Add information about the DB version and for the git version of Selfhelp');

-- add table verison
CREATE TABLE `version` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `version` varchar(100)    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `version` (`version`) VALUES ('v3.3.0');
