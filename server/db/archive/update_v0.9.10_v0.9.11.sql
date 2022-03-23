SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'remember to update the update permission for the user fields id_languages, is_reminded, last_login';

-- Increase validation code length
ALTER TABLE `validation_codes` CHANGE `code` `code` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- Add email modification page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'email', '/admin/email/[i:id]?', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', '11', NULL, '0000000001');
SET @id_page_email = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_email, '0000000008', '0000000001', 'Email CMS');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_email, '1', '0', '1', '0'), ('0000000002', @id_page_email, '1', '0', '1', '0');

-- Add email default content
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'email', '90');
SET @id_field_email = LAST_INSERT_ID();

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_activate', @id_field_email, '1');
SET @id_field_email_activate = LAST_INSERT_ID();
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_reset', @id_field_email, '1');
SET @id_field_email_reset = LAST_INSERT_ID();
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_reminder', @id_field_email, '1');
SET @id_field_email_reminder = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
(@id_page_email, @id_field_email_activate, '0000000002', 'Guten Tag\r\n\r\nUm Ihre Email Adresse zu verifizieren und Ihren @project Account zu aktivieren klicken Sie bitte auf den untenstehenden Link.\r\n\r\n@link\r\n\r\nVielen Dank!\r\n\r\nIhr @project Team'),
(@id_page_email, @id_field_email_activate, '0000000003', 'Hello\r\n\r\nTo verify you email address and to activate your @project account please click the link below.\r\n\r\n@link\r\n\r\nThank you!\r\n\r\nSincerely, your @project team'),
(@id_page_email, @id_field_email_reset, '0000000002', 'Guten Tag\r\n\r\nUm das Passwort von Ihrem @project Account zurück zu setzten klicken Sie bitte auf den untenstehenden Link.\r\n\r\n@link\r\n\r\nVielen Dank!\r\n\r\nIhr @project Team\r\n'),
(@id_page_email, @id_field_email_reset, '0000000003', 'Hello\r\n\r\nTo reset password of your @project account please click the link below.\r\n\r\n@link\r\n\r\nThank you!\r\n\r\nSincerely, your @project team.\r\n'),
(@id_page_email, @id_field_email_reminder, '0000000002', 'Guten Tag\r\n\r\nSie waren für längere Zeit nicht mehr aktiv auf der @project Plattform.\r\nEs würde uns freuen wenn Sie wieder vorbeischauen würden.\r\n\r\n@link\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team'),
(@id_page_email, @id_field_email_reminder, '0000000003', 'Hello\r\n\r\nYou did not visit the @project platform for some time now.\r\nWe would be pleased if you would visit us again.\r\n\r\n@link\r\n\r\nSincerely, your @project team');

-- fix typo
UPDATE `sections_fields_translation` SET `content` = 'Benutzername' WHERE `sections_fields_translation`.`id_sections` = 0000000026 AND `sections_fields_translation`.`id_fields` = 0000000036 AND `sections_fields_translation`.`id_languages` = 0000000002 AND `sections_fields_translation`.`id_genders` = 0000000001;

-- update user table to set a default language
ALTER TABLE `users` ADD `id_languages` INT UNSIGNED ZEROFILL NULL DEFAULT NULL AFTER `token`, ADD INDEX (`id_languages`);
ALTER TABLE `users` ADD  CONSTRAINT `fk_users_id_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `users` ADD `is_reminded` TINYINT(1) NOT NULL DEFAULT '1' AFTER `id_languages`;
UPDATE `users` SET `is_reminded` = '0' WHERE `users`.`id` = 0000000001; UPDATE `users` SET `is_reminded` = '0' WHERE `users`.`id` = 0000000002;
ALTER TABLE `users` ADD `last_login` DATE NULL DEFAULT NULL AFTER `is_reminded`;
