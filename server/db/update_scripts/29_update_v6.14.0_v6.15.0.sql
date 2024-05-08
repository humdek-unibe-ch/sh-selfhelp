-- set DB version
UPDATE version
SET version = 'v6.15.0';

-- remove old entries for TPF and sysadmin users without records
DELETE FROM user_input
WHERE id_users in (3,4) AND id_user_input_record IS NULL;

--  for external data type
CALL add_table_column('scheduledJobs_formActions', 'id_user_input_record', 'INT(10) UNSIGNED ZEROFILL');
CALL add_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_user_input_record', 'id_user_input_record', 'user_input_record (id)');

--  for internal data type
CALL add_table_column('scheduledJobs_formActions', 'id_uploadRows', 'INT(10) UNSIGNED ZEROFILL');
CALL add_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_uploadRows', 'id_uploadRows', 'uploadRows (id)');

--  for user_input move the form_id in the record table form the value table
CALL add_table_column('user_input_record', 'id_sections', 'INT(10) UNSIGNED ZEROFILL');
CALL add_foreign_key('user_input_record', 'user_input_record_fk_id_sections', 'id_sections', 'sections (id)');

-- move `id_section_form` info from table `user_input` to table `user_input_record`. Normalize data
DELIMITER //
CREATE PROCEDURE update_user_input_columns()
BEGIN
	SET @column_exists = (
		SELECT COUNT(*)
		FROM information_schema.`columns`
		WHERE table_schema = DATABASE()
		AND `table_name` = 'user_input'
		AND `column_name` = 'id_section_form'
	);
	IF @column_exists > 0 THEN
		UPDATE IGNORE user_input_record
		SET id_sections = (SELECT id_section_form FROM user_input WHERE user_input_record.id = user_input.id_user_input_record LIMIT 1);
	END IF;
END;
//
DELIMITER ;
CALL update_user_input_columns();
DROP PROCEDURE IF EXISTS update_user_input_columns;
CALL drop_foreign_key('user_input', 'user_input_fk_id_section_form');
CALL drop_table_column('user_input', 'id_section_form');

DELIMITER //
DROP FUNCTION IF EXISTS get_form_fields_helper //

CREATE FUNCTION get_form_fields_helper(form_id_param INT) RETURNS TEXT
READS SQL DATA
DETERMINISTIC
BEGIN 
	SET @@group_concat_max_len = 32000000;
	SET @sql = NULL;
	SELECT
	  GROUP_CONCAT(DISTINCT
		CONCAT(
		  'max(case when sft_in.content = "',
		  sft_in.content,
		  '" then value end) as `',
		  replace(sft_in.content, ' ', ''), '`'
		)
	  ) INTO @sql
	from user_input ui
	left join users u on (ui.id_users = u.id)
	left join validation_codes vc on (ui.id_users = vc.id_users)
	left join sections field on (ui.id_sections = field.id)	
	left join user_input_record record  on (ui.id_user_input_record = record.id)
    LEFT JOIN sections form ON (record.id_sections = form.id)
	LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
	LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57
    WHERE form.id = form_id_param;
	
    RETURN @sql;
END
//

DELIMITER ;

DROP VIEW IF EXISTS view_user_input;

CREATE VIEW view_user_input AS
SELECT 
    CAST(ui.id AS UNSIGNED) AS id,
    CAST(u.id AS UNSIGNED) AS user_id,
    u.`name` AS user_name,
    vc.`code` AS user_code,
    CAST(form.id AS UNSIGNED) AS form_id,
    sft_if.content AS form_name,
    CAST(field.id AS UNSIGNED) AS field_id,
    sft_in.content AS field_name,
    ui.`value`,
    record.id AS record_id,
    ui.edit_time,
    ui.removed
FROM user_input ui
LEFT JOIN users u ON (ui.id_users = u.id)
LEFT JOIN validation_codes vc ON (ui.id_users = vc.id_users)
LEFT JOIN sections field ON (ui.id_sections = field.id)
LEFT JOIN user_input_record record ON (ui.id_user_input_record = record.id)
LEFT JOIN sections form ON (record.id_sections = form.id)
LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57;

DROP VIEW IF EXISTS view_form;
CREATE VIEW view_form
AS
SELECT DISTINCT cast(form.id AS UNSIGNED) form_id, sft_if.content AS form_name, IFNULL(sft_intern.content, 0) AS internal
FROM user_input ui
LEFT JOIN user_input_record record ON (ui.id_user_input_record = record.id)
LEFT JOIN sections form ON (record.id_sections = form.id)
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_intern ON sft_intern.id_sections = record.id_sections AND sft_intern.id_fields = (SELECT id
FROM `fields`
WHERE `name` = 'internal');

DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_for_user_with_filter //

CREATE PROCEDURE get_form_data_for_user_with_filter( form_id_param INT, user_id_param INT, filter_param VARCHAR(1000) )
READS SQL DATA
DETERMINISTIC
BEGIN  
    SET @@group_concat_max_len = 32000000;
	SET @sql = NULL;
	SELECT get_form_fields_helper(form_id_param) INTO @sql;	
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select * from (select  record.id as record_id, max(edit_time) as edit_time, u.id as user_id, u.name as user_name, vc.code as user_code, ', @sql, ' , removed as deleted from user_input ui
		left join users u on (ui.id_users = u.id)
		left join validation_codes vc on (ui.id_users = vc.id_users)
		left join sections field on (ui.id_sections = field.id)		
		left join user_input_record record  on (ui.id_user_input_record = record.id)
        LEFT JOIN sections form ON (record.id_sections = form.id)
		LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
		where form.id = ', form_id_param, ' and u.id = ', user_id_param,
		' group by u.id, u.name, record.id, vc.code, removed) as r where 1=1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;

DROP VIEW IF EXISTS view_scheduledJobs;
CREATE VIEW view_scheduledJobs
AS
SELECT sj.id AS id, l_status.lookup_code AS status_code, l_status.lookup_value AS status, l_types.lookup_code AS type_code, l_types.lookup_value AS type, sj.config,
sj.date_create, date_to_be_executed, date_executed, description, 
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.recipient_emails
    -- WHEN l_types.lookup_code = 'notification' THEN (SELECT GROUP_CONCAT(DISTINCT u.name SEPARATOR '; ') FROM scheduledJobs_users sj_u INNER JOIN users u on (u.id = sj_u.id_users) WHERE id_scheduledJobs = sj.id)
    -- WHEN l_types.lookup_code = 'task' THEN (SELECT GROUP_CONCAT(DISTINCT u.name SEPARATOR '; ') FROM scheduledJobs_users sj_u INNER JOIN users u on (u.id = sj_u.id_users) WHERE id_scheduledJobs = sj.id)
    WHEN l_types.lookup_code = 'notification' THEN ''
    WHEN l_types.lookup_code = 'task' THEN ''
    ELSE ""
END AS recipient,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.subject
    WHEN l_types.lookup_code = 'notification' THEN n.subject
    ELSE ""
END AS title,
CASE
	WHEN l_types.lookup_code = 'email' THEN mq.body
    WHEN l_types.lookup_code = 'notification' THEN n.body
    ELSE ""
END AS message,
sj_mq.id_mailQueue,
id_jobTypes,
id_jobStatus,
a.id_formActions,
id_user_input_record,
sft_if.content AS internal_table,
id_uploadRows,
ut.`name` AS external_table
FROM scheduledJobs sj
INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus)
INNER JOIN lookups l_types ON (l_types.id = sj.id_jobTypes)
LEFT JOIN scheduledJobs_mailQueue sj_mq ON (sj_mq.id_scheduledJobs = sj.id)
LEFT JOIN mailQueue mq ON (mq.id = sj_mq.id_mailQueue)
LEFT JOIN scheduledJobs_notifications sj_n ON (sj_n.id_scheduledJobs = sj.id)
LEFT JOIN notifications n ON (n.id = sj_n.id_notifications)
LEFT JOIN scheduledJobs_formActions a ON (a.id_scheduledJobs = sj.id)

LEFT JOIN user_input_record uir ON (id_user_input_record = uir.id)
LEFT JOIN sections_fields_translation AS sft_if ON (sft_if.id_sections = uir.id_sections AND sft_if.id_fields = 57)

LEFT JOIN uploadRows ur ON (id_uploadRows = ur.id)
LEFT JOIN uploadTables ut ON (ur.id_uploadTables = ut.id);

DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT sj.id AS id, from_email, from_name,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
reply_to, recipient_emails, cc_emails, bcc_emails, `subject`, body, is_html, mq.id AS id_mailQueue, id_jobTypes,
id_jobStatus, sj.config, id_user_input_record, id_uploadRows, internal_table, external_table
FROM mailQueue mq
INNER JOIN scheduledJobs_mailQueue sj_mq ON (sj_mq.id_mailQueue = mq.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_mq.id_scheduledJobs);

DROP VIEW IF EXISTS view_tasks;
CREATE VIEW view_tasks
AS
SELECT sj.id AS id,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
recipient, t.config, id_tasks, id_jobTypes, id_jobStatus, `description`, id_user_input_record, id_uploadRows, internal_table, external_table
FROM tasks t
INNER JOIN scheduledJobs_tasks sj_t ON (sj_t.id_tasks = t.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_t.id_scheduledJobs);

DROP VIEW IF EXISTS view_notifications;
CREATE VIEW view_notifications
AS
SELECT sj.id AS id,
status_code, `status`, type_code, `type`, 
sj.date_create, date_to_be_executed, date_executed,
recipient, `subject`, body, url, id_notifications, id_jobTypes,
id_jobStatus, sj.config, id_user_input_record, id_uploadRows, internal_table, external_table
FROM notifications n
INNER JOIN scheduledJobs_notifications sj_n ON (sj_n.id_notifications = n.id)
INNER JOIN view_scheduledJobs sj ON (sj.id = sj_n.id_scheduledJobs);

DELIMITER //

DROP PROCEDURE IF EXISTS get_form_data_with_filter //

CREATE PROCEDURE get_form_data_with_filter( form_id_param INT, filter_param VARCHAR(1000) )
READS SQL DATA
DETERMINISTIC
BEGIN  
    SET @@group_concat_max_len = 32000000;
	SELECT get_form_fields_helper(form_id_param) INTO @sql;	
	
    IF (@sql is null) THEN
		select user_id, form_name from view_user_input where 1=2;
    ELSE 
		begin
		SET @sql = CONCAT('select * from (select record.id as record_id, max(edit_time) as edit_time, u.id as user_id, u.name as user_name, vc.code as user_code, ', @sql, ' , removed as deleted from user_input ui
		left join users u on (ui.id_users = u.id)
		left join validation_codes vc on (ui.id_users = vc.id_users)
		left join sections field on (ui.id_sections = field.id)		
		left join user_input_record record  on (ui.id_user_input_record = record.id)
        LEFT JOIN sections form ON (record.id_sections = form.id)
		LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57		
		where form.id = ', form_id_param, ' group by u.id, u.name, record.id, vc.code, removed) as r where 1=1 ', filter_param);

		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
        end;
    END IF;
END 
//

DELIMITER ;
