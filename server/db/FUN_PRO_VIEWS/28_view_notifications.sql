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
