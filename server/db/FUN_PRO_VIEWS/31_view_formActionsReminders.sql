DROP VIEW IF EXISTS view_formActionsReminders;
CREATE VIEW view_formActionsReminders
AS
SELECT u.id as user_id, u.email, u.name AS user_name, code, sj.id AS id_scheduledJobs,
sj.status_code as status_code, sj.status AS status, r.id_forms AS id_forms,
fa.id_formActions,
	(SELECT sess.date_to_be_executed 
	FROM scheduledJobs sess 
    INNER JOIN scheduledJobs_formActions sj_fa2 on (sj_fa2.id_scheduledJobs = sess.id)
	INNER JOIN formActions fa2 ON (fa2.id = sj_fa2.id_formActions)
    WHERE fa2.id = fa.id_formActions 
    ORDER BY sess.date_to_be_executed DESC
    LIMIT 0, 1) AS session_start_date,
(SELECT CAST(JSON_EXTRACT(fa2.schedule_info, '$.valid') AS UNSIGNED)
FROM formActions fa2
WHERE fa2.id = fa.id_formActions) AS valid,
(SELECT sess.date_to_be_executed 
	FROM scheduledJobs sess 
    INNER JOIN scheduledJobs_formActions sj_fa2 on (sj_fa2.id_scheduledJobs = sess.id)
	INNER JOIN formActions fa2 ON (fa2.id = sj_fa2.id_formActions)
    WHERE fa2.id = fa.id_formActions 
    ORDER BY sess.date_to_be_executed DESC
    LIMIT 0, 1) + INTERVAL (SELECT CAST(JSON_EXTRACT(fa2.schedule_info, '$.valid') AS UNSIGNED)
FROM formActions fa2
WHERE fa2.id = fa.id_formActions) MINUTE AS valid_till
FROM formActionsReminders r
INNER JOIN view_users u ON (u.id = r.id_users)
LEFT JOIN view_scheduledJobs sj ON (sj.id = r.id_scheduledJobs) 
LEFT JOIN scheduledJobs_formActions sj_fa on (sj_fa.id_scheduledJobs = sj.id)
LEFT JOIN formActions fa ON (fa.id = sj_fa.id_formActions);
