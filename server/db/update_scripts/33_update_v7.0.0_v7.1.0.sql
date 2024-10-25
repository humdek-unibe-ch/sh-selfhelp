-- set DB version
UPDATE version
SET version = 'v7.1.0';

UPDATE sections_fields_translation
SET content = REPLACE(content, 'border-left', 'border-start')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'border-right', 'border-end')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'float-left', 'float-start')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'float-right', 'float-end')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'ml-', 'ms-')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'mr-', 'me-')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'pl-', 'ps-')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'pr-', 'pe-')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'font-weight-bold', 'fw-bold')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'font-italic', 'fst-italic')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'text-muted', 'text-body-secondary')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'text-left', 'text-start')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'text-right', 'text-end')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'form-group', 'mb-3')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'form-inline', 'd-flex align-items-center')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'jumbotron', 'card card-header mb-4 rounded-2 py-5 px-3')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));

UPDATE sections_fields_translation
SET content = REPLACE(content, 'media', 'd-flex')
WHERE id_fields IN (get_field_id('css'), get_field_id('css_mobile'));


