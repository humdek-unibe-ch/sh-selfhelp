-- set DB version
UPDATE version
SET version = 'v6.6.0';

-- add field `meta` to table `sections_fields_translation`. The field will be used for soem extra data needed to the entry record
CALL add_table_column('sections_fields_translation', 'meta', 'VARCHAR(10000)');

-- move all the data from `jquery_builder_json` to the `meta` for field `condition`
UPDATE sections_fields_translation sft
JOIN (
    SELECT sft2.id_sections, sft2.content
    FROM sections_fields_translation sft2
    WHERE sft2.id_fields = (SELECT id FROM `fields` WHERE `name` = 'jquery_builder_json' LIMIT 1)
) AS sft_temp ON sft.id_sections = sft_temp.id_sections
SET sft.meta = sft_temp.content
WHERE sft.id_fields = (SELECT id FROM `fields` WHERE `name` = 'condition' LIMIT 1);