START TRANSACTION;

-- Bump version only with a successful migration (same transaction as the updates).
UPDATE version
SET version = 'v7.14.0';

-- ---------------------------------------------------------------------------
-- Landing pages (CMS Preferences)
--
-- The site root `/` (and the logo in the header, which links to it) opens:
-- `id_pages_landing_user`  for a logged-in user with access to that page
-- `id_pages_landing_guest` for a guest, and for a logged-in user without
--                          access to the page above (never `login` for a
--                          logged-in user: the login page logs out)
-- `id_pages_after_login`   page a user lands on after login (and 2FA);
--                          NULL = the site root, i.e. the landing page above
-- `login_return_last_page` 1 = after login go back to the last visited page
--                          first (behaviour before v7.14.0), 0 = always open
--                          the after-login page. A deep link that sent the
--                          user to the login page is followed in both cases.
-- A NULL landing page = the `home` page, so existing installations keep their
-- behaviour. Deleting a page resets the preference to NULL.
-- ---------------------------------------------------------------------------

CALL add_table_column('cmsPreferences', 'id_pages_landing_user', 'INT(10) UNSIGNED ZEROFILL DEFAULT NULL');
CALL add_table_column('cmsPreferences', 'id_pages_landing_guest', 'INT(10) UNSIGNED ZEROFILL DEFAULT NULL');
CALL add_table_column('cmsPreferences', 'id_pages_after_login', 'INT(10) UNSIGNED ZEROFILL DEFAULT NULL');
CALL add_table_column('cmsPreferences', 'login_return_last_page', 'TINYINT(1) NOT NULL DEFAULT 1');

-- Not `add_foreign_key()`: it always adds ON DELETE CASCADE, which would delete
-- the whole preferences row together with the page. Guarded so a re-run is a no-op.
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE `table_schema` = DATABASE() AND `table_name` = 'cmsPreferences'
    AND `constraint_name` = 'fk_cmsPreferences_id_pages_landing_user');
SET @sqlstmt = IF(@fk_exists > 0, "SELECT 'fk_cmsPreferences_id_pages_landing_user exists'",
    'ALTER TABLE cmsPreferences ADD CONSTRAINT fk_cmsPreferences_id_pages_landing_user FOREIGN KEY (id_pages_landing_user) REFERENCES pages (id) ON DELETE SET NULL ON UPDATE CASCADE');
PREPARE st FROM @sqlstmt;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE `table_schema` = DATABASE() AND `table_name` = 'cmsPreferences'
    AND `constraint_name` = 'fk_cmsPreferences_id_pages_landing_guest');
SET @sqlstmt = IF(@fk_exists > 0, "SELECT 'fk_cmsPreferences_id_pages_landing_guest exists'",
    'ALTER TABLE cmsPreferences ADD CONSTRAINT fk_cmsPreferences_id_pages_landing_guest FOREIGN KEY (id_pages_landing_guest) REFERENCES pages (id) ON DELETE SET NULL ON UPDATE CASCADE');
PREPARE st FROM @sqlstmt;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE `table_schema` = DATABASE() AND `table_name` = 'cmsPreferences'
    AND `constraint_name` = 'fk_cmsPreferences_id_pages_after_login');
SET @sqlstmt = IF(@fk_exists > 0, "SELECT 'fk_cmsPreferences_id_pages_after_login exists'",
    'ALTER TABLE cmsPreferences ADD CONSTRAINT fk_cmsPreferences_id_pages_after_login FOREIGN KEY (id_pages_after_login) REFERENCES pages (id) ON DELETE SET NULL ON UPDATE CASCADE');
PREPARE st FROM @sqlstmt;
EXECUTE st;
DEALLOCATE PREPARE st;

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

COMMIT;

