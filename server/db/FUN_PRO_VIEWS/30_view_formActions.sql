DROP VIEW IF EXISTS view_formActions;
CREATE VIEW view_formActions
AS
SELECT fa.id as id, fa.name as action_name, fa.id_forms as id_forms, f.form_name,
fa.id_formProjectActionTriggerTypes, trig.lookup_value as trigger_type, trig.lookup_code as trigger_type_code,
GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS groups, 
GROUP_CONCAT(DISTINCT g.id*1 SEPARATOR ', ') AS id_groups, 
schedule_info, fa.id_formActionScheduleTypes, action_type.lookup_code as action_schedule_type_code, action_type.lookup_value as action_schedule_type, id_forms_reminder, 
CASE 
	WHEN action_type.lookup_value = 'Reminder' THEN f_reminder.form_name 
    ELSE NULL
END as form_reminder_name, fa.id_formActions
FROM formActions fa 
INNER JOIN view_form f ON (fa.id_forms = f.form_id)
INNER JOIN lookups trig ON (trig.id = fa.id_formProjectActionTriggerTypes)
INNER JOIN lookups action_type ON (action_type.id = fa.id_formActionScheduleTypes)
LEFT JOIN view_form f_reminder ON (fa.id_forms_reminder = f_reminder.form_id)
LEFT JOIN formActions_groups fg on (fg.id_formActions = fa.id)
LEFT JOIN groups g on (fg.id_groups = g.id)
GROUP BY fa.id, fa.name, fa.id_forms, fa.id_formProjectActionTriggerTypes, trig.lookup_value, f.form_name, form_reminder_name;
