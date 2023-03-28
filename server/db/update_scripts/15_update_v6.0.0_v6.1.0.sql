-- set DB version
UPDATE version
SET version = 'v6.1.0';

 -- add Calendar view in moduleScheduledJobs 
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'moduleScheduledJobsCalendar', '/admin/scheduledJobs/calendar', 'GET|POST', '0000000002', NULL, NULL, '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE type_code = "pageAccessTypes" AND lookup_code = "mobile_and_web"));

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), '0000000008', '0000000001', 'Scheduled Jobs - Calendar View');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), '0000000054', '0000000001', '');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', (SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), '1', '0', '0', '0');
