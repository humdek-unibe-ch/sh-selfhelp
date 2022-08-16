-- set DB version
UPDATE version
SET version = 'v5.2.0';

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_activate_subject', get_field_type_id('email'), '1');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'email_reminder_subject', get_field_type_id('email'), '1');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
((SELECT id FROM pages WHERE keyword = 'email' LIMIT 0,1), get_field_id('email_activate_subject'), '0000000002', 'Email Verification');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
((SELECT id FROM pages WHERE keyword = 'email' LIMIT 0,1), get_field_id('email_activate_subject'), '0000000003', 'Email Verification');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
((SELECT id FROM pages WHERE keyword = 'email' LIMIT 0,1), get_field_id('email_reminder_subject'), '0000000002', '');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES
((SELECT id FROM pages WHERE keyword = 'email' LIMIT 0,1), get_field_id('email_reminder_subject'), '0000000003', '');

