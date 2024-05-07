-- set DB version
UPDATE version
SET version = 'v6.15.0';

--  for internal data type
CALL add_table_column('scheduledJobs_formActions', 'id_uploadRows', 'INT(10) UNSIGNED ZEROFILL');
CALL add_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_uploadRows', 'id_uploadRows', 'uploadRows (id)');

--  for external data type
CALL add_table_column('scheduledJobs_formActions', 'id_user_input_record', 'INT(10) UNSIGNED ZEROFILL');
CALL add_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_user_input_record', 'id_user_input_record', 'user_input_record (id)');
