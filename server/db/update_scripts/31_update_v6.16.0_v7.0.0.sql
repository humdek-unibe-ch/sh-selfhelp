-- set DB version
UPDATE version
SET version = 'v7.0.0';

-- add actionTrigger types
INSERT IGNORE INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('actionTriggerTypes', 'updated', 'Updated', 'When the user saved data is saved with statut `updated`');
INSERT IGNORE INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('actionTriggerTypes', 'deleted', 'Deleted', 'When the user saved data is saved with statut `deleted`');

UPDATE lookups
SET lookup_description = 'When the user saved data is saved with statut `started`'
WHERE type_code = 'actionTriggerTypes' AND lookup_code = 'started';

UPDATE lookups
SET lookup_description = 'When the user saved data is saved with statut `finished`'
WHERE type_code = 'actionTriggerTypes' AND lookup_code = 'finished';

CALL add_table_column('uploadRows', 'id_actionTriggerTypes', "int(10) unsigned zerofill DEFAULT NULL");
CALL add_foreign_key('uploadRows', 'uploadRows_fk_id_actionTriggerTypes', 'id_actionTriggerTypes', 'lookups (id)');

CALL add_table_column('uploadRows', 'old_row_id', "int(10) unsigned zerofill DEFAULT NULL");
CALL add_table_column('uploadCols', 'old_col_id', "int(10) unsigned zerofill DEFAULT NULL");

INSERT INTO uploadTables (`name`)
SELECT DISTINCT CAST(id_sections AS CHAR) AS `name`
FROM user_input_record WHERE id_sections > 0;

INSERT INTO uploadRows (id_uploadTables, `timestamp`, id_users, old_row_id)
SELECT DISTINCT ut.id, uir.create_time, id_users, uir.id
FROM user_input_record uir
JOIN uploadTables ut ON ut.`name` = CAST(uir.id_sections AS CHAR)
JOIN user_input ui ON ui.id_user_input_record = uir.id
WHERE uir.id_sections > 0;

INSERT INTO uploadCols (`name`, id_uploadTables, old_col_id)
SELECT DISTINCT sft_in.content AS `name`, ut.id, ui.id_sections
FROM uploadTables ut
JOIN user_input_record uir ON CAST(uir.id_sections AS CHAR) = ut.`name`
JOIN user_input ui ON ui.id_user_input_record = uir.id
JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57;

INSERT IGNORE INTO uploadCells (id_uploadRows, id_uploadCols, `value`)
SELECT DISTINCT ur.id, uc.id, ui.`value`
FROM user_input ui
JOIN uploadRows ur ON (ui.id_user_input_record = ur.old_row_id)
JOIN uploadCols uc ON uc.old_col_id = ui.id_sections;

CALL drop_table_column('uploadRows', 'old_row_id');
CALL drop_table_column('uploadCols', 'old_col_id');