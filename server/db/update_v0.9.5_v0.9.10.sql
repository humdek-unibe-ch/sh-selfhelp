SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'set __db_name__ and __user__ in line 43';

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'css_nav', '0000000001', '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000033', '0000000089', NULL);

UPDATE `fields` SET `display` = '1' WHERE `fields`.`id` = 0000000053;
UPDATE `fields` SET `display` = '1' WHERE `fields`.`id` = 0000000071;
-- Set existing source and sources to german
UPDATE `sections_fields_translation` SET `id_languages` = 0000000002 WHERE `id_fields` = 0000000053;
UPDATE `sections_fields_translation` SET `id_languages` = 0000000002 WHERE `id_fields` = 0000000071;


-- Fix typos
UPDATE `sections_fields_translation` SET `content` = 'Alle Benutzerdaten werden gelöscht. Das Löschen des Accounts ist permanent und kann nicht rückgängig gemacht werden!' WHERE `sections_fields_translation`.`id_sections` = 0000000002 AND `sections_fields_translation`.`id_fields` = 0000000014 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;
UPDATE `sections_fields_translation` SET `content` = 'Wollen sie ihren Account wirklich löschen? Bestätigen Sie dies indem Sie ihre Email Adresse eingeben.' WHERE `sections_fields_translation`.`id_sections` = 0000000002 AND `sections_fields_translation`.`id_fields` = 0000000016 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;
UPDATE `sections_fields_translation` SET `content` = 'Ein Name mit dem Sie angesprochen werden wollen. Aus Gründen der Anonymisierung verwenden Sie bitte **nicht** ihren richtigen Namen.' WHERE `sections_fields_translation`.`id_sections` = 0000000026 AND `sections_fields_translation`.`id_fields` = 0000000038 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;
UPDATE `sections_fields_translation` SET `content` = 'Benutzer erfolgreich aktiviert' WHERE `sections_fields_translation`.`id_sections` = 0000000026 AND `sections_fields_translation`.`id_fields` = 0000000044 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;
UPDATE `styleGroup` SET `description` = 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:\r\n\r\n- `alert` is **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.\r\n- `card` is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.\r\n- `container` is an **invisible** wrapper.\r\n- `jumbotron` is a **visible** wrapper that wraps its content in a grey box with large spacing.\r\n- `navigationContainer` is an **invisible** wrapper and is used specifically for navigation pages.\r\n- `quiz` is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.\r\n- `tabs` is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.' WHERE `styleGroup`.`id` = 0000000004;
UPDATE `styleGroup` SET `description` = 'Lists are styles that allow to define more sophisticated lists than the markdown syntax allows. They come with attached javascript functionality. The following lists are available:\r\n\r\n- `accordionList` is a hierarchical list where the root level is rendered as an accordion with only one root item expanded at a time.\r\n- `nestedList`is a hierarchical list where each root item item can be collapsed and expanded by clicking on a chevron.\r\n- `sortableList` is not hierarchical but can be sorted, new items can be added as well as items can be deleted. Note that only the visual aspects of these functions are rendered. The implementation of the functions need to be defined separately with javascript (See <a href=\"https://github.com/RubaXa/Sortable\" target=\"_blank\">Sortable</a> for more details).' WHERE `styleGroup`.`id` = 0000000006;


-- Udate Autosize tool in impressum
UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)  | 1.1.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.1.3 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.0/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.1/about/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0 | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0 | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = 0000000034 AND `sections_fields_translation`.`id_fields` = 0000000025 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;
UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)  | 1.1.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.1.3 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.0/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.1/about/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0 | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0 | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = 0000000034 AND `sections_fields_translation`.`id_fields` = 0000000025 AND `sections_fields_translation`.`id_languages` = 0000000003 AND `sections_fields_translation`.`id_genders` = 0000000001;

-- Change action of login page and remove chlidren field
UPDATE `pages` SET `id_actions` = '0000000003' WHERE `pages`.`id` = 0000000001;
DELETE FROM `styles_fields` WHERE `styles_fields`.`id_styles` = 0000000001 AND `styles_fields`.`id_fields` = 0000000006;

-- Crate new style group and add style login to this group
INSERT INTO `styleGroup` (`id`, `name`, `description`, `position`) VALUES (NULL, 'Admin', 'The admin styles are for user registration and access handling.\r\nThe following styles are available:\r\n\r\n- `login` provides a small form where the user can enter his or her email and password to access the WebApp. It also includes a link to reset a password.\r\n- `register` provides a small form to allow a user to register for the WebApp. In order to register a user must provide a valid email and activation code. Activation codes can be generated in the admin section of the WebApp. The list of available codes can be exported.', '80');
SET @id_styleGroup = LAST_INSERT_ID();
UPDATE `styles` SET `id_group` = @id_styleGroup WHERE `styles`.`id` = 0000000001;

-- Remove page user_input field
ALTER TABLE `pages` DROP `user_input`;

-- Headless property of a page
ALTER TABLE `pages` ADD `is_headless` TINYINT(1) NOT NULL DEFAULT '0' AFTER `parent`;
UPDATE `pages` SET `is_headless` = '1' WHERE `pages`.`id` = 0000000001;

-- Empty div style
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'div', '0000000001', '0000000004');
SET @id_style_div = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES (@id_style_div, '0000000006', NULL);
UPDATE `styleGroup` SET `description` = 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:\r\n\r\n- `alert` is **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.\r\n- `card` is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.\r\n- `container` is an **invisible** wrapper.\r\n- `div` allows to wrap its children in a simple `<div>` tag. This allows to create more complex layouts with the help of bootstrap classes.\r\n- `jumbotron` is a **visible** wrapper that wraps its content in a grey box with large spacing.\r\n- `navigationContainer` is an **invisible** wrapper and is used specifically for navigation pages.\r\n- `quiz` is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.\r\n- `tabs` is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.' WHERE `styleGroup`.`id` = 0000000004;

-- Validation Codes
CREATE TABLE `validation_codes` (
  `code` varchar(8) NOT NULL,
  `id_users` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `validation_codes`
  ADD PRIMARY KEY (`code`),
  ADD KEY `id_users` (`id_users`);

ALTER TABLE `validation_codes`
  ADD CONSTRAINT `validation_codes_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

GRANT INSERT, UPDATE (id_users) ON __db_name__.validation_codes TO '__user__'@'localhost';

INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'userGenCode', '/admin/user_gen_code', 'GET|POST|PUT', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');

SET @id_page_userGenCode = LAST_INSERT_ID();

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES
('0000000001', @id_page_userGenCode, '1', '1', '0', '0'),
('0000000002', @id_page_userGenCode, '1', '1', '0', '0'),
('0000000003', @id_page_userGenCode, '0', '0', '0', '0');

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_userGenCode, '0000000008', '0000000001', 'Generate Validation Codes');

UPDATE `pages` SET `url` = '/admin/export/[user_input|user_activity|validation_codes:selector]' WHERE `pages`.`id` = 0000000023;

-- Register style
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'register', '0000000002', '0000000009');
SET @id_style_register = LAST_INSERT_ID();

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_submit', '0000000001', '1');
SET @id_field_label_submit = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES
(@id_style_register, '0000000005', NULL),
(@id_style_register, '0000000001', NULL),
(@id_style_register, '0000000002', NULL),
(@id_style_register, '0000000022', NULL),
(@id_style_register, '0000000028', 'success'),
(@id_style_register, '0000000035', NULL),
(@id_style_register, '0000000044', NULL),
(@id_style_register, @id_field_label_submit, NULL);

INSERT INTO `sections` (`id`, `id_styles`, `name`, `owner`) VALUES (NULL, @id_style_register, 'register-register', NULL);

SET @id_section_register = LAST_INSERT_ID();

INSERT INTO `pages_sections` (`id_pages`, `id_sections`, `position`) VALUES ('0000000001', @id_section_register, NULL);

INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_register, '0000000005', '0000000002', '0000000001', 'Die Email Adresse oder der Aktivierungs-Code ist ungültig'),
(@id_section_register, '0000000005', '0000000003', '0000000001', 'The email address or the activation code is invalid'),
(@id_section_register, '0000000001', '0000000002', '0000000001', 'Email'),
(@id_section_register, '0000000001', '0000000003', '0000000001', 'Email'),
(@id_section_register, '0000000002', '0000000002', '0000000001', 'Validierungs-Code'),
(@id_section_register, '0000000002', '0000000003', '0000000001', 'Validation Code'),
(@id_section_register, '0000000022', '0000000002', '0000000001', 'Registration'),
(@id_section_register, '0000000022', '0000000003', '0000000001', 'Registration'),
(@id_section_register, '0000000035', '0000000002', '0000000001', 'Der erste Schritt der Registrierung war erfolgreich. Sie werden in Kürze eine Email mit einem Aktivierunks-Link erhalten.\r\n\r\nBitte folgen Sie diesem Link um die Registrierung abzuschliessen.'),
(@id_section_register, '0000000035', '0000000003', '0000000001', 'The first step of the registration was successful.\r\nShortly you will receive an email with an activation link.\r\n\r\nPlease follow this activation link to complete the registration.'),
(@id_section_register, '0000000044', '0000000002', '0000000001', 'Registrierung erfolgreich'),
(@id_section_register, '0000000044', '0000000003', '0000000001', 'Registration Successful'),
(@id_section_register, '0000000090', '0000000002', '0000000001', 'Registrieren'),
(@id_section_register, '0000000090', '0000000003', '0000000001', 'Register');

-- add typ efield to login style
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES ('0000000001', '0000000028', 'dark');

-- update login page view
DELETE FROM `pages_sections` WHERE `pages_sections`.`id_pages` = 0000000001 AND `pages_sections`.`id_sections` = 0000000001;
DELETE FROM `pages_sections` WHERE `pages_sections`.`id_pages` = 0000000001 AND `pages_sections`.`id_sections` = @id_section_register;
INSERT INTO `sections` (`id`, `id_styles`, `name`, `owner`) VALUES (NULL, '0000000003', 'login-container', NULL);
SET @id_section_login_container = LAST_INSERT_ID();
INSERT INTO `pages_sections` (`id_pages`, `id_sections`, `position`) VALUES ('0000000001', @id_section_login_container, NULL);
INSERT INTO `sections_hierarchy` (`parent`, `child`, `position`) VALUES (@id_section_login_container, '0000000001', '1'), (@id_section_login_container, @id_section_register, '2');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_login_container, '0000000023', '0000000001', '0000000001', 'mt-3');
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES (@id_section_register, '0000000023', '0000000001', '0000000001', 'mt-3');


-- Adding the style conditional container to the db

INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'conditionalContainer', '0000000002', '0000000004');
SET @id_style_conditionalContainer = LAST_INSERT_ID();

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'condition', '0000000008', '0');
SET @id_field_condition = LAST_INSERT_ID();

INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES (@id_style_conditionalContainer, @id_field_condition, NULL), (@id_style_conditionalContainer, '0000000006', NULL);

UPDATE `styleGroup` SET `description` = 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:\r\n\r\n- `alert` is **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.\r\n- `card` is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.\r\n- `conditionalContainer` is a **invisible** wrapper which has a condition attached. The content of the wrapper is only displayed if the condition is true.\r\n- `container` is an **invisible** wrapper.\r\n- `jumbotron` is a **visible** wrapper that wraps its content in a grey box with large spacing.\r\n- `navigationContainer` is an **invisible** wrapper and is used specifically for navigation pages.\r\n- `quiz` is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.\r\n- `tabs` is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.' WHERE `styleGroup`.`id` = 0000000004;

UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)  | 1.1.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.1.3 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.0/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.1/about/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0 | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0 | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = 0000000034 AND `sections_fields_translation`.`id_fields` = 0000000025 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;

UPDATE `sections_fields_translation` SET `content` = '| Frameworks & Libararies                                    | Version | License | Comments |\r\n|-|-|-|-|\r\n| [Altorouter](http://altorouter.com/)                       | 1.2.0 | [MIT](https://tldrlegal.com/license/mit-license) | [License Details](http://altorouter.com/license.html) |\r\n| [Autosize](https://github.com/jackmoore/autosize)  | 1.1.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Bootstrap](https://getbootstrap.com/)                     | 4.1.3 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://getbootstrap.com/docs/4.0/getting-started/browsers-devices/), [License Details](https://getbootstrap.com/docs/4.1/about/license/) |\r\n| [Font Awesome](https://fontawesome.com/)                   | 5.2.0 | Code: [MIT](https://tldrlegal.com/license/mit-license), Icons: [CC](https://creativecommons.org/licenses/by/4.0/), Fonts: [OFL](https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL) | [Browser Support](https://fontawesome.com/how-to-use/on-the-web/other-topics/browser-support), [License Details](https://fontawesome.com/license/free) |\r\n| [GUMP](https://github.com/Wixel/GUMP.git)                  | 1.5.6 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [jQuery](https://jquery.com/)                              | 3.3.1 | [MIT](https://tldrlegal.com/license/mit-license) | [Browser Support](https://jquery.com/browser-support/), [License Details](https://jquery.org/license/) |\r\n| [JsonLogic](https://github.com/jwadhams/json-logic-php/)   | 1.3.10 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Parsedown](https://github.com/erusev/parsedown)           | 1.7.1 | [MIT](https://tldrlegal.com/license/mit-license) | |\r\n| [Sortable](https://rubaxa.github.io/Sortable/)             | 1.7.0 | [MIT](https://tldrlegal.com/license/mit-license) | |' WHERE `sections_fields_translation`.`id_sections` = 0000000034 AND `sections_fields_translation`.`id_fields` = 0000000025 AND `sections_fields_translation`.`id_languages` = 0000000003 AND `sections_fields_translation`.`id_genders` = 0000000001;

-- Audio Style
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'audio', '0000000001', '0000000007');
SET @id_style_audio = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES (@id_style_audio, '0000000030', NULL), (@id_style_audio, '0000000071', NULL);
UPDATE `styleGroup` SET `description` = 'The media styles allow to display different media on a webpage. The following styles are available:\r\n\r\n- `audio` allows to load and replay an audio source on a page.\r\n- `figure` allows to attach a caption to media elements. A figure expects a media style as its immediate child.\r\n- `image` allows to render an image on a page.- `progressBar` allows to render a static progress bar.\r\n- `video` allows to load and display a video on a page.' WHERE `styleGroup`.`id` = 0000000007;


UPDATE `styleGroup` SET `description` = 'A wrapper is a style that allows to group child elements. Wrappers can have a visual component or can be invisible. Visible wrapper are useful to provide some structure in a document while invisible wrappers serve merely as a grouping option . The latter can be useful in combination with CSS classes. The following wrappers are available:\r\n\r\n- `alert` is **visible** wrapper that draws a solid, coloured box around its content. The text colour of the content is changed according to the type of alert.\r\n- `card` is a versatile **visible** wrapper that draws a fine border around its content. A card can also have a title and can be made collapsible.\r\n- `conditionalContainer` is a **invisible** wrapper which has a condition attached. The content of the wrapper is only displayed if the condition is true.\r\n- `container` is an **invisible** wrapper.\r\n- `div` allows to wrap its children in a simple `<div>` tag. This allows to create more complex layouts with the help of bootstrap classes.\r\n- `jumbotron` is a **visible** wrapper that wraps its content in a grey box with large spacing.\r\n- `navigationContainer` is an **invisible** wrapper and is used specifically for navigation pages.\r\n- `quiz` is a predefined assembly of tabs, intended to ask a question and provide a right and wrong answer tab.\r\n- `tabs` is a **visible** wrapper that allows to group content into tabs and only show one tab at a time. It requires `tab` styles as its immediate children. Each `tab` then accepts children which represent the content of each tab.' WHERE `styleGroup`.`id` = 0000000004;
UPDATE `styleGroup` SET `description` = 'The media styles allow to display different media on a webpage. The following styles are available:\r\n\r\n- `audio` allows to load and replay an audio source on a page.\r\n- `figure` allows to attach a caption to media elements. A figure expects a media style as its immediate child.\r\n- `image` allows to render an image on a page.\r\n- `progressBar` allows to render a static progress bar.\r\n- `video` allows to load and display a video on a page.' WHERE `styleGroup`.`id` = 0000000007;
