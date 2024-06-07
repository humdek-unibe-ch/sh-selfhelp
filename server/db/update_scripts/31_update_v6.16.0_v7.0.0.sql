-- set DB version
UPDATE version
SET version = 'v7.0.0';

DELIMITER //
DROP PROCEDURE IF EXISTS rename_table_column //
CREATE PROCEDURE rename_table_column(param_table VARCHAR(100), param_old_column_name VARCHAR(100), param_new_column_name VARCHAR(100))
BEGIN	
	DECLARE columnExists INT;
    DECLARE columnType VARCHAR(255);
    SELECT COUNT(*), COLUMN_TYPE 
            INTO columnExists, columnType
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
			AND `COLUMN_NAME` = param_old_column_name; 
    SET @sqlstmt = (SELECT IF(
		columnExists > 0,        
        CONCAT('ALTER TABLE ', param_table, ' CHANGE COLUMN ', param_old_column_name, ' ', param_new_column_name, ' ', columnType, ';'),
        "SELECT 'Column does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;


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


-- Procedure for update dataConfigs with the new tables
DROP PROCEDURE IF EXISTS update_dataConfig;
DELIMITER //

CREATE PROCEDURE update_dataConfig()
BEGIN
	DECLARE done INT DEFAULT 0;
	DECLARE id_sec INT;
	DECLARE id_fld INT;
	DECLARE num_entries INT;
	DECLARE cur CURSOR FOR 
		SELECT id_sections, id_fields, JSON_LENGTH(content) AS num_entries
		FROM sections_fields_translation
		WHERE id_fields = 145 AND JSON_VALID(content);
		
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

	OPEN cur;

	read_loop: LOOP
		FETCH cur INTO id_sec, id_fld, num_entries;
		IF done THEN
			LEAVE read_loop;
		END IF;

		SET @sql_base = 'UPDATE sections_fields_translation sft
			INNER JOIN sections AS s ON sft.id_sections = s.id
			INNER JOIN styles AS st ON st.id = s.id_styles
			INNER JOIN styles_fields AS sf ON sf.id_styles = st.id
			INNER JOIN fields AS f ON f.id = sf.id_fields
			SET ';

		SET @updates = '';
		SET @i = 0;

		WHILE @i < num_entries DO
			SET @update_part = CONCAT(
				'sft.content = JSON_SET(
					JSON_SET(sft.content, ''$[', @i, '].old_form'', JSON_UNQUOTE(JSON_EXTRACT(sft.content, ''$[', @i, '].table''))),
					''$[', @i, '].table'', (SELECT ut.name FROM uploadTables ut WHERE ut.displayName = JSON_UNQUOTE(JSON_EXTRACT(sft.content, ''$[', @i, '].table'')))
				),'
			);
			SET @updates = CONCAT(@updates, @update_part);
			SET @i = @i + 1;
		END WHILE;

		SET @updates = LEFT(@updates, LENGTH(@updates) - 1); -- Remove the trailing comma
		SET @sql = CONCAT(@sql_base, ' ', @updates, ' WHERE sft.id_sections = ', id_sec, ' AND sft.id_fields = ', id_fld, ' AND sf.disabled = 0 AND f.id = 145 AND IFNULL(sft.content, '''') <> '''';');

		-- Execute the dynamically constructed SQL statement        
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END LOOP;

	CLOSE cur;
END //

DELIMITER ;

-- main refactor procedure
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
		CALL update_dataConfig();        

		CALL drop_table_column('uploadRows', 'old_row_id');
		CALL drop_table_column('uploadCols', 'old_col_id');
        -- drop column `id_user_input_record` from `scheduledJobs_formActions`
        -- CALL drop_table_column('scheduledJobs_formActions', 'id_user_input_record');
        
        -- drop column `id_user_input_record` from `scheduledJobs_formActions`
        CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_forms_INTERNAL');
        CALL drop_table_column('scheduledJobs_reminders', 'id_forms_INTERNAL');
        
        -- rename column `id_forms_EXTERNAL` in table `scheduledJobs_reminders` to `id_dataTables`
        CALL rename_table_column('scheduledJobs_reminders', 'id_forms_EXTERNAL', 'id_dataTables');

		RENAME TABLE user_input TO deprecated_user_input;
		RENAME TABLE user_input_record TO deprecated_user_input_record;
	ELSE
		SELECT 'User input is already refactored' AS message;
	END IF;	 
     
 END //

DELIMITER ;

CALL refactor_user_input();

DROP PROCEDURE IF EXISTS refactor_user_input;
DROP PROCEDURE IF EXISTS update_dataConfig;

DROP VIEW IF EXISTS view_scheduledJobs_reminders;
CREATE VIEW view_scheduledJobs_reminders
AS
SELECT r.id_scheduledJobs, r.id_dataTables,
r.session_start_date, r.session_end_date, sju.id_users,l_status.lookup_code as job_status_code, l_status.lookup_value as job_status
FROM scheduledJobs_reminders r
INNER JOIN scheduledJobs sj ON (sj.id = r.id_scheduledJobs)
INNER JOIN scheduledJobs_users sju ON (sj.id = sju.id_scheduledJobs)
INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus);


-- remove the delete option from the form; create a new style for deleteing form record

-- rename uploadTables and so on to dataTables and so on