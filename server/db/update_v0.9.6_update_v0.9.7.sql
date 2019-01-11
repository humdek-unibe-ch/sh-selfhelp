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

-- Validation Codes
--
-- Table structure for table `validation codes`
--

CREATE TABLE `validation codes` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `code` varchar(8) NOT NULL,
  `id_users` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `timestamp` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `validation codes`
--
ALTER TABLE `validation codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_users` (`id_users`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `validation codes`
--
ALTER TABLE `validation codes`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `validation codes`
--
ALTER TABLE `validation codes`
  ADD CONSTRAINT `validation_codes_fk_id_users` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
