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
