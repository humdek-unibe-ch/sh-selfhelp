-- use styles to build the profile page
INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000003, 'profile-container', NULL);
SET @id_section_pc = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pc, 0000000023, 0000000001, 0000000001, 'my-3'),
(@id_section_pc, 0000000029, 0000000001, 0000000001, '0');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000040, 'profile-row-div', NULL);
SET @id_section_prd = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_prd, 0000000023, 0000000001, 0000000001, 'row');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000040, 'profile-col1-div', NULL);
SET @id_section_pc1d = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pc1d, 0000000023, 0000000001, 0000000001, 'col-12 col-lg');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000040, 'profile-col2-div', NULL);
SET @id_section_pc2d = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pc2d, 0000000023, 0000000001, 0000000001, 'col');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000012, 'profile-username-card', NULL);
SET @id_section_puc = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_puc, 0000000022, 0000000002, 0000000001, 'Benutzername ändern'),
(@id_section_puc, 0000000022, 0000000003, 0000000001, 'Change the Username'),
(@id_section_puc, 0000000023, 0000000001, 0000000001, 'mb-3'),
(@id_section_puc, 0000000028, 0000000001, 0000000001, 'light'),
(@id_section_puc, 0000000046, 0000000001, 0000000001, '1'),
(@id_section_puc, 0000000047, 0000000001, 0000000001, '0'),
(@id_section_puc, 0000000048, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000012, 'profile-password-card', NULL);
SET @id_section_ppc = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_ppc, 0000000022, 0000000002, 0000000001, 'Passwort ändern'),
(@id_section_ppc, 0000000022, 0000000003, 0000000001, 'Change the Password'),
(@id_section_ppc, 0000000023, 0000000001, 0000000001, ''),
(@id_section_ppc, 0000000028, 0000000001, 0000000001, 'light'),
(@id_section_ppc, 0000000046, 0000000001, 0000000001, '1'),
(@id_section_ppc, 0000000047, 0000000001, 0000000001, '0'),
(@id_section_ppc, 0000000048, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000012, 'profile-delete-card', NULL);
SET @id_section_pdc = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pdc, 0000000022, 0000000002, 0000000001, 'Account löschen'),
(@id_section_pdc, 0000000022, 0000000003, 0000000001, 'Delete the Account'),
(@id_section_pdc, 0000000023, 0000000001, 0000000001, 'mt-3'),
(@id_section_pdc, 0000000028, 0000000001, 0000000001, 'danger'),
(@id_section_pdc, 0000000046, 0000000001, 0000000001, '0'),
(@id_section_pdc, 0000000047, 0000000001, 0000000001, '1'),
(@id_section_pdc, 0000000048, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000014, 'profile-username-form', NULL);
SET @id_section_puf = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_puf, 0000000008, 0000000002, 0000000001, 'Ändern'),
(@id_section_puf, 0000000008, 0000000003, 0000000001, 'Change'),
(@id_section_puf, 0000000023, 0000000001, 0000000001, ''),
(@id_section_puf, 0000000027, 0000000001, 0000000001, '#self'),
(@id_section_puf, 0000000028, 0000000001, 0000000001, 'primary'),
(@id_section_puf, 0000000051, 0000000002, 0000000001, ''),
(@id_section_puf, 0000000051, 0000000003, 0000000001, ''),
(@id_section_puf, 0000000052, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'profile-username-input', NULL);
SET @id_section_pui = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pui, 0000000008, 0000000002, 0000000001, ''),
(@id_section_pui, 0000000008, 0000000003, 0000000001, ''),
(@id_section_pui, 0000000023, 0000000001, 0000000001, 'mb-3'),
(@id_section_pui, 0000000054, 0000000001, 0000000001, 'text'),
(@id_section_pui, 0000000055, 0000000002, 0000000001, 'Neuer Benutzername'),
(@id_section_pui, 0000000055, 0000000003, 0000000001, 'New Username'),
(@id_section_pui, 0000000056, 0000000001, 0000000001, '1'),
(@id_section_pui, 0000000057, 0000000001, 0000000001, 'user_name'),
(@id_section_pui, 0000000058, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000014, 'profile-password-form', NULL);
SET @id_section_ppf = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_ppf, 0000000008, 0000000002, 0000000001, 'Ändern'),
(@id_section_ppf, 0000000008, 0000000003, 0000000001, 'Change'),
(@id_section_ppf, 0000000023, 0000000001, 0000000001, ''),
(@id_section_ppf, 0000000027, 0000000001, 0000000001, '#self'),
(@id_section_ppf, 0000000028, 0000000001, 0000000001, 'primary'),
(@id_section_ppf, 0000000051, 0000000002, 0000000001, ''),
(@id_section_ppf, 0000000051, 0000000003, 0000000001, ''),
(@id_section_ppf, 0000000052, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'profile-password-input', NULL);
SET @id_section_ppi = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_ppi, 0000000008, 0000000002, 0000000001, ''),
(@id_section_ppi, 0000000008, 0000000003, 0000000001, ''),
(@id_section_ppi, 0000000023, 0000000001, 0000000001, 'mb-3'),
(@id_section_ppi, 0000000054, 0000000001, 0000000001, 'password'),
(@id_section_ppi, 0000000055, 0000000002, 0000000001, 'Neues Passwort'),
(@id_section_ppi, 0000000055, 0000000003, 0000000001, 'New Password'),
(@id_section_ppi, 0000000056, 0000000001, 0000000001, '1'),
(@id_section_ppi, 0000000057, 0000000001, 0000000001, 'password'),
(@id_section_ppi, 0000000058, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'profile-password-confirm-input', NULL);
SET @id_section_ppci = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_ppci, 0000000008, 0000000002, 0000000001, ''),
(@id_section_ppci, 0000000008, 0000000003, 0000000001, ''),
(@id_section_ppci, 0000000023, 0000000001, 0000000001, 'mb-3'),
(@id_section_ppci, 0000000054, 0000000001, 0000000001, 'password'),
(@id_section_ppci, 0000000055, 0000000002, 0000000001, 'Neues Passwort wiederholen'),
(@id_section_ppci, 0000000055, 0000000003, 0000000001, 'Repeat New Password'),
(@id_section_ppci, 0000000056, 0000000001, 0000000001, '1'),
(@id_section_ppci, 0000000057, 0000000001, 0000000001, 'verification'),
(@id_section_ppci, 0000000058, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000006, 'profile-delete-markdown', NULL);
SET @id_section_pdm = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pdm, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pdm, 0000000025, 0000000002, 0000000001, 'Alle Benutzerdaten werden gelöscht. Das Löschen des Accounts ist permanent und kann **nicht** rückgängig gemacht werden!\r\n\r\nWenn sie ihren Account wirklich löschen wollen bestätigen Sie dies indem Sie ihre Email Adresse eingeben.'),
(@id_section_pdm, 0000000025, 0000000003, 0000000001, 'All user data will be deleted. The deletion of the account is permanent and **cannot** be undone!\r\n\r\nIf you are sure you want to delete the account confirm this by entering your email address.');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000014, 'profile-delete-form', NULL);
SET @id_section_pdf = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pdf, 0000000008, 0000000002, 0000000001, 'Löschen'),
(@id_section_pdf, 0000000008, 0000000003, 0000000001, 'Delete'),
(@id_section_pdf, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pdf, 0000000027, 0000000001, 0000000001, '#self'),
(@id_section_pdf, 0000000028, 0000000001, 0000000001, 'danger'),
(@id_section_pdf, 0000000051, 0000000002, 0000000001, ''),
(@id_section_pdf, 0000000051, 0000000003, 0000000001, ''),
(@id_section_pdf, 0000000052, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'profile-delete-input', NULL);
SET @id_section_pdi = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pdi, 0000000008, 0000000002, 0000000001, ''),
(@id_section_pdi, 0000000008, 0000000003, 0000000001, ''),
(@id_section_pdi, 0000000023, 0000000001, 0000000001, 'mb-3'),
(@id_section_pdi, 0000000054, 0000000001, 0000000001, 'email'),
(@id_section_pdi, 0000000055, 0000000002, 0000000001, 'Email Adresse'),
(@id_section_pdi, 0000000055, 0000000003, 0000000001, 'Email Address'),
(@id_section_pdi, 0000000056, 0000000001, 0000000001, '1'),
(@id_section_pdi, 0000000057, 0000000001, 0000000001, 'email'),
(@id_section_pdi, 0000000058, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000006, 'profile-username-markdown', NULL);
SET @id_section_pum = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pum, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pum, 0000000025, 0000000002, 0000000001, 'Dies ist der Name mit dem Sie angesprochen werden wollen. Aus Gründen der Anonymisierung verwenden Sie bitte **nicht** ihren richtigen Namen.'),
(@id_section_pum, 0000000025, 0000000003, 0000000001, 'The name with which you would like to be addressed. For reasons of anonymity please do **not** use your real name.');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000012, 'profile-notification-card', NULL);
SET @id_section_pnc = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnc, 0000000022, 0000000002, 0000000001, 'Benachrichtigungen'),
(@id_section_pnc, 0000000022, 0000000003, 0000000001, 'Notifications'),
(@id_section_pnc, 0000000023, 0000000001, 0000000001, 'mb-3 mb-lg-0'),
(@id_section_pnc, 0000000028, 0000000001, 0000000001, 'light'),
(@id_section_pnc, 0000000046, 0000000001, 0000000001, '1'),
(@id_section_pnc, 0000000047, 0000000001, 0000000001, '0'),
(@id_section_pnc, 0000000048, 0000000001, 0000000001, '');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000006, 'profile-notification-markdown', NULL);
SET @id_section_pnm = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnm, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pnm, 0000000025, 0000000002, 0000000001, 'Hier können sie automatische Benachrichtigungen ein- und ausschalten. Asserdem können sie eine Telefonnummer hinterlegen um per SMS benachrichtigt zu werden. '),
(@id_section_pnm, 0000000025, 0000000003, 0000000001, 'Here you can enable and disable automatic notifications.\r\nAlso, by entering a phone number you can choose to be notified by SMS.');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000036, 'profile-notification-formUserInput', NULL);
SET @id_section_pnf = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnf, 0000000008, 0000000002, 0000000001, 'Ändern'),
(@id_section_pnf, 0000000008, 0000000003, 0000000001, 'Change'),
(@id_section_pnf, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pnf, 0000000028, 0000000001, 0000000001, 'primary'),
(@id_section_pnf, 0000000057, 0000000001, 0000000001, 'notification'),
(@id_section_pnf, 0000000087, 0000000001, 0000000001, '0'),
(@id_section_pnf, 0000000035, 0000000002, 0000000001, 'Die Einstellungen für Benachrichtigungen wurden erfolgreich gespeichert'),
(@id_section_pnf, 0000000035, 0000000003, 0000000001, 'The notification settings were successfully saved');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'profile-notification-chat-input', NULL);
SET @id_section_pnci = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnci, 0000000008, 0000000002, 0000000001, 'Benachrichtigung bei neuer Nachricht im Chat'),
(@id_section_pnci, 0000000008, 0000000003, 0000000001, 'Notification on new chat message'),
(@id_section_pnci, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pnci, 0000000054, 0000000001, 0000000001, 'checkbox'),
(@id_section_pnci, 0000000055, 0000000002, 0000000001, 'chat'),
(@id_section_pnci, 0000000055, 0000000003, 0000000001, 'chat'),
(@id_section_pnci, 0000000056, 0000000001, 0000000001, '0'),
(@id_section_pnci, 0000000057, 0000000001, 0000000001, 'chat'),
(@id_section_pnci, 0000000058, 0000000001, 0000000001, 'chat');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'profile-notification-reminder-input', NULL);
SET @id_section_pnri = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnri, 0000000008, 0000000002, 0000000001, 'Benachrichtung bei Inaktivität'),
(@id_section_pnri, 0000000008, 0000000003, 0000000001, 'Notification by inactivity'),
(@id_section_pnri, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pnri, 0000000054, 0000000001, 0000000001, 'checkbox'),
(@id_section_pnri, 0000000055, 0000000002, 0000000001, 'reminder'),
(@id_section_pnri, 0000000055, 0000000003, 0000000001, 'reminder'),
(@id_section_pnri, 0000000056, 0000000001, 0000000001, '0'),
(@id_section_pnri, 0000000057, 0000000001, 0000000001, 'reminder'),
(@id_section_pnri, 0000000058, 0000000001, 0000000001, 'reminder');

INSERT INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'profile-notification-phone-input', NULL);
SET @id_section_pnpi = LAST_INSERT_ID();
INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(@id_section_pnpi, 0000000008, 0000000002, 0000000001, 'Telefonnummer für SMS Benachrichtigung'),
(@id_section_pnpi, 0000000008, 0000000003, 0000000001, 'Phone Number for receiving SMS notifications'),
(@id_section_pnpi, 0000000023, 0000000001, 0000000001, ''),
(@id_section_pnpi, 0000000054, 0000000001, 0000000001, 'text'),
(@id_section_pnpi, 0000000055, 0000000002, 0000000001, 'Bitte Telefonnummer eingeben'),
(@id_section_pnpi, 0000000055, 0000000003, 0000000001, 'Please enter a phone number'),
(@id_section_pnpi, 0000000056, 0000000001, 0000000001, '0'),
(@id_section_pnpi, 0000000057, 0000000001, 0000000001, 'phone'),
(@id_section_pnpi, 0000000058, 0000000001, 0000000001, '');

INSERT INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
(0000000002, 0000000005, 0000000002, 0000000001, 'Die Benutzerdaten konnten nicht geändert werden.'),
(0000000002, 0000000005, 0000000003, 0000000001, 'Unable to change the user data.'),
(0000000002, 0000000023, 0000000001, 0000000001, ''),
(0000000002, 0000000035, 0000000002, 0000000001, 'Die Benutzerdaten wurden erfolgreich geändert.'),
(0000000002, 0000000035, 0000000003, 0000000001, 'The user data were successfully changed.');

INSERT INTO `sections_hierarchy` (`parent`, `child`, `position`) VALUES
(0000000002, @id_section_pc, 0),
(@id_section_pc, @id_section_prd, 0),
(@id_section_prd, @id_section_pc1d, 0),
(@id_section_prd, @id_section_pc2d, 10),
(@id_section_pc1d, @id_section_puc, 0),
(@id_section_pc2d, @id_section_ppc, 0),
(@id_section_pc2d, @id_section_pdc, 10),
(@id_section_puc, @id_section_puf, 0),
(@id_section_ppc, @id_section_ppf, 0),
(@id_section_pdc, @id_section_pdm, 0),
(@id_section_pdc, @id_section_pdf, 10),
(@id_section_puf, @id_section_pui, 10),
(@id_section_puf, @id_section_pum, 0),
(@id_section_ppf, @id_section_ppi, 0),
(@id_section_ppf, @id_section_ppci, 10),
(@id_section_pdf, @id_section_pdi, 0),
(@id_section_pc1d, @id_section_pnc, 10),
(@id_section_pnc, @id_section_pnm, 0),
(@id_section_pnc, @id_section_pnf, 10),
(@id_section_pnf, @id_section_pnci, 0),
(@id_section_pnf, @id_section_pnri, 10),
(@id_section_pnf, @id_section_pnpi, 20);

-- remove unused profile field content
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 1;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 2;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 9;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 10;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 11;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 12;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 13;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 14;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 15;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 16;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 17;
DELETE FROM `sections_fields_translation` WHERE id_sections = 2 AND id_fields = 18;

-- fix profile fields
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES
(0000000002, 0000000005, NULL),
(0000000002, 0000000006, NULL),
(0000000002, 0000000019, NULL),
(0000000002, 0000000020, NULL),
(0000000002, 0000000035, NULL);

-- Cleanup user_input_success page (not needed anymore as input is handeled differntly)
DELETE FROM `pages` WHERE keyword = "user_input_success";
DELETE FROM `sections` WHERE name = "user_input_success-container";
DELETE FROM `sections` WHERE name = "user_input_success-jumbotron";
DELETE FROM `sections` WHERE name = "user_input_success-heading";
DELETE FROM `sections` WHERE name = "user_input_success-markdown";

-- Update login text
UPDATE `sections_fields_translation` SET content = 'Email' WHERE id_sections = 1 AND id_fields = 1;
UPDATE `sections_fields_translation` SET content = 'Die Email Adresse oder das Passwort ist nicht korrekt.' WHERE id_sections = 1 AND id_fields = 5 AND id_languages = 2;
UPDATE `sections_fields_translation` SET content = 'The email address or the password is not correct.' WHERE id_sections = 1 AND id_fields = 5 AND id_languages = 3;

-- section names must be unique
ALTER TABLE `sections` ADD UNIQUE(`name`);

-- add notification email
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_notification', '0000000011', '1');
SET @id_field_email_notification = LAST_INSERT_ID();
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) SELECT id AS id_pages, @id_field_email_notification AS id_fields, 0000000002 AS id_languages, 'Guten Tag\r\n\r\nSie haben eine neue Nachricht auf der @project Plattform erhalten.\r\n\r\n@link\r\n\r\nMit freundlichen Grüssen\r\nihr @project Team' AS content FROM pages WHERE keyword = 'email');
INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) SELECT id AS id_pages, @id_field_email_notification AS id_fields, 0000000003 AS id_languages, 'Hello\r\n\r\nYou received a new message on the @project Plattform.\r\n\r\n@link\r\n\r\nSincerely, your @project team' AS content FROM pages WHERE keyword = 'email');

-- allow to upload css files
UPDATE `pages` SET `url` = '/admin/asset_insert/[css|asset:mode]' WHERE `pages`.`id` = 0000000025;

-- allow to delete asset and css files
UPDATE `pages` SET `url` = '/admin/asset_delete/[css|asset:mode]/[*:file]' WHERE `pages`.`id` = 0000000027;

-- new style carousel
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`) VALUES (NULL, 'carousel', '0000000001', '0000000007');
SET @id_style_carousel = LAST_INSERT_ID();
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'has_controls', '0000000003', '0');
SET @id_fields_has_controls = LAST_INSERT_ID();
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'has_indicators', '0000000003', '0');
SET @id_fields_has_indicators = LAST_INSERT_ID();
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`) VALUES
(@id_style_carousel, @id_fields_has_controls, '1'),
(@id_style_carousel, @id_fields_has_indicators, '0'),
(@id_style_carousel, '0000000083', NULL),
(@id_style_carousel, '0000000071', NULL);

UPDATE `styleGroup` SET `description` = 'The media styles allow to display different media on a webpage. The following styles are available:\r\n\r\n- `audio` allows to load and replay an audio source on a page.\r\n- `carousel` allows to render multiple images as a slide-show.\r\n- `figure` allows to attach a caption to media elements. A figure expects a media style as its immediate child.\r\n- `image` allows to render an image on a page.\r\n- `progressBar` allows to render a static progress bar.\r\n- `video` allows to load and display a video on a page.' WHERE `styleGroup`.`id` = 0000000007;
