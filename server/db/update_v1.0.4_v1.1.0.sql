ALTER TABLE `users` ADD `last_url` VARCHAR(100) NULL DEFAULT NULL AFTER `last_login`;

ALTER TABLE `styles_fields` ADD `help` LONGTEXT NULL DEFAULT NULL AFTER `default_value`;

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'json', '0000000008', '1');
SET @id_field_json = LAST_INSERT_ID();
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'json', '0000000001', '0000000004', 'allows to describe `baseStyles` with `json` Syntax');
SET @id_style_json = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_json, @id_field_json, NULL, 'The JSON string to specify the (potentially) nested base styles.');

UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'The JSON string to specify the (potentially) nested base styles.\r\n\r\nThere are a few things to note:\r\n - the key `baseStyle` must be used to indicate that the assigned object is a *style object*\r\n - the *style object* must contain the key `name` where the value must match a style name\r\n - the *style object* must contain the key `fields` where the value is an object holding all required fields of the style (refer to the <a href=\"https://selfhelp.psy.unibe.ch/demo/styles\" target=\"_blank\">style documentation</a> for more information)' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'json' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'json';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'Select for a full width container, spanning the entire width of the viewport.' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'container' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'is_fluid';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'The HTML heading level (1-6)' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'heading' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'level';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'Use <a href=\"https://en.wikipedia.org/wiki/Markdown\" target=\"_blank\">markdown</a> syntax here.' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'markdown' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'text_md';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'Only use <a href=\"https://en.wikipedia.org/wiki/Markdown\" target=\"_blank\">markdown</a> elements that can be displayed inline (e.g. bold, italic, etc).' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'markdownInline' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'text_md_inline';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'The text to appear on the button.' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'button' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'label';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'Use a full URL or any special characters as defined <a href=\"https://selfhelp.psy.unibe.ch/demo/style/440\" target=\"_blank\">here</a>.' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'button' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'url';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'The <a href=\"https://getbootstrap.com/docs/4.1/components/buttons/#examples\" target=\"_blank\">bootstrap type</a> of the button.' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'button' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'type';



UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0   | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)          | 1.1.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.3/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.3/about/license/) |\r\n| [Datatables](https://datatables.net/)                      | 1.10.18 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](https://datatables.net/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0   | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10  | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [mermaid](https://mermaidjs.github.io/)                    | 8.2.3   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [PHPMailer](https://github.com/PHPMailer/PHPMailer)        | 6.0.7   | [LGPL](https://tldrlegal.com/license/gnu-lesser-general-public-license-v2.1-(lgpl-2.1)) | [License Details](https://github.com/PHPMailer/PHPMailer#license) |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0   | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = (SELECT id FROM sections WHERE name = 'impressum-ext-markdown') AND `sections_fields_translation`.`id_fields` = (SELECT id FROM fields WHERE name = 'text_md') AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;
UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0   | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)          | 1.1.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.3/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.3/about/license/) |\r\n| [Datatables](https://datatables.net/)                      | 1.10.18 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](https://datatables.net/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0   | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1   | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10  | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [mermaid](https://mermaidjs.github.io/)                    | 8.2.3   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1   | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [PHPMailer](https://github.com/PHPMailer/PHPMailer)        | 6.0.7   | [LGPL](https://tldrlegal.com/license/gnu-lesser-general-public-license-v2.1-(lgpl-2.1)) | [License Details](https://github.com/PHPMailer/PHPMailer#license) |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0   | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = (SELECT id FROM sections WHERE name = 'impressum-ext-markdown') AND `sections_fields_translation`.`id_fields` = (SELECT id FROM fields WHERE name = 'text_md') AND `sections_fields_translation`.`id_languages` = 0000000003 AND `sections_fields_translation`.`id_genders` = 0000000001;


--
-- Table structure for table `activityType`
--

CREATE TABLE `activityType` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `activityType`
--

INSERT INTO `activityType` (`id`, `name`) VALUES
(0000000001, 'experiment'),
(0000000002, 'export');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activityType`
--
ALTER TABLE `activityType`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activityType`
--
ALTER TABLE `activityType`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `user_activity` ADD `id_type` INT UNSIGNED ZEROFILL NOT NULL DEFAULT '1' AFTER `timestamp`, ADD INDEX (`id_type`);
ALTER TABLE `user_activity` ADD CONSTRAINT `fk_user_activity_fk_id_type` FOREIGN KEY (`id_type`) REFERENCES `activityType`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
UPDATE `user_activity` SET `id_type` = (SELECT `id` FROM `activityType` WHERE `name` = 'export') WHERE `url` LIKE '%/admin/export/%';

INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'userProgress', '0000000001', '0000000009', 'A progress bar to indicate the overall experiment progress of a user.');
SET @id_style_userProgress = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_userProgress, (SELECT `id` FROM `fields` WHERE `name` = 'type'), NULL, '.Use the type to change the appearance of individual progress bars');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_userProgress, (SELECT `id` FROM `fields` WHERE `name` = 'is_striped'), NULL, 'iIf set apply a stripe via CSS gradient over the progress bar’s background color.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_userProgress, (SELECT `id` FROM `fields` WHERE `name` = 'has_label'), NULL, 'If set display the progress in numbers ontop of the progress bar.');

-- issue 164
--
-- Table structure for table `pages_fields`
--

CREATE TABLE `pages_fields` (
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fields` int(10) UNSIGNED ZEROFILL NOT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  `help` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `pages_fields`
--
ALTER TABLE `pages_fields`
  ADD PRIMARY KEY (`id_pages`,`id_fields`),
  ADD KEY `id_pages` (`id_pages`),
  ADD KEY `id_fields` (`id_fields`);

--
-- Constraints for table `pages_fields`
--
ALTER TABLE `pages_fields`
  ADD CONSTRAINT `fk_page_fields_id_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_page_fields_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES('description', (SELECT id FROM `fieldType` WHERE `name` = 'textarea'), 1);
SET @id_field_description = LAST_INSERT_ID();
INSERT INTO `pages_fields` (`id_pages`, `id_fields`, `default_value`, `help`) VALUES((SELECT id FROM `pages` WHERE `keyword` = 'home'), @id_field_description, NULL, 'A short description of the research project. This field will be used as `meta:description` in the HTML header. Some services use this tag to provide the user with information on the webpage (e.g. automatic link-replacement in messaging tools on smartphones use this description.)');

-- style mermaidForm

#fieldType code
INSERT INTO fieldType (name, position) VALUES ('code', 42);
#field code
INSERT INTO fields (name, id_type, display) VALUES ('code', (select id from `fieldType` where `name` = 'code' limit 1), 1);
#mermaidForm style
INSERT INTO `styles` (`name`, `id_type`, id_group, description) VALUES ('mermaidForm', '2', (select id from styleGroup where `name` = 'Form' limit 1), 'Style to create diagrams using markdown syntax. Use <a href="https://mermaidjs.github.io/demos.html" target="_blank">mermaid markdown</a> syntax here.');
#mermaid styles fields
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'code' limit 1), 'Use <a href="https://mermaidjs.github.io/demos.html" target="_blank">mermaid markdown</a> syntax here.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'name' limit 1), 'Name of the form');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'label' limit 1), 'Label of the form');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'type' limit 1), 'Type of the form');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'children' limit 1), 'Add only styles from type `input` for the edditable nodes. If they have input they could be eddited by the subject when they are clicked.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'mermaidForm' limit 1), (select id from `fields` where `name` = 'alert_success' limit 1), 'The alert message for the succes');

-- merge i118

ALTER TABLE `user_input` ADD `removed` BOOLEAN NOT NULL DEFAULT FALSE AFTER `edit_time`;

INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'showUserInput' limit 1), (select id from `fields` where `name` = 'label_delete' limit 1), 'The label of the remove button of the modal form.\n\nNote the following important points:\n- this field only has an effect if `is_log` is enabled.\n- if this field is not set, the remove button is not rendered.\n- entries that are removed with this button are only marked as removed but not deleted from the DB.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'showUserInput' limit 1), (select id from `fields` where `name` = 'delete_title' limit 1), 'The title of the modal form that pops up when the delete button is clicked.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES ((select id from styles where `name` = 'showUserInput' limit 1), (select id from `fields` where `name` = 'delete_content' limit 1), 'The content of the modal form that pops up when the delete button is clicked.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.');

UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'The column title of the timestamp column.\n\nNote the following important point:\n- this field only has an effect if `is_log` is enabled.' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'showUserInput' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'label_date_time';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'The name of the source form (i.e. the field `name` of the target form style).' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'showUserInput' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'source';
UPDATE `styles_fields`, `styles`, `fields` SET `help` = 'If *checked*, the style will render a table where each row represents all fields of the source form at the time instant of data submission.\n\nIf left *unchecked*, a table is rendered where each row represents one field of the source form.\n\nNote the following important points:\n- Check this only if the source form also has `is_log` checked.\n- The fields, `delete_title`, `label_date_time`, `label_delete`, and `delete_content` only have an effect if `is_log` is *checked*.' WHERE `styles_fields`.`id_styles` = `styles`.`id` AND `styles`.`name` = 'showUserInput' AND `styles_fields`.`id_fields` = `fields`.`id` AND `fields`.`name` = 'is_log';

-- merge i174

-- add page type open
INSERT INTO `pageType` (`id`, `name`) VALUES (NULL, 'open');

--
-- Table structure for table `userStatus`
--

CREATE TABLE `userStatus` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `userStatus`
--

INSERT INTO `userStatus` (`id`, `name`, `description`) VALUES
(0000000001, 'interested', 'This user has shown interest in the platform but has not yet met the preconditions to be invited.'),
(0000000002, 'invited', 'This user was invited to join the platform but has not yet validated the email address.'),
(0000000003, 'active', 'This user can log in and visit all accessible pages.');

--
-- Indexes for table `userStatus`
--
ALTER TABLE `userStatus`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `userStatus`
--
ALTER TABLE `userStatus`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- Add id_status field to users table
ALTER TABLE `users` ADD `id_status` INT UNSIGNED ZEROFILL NULL DEFAULT '1' AFTER `blocked`, ADD INDEX (`id_status`);
ALTER TABLE `users` ADD CONSTRAINT `fk_users_id_status` FOREIGN KEY (`id_status`) REFERENCES `userStatus`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Update status of users
UPDATE `users` SET `id_status` = '2' WHERE token IS NOT NULL;
UPDATE `users` SET `id_status` = '3' WHERE password IS NOT NULL;
-- Update status of user guest
UPDATE `users` SET `id_status` = NULL WHERE id = '1';

-- add style interstedUserForm
INSERT INTO `styles` (`name`, `id_type`, `id_group`, `description`) VALUES ('emailForm', '2', (SELECT `id` FROM `styleGroup` WHERE `name` = 'Form'), 'A form to accept an email address and automatically send two emails: An email to the address entered in the form and another email to admins, specified in the style.');
SET @id_style_emailForm = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('admins', (SELECT `id` FROM `fieldType` WHERE `name` = 'json'), '0');
SET @id_field_admins = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('email_admins', (SELECT `id` FROM `fieldType` WHERE `name` = 'email'), '1');
SET @id_field_email_admins = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('email_user', (SELECT `id` FROM `fieldType` WHERE `name` = 'email'), '1');
SET @id_field_email_user = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('subject_user', (SELECT `id` FROM `fieldType` WHERE `name` = 'text'), '1');
SET @id_field_subject_user = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('attachments_user', (SELECT `id` FROM `fieldType` WHERE `name` = 'json'), '1');
SET @id_field_attachments_user = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('do_store', (SELECT `id` FROM `fieldType` WHERE `name` = 'checkbox'), '0');
SET @id_field_do_store = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('is_html', (SELECT `id` FROM `fieldType` WHERE `name` = 'checkbox'), '0');
SET @id_field_is_html = LAST_INSERT_ID();

INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, (SELECT `id` FROM `fields` WHERE `name` = 'label'), 'The label on the submit button.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, (SELECT `id` FROM `fields` WHERE `name` = 'type'), 'The bootstrap color of the submit button.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, (SELECT `id` FROM `fields` WHERE `name` = 'placeholder'), 'The placeholder in the email input field.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, (SELECT `id` FROM `fields` WHERE `name` = 'alert_success'), 'The success message to be shown when an email address was successfully stored in the database (if enabled) and the automatic emails were sent successfully.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_admins, 'A list of email addresses to be notified on submit with an email as defined in field `email_admins`. Use `json` syntax to specify the list of admins (e.g. `["__admin_1__", ..., "__admin_n__"]`) where `__admin_*__` is the email address of an admin.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_email_admins, 'The email to be sent to the the list of admins defined in the field `admins`. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@email` will be replaced by the email address entered in the form.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_email_user, 'The email to be sent to the the email address that was entered into the form. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content.\n');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_subject_user, 'The subject of the email to be sent to the the email address that was entered into the form. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_attachments_user, 'The list of attachments to the email to be sent to the the address that was entered into the form. Use `json` syntax to specify a list of assets (e.g. `["__asset_1__", ..., "__asset_n__"]`) where `__asset_*__` is the name of an uploaded asset.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_emailForm, @id_field_do_store, '0', 'If checked, the entered email address will be stored in the database.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_emailForm, @id_field_is_html, '0', 'If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext');

-- fix image syntax in markdown
UPDATE `sections_fields_translation` SET `content` = '![Logo University of Bern](%logo/Unibe_Logo_16pt_RGB_201807.png|250x|float-left,border-0,mr-5 \"Logo University of Bern\")\r\n\r\n**Universität Bern**  \r\n**Philosophisch-humanwissenschaftliche Fakultät**\r\n\r\nFabrikstrasse 8  \r\n3012 Bern\r\n\r\nTelefon: +41 31 631 55 11\r\n\r\n**Entwicklung:** [Technologieplatform (TPF)](http://www.philhum.unibe.ch/forschung/tpf/index_ger.html)' WHERE `sections_fields_translation`.`id_sections` = (SELECT `id` FROM `sections` WHERE `name` = 'impressum-markdown') AND `sections_fields_translation`.`id_fields` = (SELECT id FROM fields WHERE name = 'text_md') AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;
UPDATE `sections_fields_translation` SET `content` = '![Logo University of Bern](%logo/Unibe_Logo_16pt_RGB_201807.png|250x|float-left,border-0,mr-5 \"Logo University of Bern\")\r\n\r\n**University of Bern**  \r\n**Faculty of Human Sciences**\r\n\r\nFabrikstrasse 8  \r\n3012 Bern\r\n\r\nPhone: +41 31 631 55 11\r\n\r\n**Development:** [Technologieplatform (TPF)](http://www.philhum.unibe.ch/forschung/tpf/index_ger.html)' WHERE `sections_fields_translation`.`id_sections` = (SELECT `id` FROM `sections` WHERE `name` = 'impressum-markdown') AND `sections_fields_translation`.`id_fields` = (SELECT id FROM fields WHERE name = 'text_md') AND `sections_fields_translation`.`id_languages` = 0000000003 AND `sections_fields_translation`.`id_genders` = 0000000001;

-- update reset password style fields
SET @id_style_resetPassword = (SELECT id FROM `styles` WHERE `name` = "resetPassword");
-- remove obsolete fields
DELETE FROM `styles_fields` WHERE `id_styles` = @id_style_resetPassword AND `id_fields` IN (SELECT id FROM `fields` WHERE name = 'alert_fail');
DELETE FROM `styles_fields` WHERE `id_styles` = @id_style_resetPassword AND `id_fields` IN (SELECT id FROM `fields` WHERE name = 'label_login');
DELETE FROM `styles_fields` WHERE `id_styles` = @id_style_resetPassword AND `id_fields` IN (SELECT id FROM `fields` WHERE name = 'success');
-- insert new fields
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_resetPassword, @id_field_is_html, '0', 'If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_resetPassword, @id_field_subject_user, 'The subject of the email to be sent to the the email address that was entered into the form. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_resetPassword, @id_field_email_user, 'The email to be sent to the the email address that was entered into the form. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@link` will be replaced by the activation link the user needs to reset the password.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_resetPassword, (SELECT `id` FROM `fields` WHERE `name` = 'type'), 'The bootstrap color of the submit button.');
-- assign values to the new fields
SET @id_section_resetPassword = (SELECT id FROM `sections` WHERE `name` = 'resetPassword-resetPassword');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_resetPassword, (SELECT `id` FROM `fields` WHERE `name` = 'type'), 1, 1, 'primary');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_resetPassword, @id_field_is_html, 1, 1, 0);
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_resetPassword, @id_field_subject_user, 2, 1, '@project Passwort zurück setzen');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_resetPassword, @id_field_subject_user, 3, 1, '@project Password Reset');
SET @email_2 = (SELECT content FROM `pages_fields_translation` WHERE `id_pages` IN (SELECT id FROM pages WHERE keyword = 'email') AND id_fields IN (SELECT id FROM fields WHERE `name` = 'email_reset') AND id_languages = 2);
SET @email_3 = (SELECT content FROM `pages_fields_translation` WHERE `id_pages` IN (SELECT id FROM pages WHERE keyword = 'email') AND id_fields IN (SELECT id FROM fields WHERE `name` = 'email_reset') AND id_languages = 3);
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_resetPassword, @id_field_email_user, 2, 1, @email_2);
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_resetPassword, @id_field_email_user, 3, 1, @email_3);
-- update help message of existing fields
UPDATE `styles_fields` SET `help` = 'The label on the submit button.' WHERE `id_styles` = @id_style_resetPassword AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'label_pw_reset');
UPDATE `styles_fields` SET `help` = 'The placeholder in the email input field.' WHERE `id_styles` = @id_style_resetPassword AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'placeholder');
UPDATE `styles_fields` SET `help` = 'The success message to be shown when an email address was successfully stored in the database (if enabled) and the automatic emails were sent successfully.' WHERE `id_styles` = @id_style_resetPassword AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'alert_success');
UPDATE `styles_fields` SET `help` = 'The description to be displayed on the page when a user wants to reset the password.' WHERE `id_styles` = @id_style_resetPassword AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'text_md');
DELETE FROM `fields` WHERE `name` = 'email_reset';

-- move chat notification to chat style
SET @id_style_chat = (SELECT `id` FROM `styles` WHERE `name` = 'chat');
-- insert new fields
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_chat, @id_field_is_html, '0', 'If *checked*, the email will be parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_chat, @id_field_subject_user, 'The subject of the notification email to be sent to the receiver of the chat message. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_chat, @id_field_email_user, 'The notification email to be sent to receiver of the chat message. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@link` will be replaced by the link to the chat page.');
-- assign values to the new fields
SET @email_2 = (SELECT content FROM `pages_fields_translation` WHERE `id_pages` IN (SELECT id FROM pages WHERE keyword = 'email') AND id_fields IN (SELECT id FROM fields WHERE `name` = 'email_notification') AND id_languages = 2);
SET @email_3 = (SELECT content FROM `pages_fields_translation` WHERE `id_pages` IN (SELECT id FROM pages WHERE keyword = 'email') AND id_fields IN (SELECT id FROM fields WHERE `name` = 'email_notification') AND id_languages = 3);
SET @id_section_chat = (SELECT id FROM `sections` WHERE `name` = 'contact-chat');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_chat, @id_field_email_user, 2, 1, @email_2);
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_chat, @id_field_email_user, 3, 1, @email_3);
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_chat, @id_field_subject_user, 2, 1, '@project Chat Benachrichtigung');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_chat, @id_field_subject_user, 3, 1, '@project Chat Notification');
-- update field help messages
UPDATE `styles_fields` SET `help` = 'The alert to be shown if the message could not be sent.' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'alert_fail');
UPDATE `styles_fields` SET `help` = 'This text is displayed when an experimenter has not yet chosen a subject to chat with.' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'alt');
UPDATE `styles_fields` SET `help` = 'The postfix of the chat title which serves to indicate to the subject with whom he/she is talking. Only a subject sees this. It should be a general description of experimenters. The chat title is composed as follows:\n- if user is an experimenter the title is composed from the field `title_prefix` and the selected subject_name\n- if user is a subject the title is composed from the fields `title_prefix` and `experimenter`' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'experimenter');
UPDATE `styles_fields` SET `help` = 'The name of the default chat room.' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'label_lobby');
UPDATE `styles_fields` SET `help` = 'The label to be displayed in the chat window that seperates new messges from old ones.' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'label_new');
UPDATE `styles_fields` SET `help` = 'The label on the button to send a message.' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'label_submit');
UPDATE `styles_fields` SET `help` = 'The title of on the collapsed list of subjects (only on small screens).' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'subjects');
UPDATE `styles_fields` SET `help` = 'The postfix of the chat title which serves to indicate to the subject with whom he/she is talking. Only a subject sees this. It should be a general description of experimenters. The chat title is composed as follows:\n- if user is an experimenter the title is composed from the field `title_prefix` and the selected subject_name\n- if user is a subject the title is composed from the fields `title_prefix` and `experimenter`' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'experimenter');
UPDATE `styles_fields` SET `help` = 'The prefix of the chat title which serves to indicate to the user with whom he/she is talking. The chat title is composed as follows:\n- if user is an experimenter the title is composed from the field `title_prefix` and the selected subject_name\n- if user is a subject the title is composed from the fields `title_prefix` and `experimenter`.' WHERE `id_styles` = @id_style_chat AND `id_fields` IN (SELECT `id` FROM `fields` WHERE `name` = 'title_prefix');
DELETE FROM `fields` WHERE `name` = 'email_notification';

SET @id_page_exportData = (SELECT `id` FROM `pages` WHERE `keyword` = 'exportData');
UPDATE `pages` SET `url` = '/admin/export/[user_input|user_activity|validation_codes:selector]/[all|used|open:option]?' WHERE `pages`.`id` = @id_page_exportData;

ALTER TABLE `validation_codes` CHANGE `timestamp` `consumed` DATETIME on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `validation_codes` ADD `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `id_users`;

-- add page to remove all user activity and user input
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'exportDelete', '/admin/exportDelete/[user_activity|user_input:selector]', 'GET|POST|DELETE', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page_exportDelete = LAST_INSERT_ID();
SET @id_field_label = (SELECT `id` FROM `fields` WHERE `name` = 'label');
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_exportDelete, @id_field_label, '0000000002', 'Userdaten Löschen'), (@id_page_exportDelete, @id_field_label, '0000000003', 'Remove User Data');
-- set ACL for export page
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_exportDelete, '1', '0', '0', '1');

-- set style description to NULL by default
ALTER TABLE `styles` CHANGE `description` `description` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- add CSV seperator to the language table
ALTER TABLE `languages` ADD `csv_separator` VARCHAR(1) NOT NULL DEFAULT ',' AFTER `language`;

-- update style field help texts
-- alert
SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'alert');
SET @id_field_chidlren = (SELECT `id` FROM `fields` WHERE `name` = 'children');
SET @id_field_type = (SELECT `id` FROM `fields` WHERE `name` = 'type');
SET @id_field_title = (SELECT `id` FROM `fields` WHERE `name` = 'title');
SET @id_field_is_dismissable = (SELECT `id` FROM `fields` WHERE `name` = 'is_dismissable');
SET @id_field_is_collapsible = (SELECT `id` FROM `fields` WHERE `name` = 'is_collapsible');
SET @id_field_is_expanded = (SELECT `id` FROM `fields` WHERE `name` = 'is_expanded');
SET @id_field_url_edit = (SELECT `id` FROM `fields` WHERE `name` = 'url_edit');
UPDATE `styles_fields` SET `help` = 'The child elements to be added to the alert wrapper.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_chidlren;
UPDATE `styles_fields` SET `help` = 'The bootstrap color styling of the alert wrapper.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_type;
UPDATE `styles_fields` SET `help` = 'If *checked* the alert wrapper can be dismissed by clicking on a close symbol.\r\nIf *unchecked* the close symbol is not rendered.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_is_dismissable;
-- card
SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'card');
UPDATE `styles_fields` SET `help` = 'The child elements to be added to the card body.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_chidlren;
UPDATE `styles_fields` SET `help` = 'A bootstrap-esque color styling of the card border and header.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_type;
UPDATE `styles_fields` SET `help` = 'The target url of the edit button. If set, an edit button will appear on right of the card header and link to the specified url. If not set no button will be shown.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_url_edit;
UPDATE `styles_fields` SET `help` = 'The content of the card header. If not set, the card will be rendered without a header section.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_title;
UPDATE `styles_fields` SET `help` = 'If *checked* the card body can be collapsed into the header by clicking on the header.\nIf left *unchecked* no such interaction is possible.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_is_collapsible;
UPDATE `styles_fields` SET `help` = 'If the field `is_collapsible` is *checked* and the field `is_expanded` is *unchecked* the card is collapsed by default and only by clicking on the header will the body be shown. This field has no effect if `is_collapsible` is left *unchecked*.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field_is_expanded;
