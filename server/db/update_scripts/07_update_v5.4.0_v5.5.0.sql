-- set DB version
UPDATE version
SET version = 'v5.5.0';

INSERT IGNORE INTO sections_fields_translation
SELECT id_Sections, id_fields, 1, 1, content
FROM sections_fields_translation
WHERE id_fields = get_field_id('source') AND id_languages = 2 AND id_genders = 1;

