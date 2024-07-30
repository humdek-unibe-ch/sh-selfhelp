-- set DB version
UPDATE version
SET version = 'v6.13.0';

UPDATE pages_fields_translation pft
INNER JOIN pages p ON (pft.id_pages  = p.id)
SET content = 'CMS Preferences'
WHERE p.keyword = 'cmsPreferences' AND id_languages = 2;

UPDATE pages_fields_translation pft
INNER JOIN pages p ON (pft.id_pages  = p.id)
SET content = 'CMS Preferences Update'
WHERE p.keyword = 'cmsPreferencesUpdate' AND id_languages = 2;

UPDATE `fields`
SET id_type = get_field_type_id('markdown-inline')
WHERE `name` = 'label';
