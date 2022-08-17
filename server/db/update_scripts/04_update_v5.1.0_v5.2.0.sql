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

DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT sj.id AS id, from_email, from_name,
status_code, status, type_code, type, 
sj.date_create, date_to_be_executed, date_executed,
reply_to, recipient_emails, cc_emails, bcc_emails, subject, body, is_html, mq.id as id_mailQueue, id_jobTypes,
id_jobStatus, sj.config
FROM mailQueue mq
INNER JOIN scheduledJobs_mailQueue sj_mq ON (sj_mq.id_mailQueue = mq.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_mq.id_scheduledJobs);

DROP VIEW IF EXISTS view_notifications;
CREATE VIEW view_notifications
AS
SELECT sj.id AS id,
status_code, status, type_code, type, 
sj.date_create, date_to_be_executed, date_executed,
recipient, subject, body, url, id_notifications, id_jobTypes,
id_jobStatus, sj.config
FROM notifications n
INNER JOIN scheduledJobs_notifications sj_n ON (sj_n.id_notifications = n.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_n.id_scheduledJobs);

-- add keyword ajax_get_languages
INSERT IGNORE INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'ajax_get_languages', '/request/[AjaxDataSource:class]/[get_languages:method]', 'GET|POST', '0000000005', NULL, NULL, '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) 
VALUES ('0000000001', (SELECT id FROM pages WHERE keyword = 'ajax_get_languages'), '1', '0', '0', '0');

