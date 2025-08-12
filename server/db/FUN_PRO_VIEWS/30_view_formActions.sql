DROP VIEW IF EXISTS view_formActions;
CREATE VIEW view_formActions
AS
SELECT fa.id AS id, fa.`name` AS action_name, dt.`name` AS dataTable_name,
fa.id_actionTriggerTypes, trig.lookup_value AS trigger_type, trig.lookup_code AS trigger_type_code,
config,
dt.id AS id_dataTables
FROM actions fa 
INNER JOIN lookups trig ON (trig.id = fa.id_actionTriggerTypes)
LEFT JOIN view_dataTables dt ON (dt.id = fa.id_dataTables);
