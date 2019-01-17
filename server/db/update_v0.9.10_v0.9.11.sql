ALTER TABLE `validation_codes` CHANGE `code` `code` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) VALUES (NULL, 'email', '/admin/email/[i:id]?', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', '11', NULL, '0000000001');
SET @id_page_email = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_email, '0000000008', '0000000001', 'Email CMS');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_email, '1', '0', '1', '0'), ('0000000002', @id_page_email, '1', '0', '1', '0');

INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'email', '90');
SET @id_field_email = LAST_INSERT_ID();

INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_activate', @id_field_email, '1'), (NULL, 'email_reset', @id_field_email, '1'), (NULL, 'email_reminder', @id_field_email, '1');

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
(@id_page_email, @id_field_email, '0000000002', 'Guten Tag\r\n\r\nUm Ihre Email Adresse zu verifizieren und Ihren @project Account zu aktivieren klicken Sie bitte auf den untenstehenden Link.\r\n\r\n@activate_link\r\n\r\nVielen Dank!\r\n\r\nIhr @project Team'),
(@id_page_email, @id_field_email, '0000000003', 'Hello\r\n\r\nTo verify you email address and to activate your @project account please click the link below.\r\n\r\n@activate_link\r\n\r\nThank you!\r\n\r\nSincerely, your @project team'),
(@id_page_email, @id_field_email, '0000000002', 'Guten Tag\r\n\r\nUm das Passwort von Ihrem @project Account zurück zu setzten klicken Sie bitte auf den untenstehenden Link.\r\n\r\n@reset_link\r\n\r\nVielen Dank!\r\n\r\nIhr @project Team\r\n'),
(@id_page_email, @id_field_email, '0000000003', 'Hello\r\n\r\nTo reset password of your @project account please click the link below.\r\n\r\n@reset_link\r\n\r\nThank you!\r\n\r\nSincerely, your @project team.\r\n'),
(@id_page_email, @id_field_email, '0000000002', 'Guten Tag\r\n\r\nSie waren für längere Zeit nicht mehr aktiv auf der @project Plattform.\r\nEs würde uns freuen wenn Sie wieder vorbeischauen wollen.\r\n\r\n@link_project\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team'),
(@id_page_email, @id_field_email, '0000000003', 'Hello\r\n\r\nYou did not visit the @ Project platform for some time now.\r\nWe would be pleased if you would visit us again.\r\n\r\n@link_project\r\n\r\nSincerely, your @project team');
