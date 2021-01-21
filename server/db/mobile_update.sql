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
ALTER TABLE mailQueue RENAME eventQueue;

ALTER TABLE `mailQueue`
ADD CONSTRAINT `mailQueue_fk_id_mailQueueStatus` FOREIGN KEY (`id_mailQueueStatus`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;