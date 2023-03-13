-- set DB version
UPDATE version
SET version = 'v5.12.0';

UPDATE sections_fields_translation
SET content = REPLACE(content, '"type": "static"', '"type": "EXTERNAL"')
WHERE id_fields = get_field_id('data_config');

UPDATE sections_fields_translation
SET content = REPLACE(content, '"type": "dynamic"', '"type": "INTERNAL"')
WHERE id_fields = get_field_id('data_config');

UPDATE sections_fields_translation
SET content = REPLACE(content, '-static', '-EXTERNAL')
WHERE id_fields = get_field_id('formName');

UPDATE sections_fields_translation
SET content = REPLACE(content, '-dynamic', '-INTERNAL')
WHERE id_fields = get_field_id('formName');

CREATE TABLE IF NOT EXISTS `formActions_INTERNAL` (
  `id_forms` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_formActions` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id_forms`,`id_formActions`),
  CONSTRAINT `fk_formActions_INTERNAL_id_forms` FOREIGN KEY (`id_forms`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_formActions_INTERNAL_id_formActions` FOREIGN KEY (`id_formActions`) REFERENCES `formActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `formActions_EXTERNAL` (
  `id_forms` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_formActions` int(10) UNSIGNED ZEROFILL NOT NULL,
  PRIMARY KEY (`id_forms`,`id_formActions`),
  CONSTRAINT `fk_formActions_EXTERNAL_id_forms` FOREIGN KEY (`id_forms`) REFERENCES `uploadTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_formActions_EXTERNALformActions_EXTERNAL_id_formActions` FOREIGN KEY (`id_formActions`) REFERENCES `formActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET @old_form_exists:= (SELECT COUNT(*) FROM information_schema.`tables` WHERE table_schema = DATABASE() AND `table_name` = 'old_formActions');
SELECT @old_form_exists;

SET @existing_actions := (SELECT COUNT(*) FROM formActions);
SET @query = If(@existing_actions > 0 AND @old_form_exists = 0,
    'RENAME TABLE formActions TO old_formActions; DROP TABLE IF EXISTS formActions;',
    'SELECT \'no legacy actions\' ');
PREPARE stmt FROM @query;
EXECUTE stmt;

-- add table formActions
CREATE TABLE IF NOT EXISTS `formActions` (
	`id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,		
	`name` VARCHAR(200) NOT NULL,    
    `id_formProjectActionTriggerTypes` INT(10 ) UNSIGNED ZEROFILL NOT NULL,            
    `config` TEXT,
	`condition_logic` VARCHAR(10000),
    `condition_jquery_builder_json` VARCHAR(10000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


UPDATE pages
SET url = '/admin/formsActions/[select|update|insert|delete:mode]?/[i:aid]?'
WHERE keyword = 'moduleFormsAction';

DROP VIEW IF EXISTS view_data_tables;
CREATE VIEW view_data_tables
AS
SELECT 'INTERNAL' AS `type`, form_id AS id, form_name AS orig_name, concat(form_name, '_dynamic') AS `table_name`, CONCAT(form_id,"-","INTERNAL") AS form_id_plus_type, internal
FROM view_form

UNION

SELECT 'EXTERNAL' AS `type`, id AS id, `name` AS orig_name, CONCAT(`name`, '_static') AS `table_name`, CONCAT(FLOOR(id),"-","EXTERNAL") AS form_id_plus_type, 0  AS internal
FROM uploadTables;

DROP VIEW IF EXISTS view_formActions;
CREATE VIEW view_formActions
AS
SELECT fa.id AS id, fa.`name` AS action_name, orig_name AS form_name,
fa.id_formProjectActionTriggerTypes, trig.lookup_value AS trigger_type, trig.lookup_code AS trigger_type_code,
config,
condition_logic, condition_jquery_builder_json,
CASE
	WHEN ex.id_forms > 0 THEN CONCAT(FLOOR(ex.id_forms), '-EXTERNAL')
	WHEN inter.id_forms > 0 THEN CONCAT(FLOOR(inter.id_forms), '-INTERNAL')
END AS id_forms
FROM formActions fa 
INNER JOIN lookups trig ON (trig.id = fa.id_formProjectActionTriggerTypes)
LEFT JOIN formActions_EXTERNAL ex ON (fa.id = ex.id_formActions)
LEFT JOIN formActions_INTERNAL inter ON (fa.id = inter.id_formActions)
LEFT JOIN view_data_tables dt ON (dt.form_id_plus_type = CASE
	WHEN ex.id_forms > 0 THEN CONCAT(FLOOR(ex.id_forms), '-EXTERNAL')
	WHEN inter.id_forms > 0 THEN CONCAT(FLOOR(inter.id_forms), '-INTERNAL')
END);





