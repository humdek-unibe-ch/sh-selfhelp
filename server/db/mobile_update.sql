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

-- rename mailQueue table to eventQueue table
#ALTER TABLE mailQueue RENAME eventQueue;

#ALTER TABLE `mailQueue`
#ADD CONSTRAINT `mailQueue_fk_id_mailQueueStatus` FOREIGN KEY (`id_mailQueueStatus`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add field id_notificationTypes in table mailQueue
ALTER TABLE mailQueue
ADD COLUMN `id_notificationTypes` int(10 ) UNSIGNED ZEROFILL NOT NULL;

-- set all mailQueue types to email as all of them were email before adding the type
update mailQueue
set id_notificationTypes = (select id from lookups where lookup_code = "email");

ALTER TABLE `mailQueue`
ADD CONSTRAINT `mailQueue_fk_id_notificationTypes` FOREIGN KEY (`id_notificationTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add table mailQueue_users
CREATE TABLE `mailQueue_users` (
  `id_users` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_mailQueue` INT(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY(id_users, id_mailQueue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `mailQueue_users`
ADD CONSTRAINT `mailQueue_users_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `mailQueue_users_fk_id_mailQueue` FOREIGN KEY (`id_mailQueue`) REFERENCES `mailQueue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT mq.id AS id, l_status.lookup_code AS status_code, l_status.lookup_value AS status, l_types.lookup_code AS type_code, l_types.lookup_value AS type, 
date_create, date_to_be_sent, date_sent, from_email, from_name,
reply_to, recipient_emails, cc_emails, bcc_emails, subject, body, is_html
FROM mailQueue mq
INNER JOIN lookups l_status ON (l_status.id = mq.id_mailQueueStatus)
INNER JOIN lookups l_types ON (l_types.id = mq.id_notificationTypes);

-- add transactionTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'send_notification_ok', 'Send notification successfully', 'Send notification successfully');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'send_notification_fail', 'Send notification failed', 'Send notification failed');

-- add fields fcm_api_key and fcm_sender_id to table for FCM configuration
ALTER TABLE cmsPreferences
ADD COLUMN `fcm_api_key` varchar(200) DEFAULT NULL,
ADD COLUMN `fcm_sender_id` varchar(500) DEFAULT NULL;

DROP VIEW IF EXISTS view_cmsPreferences;
CREATE VIEW view_cmsPreferences
AS
SELECT p.callback_api_key, p.default_language_id, l.language as default_language, l.locale, p.fcm_api_key, p.fcm_sender_id
FROM cmsPreferences p
LEFT JOIN languages l ON (l.id = p.default_language_id)
WHERE p.id = 1;
