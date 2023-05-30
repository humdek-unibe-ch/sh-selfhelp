-- set DB version
UPDATE version
SET version = 'v6.3.0';

 ALTER TABLE `tasks`
 MODIFY COLUMN config LONGTEXT;
 
 INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'password', '11');
 INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'panel', '0');

-- add title to the page calendar view 
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), get_field_id('title'), '0000000001', 'Scheduled Jobs - Calendar View');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = 'moduleScheduledJobsCalendar'), get_field_id('title'), '0000000002', 'Scheduled Jobs - Calendar View');

UPDATE pages
SET url = "/admin/scheduledJobs/calendar/[i:uid]?/[i:aid]?"
WHERE keyword = 'moduleScheduledJobsCalendar'