-- set DB version
UPDATE version
SET version = 'v6.15.0';

--  for external data type
CALL add_table_column('scheduledJobs_formActions', 'id_user_input_record', 'INT(10) UNSIGNED ZEROFILL');
CALL add_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_user_input_record', 'id_user_input_record', 'user_input_record (id)');

--  for internal data type
CALL add_table_column('scheduledJobs_formActions', 'id_uploadRows', 'INT(10) UNSIGNED ZEROFILL');
CALL add_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_uploadRows', 'id_uploadRows', 'uploadRows (id)');

--  for user_input move the form_id in the record table form the value table
CALL add_table_column('user_input_record', 'id_sections', 'INT(10) UNSIGNED ZEROFILL');
CALL add_foreign_key('user_input_record', 'user_input_record_fk_id_sections', 'id_sections', 'sections (id)');

UPDATE user_input_record
SET id_sections = (SELECT id_section_form FROM user_input WHERE user_input_record.id = user_input.id_user_input_record LIMIT 1);