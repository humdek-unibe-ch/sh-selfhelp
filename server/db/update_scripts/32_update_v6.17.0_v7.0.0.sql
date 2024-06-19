-- set DB version
UPDATE version
SET version = 'v7.0.0';

DELIMITER //
DROP PROCEDURE IF EXISTS drop_index //
CREATE PROCEDURE drop_index(param_table VARCHAR(100), param_index_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.STATISTICS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `index_name` = param_index_name
		) > 0,        
        CONCAT('ALTER TABLE ', param_table, ' DROP INDEX ', param_index_name),
        "SELECT 'The index does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;


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

DELIMITER //
DROP PROCEDURE IF EXISTS rename_table //
CREATE PROCEDURE rename_table(param_old_table_name VARCHAR(100), param_new_table_name VARCHAR(100))
BEGIN	
	DECLARE tableExists INT;
    SELECT COUNT(*) 
            INTO tableExists
			FROM information_schema.COLUMNS
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_old_table_name; 
    SET @sqlstmt = (SELECT IF(
		tableExists > 0,        
        CONCAT('RENAME TABLE ', param_old_table_name, ' TO ', param_new_table_name),
        "SELECT 'Table does not exists in the table'"
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

-- update action reminders form id
DROP PROCEDURE IF EXISTS update_formId_reminders;
DELIMITER //

DELIMITER $$

CREATE PROCEDURE update_formId_reminders()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE record_id INT;
    DECLARE json_data JSON;
    DECLARE block_index INT DEFAULT 0;
    DECLARE job_index INT DEFAULT 0;
    DECLARE reminder_form_id VARCHAR(255);
    DECLARE new_id INT;

    -- Declare cursor for iterating over records
    DECLARE cur CURSOR FOR
        SELECT id, config FROM formActions FOR UPDATE;

    -- Declare handler to handle end of data
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    -- Open the cursor
    OPEN cur;

    -- Loop through the records
    read_loop: LOOP
        FETCH cur INTO record_id, json_data;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Loop through the blocks and jobs to update the reminder_form_id
        SET block_index = 0;
        WHILE JSON_LENGTH(json_data, '$.blocks') > block_index DO
            SET job_index = 0;
            WHILE JSON_LENGTH(json_data, CONCAT('$.blocks[', block_index, '].jobs')) > job_index DO
                SET reminder_form_id = JSON_UNQUOTE(JSON_EXTRACT(json_data, CONCAT('$.blocks[', block_index, '].jobs[', job_index, '].reminder_form_id')));
                
                IF reminder_form_id LIKE '%-INTERNAL' THEN
                    -- Handle -INTERNAL case
                    SET reminder_form_id = SUBSTRING_INDEX(reminder_form_id, '-', 1);
                    SELECT LPAD(id, 10, '0') INTO new_id FROM dataTables WHERE CAST(name AS UNSIGNED) = CAST(reminder_form_id AS UNSIGNED) LIMIT 1;
                    SET json_data = JSON_SET(json_data, CONCAT('$.blocks[', block_index, '].jobs[', job_index, '].reminder_form_id'), CAST(new_id AS CHAR));
                    
                ELSEIF reminder_form_id LIKE '%-EXTERNAL' THEN
                    -- Handle -EXTERNAL case
                    SET reminder_form_id = SUBSTRING_INDEX(reminder_form_id, '-', 1);
                    SET new_id = LPAD(reminder_form_id, 10, '0');
                    SET json_data = JSON_SET(json_data, CONCAT('$.blocks[', block_index, '].jobs[', job_index, '].reminder_form_id'), new_id);
                END IF;
                
                SET job_index = job_index + 1;
            END WHILE;
            SET block_index = block_index + 1;
        END WHILE;

        -- Update the JSON back to the table
        UPDATE formActions SET config = json_data WHERE id = record_id;
    END LOOP;

    -- Close the cursor
    CLOSE cur;
END$$

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
    
		CALL add_table_column('uploadRows', 'id_actionTriggerTypes', "int(10) unsigned zerofill DEFAULT NULL");
		CALL add_foreign_key('uploadRows', 'uploadRows_fk_id_actionTriggerTypes', 'id_actionTriggerTypes', 'lookups (id)');

		-- add `displayName` column to table `uploadTables`; It can be used for the users to customize the name of their tables
		CALL add_table_column('uploadTables', 'displayName', "VARCHAR(1000) DEFAULT NULL");

		CALL add_table_column('uploadRows', 'old_row_id', "int(10) unsigned zerofill DEFAULT NULL");
		CALL add_table_column('uploadCols', 'old_col_id', "int(10) unsigned zerofill DEFAULT NULL");

		INSERT IGNORE INTO uploadTables (`name`)
		SELECT DISTINCT CAST(id_sections AS CHAR) AS `name`
		FROM user_input_record WHERE id_sections > 0;

		INSERT IGNORE INTO uploadRows (id_uploadTables, `timestamp`, id_users, old_row_id)
		SELECT DISTINCT ut.id, uir.create_time, id_users, uir.id
		FROM user_input_record uir
		JOIN uploadTables ut ON ut.`name` = CAST(uir.id_sections AS CHAR)
		JOIN user_input ui ON ui.id_user_input_record = uir.id
		WHERE uir.id_sections > 0;
		
		ALTER TABLE uploadCols MODIFY `name` VARCHAR(255);
        ALTER TABLE uploadCols ADD UNIQUE KEY unique_name_id_dataTables(`name`, id_uploadTables);
        
		INSERT IGNORE INTO uploadCols (`name`, id_uploadTables, old_col_id)
		SELECT DISTINCT SUBSTRING(sft_in.content, 1, 255) AS `name`, ut.id, ui.id_sections
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
        
        -- RENAME UPLOAD TABLE
        CALL rename_table('uploadTables', 'dataTables');
        CALL rename_table('uploadRows', 'dataRows');
        CALL rename_table('uploadCols', 'dataCols');
        CALL rename_table('uploadCells', 'dataCells');
        CALL rename_table_column('dataRows', 'id_uploadTables', 'id_dataTables');
        CALL rename_table_column('dataCols', 'id_uploadTables', 'id_dataTables');
        CALL rename_table_column('dataCells', 'id_uploadRows', 'id_dataRows');
        CALL rename_table_column('dataCells', 'id_uploadCols', 'id_dataCols');
                
        -- refactor `select-formName` linking for EXTERNAL
        UPDATE sections_fields_translation sft
		LEFT JOIN fields f ON f.id = sft.id_fields
		LEFT JOIN fieldtype ft ON f.id_type = ft.id
		SET sft.content = REPLACE(sft.content, '-EXTERNAL', '')
		WHERE ft.`name` = 'select-formName' AND sft.content LIKE '%-EXTERNAL%';
        
        -- refactor `select-formName` linking for INTERNAL
        UPDATE sections_fields_translation sft
		INNER JOIN fields f ON f.id = sft.id_fields
		INNER JOIN fieldtype ft ON f.id_type = ft.id AND ft.`name` = 'select-formName'
		INNER JOIN dataTables dt ON
			CAST(dt.`name` AS UNSIGNED) = CAST(SUBSTRING_INDEX(sft.content, '-INTERNAL', 1) AS UNSIGNED)
		SET sft.content = dt.id
		WHERE
			sft.content LIKE '%-INTERNAL' 
			AND sft.content REGEXP '^[0-9]+-INTERNAL$'
			AND CHAR_LENGTH(SUBSTRING_INDEX(sft.content, '-INTERNAL', 1)) > 0
			AND dt.name REGEXP '^[0-9]+$';

                
                
        CALL add_table_column('formActions', 'id_dataTables', 'int(10) unsigned zerofill DEFAULT NULL');
        CALL add_foreign_key('formActions', 'formActions_id_dataTables', 'id_dataTables', '`dataTables` (`id`)');  			
        
        -- replace the old relation of the forms in formActions
        UPDATE formActions a
		INNER JOIN formActions_INTERNAL i ON a.id = i.id_formActions
		INNER JOIN dataTables dt ON CAST(dt.`name` AS CHAR) = CAST(i.id_forms AS CHAR)
		SET a.id_dataTables = dt.id;
        
        UPDATE formActions a
		INNER JOIN formActions_EXTERNAL e ON a.id = e.id_formActions
		INNER JOIN dataTables dt ON dt.id = e.id_forms
		SET a.id_dataTables = dt.id;	
        
        -- replace reminder formId in formActions
        CALL update_formId_reminders();
        
        -- add column `id_dataRows` in table `scheduledJobs_formActions`. Move all linking there
        CALL add_table_column('scheduledJobs_formActions', 'id_dataRows', 'int(10) unsigned zerofill DEFAULT NULL');
        CALL add_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_id_dataRows', 'id_dataRows', '`dataRows` (`id`)');          
        UPDATE scheduledJobs_formActions
        SET id_dataRows = id_uploadRows;          
        CALL drop_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_uploadRows');
        CALL drop_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_user_input_record');
        CALL drop_table_column('scheduledJobs_formActions', 'id_user_input_record');
        CALL drop_table_column('scheduledJobs_formActions', 'id_uploadRows');
        
        -- drop column `id_user_input_record` from `scheduledJobs_formActions`
        CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_forms_INTERNAL');
        CALL drop_table_column('scheduledJobs_reminders', 'id_forms_INTERNAL');
        
        -- rename column `id_forms_EXTERNAL` in table `scheduledJobs_reminders` to `id_dataTables`
        CALL rename_table_column('scheduledJobs_reminders', 'id_forms_EXTERNAL', 'id_dataTables');
        -- rename foreign key in `scheduledJobs_reminders` from `scheduledJobs_reminders_id_forms_EXTERNAL` to `scheduledJobs_reminders_id_dataTables`
        CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_forms_EXTERNAL');
        CALL drop_index('scheduledJobs_reminders', 'scheduledJobs_reminders_id_forms_EXTERNAL');
        CALL add_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_dataTables', 'id_dataTables', '`dataTables` (`id`)');        
		
        CALL rename_table('formActions_INTERNAL', 'deprecated_formActions_INTERNAL');
        CALL rename_table('formActions_EXTERNAL', 'deprecated_formActions_EXTERNAL');
        CALL rename_table('user_input_record', 'deprecated_user_input_record');
        CALL rename_table('user_input', 'deprecated_user_input');        
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

-- drop view view_data_tables - not needed anymore after the data refactoring
DROP VIEW IF EXISTS view_data_tables;
DROP VIEW IF EXISTS view_dataTables;
CREATE VIEW view_dataTables
AS
SELECT id, 
`name` AS name_id,
CASE 
	WHEN IFNULL(displayName, '') = '' THEN `name`
    ELSE displayName
END AS `name`,
`timestamp`,
id AS `value`, -- used for slect dropdowns
CASE 
	WHEN IFNULL(displayName, '') = '' THEN `name`
    ELSE displayName
END AS `text` -- used for slect dropdowns
FROM dataTables;

DROP VIEW IF EXISTS view_formActions;
CREATE VIEW view_formActions
AS
SELECT fa.id AS id, fa.`name` AS action_name, dt.`name` AS dataTable_name,
fa.id_formProjectActionTriggerTypes, trig.lookup_value AS trigger_type, trig.lookup_code AS trigger_type_code,
config,
dt.id AS id_dataTables
FROM formActions fa 
INNER JOIN lookups trig ON (trig.id = fa.id_formProjectActionTriggerTypes)
LEFT JOIN view_dataTables dt ON (dt.id = fa.id_dataTables);

DROP VIEW IF EXISTS view_scheduledJobs;
CREATE VIEW view_scheduledJobs
AS
SELECT sj.id AS id, l_status.lookup_code AS status_code, l_status.lookup_value AS `status`, 
l_types.lookup_code AS type_code, l_types.lookup_value AS `type`, sj.config,
sj.date_create, date_to_be_executed, date_executed, `description`, 
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.recipient_emails    
    WHEN l_types.lookup_code = 'notification' THEN ''
    WHEN l_types.lookup_code = 'task' THEN ''
    ELSE ""
END AS recipient,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.`subject`
    WHEN l_types.lookup_code = 'notification' THEN n.`subject`
    ELSE ""
END AS title,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.body
    WHEN l_types.lookup_code = 'notification' THEN n.body
    ELSE ""
END AS message,
sj_mq.id_mailQueue, id_jobTypes, id_jobStatus, a.id_formActions,
a.id_dataRows, dt.`name` AS dataTables_name
FROM scheduledJobs sj
INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus)
INNER JOIN lookups l_types ON (l_types.id = sj.id_jobTypes)
LEFT JOIN scheduledJobs_mailQueue sj_mq ON (sj_mq.id_scheduledJobs = sj.id)
LEFT JOIN mailQueue mq ON (mq.id = sj_mq.id_mailQueue)
LEFT JOIN scheduledJobs_notifications sj_n ON (sj_n.id_scheduledJobs = sj.id)
LEFT JOIN notifications n ON (n.id = sj_n.id_notifications)
LEFT JOIN scheduledJobs_formActions a ON (a.id_scheduledJobs = sj.id)
LEFT JOIN dataRows r ON (r.id = a.id_dataRows)
LEFT JOIN view_dataTables dt ON (r.id_dataTables = dt.id);

DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT sj.id AS id, from_email, from_name,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
reply_to, recipient_emails, cc_emails, bcc_emails, `subject`, body, is_html, mq.id AS id_mailQueue, id_jobTypes,
id_jobStatus, sj.config, id_dataRows, dataTables_name
FROM mailQueue mq
INNER JOIN scheduledJobs_mailQueue sj_mq ON (sj_mq.id_mailQueue = mq.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_mq.id_scheduledJobs);

DROP VIEW IF EXISTS view_notifications;
CREATE VIEW view_notifications
AS
SELECT sj.id AS id,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
recipient, `subject`, body, url, id_notifications, id_jobTypes,
id_jobStatus, sj.config, id_dataRows, dataTables_name
FROM notifications n
INNER JOIN scheduledJobs_notifications sj_n ON (sj_n.id_notifications = n.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_n.id_scheduledJobs);

DROP VIEW IF EXISTS view_tasks;
CREATE VIEW view_tasks
AS
SELECT sj.id AS id,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
recipient, t.config, id_tasks, id_jobTypes, id_jobStatus, `description`, id_dataRows, dataTables_name
FROM tasks t
INNER JOIN scheduledJobs_tasks sj_t ON (sj_t.id_tasks = t.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_t.id_scheduledJobs);

DROP VIEW IF EXISTS view_uploadTables;

DROP PROCEDURE IF EXISTS get_uploadTable_with_filter;

DELIMITER //

DROP PROCEDURE IF EXISTS get_dataTable_with_filter //

CREATE PROCEDURE get_dataTable_with_filter( 
	IN table_id_param INT, 
    IN user_id_param INT, 
    IN filter_param VARCHAR(1000),
    IN exclude_deleted_param BOOLEAN -- If true it will exclude the deleted records and it will not return them
)
-- if the filter_param contains any of these we additionaly filter: LAST_HOUR, LAST_DAY, LAST_WEEK, LAST_MONTH, LAST_YEAR
READS SQL DATA
DETERMINISTIC
BEGIN
    SET @@group_concat_max_len = 32000000;
    SET @sql = NULL;
    SELECT
    GROUP_CONCAT(DISTINCT
        CONCAT(
            'MAX(CASE WHEN col.`name` = "',
                col.name,
                '" THEN `value` END) AS `',
            replace(col.name, ' ', ''), '`'
        )
    ) INTO @sql
    FROM  dataTables t
	INNER JOIN dataCols col on (t.id = col.id_dataTables)
    WHERE t.id = table_id_param AND col.`name` NOT IN ('id_users','record_id','user_name','id_actionTriggerTypes','triggerType', 'entry_date');

    IF (@sql is null) THEN
        SELECT `name` from view_dataTables where 1=2;
    ELSE
        BEGIN
			SET @user_filter = '';
            IF user_id_param > 0 THEN
				SET @user_filter = CONCAT(' AND r.id_users = ', user_id_param);
            END IF;	
            
            SET @time_period_filter = '';
            CASE 
				WHEN filter_param LIKE '%LAST_HOUR%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 HOUR';
				WHEN filter_param LIKE '%LAST_DAY%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 DAY';
				WHEN filter_param LIKE '%LAST_WEEK%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 WEEK';
				WHEN filter_param LIKE '%LAST_MONTH%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 MONTH';
				WHEN filter_param LIKE '%LAST_YEAR%' THEN
					SET @time_period_filter = ' AND r.`timestamp` >= NOW() - INTERVAL 1 YEAR';
				ELSE
					SET @time_period_filter = '';					
			END CASE;
            
            SET @exclude_deleted_filter = '';
            CASE 
				WHEN exclude_deleted_param = TRUE THEN
					SET @exclude_deleted_filter = CONCAT(' AND IFNULL(r.id_actionTriggerTypes, 0) <> ', (SELECT id FROM lookups WHERE type_code = 'actionTriggerTypes' AND lookup_code = 'deleted' LIMIT 0,1));				
				ELSE
					SET @exclude_deleted_filter = '';					
			END CASE;
            
            SET @sql = CONCAT('SELECT * FROM (SELECT r.id AS record_id, 
					r.`timestamp` AS entry_date, r.id_users, u.`name` AS user_name, r.id_actionTriggerTypes, l.lookup_code AS triggerType,', @sql, 
					' FROM dataTables t
					INNER JOIN dataRows r ON (t.id = r.id_dataTables)
					INNER JOIN dataCells cell ON (cell.id_dataRows = r.id)
					INNER JOIN dataCols col ON (col.id = cell.id_dataCols)
                    LEFT JOIN users u ON (r.id_users = u.id)
                    LEFT JOIN lookups l ON (l.id = r.id_actionTriggerTypes)
					WHERE t.id = ', table_id_param, @user_filter, @time_period_filter, @exclude_deleted_filter, 
					' GROUP BY r.id ) AS r WHERE 1=1  ', filter_param);
            -- select @sql;
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END
//

DELIMITER ;

-- add field `formName` to style `showUserInput`; Set the data from `source` field to the formName and remove the `source` field
-- insert field `formName` in style `showUserInput`
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('showUserInput'), get_field_id('formName'), '', 'Select a data table that will be loaded and show the user data entries.');
-- link the source field to the dataTables
UPDATE sections_fields_translation sft
INNER JOIN sections s ON (s.id = sft.id_sections)
INNER JOIN styles st ON (st.id = s.id_styles)
INNER JOIN view_dataTables dt ON (dt.`name` = sft.content)
SET sft.content = dt.id
WHERE id_fields = get_field_id('source') AND st.`name` = 'showUserInput';
-- set the field based on the `source` fields
UPDATE sections_fields_translation sft
INNER JOIN sections s ON (s.id = sft.id_sections)
INNER JOIN styles st ON (st.id = s.id_styles)
SET sft.id_fields = get_field_id('formName')
WHERE sft.id_fields = get_field_id('source') AND st.id = get_style_id('showUserInput');
-- delete the `source` field from `showUserInput` in the styles relations
DELETE FROM styles_fields
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('source');
-- delete already entered source info; now is moved to formName
DELETE sft
FROM sections_fields_translation sft
INNER JOIN sections s ON (s.id = sft.id_sections)
INNER JOIN styles st ON (st.id = s.id_styles)
WHERE st.id = get_style_id('showUserInput') AND sft.id_fields = get_field_id('source');

-- rename style field `formName` to `data_table`
UPDATE `fields`
SET `name` = 'data_table'
WHERE `name` = 'formName';

-- rename fieldType `select-formName` to `select-data_table`
UPDATE fieldType
SET `name` = 'select-data_table'
WHERE `name` = 'select-formName';

UPDATE styles_fields
SET `help` = 'Select a data tabe which will be linked to the style'
WHERE id_fields = get_field_id('data_table') AND id_styles IN (get_style_id('entryRecord'), get_style_id('entryList'));

-- add field `fields_map`
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'fields_map', get_field_type_id('json'), '1');
-- insert field `fields_map` in style `showUserInput`
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('showUserInput'), get_field_id('fields_map'), '', 'Map the fields that should be displayed in the table. Only the specified fields will be loaded. Use the field name as the key and its label as the value. Example:

 ```
 {
	"record_id": "Record ID",
	"entry_date": "Date",
	"user_name": "User name"
}
```');

-- remove fields `submit_and_send_label`, `email_body` and `email_subject` from form styles. This functionality can be achieved with actions
DELETE FROM styles_fields
WHERE id_fields IN (get_field_id('submit_and_send_label'), get_field_id('email_body'), get_field_id('email_subject'), get_field_id('submit_and_send_email'), get_field_id('email_address'));

-- remove style `emailForm`
DELETE FROM styles
WHERE `name` = 'emailForm';

UPDATE `fields`
SET `name` = 'confirmation_continue'
WHERE `name` = 'label_continue';

UPDATE `fields`
SET `name` = 'confirmation_message'
WHERE `name` = 'label_message';

-- add new style `entryRecordDelete`
INSERT IGNORE INTO `styles` (`name`, `id_type`, `id_group`, `description`) VALUES ('entryRecordDelete', '2', (select id from styleGroup where `name` = 'Wrapper' limit 1), 'Style that allows the user to delete entry record');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('css_mobile'), NULL, 'Allows to assign CSS classes to the root item of the style for the mobile version.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('condition'), NULL, 'The field `condition` allows to specify a condition. Note that the field `condition` is of type `json` and requires\n1. valid json syntax (see https://www.json.org/)\n2. a valid condition structure (see https://github.com/jwadhams/json-logic-php/)\n\nOnly if a condition resolves to true the sections added to the field `children` will be rendered.\n\nIn order to refer to a form-field use the syntax `"@__form_name__#__from_field_name__"` (the quotes are necessary to make it valid json syntax) where `__form_name__` is the value of the field `name` of the style `formUserInput` and `__form_field_name__` is the value of the field `name` of any form-field style.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('data_config'), '', 'Define data configuration for fields that are loaded from DB and can be used inside the style with their param names. The name of the field can be used between {{param_name}} to load the required value');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('debug'), 0, 'If *checked*, debug messages will be rendered to the screen. These might help to understand the result of a condition evaluation. **Make sure that this field is *unchecked* once the page is productive**.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('label_delete'), 'Delete', 'The label for the delte button.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('entryRecordDelete'), get_field_id('confirmation_title'), '', 'Confirmation title for the modal when the button is clicked');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('entryRecordDelete'), get_field_id('confirmation_continue'), 'OK', 'Continue button for the modal when the button is clicked');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('entryRecordDelete'), get_field_id('confirmation_message'), 'Do you want to continue?', 'The message shown on the modal');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('type'), 'danger', 'The visual appearance of the button as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.6/utilities/colors/).');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('redirect_at_end'), '', 'Redirect to this url once the `delete` is executed.');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('close_modal_at_end'), 0, '`Only for mobile` - if selected the modal form will be closed once the survey is done');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'confirmation_cancel', get_field_type_id('markdown-inline'), '1');
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)  VALUES (get_style_id('entryRecordDelete'), get_field_id('confirmation_cancel'), '', 'Cancel button label on the confirmation modal');


-- delete field `label_date_time`
DELETE FROM `fields`
WHERE `name` = 'label_date_time';

DROP VIEW IF EXISTS view_dataTables_data;
CREATE VIEW view_dataTables_data
AS
SELECT t.id as table_id, r.id AS row_id, r.`timestamp` AS entry_date, col.id AS col_id, 
t.`name` AS `table_name`, col.`name` AS col_name, cell.`value` AS `value`, t.`timestamp`, r.id_users,
t.displayName AS displayName
FROM dataTables t
INNER JOIN dataRows r ON (t.id = r.id_dataTables)
INNER JOIN dataCells cell ON (cell.id_dataRows = r.id)
INNER JOIN dataCols col ON (col.id = cell.id_dataCols);

DROP VIEW IF EXISTS view_form;
DROP VIEW IF EXISTS view_user_input;
DROP PROCEDURE IF EXISTS get_form_data_for_user_with_filter;
DROP PROCEDURE IF EXISTS get_form_data_with_filter;

