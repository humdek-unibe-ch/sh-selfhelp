-- set DB version
UPDATE version
SET version = 'v6.9.0';

UPDATE `fields`
SET id_type = get_field_type_id('text')
WHERE `name` = 'count';