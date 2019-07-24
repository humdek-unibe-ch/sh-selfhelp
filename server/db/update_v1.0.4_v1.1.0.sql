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


UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)  | 1.1.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.3/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.3/about/license/) |\r\n| [Datatables](https://datatables.net/)                     | 1.10.18 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](https://datatables.net/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0 | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0 | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = (SELECT id FROM sections WHERE name = 'impressum-ext-markdown') AND `sections_fields_translation`.`id_fields` = (SELECT id FROM fields WHERE name = 'text_md') AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;

UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)  | 1.1.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.3/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.3/about/license/) |\r\n| [Datatables](https://datatables.net/)                     | 1.10.18 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](https://datatables.net/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0 | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0 | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = (SELECT id FROM sections WHERE name = 'impressum-ext-markdown') AND `sections_fields_translation`.`id_fields` = (SELECT id FROM fields WHERE name = 'text_md') AND `sections_fields_translation`.`id_languages` = 0000000003 AND `sections_fields_translation`.`id_genders` = 0000000001;

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
