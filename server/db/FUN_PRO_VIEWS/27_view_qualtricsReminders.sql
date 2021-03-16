DROP VIEW IF EXISTS view_qualtricsReminders;
CREATE VIEW view_qualtricsReminders
AS
SELECT u.id as user_id, u.email, u.name AS user_name, code, sj.id AS id_scheduledJobs,
sj.status_code as status_code, sj.status AS status, r.id_qualtricsSurveys AS id_qualtricsSurveys, s.qualtrics_survey_id,
qa.id_qualtricsActions,
	(SELECT sess.date_to_be_executed 
	FROM scheduledJobs sess 
    INNER JOIN scheduledJobs_qualtricsActions sj_qa2 on (sj_qa2.id_scheduledJobs = sess.id)
	INNER JOIN qualtricsActions qa2 ON (qa2.id = sj_qa2.id_qualtricsActions)
    WHERE qa2.id = qa.id_qualtricsActions 
    ORDER BY sess.date_to_be_executed DESC
    LIMIT 0, 1) AS session_start_date,
(SELECT CAST(JSON_EXTRACT(qa2.schedule_info, '$.valid') AS UNSIGNED)
FROM qualtricsActions qa2
WHERE qa2.id = qa.id_qualtricsActions) AS valid,
(SELECT sess.date_to_be_executed 
	FROM scheduledJobs sess 
    INNER JOIN scheduledJobs_qualtricsActions sj_qa2 on (sj_qa2.id_scheduledJobs = sess.id)
	INNER JOIN qualtricsActions qa2 ON (qa2.id = sj_qa2.id_qualtricsActions)
    WHERE qa2.id = qa.id_qualtricsActions 
    ORDER BY sess.date_to_be_executed DESC
    LIMIT 0, 1) + INTERVAL (SELECT CAST(JSON_EXTRACT(qa2.schedule_info, '$.valid') AS UNSIGNED)
FROM qualtricsActions qa2
WHERE qa2.id = qa.id_qualtricsActions) MINUTE AS valid_till
FROM qualtricsReminders r
INNER JOIN view_users u ON (u.id = r.id_users)
INNER JOIN qualtricsSurveys s ON (s.id = r.id_qualtricsSurveys)
LEFT JOIN view_scheduledJobs sj ON (sj.id = r.id_scheduledJobs) 
LEFT JOIN scheduledJobs_qualtricsActions sj_qa on (sj_qa.id_scheduledJobs = sj.id)
LEFT JOIN qualtricsActions qa ON (qa.id = sj_qa.id_qualtricsActions);
