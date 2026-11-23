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

-- ---------------------------------------------------------------------------
-- Date inputs: display format `date_format`, storage always ISO
--
-- Inputs of type date, datetime and time store date `Y-m-d`, datetime
-- `Y-m-d H:i` and time `H:i`. The new select field `date_format` (input
-- style) sets only what the user sees (flatpickr tokens; datetime adds
-- ` H:i`, time is always `H:i`). The list must match
-- InputDateFormat::DATE_FORMATS. Before, the free-text field `format` set the
-- shown AND the stored format; it is now only the regex pattern of the other
-- input types. An existing `format` of a date/datetime input that matches an
-- option is copied into `date_format` (case-sensitive: `m` is month, `M`
-- would be a month name); any other keeps the default `Y-m-d`.
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO `lookups` (`type_code`, `lookup_code`, `lookup_value`, `lookup_description`)
VALUES
    ('dateInputFormats', 'Y-m-d', '2026-12-31 (YYYY-MM-DD)', 'YYYY-MM-DD'),
    ('dateInputFormats', 'd.m.Y', '31.12.2026 (DD.MM.YYYY)', 'DD.MM.YYYY'),
    ('dateInputFormats', 'd/m/Y', '31/12/2026 (DD/MM/YYYY)', 'DD/MM/YYYY'),
    ('dateInputFormats', 'm/d/Y', '12/31/2026 (MM/DD/YYYY)', 'MM/DD/YYYY'),
    ('dateInputFormats', 'j. F Y', '31. Dezember 2026 (D. MMMM YYYY)', 'D. MMMM YYYY'),
    ('dateInputFormats', 'j F Y', '31 December 2026 (D MMMM YYYY)', 'D MMMM YYYY'),
    ('dateInputFormats', 'F j, Y', 'December 31, 2026 (MMMM D, YYYY)', 'MMMM D, YYYY');

INSERT IGNORE INTO `fieldType` (`name`, `position`)
VALUES ('select-date-format', 8);

INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`)
VALUES ('date_format', get_field_type_id('select-date-format'), 0);

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('input'), get_field_id('date_format'), 'Y-m-d', 'Only for the types `date` and `datetime`: the format the user sees, e.g. `31.12.2026` or `31 December 2026` (month names in the user language). A `datetime` shows the time after it (`HH:mm`, 24 hours); a `time` is always shown as `HH:mm`. The value is always stored as `YYYY-MM-DD` (date), `YYYY-MM-DD HH:mm` (datetime) or `HH:mm` (time), whatever is chosen here, so conditions and exports can rely on it.');

UPDATE `styles_fields`
SET `help` = 'Only for the text-like types: a regex pattern the value must match (HTML `pattern`). Ignored for `date`, `datetime` and `time`: use `date_format` for the format the user sees.'
WHERE `id_styles` = get_style_id('input') AND `id_fields` = get_field_id('format');

INSERT IGNORE INTO `sections_fields_translation` (`id_sections`, `id_fields`, `id_languages`, `id_genders`, `content`)
SELECT sft_format.id_sections, get_field_id('date_format'), 1, 1, l.lookup_code
FROM `sections_fields_translation` sft_format
INNER JOIN `sections_fields_translation` sft_type
    ON sft_type.id_sections = sft_format.id_sections
    AND sft_type.id_fields = get_field_id('type_input')
    AND sft_type.content IN ('date', 'datetime')
INNER JOIN `lookups` l
    ON l.type_code = 'dateInputFormats'
    AND BINARY l.lookup_code = BINARY TRIM(IF(sft_type.content = 'datetime',
        REPLACE(REPLACE(sft_format.content, ' H:i:S', ''), ' H:i', ''), sft_format.content))
WHERE sft_format.id_fields = get_field_id('format');

COMMIT;

