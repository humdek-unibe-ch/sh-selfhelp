-- set DB version
UPDATE version
SET version = 'v6.3.0';

 ALTER TABLE `tasks`
 MODIFY COLUMN config LONGTEXT;