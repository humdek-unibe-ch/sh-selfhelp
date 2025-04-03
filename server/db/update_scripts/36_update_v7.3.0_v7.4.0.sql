-- set DB version
UPDATE version
SET version = 'v7.4.0';

-- add field `own_entries_only` to style `entryRecordDelete`
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('entryRecordDelete'), get_field_id('own_entries_only'), '1', 'If enabled the `entryRecordDelete` will be able to delete only entries that belong to the user.');