-- set DB version
UPDATE version
SET version = 'v8.0.0';

DELIMITER //
DROP PROCEDURE IF EXISTS add_index //
CREATE PROCEDURE add_index(
    param_table VARCHAR(100), 
    param_index_name VARCHAR(100), 
    param_index_column VARCHAR(1000),
    param_is_unique BOOLEAN
)
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.STATISTICS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `index_name` = param_index_name
		) > 0,
        "SELECT 'The index already exists in the table'",
        CONCAT(
            'CREATE ', 
            IF(param_is_unique, 'UNIQUE ', ''),
            'INDEX ', 
            param_index_name, 
            ' ON ', 
            param_table, 
            ' (', 
            param_index_column, 
            ');'
        )
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;


CALL add_table_column('pages', 'is_open_access', 'TINYINT DEFAULT 0');

-- add unique index for actions
CALL add_index('actions', 'idx_pageActions_name', 'name', true);

-- add action `cms-api` for api calls
INSERT IGNORE INTO `actions`(`name`) VALUES('cms-api');

-- add page api_v1_content_get_all_routes
SET @page_keyword = 'cms-api_v1_content_get_all_routes';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[content:class]/[all_routes:method]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Navigation Pages');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Navigation Pages');

-- make `home` page open access
INSERT IGNORE INTO `acl_users` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES (1, (SELECT id FROM pages WHERE keyword = 'home'), '1', '0', '0', '0');


-- add page api_v1_content_get_page
SET @page_keyword = 'cms-api_v1_content_get_page';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[content:class]/[page:method]/[slug:keyword]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Page');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Page');

-- add page api_v1_content_put_page
--  Used to update an existing resource or create a resource if it does not exist.
SET @page_keyword = 'cms-api_v1_content_put_page';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[content:class]/[page:method]/[slug:keyword]', 'PUT', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Put Data to Page');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Put Data to Page');

-- add page cms-api_v1_auth_login
SET @page_keyword = 'cms-api_v1_auth_login';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[auth:class]/[login:method]', 'POST', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Login');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Login');

-- add page cms-api_v1_auth_two-factor-verify
SET @page_keyword = 'cms-api_v1_auth_two-factor-verify';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[auth:class]/[two_factor_verify:method]', 'POST', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Two Factor Authenitcation');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Two Factor Authenitcation');

-- add page cms-api_v1_auth_refresh_token
SET @page_keyword = 'cms-api_v1_auth_refresh_token';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[auth:class]/[refresh_token:method]', 'POST', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Refresh Token');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Refresh Token');

-- add page cms-api_v1_auth_logout
SET @page_keyword = 'cms-api_v1_auth_logout';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[auth:class]/[logout:method]', 'POST', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Logout');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Logout');

-- add page cms-api_v1_admin
SET @page_keyword = 'cms-api_v1_admin_get_access';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[access:method]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 0);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Admin - get access');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Admin - get access');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT id FROM `groups` WHERE `name` = 'admin'), (SELECT id FROM pages WHERE keyword = 'cms-api_v1_admin_get_access'), '1', '0', '0', '0');

-- add page cms-api_v1_admin_get_pages
SET @page_keyword = 'cms-api_v1_admin_get_pages';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[pages:method]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 0);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Admin - get pages');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Admin - get pages');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT id FROM `groups` WHERE `name` = 'admin'), (SELECT id FROM pages WHERE keyword = 'cms-api_v1_admin_get_pages'), '1', '0', '0', '0');

DELIMITER //

DROP PROCEDURE IF EXISTS get_user_acl //
CREATE PROCEDURE get_user_acl(
    IN param_user_id INT,
    IN param_page_id INT  -- -1 means “all pages”
)
BEGIN

    SELECT
        param_user_id  AS id_users,
        id_pages,
        MAX(acl_select) AS acl_select,
        MAX(acl_insert) AS acl_insert,
        MAX(acl_update) AS acl_update,
        MAX(acl_delete) AS acl_delete,
        keyword,
        url,
        protocol,
        id_actions,
        id_navigation_section,
        parent,
        is_headless,
        nav_position,
        footer_position,
        id_type,
        id_pageAccessTypes
    FROM
    (
        -- 1) Group‐based ACL
        SELECT
            ug.id_users,
            acl.id_pages,
            acl.acl_select,
            acl.acl_insert,
            acl.acl_update,
            acl.acl_delete,
            p.keyword,
            p.url,
            p.protocol,
            p.id_actions,
            p.id_navigation_section,
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,
            p.id_type,
            p.id_pageAccessTypes
        FROM users_groups ug
        JOIN users u             ON ug.id_users   = u.id
        JOIN acl_groups acl      ON acl.id_groups = ug.id_groups
        JOIN pages p             ON p.id           = acl.id_pages
        WHERE ug.id_users = param_user_id
          AND (param_page_id = -1 OR acl.id_pages = param_page_id)

        UNION ALL

        -- 2) User‐specific ACL
        SELECT
            acl.id_users,
            acl.id_pages,
            acl.acl_select,
            acl.acl_insert,
            acl.acl_update,
            acl.acl_delete,
            p.keyword,
            p.url,
            p.protocol,
            p.id_actions,
            p.id_navigation_section,
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,
            p.id_type,
            p.id_pageAccessTypes
        FROM acl_users acl
        JOIN pages p ON p.id = acl.id_pages
        WHERE acl.id_users = param_user_id
          AND (param_page_id = -1 OR acl.id_pages = param_page_id)

        UNION ALL

        -- 3) Open-access pages (only all if param_page_id = -1, or just that page if it’s open)
        SELECT
            param_user_id       AS id_users,
            p.id                AS id_pages,
            1                   AS acl_select,
            0                   AS acl_insert,
            0                   AS acl_update,
            0                   AS acl_delete,
            p.keyword,
            p.url,
            p.protocol,
            p.id_actions,
            p.id_navigation_section,
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,
            p.id_type,
            p.id_pageAccessTypes
        FROM pages p
        WHERE p.is_open_access = 1
          AND (param_page_id = -1 OR p.id = param_page_id)

    ) AS combined_acl
    GROUP BY
        id_pages,
        keyword,
        url,
        protocol,
        id_actions,
        id_navigation_section,
        parent,
        is_headless,
        nav_position,
        footer_position,
        id_type,
        id_pageAccessTypes;

END
//
DELIMITER ;


CREATE TABLE IF NOT EXISTS `logPerformance` (
  `id_user_activity` INT(10) UNSIGNED NOT NULL,
  `log` LONGTEXT,
  PRIMARY KEY (`id_user_activity`),
  FOREIGN KEY (`id_user_activity`) REFERENCES `user_activity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS refreshTokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    id_users BIGINT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_users) REFERENCES users(id),
    INDEX idx_token_hash (token_hash),
    INDEX idx_user_id (id_users)
);

-- remove open type pages and make them experiment pages with open_access flag
UPDATE pages
SET id_type = (
        SELECT id
        FROM pageType
        WHERE name = 'experiment'
    ),
    is_open_Access = 1
WHERE id_type = (
        SELECT id
        FROM pageType
        WHERE name = 'open'
    );

-- delete the open page type
DELETE FROM pageType
WHERE name = 'open';

-- Register the new API endpoint for retrieving page sections
SET @page_keyword = 'cms-api_v1_admin_page_fields';

-- Add the page entry for the new API endpoint
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[page_fields:method]/[slug:page_keyword]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 0);

-- Add translations for the page title in English
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Page Fields');

-- Add translations for the page title in German
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Page Fields');

INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT id FROM `groups` WHERE `name` = 'admin'), (SELECT id FROM pages WHERE keyword = @page_keyword), '1', '0', '0', '0');

-- add column `is_system`, where if enabled the page is system and it should not be delete it or change its properties. Only its content fields can be edited
CALL add_table_column('pages', 'is_system', 'TINYINT DEFAULT 0');

UPDATE pages
SET is_system = 1
WHERE keyword IN ("login", "home", "profile", "missing", "no_access", "no_access_guest", "agb", "impressum", "disclaimer", "validate", "reset_password", "two-factor-authentication");

CALL drop_foreign_key('sections', 'sections_fk_owner');
CALL drop_table_column('sections', 'owner');

-- Register the new API endpoint for retrieving page sections
SET @page_keyword = 'cms-api_v1_admin_page_sections';

-- Add the page entry for the new API endpoint
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[page_sections:method]/[slug:page_keyword]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 0);

-- Add translations for the page title in English
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Page Sections');

-- Add translations for the page title in German
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Page Sections');

INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT id FROM `groups` WHERE `name` = 'admin'), (SELECT id FROM pages WHERE keyword = @page_keyword), '1', '0', '0', '0');

DELIMITER //

DROP PROCEDURE IF EXISTS `get_page_sections_hierarchical` //

CREATE PROCEDURE `get_page_sections_hierarchical`(IN page_id INT)
BEGIN
    WITH RECURSIVE section_hierarchy AS (
        -- Base case: get top-level sections for the page
        SELECT 
            s.id,
            s.`name`,
            s.id_styles,
            st.`name` AS style_name,
            ps.`position`,
            0 AS `level`,
            CAST(s.id AS CHAR(200)) AS `path`
        FROM pages_sections ps
        JOIN sections s ON ps.id_sections = s.id
        JOIN styles st ON s.id_styles = st.id
        LEFT JOIN sections_hierarchy sh ON s.id = sh.child
        WHERE ps.id_pages = page_id
        AND sh.parent IS NULL
        
        UNION ALL
        
        -- Recursive case: get children of sections
        SELECT 
            s.id,
            s.`name`,
            s.id_styles,
            st.`name` AS style_name,
            sh.position,
            h.`level` + 1,
            CONCAT(h.`path`, ',', s.id) AS `path`
        FROM section_hierarchy h
        JOIN sections_hierarchy sh ON h.id = sh.parent
        JOIN sections s ON sh.child = s.id
        JOIN styles st ON s.id_styles = st.id
    )
    
    -- Select the result
    SELECT 
        id,
        `name`,
        id_styles,
        style_name,
        position,
        `level`,
        `path`
    FROM section_hierarchy
    ORDER BY `path`, `position`;
END //

DELIMITER ;


CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_dataTables');
CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_scheduledJobs');
ALTER TABLE scheduledJobs_reminders
ADD PRIMARY KEY (id_scheduledJobs, id_dataTables);
CALL add_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_dataTables', 'id_dataTables', '`dataTables` (`id`)');        
CALL add_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_scheduledJobs', 'id_scheduledJobs', '`scheduledJobs` (`id`)');        

DROP TABLE IF EXISTS `deprecated_formActions_external`;
DROP TABLE IF EXISTS `deprecated_formActions_internal`;
DROP TABLE IF EXISTS `deprecated_user_input`;
DROP TABLE IF EXISTS `deprecated_user_input_record`;

DROP VIEW IF EXISTS view_form;
DROP VIEW IF EXISTS view_user_input;

DROP TABLE IF EXISTS `api_routes`;
CREATE TABLE `api_routes` (
  `id`           INT             NOT NULL AUTO_INCREMENT,
  `route_name`   VARCHAR(100)    NOT NULL,
  `version`      VARCHAR(10)     NOT NULL DEFAULT 'v1',
  `path`         VARCHAR(255)    NOT NULL,
  `controller`   VARCHAR(255)    NOT NULL,
  `methods`      VARCHAR(50)     NOT NULL,
  `requirements` JSON            NULL,
  `params`       JSON            NULL COMMENT 'Expected parameters: name → {in: body|query, required: bool}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_route_name_version` (`route_name`, `version`),
  UNIQUE KEY `uniq_version_path` (`version`, `path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `api_routes` (`route_name`,`version`,`path`,`controller`,`methods`,`requirements`,`params`) VALUES
('auth_login','v1','/auth/login','App\\Controller\\AuthController::login','POST',NULL,JSON_OBJECT('user',JSON_OBJECT('in','body','required',true),'password',JSON_OBJECT('in','body','required',true))),
('auth_two_factor_verify','v1','/auth/two-factor-verify','App\\Controller\\AuthController::two_factor_verify','POST',NULL,JSON_OBJECT('code',JSON_OBJECT('in','body','required',true),'id_users',JSON_OBJECT('in','body','required',true))),
('auth_refresh_token','v1','/auth/refresh-token','App\\Controller\\AuthController::refresh_token','POST',NULL,JSON_OBJECT('refresh_token',JSON_OBJECT('in','body','required',true))),
('auth_logout','v1','/auth/logout','App\\Controller\\AuthController::logout','POST',NULL,JSON_OBJECT('access_token',JSON_OBJECT('in','body','required',false),'refresh_token',JSON_OBJECT('in','body','required',false))),
('content_pages','v1','/pages','App\\Controller\\ContentController::getAllPages','GET',NULL,NULL),
('content_page','v1','/pages/{page_keyword}','App\\Controller\\ContentController::getPage','GET',JSON_OBJECT('page_keyword','[A-Za-z0-9_-]+'),NULL),
('content_update_page','v1','/pages/{page_keyword}','App\\Controller\\ContentController::updatePage','PUT',JSON_OBJECT('page_keyword','[A-Za-z0-9_-]+'),JSON_OBJECT('body',JSON_OBJECT('in','body','required',true))),
('admin_get_pages','v1','/admin/pages','App\\Controller\\AdminController::getPages','GET',NULL,NULL),
('admin_page_fields','v1','/admin/pages/{page_keyword}/fields','App\\Controller\\AdminController::getPageFields','GET',JSON_OBJECT('page_keyword','[A-Za-z0-9_-]+'),NULL),
('admin_page_sections','v1','/admin/pages/{page_keyword}/sections','App\\Controller\\AdminController::getPageSections','GET',JSON_OBJECT('page_keyword','[A-Za-z0-9_-]+'),NULL);

-- drop qualtrics tables if the plugin is not installed
-- 1) Check plugin
SELECT COUNT(*) INTO @cnt
  FROM plugins
 WHERE name = 'qualtrics';

-- 2) Flag whether it’s missing
SET @plugin_missing = (@cnt = 0);

-- 3a) If missing, disable FK checks
SET @sql := IF(
  @plugin_missing,
  'SET FOREIGN_KEY_CHECKS = 0;',
  'SELECT "Plugin exists. Nothing dropped."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3b) Drop each table in correct order
SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `qualtricsActions_functions`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `qualtricsActions_groups`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `qualtricsSurveysResponses`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `scheduledJobs_qualtricsActions`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `qualtricsReminders`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `qualtricsActions`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `qualtricsProjects`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `qualtricsSurveys`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP VIEW IF EXISTS `view_qualtricsActions`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP VIEW IF EXISTS `view_qualtricsSurveys`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP VIEW IF EXISTS `view_qualtricsReminders`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3c) Finally, if we did drop, re-enable FK checks
SET @sql := IF(
  @plugin_missing,
  'SET FOREIGN_KEY_CHECKS = 1;',
  'SELECT "Nothing to re-enable."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- drop chat tables if the plugin is not installed
-- 1) Check plugin
SELECT COUNT(*) INTO @cnt
  FROM plugins
 WHERE name = 'chat';

-- 2) Flag whether it’s missing
SET @plugin_missing = (@cnt = 0);

-- 3a) If missing, disable FK checks
SET @sql := IF(
  @plugin_missing,
  'SET FOREIGN_KEY_CHECKS = 0;',
  'SELECT "Plugin exists. Nothing dropped."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3b) Drop each table in correct order
SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `chat`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `chatRecipiants`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @plugin_missing,
  'DROP TABLE IF EXISTS `chatRoom`;',
  'SELECT "Skipping drop."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3c) Finally, if we did drop, re-enable FK checks
SET @sql := IF(
  @plugin_missing,
  'SET FOREIGN_KEY_CHECKS = 1;',
  'SELECT "Nothing to re-enable."'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;




-- shoudl remove is_fluid from container style
-- create new page onpen access use new field
-- reowork all form data to use drop down for table selection. First the table should be registered by the user. Assign ACL to these dataTables.
-- remove the gender
-- pages should be moved to routes, then create link to lages, then link to pages_configurations (something else), refactor types, actions and all. Check this sql
SELECT pft.id_fields, f.`name` AS field_name, pft.id_languages, pft.content, f.display, ft.id as field_id, ft.`name` as style_name, ft.position, pf.*, pft.*
FROM pages_fields_translation pft
INNER JOIN `fields` f ON pft.id_fields = f.id
INNER JOIN `fieldType` ft ON ft.id = f.id_type
LEFT JOIN `pages_fields` pf ON (pf.id_pages = pft.id_pages AND pf.id_fields = f.id)
WHERE pft.id_pages = 96


-- drop qualtrics and all not needed deprecated tbles or these coming from plugins
-- php bin/console doctrine:schema:update --dump-sql --verbose adjust 
