DROP VIEW IF EXISTS view_qualtricsReminders;
CREATE VIEW view_qualtricsReminders
AS
select u.id as user_id, u.email, u.name as user_name, code, m.id as mailQueue_id,
m.status_code as mailQueue_status_code, m.status as mailQueue_status, s.id as qualtricsSurvey_id, qualtrics_survey_id
from qualtricsReminders r
inner join view_users u on (u.id = r.id_users)
inner join view_mailQueue m on (m.id = r.id_mailQueue)
inner join view_qualtricsSurveys s on (s.id = r.id_qualtricsSurveys);
