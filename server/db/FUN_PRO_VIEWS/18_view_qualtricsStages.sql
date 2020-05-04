DROP VIEW IF EXISTS view_qualtricsStages;
CREATE VIEW view_qualtricsStages
AS
SELECT st.id as id, st.name as stage_name, st.id_qualtricsProjects as project_id, p.name as project_name,
st.id_qualtricsSurveys as survey_id, s.qualtrics_survey_id, s.name as survey_name, id_qualtricsProjectStageTypes, typ.lookup_value as stage_type, 
id_qualtricsProjectStageTriggerTypes, trig.lookup_value as trigger_type,
GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS groups, 
GROUP_CONCAT(DISTINCT g.id SEPARATOR '; ') AS id_groups, 
GROUP_CONCAT(DISTINCT l.lookup_value SEPARATOR '; ') AS functions,
GROUP_CONCAT(DISTINCT l.id SEPARATOR '; ') AS id_functions,
notification, reminder 
FROM qualtricsStages st 
INNER JOIN qualtricsProjects p ON (st.id_qualtricsProjects = p.id)
INNER JOIN qualtricsSurveys s ON (st.id_qualtricsSurveys = s.id)
INNER JOIN lookups typ ON (typ.id = st.id_qualtricsProjectStageTypes)
INNER JOIN lookups trig ON (trig.id = st.id_qualtricsProjectStageTriggerTypes)
LEFT JOIN qualtricsStages_groups sg on (sg.id_qualtricsStages = st.id)
LEFT JOIN groups g on (sg.id_groups = g.id)
LEFT JOIN qualtricsStages_functions f on (f.id_qualtricsStages = st.id)
LEFT JOIN lookups l on (f.id_lookups = l.id)
GROUP BY st.id, st.name, st.id_qualtricsProjects, p.name,
st.id_qualtricsSurveys, s.name, id_qualtricsProjectStageTypes, typ.lookup_value, 
id_qualtricsProjectStageTriggerTypes, trig.lookup_value;