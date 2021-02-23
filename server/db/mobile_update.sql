-- set DB version
UPDATE version
SET version = 'v4.0.0';

-- add transactionBy
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionBy', 'by_system', 'By Selfhelp', 'By Selfhelp');

-- device_id field in table users
ALTER TABLE users
ADD COLUMN device_id VARCHAR(100);

-- device_token field in table users
ALTER TABLE users
ADD COLUMN device_token VARCHAR(200);

-- add notificationTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('notificationTypes', 'push_notification', 'Push Notification', 'The notification will be sent by a push message. It works only for mobile devices!');

-- add transactionTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'send_notification_ok', 'Send notification successfully', 'Send notification successfully');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'send_notification_fail', 'Send notification failed', 'Send notification failed');

-- add fields fcm_api_key and fcm_sender_id to table for FCM configuration
ALTER TABLE cmsPreferences
ADD COLUMN `fcm_api_key` VARCHAR(200) DEFAULT NULL,
ADD COLUMN `fcm_sender_id` VARCHAR(500) DEFAULT NULL;

UPDATE pages_fields_translation
SET content = "Module Scheduled Jobs"
WHERE id_pages = (SELECT id FROM pages WHERE keyword = "moduleMail");

-- add table scheduledJobs
CREATE TABLE `scheduledJobs` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `id_jobTypes` INT(10 ) UNSIGNED ZEROFILL NOT NULL,
  `id_jobStatus` INT(10) UNSIGNED ZEROFILL NOT NULL,  
  `description` VARCHAR(1000) DEFAULT NULL,
  `date_create` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
  `date_to_be_executed` DATETIME,
  `date_executed` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `scheduledJobs`
ADD CONSTRAINT `scheduledJobs_fk_id_jobTypes` FOREIGN KEY (`id_jobTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `scheduledJobs_fk_id_jobStatus` FOREIGN KEY (`id_jobStatus`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE lookups
DROP INDEX lookup_code;

ALTER TABLE lookups
DROP INDEX lookup_value;

ALTER TABLE `lookups` 
ADD UNIQUE `idx_lookups_type_code_lookup_code`(`type_code`, `lookup_code`);

-- add jobTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('jobTypes', 'email', 'Email', 'Email Job Type');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('jobTypes', 'notification', 'Notification', 'Notification Job Type');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('jobTypes', 'task', 'Task', 'Task Job Type');

UPDATE lookups
SET lookup_description = "Status for initialization. When the job is queued it goes in this status"
WHERE type_code = "mailQueueStatus" and lookup_code = "queued";

UPDATE lookups
SET lookup_description = "When the job is deleted"
WHERE type_code = "mailQueueStatus" and lookup_code = "deleted";

UPDATE lookups
SET lookup_description = "When the job is done"
WHERE type_code = "mailQueueStatus" and lookup_code = "sent";

UPDATE lookups
SET lookup_description = "When something happened and the job failed"
WHERE type_code = "mailQueueStatus" and lookup_code = "failed";

UPDATE lookups
SET lookup_code = "done", lookup_value = "Done", lookup_description = "Job was executed successfully" 
WHERE type_code = "mailQueueStatus" and lookup_code = "sent";

UPDATE lookups
SET type_code = "scheduledJobsStatus"
WHERE type_code = "mailQueueStatus";

-- add table scheduledJobs_mailQueue
CREATE TABLE `scheduledJobs_mailQueue` (
  `id_scheduledJobs` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_mailQueue` INT(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY(id_scheduledJobs, id_mailQueue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `scheduledJobs_mailQueue`
ADD CONSTRAINT `scheduledJobs_mailQueue_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `scheduledJobs_mailQueue_fk_id_mailQueue` FOREIGN KEY (`id_mailQueue`) REFERENCES `mailQueue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add column id_mailQueue which will be used for the linking during refactoring and then deleted
ALTER TABLE scheduledJobs
ADD COLUMN id_mailQueue INT(10 ) UNSIGNED;

-- generate scheduled jobs from old emails
INSERT INTO scheduledJobs (id_jobTypes, id_jobStatus, description, date_create, date_to_be_executed, date_executed, id_mailQueue)
SELECT (SELECT id FROM lookups WHERE type_code = "jobTypes" AND lookup_code = "email"), id_mailQueueStatus, 'Email Job', date_create, date_to_be_sent, date_sent, id
FROM mailQueue;

-- insert into linking table
INSERT INTO scheduledJobs_mailQueue (id_scheduledJobs, id_mailQueue)
SELECT id, id_mailQueue
FROM scheduledJobs;

-- remove the column after the linking was created
ALTER TABLE scheduledJobs
DROP COLUMN id_mailQueue;

-- update moduleMail path to module scheduledJobs
UPDATE pages
SET keyword = "moduleScheduledJobs", url = "/admin/scheduledJobs/[i:sjid]?"
WHERE keyword = 'moduleMail';

-- update moduleMailComposeEmail path to module scheduledJobs
UPDATE pages
SET keyword = "moduleScheduledJobsCompose", url = "/admin/scheduledJobs/compose/[v:type]"
WHERE keyword = 'moduleMailComposeEmail';


-- register moduleScheduledJobs
CALL proc_register_module('moduleScheduledJobs', 'moduleScheduledJobs', 1);

-- delete moduleMail
DELETE FROM modules
WHERE module_name = 'moduleMail';

UPDATE lookups
SET type_code = "scheduledJobsSearchDateTypes", lookup_code = "date_create", lookup_value = "Entry date", lookup_description = "The date that the queue record was created" 
WHERE type_code = "mailQueueSearchDateTypes" AND lookup_code = "date_create";

UPDATE lookups
SET type_code = "scheduledJobsSearchDateTypes", lookup_code = "date_to_be_executed", lookup_value = "Date to be executed", lookup_description = "The date when the queue record should be executed" 
WHERE type_code = "mailQueueSearchDateTypes" AND lookup_code = "date_to_be_sent";

UPDATE lookups
SET type_code = "scheduledJobsSearchDateTypes", lookup_code = "date_executed", lookup_value = "Execution date", lookup_description = "The date when the queue record was executed" 
WHERE type_code = "mailQueueSearchDateTypes" AND lookup_code = "date_sent";

DROP VIEW IF EXISTS view_mailQueue_transactions;

-- refactor old mail transactions to point to scheduledJobs
UPDATE transactions
SET table_name = 'scheduledJobs', id_table_name = (SELECT id_scheduledJobs FROM scheduledJobs_mailQueue sj_mq WHERE id_table_name = sj_mq.id_mailQueue)
WHERE table_name = 'mailQueue';

ALTER TABLE mailQueue
DROP COLUMN date_create;

ALTER TABLE mailQueue
DROP COLUMN date_to_be_sent;

ALTER TABLE mailQueue
DROP COLUMN date_sent;

ALTER TABLE mailQueue
DROP FOREIGN KEY mailQueue_fk_id_mailQueueStatus;

ALTER TABLE mailQueue
DROP COLUMN id_mailQueueStatus;

ALTER TABLE qualtricsReminders
ADD `id_scheduledJobs` INT(10 ) UNSIGNED ZEROFILL NOT NULL;

UPDATE qualtricsReminders
SET id_scheduledJobs = (SELECT id_scheduledJobs FROM scheduledJobs_mailQueue sjmq WHERE sjmq.id_mailQueue = id_mailQueue);

ALTER TABLE qualtricsReminders
DROP FOREIGN KEY qualtricsReminders_fk_id_mailQueue;
ALTER TABLE qualtricsReminders
DROP FOREIGN KEY qualtricsReminders_fk_id_qualtricsSurveys;
ALTER TABLE qualtricsReminders
DROP FOREIGN KEY qualtricsReminders_fk_id_users;

ALTER TABLE `qualtricsReminders`
DROP PRIMARY KEY;
ALTER TABLE `qualtricsReminders`
ADD PRIMARY KEY (`id_qualtricsSurveys`,`id_users`, `id_scheduledJobs`);

ALTER TABLE qualtricsReminders
DROP COLUMN id_mailQueue;

ALTER TABLE `qualtricsReminders`
ADD CONSTRAINT `qualtricsReminders_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsReminders_fk_id_qualtricsSurveys` FOREIGN KEY (`id_qualtricsSurveys`) REFERENCES `qualtricsSurveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsReminders_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add transactionBy
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionBy', 'by_anonymous_user', 'By anonymous user', 'The action was done by an anonymous user');

UPDATE lookups
SET type_code = "transactionTypes", lookup_code = "check_scheduledJobs", lookup_value = "Check scheduled jobs", lookup_description = "Check scheduled hobs and execute if there are any" 
WHERE type_code = "transactionTypes" AND lookup_code = "check_mailQueue";

UPDATE lookups
SET type_code = "transactionBy", lookup_code = "by_cron_job", lookup_value = "By cron job", lookup_description = "The action was executed by cron job" 
WHERE type_code = "transactionBy" AND lookup_code = "by_mail_cron";

-- add table scheduledJobs_users
CREATE TABLE `scheduledJobs_users` (
  `id_users` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_scheduledJobs` INT(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY(id_users, id_scheduledJobs)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `scheduledJobs_users`
ADD CONSTRAINT `scheduledJobs_users_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `scheduledJobs_users_fk_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add table notifications
CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,    
  `subject` VARCHAR(1000) NOT NUll,
  `body` LONGTEXT NOT NUll
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add table scheduledJobs_notifications
CREATE TABLE `scheduledJobs_notifications` (
  `id_scheduledJobs` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_notifications` INT(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY(id_scheduledJobs, id_notifications)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `scheduledJobs_notifications`
ADD CONSTRAINT `scheduledJobs_notifications_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `scheduledJobs_notifications_fk_id_notifications` FOREIGN KEY (`id_notifications`) REFERENCES `notifications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'status_change', 'Status changed', 'Status change');

-- change home page from '/' to '/home'
UPDATE pages
SET url = '/home'
WHERE keyword = 'home';

-- predefine icon page field
INSERT INTO pages_fields_translation (`id_pages`, `id_fields`, `id_languages`, `content`)
SELECT id, 54, 1, ''
FROM pages;

-- add timestamp filed to table uploadRows
ALTER TABLE uploadRows
ADD COLUMN `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- add field once_per_schedule to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'once_per_schedule', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('once_per_schedule'), 0, 'If checked the survey can be done once per schedule');

-- add field once_per_user to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'once_per_user', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('once_per_user'), 0, 'If checked the survey can be done only once by an user. The checkbox `once_per_schedule` is ignore if this is checked');

-- add field start_time to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'start_time', get_field_type_id('time'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('start_time'), '00:00', 'Start time when the survey should be available');

-- add field end_time to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'end_time', get_field_type_id('time'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('end_time'), '00:00', 'End time when the survey should be not available anymore');

-- add field label_survey_done to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_survey_done', get_field_type_id('markdown'), 1);
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('label_survey_done'), null, 'Markdown text that is shown if the survey is done and it can be filled only once per schedule');

-- add field label_survey_not_active to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'label_survey_not_active', get_field_type_id('markdown'), 1);
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('label_survey_not_active'), null, 'Markdown text that is shown if the survey is not active right now.');

-- add fields id_qualtricsActions to table qualtricsActions. It is used for linking reminders actions to notifications for surveys with sessions and block shceduling
ALTER TABLE qualtricsActions
ADD COLUMN `id_qualtricsActions` INT(10) UNSIGNED ZEROFILL;

-- add table scheduledJobs_qualtricsActions
CREATE TABLE `scheduledJobs_qualtricsActions` (
  `id_scheduledJobs` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_qualtricsActions` INT(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY(id_scheduledJobs, id_qualtricsActions)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `scheduledJobs_qualtricsActions`
ADD CONSTRAINT `scheduledJobs_qualtricsActions_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `scheduledJobs_qualtricsActions_fk_iid_qualtricsActions` FOREIGN KEY (`id_qualtricsActions`) REFERENCES `qualtricsActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add fields url to table notifications. It is used to navigate to a page when a notification is recieved
ALTER TABLE notifications
ADD COLUMN `url` VARCHAR(100) DEFAULT NULL;

-- Add style messageBoard
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'messageBoard', '0000000002', '0000000002', 'Shows a board of messages which can be rated and commented with pre-defined messages.');
SET @id_style = LAST_INSERT_ID();
-- Add new fields used for style messageBoard
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'text');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'form_name', @id_field_type, '0');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'json');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'icons', @id_field_type, '0');
SET @id_field_type = (SELECT `id` FROM `fieldType` WHERE `name` = 'json');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'comments', @id_field_type, '0');
-- Assign fields to style messageBoard
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'css');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'Allows to assign CSS classes to the root item of the style. E.g use the bootsrap class [`card-columns`](!https://getbootstrap.com/docs/4.6/components/card/#card-columns) to arrange the messages in a grid.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The title of a message. The special string `@publisher` will be replaced by the name of the user who published the message.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'text_md');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The messgae to be displayed nex to the score badge.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'form_name');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'The name of the form under which the score is stored.');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'icons');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'A list of icons to be displayed in the message footer. Use a JSON array of free [fontawesome](https://fontawesome.com/icons?d=gallery&m=free) icons. E.g.\n```json\n[\n  "fa-thumbs-up",\n  "fa-laugh",\n  "fa-heart"\n]\n```');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'comments');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'A list of comments to be displayed in a dropdown of a message footer. Use a JSON array of exclamations for the user to select. E.g.\n```json\n[\n  "Well done!",\n  "Keep it up!",\n  "Nice one!"\n]\n```');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'max');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 0, 'The maximal number of messages to be shown. `0` means all messages.');

-- add json field 'data_config' in style input
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('input'), get_field_id('data_config'), '', 
'In this ***JSON*** field we can configure a data retrieve params from the DB, either `static` or `dynamic` data. Example: 
 ```
 [
	{
		"type": "static|dynamic",
		"table": "table_name | #url_param1",
        "retrieve": "first | last | all",
		"fields": [
			{
				"field_name": "name | #url_param2",
				"field_holder": "@field_1",
				"not_found_text": "my field was not found"				
			}
		]
	}
]
```
If the page supports parameters, then the parameter can be accessed with `#` and the name of the paramer. Example `#url_param_name`. 

In order to inlcude the retrieved data in the input `value`, include the `field_holder` that wa defined in the markdown text.

We can access multiple tables by adding another element to the array. The retrieve data from the column can be: `first` entry, `last` entry or `all` entries (concatenated with ;);

`It is used for prefil of the default value`');

INSERT INTO genders (name) VALUES ('divers');

-- add field restart_on_refresh to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'restart_on_refresh', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('restart_on_refresh'), 0, 'If checked the survey is restarted on refresh');

-- add field use_as_container to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'use_as_container', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('use_as_container'), 0, 'If checked the style is used as container only and do not visualize the survey in iFrame');

-- add field children to style qualtricsSurvey
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('children'), 0, 'Children that can be added to the style. It is mainly used when the style is used as container');

-- add field close_modal_at_end to style qualtricsSurvey
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'close_modal_at_end', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('qualtricsSurvey'), get_field_id('close_modal_at_end'), 0, '`Only for mobile` - if selected the modal form will be closed once the survey is done');

-- add qualtricsActionScheduleTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('qualtricsActionScheduleTypes', 'task', 'Task', 'Schedule');

-- add table tasks
CREATE TABLE `tasks` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,    
  `config` VARCHAR(1000) NOT NUll
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add table scheduledJobs_tasks
CREATE TABLE `scheduledJobs_tasks` (
  `id_scheduledJobs` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_tasks` INT(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY(id_scheduledJobs, id_tasks)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `scheduledJobs_tasks`
ADD CONSTRAINT `scheduledJobs_tasks_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `scheduledJobs_tasks_fk_id_tasks` FOREIGN KEY (`id_tasks`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add transactionTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'execute_task_ok', 'Execute task successfully', 'Execute task successfully');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'execute_task_fail', 'Execute task failed', 'Execute task failed');

-- add field image_selector to style select
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'image_selector', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('select'), get_field_id('image_selector'), 0, 'If checked the style treat the values as images and expect image paths in the `text` property');

-- ************************** EXECUTEED ON BECCCS ***********************************************************************

-- add column config to table scheduledJobs. It is used to check if the job should be executed if the condition is fulfilled. If conditon is not defined it will be executed
ALTER TABLE scheduledJobs
ADD COLUMN `config` VARCHAR(1000) DEFAULT NULL;