DROP VIEW IF EXISTS view_user_input;

CREATE VIEW view_user_input AS
SELECT 
    CAST(ui.id AS UNSIGNED) AS id,
    CAST(u.id AS UNSIGNED) AS user_id,
    u.`name` AS user_name,
    vc.`code` AS user_code,
    CAST(form.id AS UNSIGNED) AS form_id,
    sft_if.content AS form_name,
    CAST(field.id AS UNSIGNED) AS field_id,
    sft_in.content AS field_name,
    ui.`value`,
    record.id AS record_id,
    ui.edit_time,
    ui.removed
FROM user_input ui
LEFT JOIN users u ON (ui.id_users = u.id)
LEFT JOIN validation_codes vc ON (ui.id_users = vc.id_users)
LEFT JOIN sections field ON (ui.id_sections = field.id)
LEFT JOIN user_input_record record ON (ui.id_user_input_record = record.id)
LEFT JOIN sections form ON (record.id_sections = form.id)
LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57;
