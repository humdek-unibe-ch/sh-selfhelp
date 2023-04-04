-- set DB version
UPDATE version
SET version = 'v6.1.0';

 -- add Calendar view in moduleScheduledJobs 
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'moduleScheduledJobsCalendar', '/admin/scheduledJobs/calendar/[i:uid]?', 'GET|POST', '0000000002', NULL, NULL, '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE type_code = "pageAccessTypes" AND lookup_code = "mobile_and_web"));

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), '0000000008', '0000000001', 'Scheduled Jobs - Calendar View');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), '0000000054', '0000000001', 'Scheduled Jobs - Calendar View');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', (SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), '1', '0', '0', '0');

 -- update insert group to handle type group and db_role 
UPDATE pages
SET url = '/admin/group_insert/[group|db_role:type]'
WHERE keyword = 'groupInsert';

-- add group types
INSERT IGNORE INTO `lookups` (type_code, lookup_code, lookup_value, lookup_description) values ('groupTypes', 'db_role', 'DB Role', 'In the DB role we can set up multiple types of access. It is used for specific custom roles');
INSERT IGNORE INTO `lookups` (type_code, lookup_code, lookup_value, lookup_description) values ('groupTypes', 'group', 'Group', 'The group type has only `select` privileges and it is used for access to pages and condition checks');

CALL add_table_column('groups', 'id_group_types', 'INT(10) UNSIGNED ZEROFILL');

UPDATE groups
SET id_group_types = (SELECT id FROM lookups WHERE lookup_code = 'db_role')
WHERE `name` IN ('admin', 'therapist', 'subject');

UPDATE groups
SET id_group_types = (SELECT id FROM lookups WHERE lookup_code = 'group')
WHERE `name` NOT IN ('admin', 'therapist', 'subject');

CALL add_foreign_key('groups', 'groups_fk_id_group_types', 'id_group_types', 'lookups (id)');

-- no emials option
INSERT IGNORE INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'notifications-email', NULL);
INSERT IGNORE INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000008, 0000000002, 0000000001, 'Keine E-Mails senden'),
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000008, 0000000003, 0000000001, 'Do not send emails'),
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000023, 0000000001, 0000000001, ''),
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000054, 0000000001, 0000000001, 'checkbox'),
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000055, 0000000002, 0000000001, ''),
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000055, 0000000003, 0000000001, ''),
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000056, 0000000001, 0000000001, '0'),
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000057, 0000000001, 0000000001, 'no_emails'),
((SELECT id FROM sections WHERE `name` = 'notifications-email'), 0000000058, 0000000001, 0000000001, '0');

INSERT IGNORE INTO `sections_hierarchy` (`parent`, `child`, `position`) VALUES
((SELECT id FROM sections WHERE name = "profile-notification-formUserInput"), (SELECT id FROM sections WHERE `name` = 'notifications-email'), -1);

-- no push notification option
INSERT IGNORE INTO `sections` (`id_styles`, `name`, `owner`) VALUES (0000000016, 'notifications-push_notification', NULL);
INSERT IGNORE INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`) VALUES
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000008, 0000000002, 0000000001, 'Keine Push-Benachrichtigungen senden'),
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000008, 0000000003, 0000000001, 'Do not send push notifications'),
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000023, 0000000001, 0000000001, ''),
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000054, 0000000001, 0000000001, 'checkbox'),
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000055, 0000000002, 0000000001, ''),
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000055, 0000000003, 0000000001, ''),
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000056, 0000000001, 0000000001, '0'),
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000057, 0000000001, 0000000001, 'no_push_notifications'),
((SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), 0000000058, 0000000001, 0000000001, '0');

INSERT IGNORE INTO `sections_hierarchy` (`parent`, `child`, `position`) VALUES
((SELECT id FROM sections WHERE name = "profile-notification-formUserInput"), (SELECT id FROM sections WHERE `name` = 'notifications-push_notification'), -1);

DROP VIEW IF EXISTS view_scheduledJobs;
CREATE VIEW view_scheduledJobs
AS
SELECT sj.id AS id, l_status.lookup_code AS status_code, l_status.lookup_value AS status, l_types.lookup_code AS type_code, l_types.lookup_value AS type, sj.config,
sj.date_create, date_to_be_executed, date_executed, description, 
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.recipient_emails
    -- WHEN l_types.lookup_code = 'notification' THEN (SELECT GROUP_CONCAT(DISTINCT u.name SEPARATOR '; ') FROM scheduledJobs_users sj_u INNER JOIN users u on (u.id = sj_u.id_users) WHERE id_scheduledJobs = sj.id)
    -- WHEN l_types.lookup_code = 'task' THEN (SELECT GROUP_CONCAT(DISTINCT u.name SEPARATOR '; ') FROM scheduledJobs_users sj_u INNER JOIN users u on (u.id = sj_u.id_users) WHERE id_scheduledJobs = sj.id)
    WHEN l_types.lookup_code = 'notification' THEN ''
    WHEN l_types.lookup_code = 'task' THEN ''
    ELSE ""
END AS recipient,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.subject
    WHEN l_types.lookup_code = 'notification' THEN n.subject
    ELSE ""
END AS title,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.body
    WHEN l_types.lookup_code = 'notification' THEN n.body
    ELSE ""
END AS message,
sj_mq.id_mailQueue,
id_jobTypes,
id_jobStatus,
a.id_formActions
FROM scheduledJobs sj
INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus)
INNER JOIN lookups l_types ON (l_types.id = sj.id_jobTypes)
LEFT JOIN scheduledJobs_mailQueue sj_mq on (sj_mq.id_scheduledJobs = sj.id)
LEFT JOIN mailQueue mq on (mq.id = sj_mq.id_mailQueue)
LEFT JOIN scheduledJobs_notifications sj_n on (sj_n.id_scheduledJobs = sj.id)
LEFT JOIN notifications n on (n.id = sj_n.id_notifications)
LEFT JOIN scheduledJobs_formActions a on (a.id_scheduledJobs = sj.id);

