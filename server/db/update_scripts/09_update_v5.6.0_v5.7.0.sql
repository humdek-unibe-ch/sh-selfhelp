-- set DB version
UPDATE version
SET version = 'v5.7.0';

-- add assets types
INSERT IGNORE INTO `lookups` (type_code, lookup_code, lookup_value, lookup_description) values ('assetTypes', 'css', 'CSS', 'A CSS file that will be used for custom styling');
INSERT IGNORE INTO `lookups` (type_code, lookup_code, lookup_value, lookup_description) values ('assetTypes', 'asset', 'Asset', 'Files that can be used as assets. All uploaded files are public and everyone can have access to them');
INSERT IGNORE INTO `lookups` (type_code, lookup_code, lookup_value, lookup_description) values ('assetTypes', 'static', 'Static', 'Static files which are uploaded in the static table');

-- add table `assets`
CREATE TABLE IF NOT EXISTS `assets` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `id_assetTypes` INT(10) UNSIGNED ZEROFILL NOT NULL, 
  `folder` VARCHAR(100),
  `file_name` VARCHAR(100) UNIQUE,
  `file_path` VARCHAR(1000) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `assets_fk_id_assetTypes` FOREIGN KEY (`id_assetTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
