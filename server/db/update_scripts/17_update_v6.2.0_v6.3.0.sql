-- set DB version
UPDATE version
SET version = 'v6.3.0';

 ALTER TABLE `tasks`
 MODIFY COLUMN config LONGTEXT;
 
 INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'password', '11');
 INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'panel', '0');