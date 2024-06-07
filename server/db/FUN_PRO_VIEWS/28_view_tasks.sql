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
