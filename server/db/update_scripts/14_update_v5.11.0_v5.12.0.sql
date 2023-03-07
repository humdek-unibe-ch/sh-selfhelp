-- set DB version
UPDATE version
SET version = 'v5.12.0';

UPDATE sections_fields_translation
SET content = REPLACE(content, '"type": "static"', '"type": "internal"')
WHERE id_fields = get_field_id('data_config');

UPDATE sections_fields_translation
SET content = REPLACE(content, '"type": "dynamic"', '"type": "external"')
WHERE id_fields = get_field_id('data_config');
