DROP VIEW IF EXISTS view_cmsPreferences;
CREATE VIEW view_cmsPreferences
AS
SELECT p.callback_api_key, p.default_language_id, l.`language` AS default_language, l.locale, p.firebase_config, p.anonymous_users
FROM cmsPreferences p
LEFT JOIN languages l ON (l.id = p.default_language_id)
WHERE p.id = 1;
