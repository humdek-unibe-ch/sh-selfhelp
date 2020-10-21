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
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('version', '2', (select id from styleGroup where `name` = 'Admin' limit 1), 'Add information about the DB version and for the git version of Selfhelp');

-- add table verison
CREATE TABLE `version` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `version` varchar(100)    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `version` (`version`) VALUES ('v3.3.0');

INSERT INTO sections (id_styles, name) VALUES(get_style_id('version'), 'impressum-version');
INSERT INTO sections_hierarchy (parent, child, position) VALUES((SELECT id FROM sections WHERE name = 'impressum-container'), (SELECT id FROM sections WHERE name = 'impressum-version'), 11);

-- add keyword chatSubject
INSERT INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'chatSubject', '/chat/subject/[i:gid]?/[i:uid]?', 'GET|POST', '0000000003', NULL, NULL, '0', NULL, NULL, '0000000003');

SET @id_page_data = LAST_INSERT_ID();

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`)
SELECT g.id, (SELECT id FROM pages WHERE keyword = 'chatSubject'), acl_select, acl_insert, acl_update, acl_delete
FROM groups g
INNER JOIN acl_groups acl ON (acl.id_groups = g.id)
INNER JOIN pages p ON (acl.id_pages = p.id)
WHERE keyword = 'contact';

INSERT INTO sections (id_styles, name) VALUES(get_style_id('container'), 'chatSubject-container');
INSERT INTO sections (id_styles, name) VALUES(get_style_id('chat'), 'chatSubject-chat');
INSERT INTO pages_sections (id_pages, id_Sections, position) VALUES(@id_page_data, (SELECT id FROM sections WHERE name = 'chatSubject-container'), 1);
INSERT INTO sections_hierarchy (parent, child, position) VALUES((SELECT id FROM sections WHERE name = 'chatSubject-container'), (SELECT id FROM sections WHERE name = 'chatSubject-chat'), 1);

INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`)
SELECT (SELECT id FROM sections WHERE name = 'chatSubject-chat'), id_fields, id_languages, id_genders, content
FROM sections_fields_translation
WHERE id_sections = (SELECT id FROM sections WHERE name = 'contact-chat');

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`)
SELECT @id_page_data, id_fields, id_languages, content
FROM pages_fields_translation
WHERE id_pages = (SELECT id FROM pages WHERE keyword = 'contact');

-- add keyword chatTherapist
INSERT INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'chatTherapist', '/chat/therapist/[i:gid]?/[i:uid]?', 'GET|POST', '0000000003', NULL, NULL, '0', NULL, NULL, '0000000003');

SET @id_page_data = LAST_INSERT_ID();

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
('0000000001', @id_page_data, '1', '1', '1', '1');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
('0000000002', @id_page_data, '1', '1', '0', '0');

INSERT INTO sections (id_styles, name) VALUES(get_style_id('container'), 'chatTherapist-container');
INSERT INTO sections (id_styles, name) VALUES(get_style_id('chat'), 'chatTherapist-chat');
INSERT INTO pages_sections (id_pages, id_Sections, position) VALUES(@id_page_data, (SELECT id FROM sections WHERE name = 'chatTherapist-container'), 1);
INSERT INTO sections_hierarchy (parent, child, position) VALUES((SELECT id FROM sections WHERE name = 'chatTherapist-container'), (SELECT id FROM sections WHERE name = 'chatTherapist-chat'), 1);

INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`)
SELECT (SELECT id FROM sections WHERE name = 'chatTherapist-chat'), id_fields, id_languages, id_genders, content
FROM sections_fields_translation
WHERE id_sections = (SELECT id FROM sections WHERE name = 'contact-chat');

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`)
SELECT @id_page_data, id_fields, id_languages, content
FROM pages_fields_translation
WHERE id_pages = (SELECT id FROM pages WHERE keyword = 'contact');


-- add field id_rcv_group in table chat
ALTER TABLE chat
ADD COLUMN id_rcv_group int(10) UNSIGNED ZEROFILL NOT NULL;

INSERT INTO groups (name, description)
SELECT DISTINCT chr.name, chr.description
FROM chat ch
INNER JOIN chatRoom chr ON (ch.id_rcv_grp = chr.id)
WHERE name <> 'root';

UPDATE chat ch
LEFT JOIN chatRoom chr ON (ch.id_rcv_grp = chr.id)
LEFT join groups g ON (chr.name = g.name)
SET id_rcv_group = CASE
						WHEN g.id IS NULL THEN 3
						ELSE g.id 
					END;

ALTER TABLE chat
ADD CONSTRAINT fk_chat_id_rcv_group FOREIGN KEY (id_rcv_group) REFERENCES groups(id) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO users_groups (id_users, id_groups)
SELECT u.id, g.id
FROM chatRoom cr
INNER JOIN chatRoom_users cru ON (cr.id = cru.id_chatRoom)
INNER JOIN users u ON (cru.id_users = u.id)
INNER JOIN groups g ON (cr.name = g.name);

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`)
SELECT DISTINCT g.id, (SELECT id FROM pages WHERE pages.keyword = 'chatSubject'), '1', '0', '0', '0'
FROM chatRoom cr
INNER JOIN chatRoom_users cru ON (cr.id = cru.id_chatRoom)
INNER JOIN users u ON (cru.id_users = u.id)
INNER JOIN groups g ON (cr.name = g.name);

ALTER TABLE chat
DROP FOREIGN KEY fk_chat_id_rcv_grp;

ALTER TABLE chat
DROP COLUMN id_rcv_grp;

DELETE FROM pages
WHERE keyword IN ('contact', 'chatAdminDelete', 'chatAdminInsert', 'chatAdminSelect', 'chatAdminUpdate');


