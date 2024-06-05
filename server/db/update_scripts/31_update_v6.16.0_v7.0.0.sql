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

-- add `displayName` column to table `uploadTables`; It can be used for the users to customize the name of their tables
CALL add_table_column('uploadTables', 'displayName', "VARCHAR(1000) DEFAULT NULL");


DELIMITER //
CREATE PROCEDURE refactor_user_input()
BEGIN
	
    DECLARE table_exists INT;
    
    -- Check if the table exists
    SELECT COUNT(*)
    INTO table_exists
    FROM information_schema.tables
    WHERE table_schema = DATABASE() AND `table_name` = 'user_input';
    
	IF table_exists > 0 THEN

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

		-- add trigger deleted to the removed entries
		UPDATE uploadRows ur
		JOIN (
			SELECT 
				ui.id_user_input_record,
				CASE 
					WHEN ui.removed = 1 THEN (SELECT id FROM lookups WHERE type_code = 'actionTriggerTypes' AND lookup_code = 'deleted' )
					ELSE NULL 
				END AS removed
			FROM user_input ui
		) subquery ON ur.old_row_id = subquery.id_user_input_record
		SET ur.id_actionTriggerTypes = subquery.removed
		WHERE ur.old_row_id > 0;
			
		-- replace the old relation of the forms in styles entryList and entryRecord
        UPDATE sections_fields_translation tran
		INNER JOIN view_sections_fields s ON (tran.id_sections = s.id_sections AND tran.id_fields = s.id_fields)
		INNER JOIN uploadTables t ON (t.`name` REGEXP '^[0-9]+$' AND CAST(SUBSTRING_INDEX(s.content, '-', 1) AS UNSIGNED) = CAST(t.`name` AS UNSIGNED))
		SET tran.content = CAST(t.id AS UNSIGNED)
		WHERE
			s.style_name IN ('entryList', 'entryRecord') AND
			s.field_name = 'formName' AND
			s.content <> '' AND
			s.content LIKE '%INTERNAL%';
		
        -- set the relation to be only the form id
        UPDATE sections_fields_translation tran
		INNER JOIN view_sections_fields s ON (tran.id_sections = s.id_sections AND tran.id_fields = s.id_fields)
		SET tran.content = CAST(SUBSTRING_INDEX(tran.content, '-', 1) AS UNSIGNED)
		WHERE s.style_name IN ('entryList', 'entryRecord') AND s.field_name = 'formName'; 
        
        -- move the scheduled jobs info from the internal to exteranl columns
        UPDATE scheduledJobs_formActions sj
		INNER JOIN uploadRows r ON sj.id_user_input_record = r.old_row_id
		SET sj.id_uploadRows = r.id
		WHERE id_user_input_record > 0;

		-- set displayName based on the name of the form	
        UPDATE view_sections_fields s
		INNER JOIN uploadTables t ON t.`name` = s.id_sections
		SET t.displayName = s.content
		WHERE s.field_name = 'name';
        
        -- update dataConfigs with the new tables
        UPDATE sections_fields_translation sft
		INNER JOIN (
			SELECT id_sections, id_fields, content
			FROM sections_fields_translation 
			WHERE id_fields = 145 AND JSON_VALID(content)
		) AS valid_sft ON valid_sft.id_sections = sft.id_sections AND valid_sft.id_fields = sft.id_fields
		INNER JOIN sections AS s ON valid_sft.id_sections = s.id
		INNER JOIN styles AS st ON st.id = s.id_styles
		INNER JOIN styles_fields AS sf ON sf.id_styles = st.id
		INNER JOIN fields AS f ON f.id = sf.id_fields
		INNER JOIN uploadTables ut ON ut.displayName = JSON_UNQUOTE(JSON_EXTRACT(valid_sft.content, '$[0].table'))
		SET sft.content = JSON_SET(
			JSON_SET(sft.content, '$[0].old_table', JSON_UNQUOTE(JSON_EXTRACT(valid_sft.content, '$[0].table'))),
			'$[0].table', ut.name
		)
		WHERE sf.disabled = 0
		  AND f.id = 145
		  AND IFNULL(valid_sft.content, '') <> '';

		CALL drop_table_column('uploadRows', 'old_row_id');
		CALL drop_table_column('uploadCols', 'old_col_id');
        -- drop column `id_user_input_record` from `scheduledJobs_formActions`
        -- CALL drop_table_column('scheduledJobs_formActions', 'id_user_input_record');

		RENAME TABLE user_input TO deprecated_user_input;
		RENAME TABLE user_input_record TO deprecated_user_input_record;
	ELSE
		SELECT 'User input is already refactored' AS message;
	END IF;	 
     
 END //

DELIMITER ;

CALL refactor_user_input();

DROP PROCEDURE IF EXISTS refactor_user_input;

-- adjust displayName in uplaodTables when the name is changed in the CMS

-- adjust the dataConfig where the internal forms are used

-- remove the delete option from the form; create a new style for deleteing form record