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
  CONSTRAINT `fk_formActions_EXTERNAL_id_formActions` FOREIGN KEY (`id_formActions`) REFERENCES `formActions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
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

CREATE TABLE IF NOT EXISTS `scheduledJobs_reminders` (
	`id_scheduledJobs` INT(10) UNSIGNED ZEROFILL NOT NULL,
    `id_forms_INTERNAL` INT(10) UNSIGNED ZEROFILL NULL,  
	`id_forms_EXTERNAL` INT(10) UNSIGNED ZEROFILL NULL,	
	`session_start_date` DATETIME,
    `session_end_date` DATETIME,
	CONSTRAINT `scheduledJobs_reminders_id_scheduledJobs` FOREIGN KEY (`id_scheduledJobs`) REFERENCES `scheduledJobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `scheduledJobs_reminders_id_forms_INTERNAL` FOREIGN KEY (`id_forms_INTERNAL`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `scheduledJobs_reminders_id_forms_EXTERNAL` FOREIGN KEY (`id_forms_EXTERNAL`) REFERENCES `uploadTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


UPDATE pages
SET url = '/admin/formsActions/[select|update|insert|delete:mode]?/[i:aid]?'
WHERE keyword = 'moduleFormsAction';

ALTER TABLE transactions
MODIFY COLUMN transaction_log MEDIUMTEXT;


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

DROP VIEW IF EXISTS view_formActionsReminders;

DROP VIEW IF EXISTS view_scheduledJobs_reminders;
CREATE VIEW view_scheduledJobs_reminders
AS
SELECT r.id_scheduledJobs, r.id_forms_INTERNAL, r.id_forms_EXTERNAL,
r.session_start_date, r.session_end_date, sju.id_users,l_status.lookup_code as job_status_code, l_status.lookup_value as job_status
FROM scheduledJobs_reminders r
INNER JOIN scheduledJobs sj ON (sj.id = r.id_scheduledJobs)
INNER JOIN scheduledJobs_users sju ON (sj.id = sju.id_scheduledJobs)
INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus);

DROP VIEW IF EXISTS view_form;
CREATE VIEW view_form
AS
SELECT DISTINCT cast(form.id AS UNSIGNED) form_id, sft_if.content AS form_name, IFNULL(sft_intern.content, 0) AS internal
FROM user_input ui
LEFT JOIN sections form  ON (ui.id_section_form = form.id)
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_intern ON sft_intern.id_sections = ui.id_section_form AND sft_intern.id_fields = (SELECT id
FROM `fields`
WHERE `name` = 'internal');





