-- set DB version
UPDATE version
SET version = 'v4.1.0';

-- add pageAccessTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'mobile', 'Mobile', 'The page will be loaded only for mobile apps');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'web', 'Web', 'The page will be loaded only for the website');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageAccessTypes', 'mobile_and_web', 'Mobile and web', 'The page will be loaded for web and mobile');

ALTER TABLE `pages`
ADD COLUMN `id_pageAccessTypes` int(10) UNSIGNED ZEROFILL,
ADD CONSTRAINT `pages_fk_id_pacgeAccessTypes` FOREIGN KEY (`id_pageAccessTypes`) REFERENCES `lookups` (`id`);

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE type_code = 'pageAccessTypes' AND lookup_code = 'mobile_and_web');

