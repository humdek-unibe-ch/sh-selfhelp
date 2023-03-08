DROP VIEW IF EXISTS view_formActions;
CREATE VIEW view_formActions
AS
SELECT fa.id AS id, fa.`name` AS action_name, orig_name AS form_name,
fa.id_formProjectActionTriggerTypes, trig.lookup_value AS trigger_type, trig.lookup_code AS trigger_type_code,
config,
CASE
	WHEN ex.id_forms > 0 THEN CONCAT(FLOOR(ex.id_forms), '-EXTERNAL')
	WHEN inter.id_forms > 0 THEN CONCAT(FLOOR(inter.id_forms), '-INTERNAL')
END AS id_forms
FROM formActions fa 
INNER JOIN lookups trig ON (trig.id = fa.id_formProjectActionTriggerTypes)
LEFT JOIN formActions_EXTERNAL ex ON (fa.id = ex.id_formActions)
LEFT JOIN formActions_INTERNAL inter ON (fa.id = inter.id_formActions)
LEFT JOIN view_data_tables dt ON (dt.form_id_plus_type = CASE
	WHEN ex.id_forms > 0 THEN CONCAT(FLOOR(ex.id_forms), '-EXTERNAL')
	WHEN inter.id_forms > 0 THEN CONCAT(FLOOR(inter.id_forms), '-INTERNAL')
END);
