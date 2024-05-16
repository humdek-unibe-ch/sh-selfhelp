DROP VIEW IF EXISTS view_form;
CREATE VIEW view_form
AS
SELECT DISTINCT cast(form.id AS UNSIGNED) form_id, sft_if.content AS form_name, IFNULL(sft_intern.content, 0) AS internal
FROM user_input_record record 
INNER JOIN sections form ON (record.id_sections = form.id)
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_intern ON sft_intern.id_sections = record.id_sections AND sft_intern.id_fields = (SELECT id
FROM `fields`
WHERE `name` = 'internal');
