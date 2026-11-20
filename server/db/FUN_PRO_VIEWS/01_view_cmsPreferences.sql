DROP VIEW IF EXISTS view_cmsPreferences;
CREATE VIEW view_cmsPreferences
AS
SELECT p.callback_api_key, p.default_language_id, l.`language` AS default_language, l.locale, p.firebase_config, p.anonymous_users,
    p.id_pages_landing_user, plu.keyword AS landing_user_page_keyword,
    p.id_pages_landing_guest, plg.keyword AS landing_guest_page_keyword,
    p.id_pages_after_login, pal.keyword AS after_login_page_keyword,
    p.login_return_last_page
FROM cmsPreferences p
LEFT JOIN languages l ON (l.id = p.default_language_id)
LEFT JOIN pages plu ON (plu.id = p.id_pages_landing_user)
LEFT JOIN pages plg ON (plg.id = p.id_pages_landing_guest)
LEFT JOIN pages pal ON (pal.id = p.id_pages_after_login)
WHERE p.id = 1;
