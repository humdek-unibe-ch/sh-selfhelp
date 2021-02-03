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
DROP COLUMN id_mailQueue;

ALTER TABLE `qualtricsReminders`
ADD CONSTRAINT `qualtricsReminders_fk_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add transactionBy
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionBy', 'by_anonymous_user', 'By anonymous user', 'The action was done by an anonymous user');

UPDATE lookups
SET type_code = "transactionTypes", lookup_code = "check_scheduledJobs", lookup_value = "Check scheduled jobs", lookup_description = "Check scheduled hobs and execute if there are any" 
WHERE type_code = "transactionTypes" AND lookup_code = "check_mailQueue";

UPDATE lookups
SET type_code = "transactionTypes", lookup_code = "by_cron_job", lookup_value = "By cron job", lookup_description = "The action was executed by cron job" 
WHERE type_code = "transactionTypes" AND lookup_code = "by_mail_cron";

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