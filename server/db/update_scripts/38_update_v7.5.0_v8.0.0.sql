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

-- add column `is_system`, where if enabled the page is system and it should not be delete it or change its properties. Only its content fields can be edited
CALL add_table_column('pages', 'is_system', 'TINYINT DEFAULT 0');

UPDATE pages
SET is_system = 1
WHERE keyword IN ("login", "home", "profile", "missing", "no_access", "no_access_guest", "agb", "impressum", "disclaimer", "validate", "reset_password", "two-factor-authentication");

CALL drop_foreign_key('sections', 'sections_fk_owner');
CALL drop_table_column('sections', 'owner');

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

DELIMITER $$

DROP PROCEDURE IF EXISTS `add_primary_key` $$
CREATE PROCEDURE `add_primary_key`(
  IN `param_table`   VARCHAR(100),
  IN `param_columns` VARCHAR(500)  -- e.g. 'col1, col2'
)
BEGIN
  DECLARE cnt INT DEFAULT 0;

  -- Check if a PRIMARY KEY already exists on the table
  SELECT COUNT(*) INTO cnt
    FROM information_schema.TABLE_CONSTRAINTS
   WHERE table_schema    = DATABASE()
     AND table_name      = param_table
     AND constraint_type = 'PRIMARY KEY';

  -- Build the appropriate statement
  IF cnt = 0 THEN
    SET @sqlstmt = CONCAT(
      'ALTER TABLE `', param_table,
      '` ADD PRIMARY KEY (', param_columns, ');'
    );
  ELSE
    SET @sqlstmt = "SELECT 'Primary key already exists on table.'";
  END IF;

  -- Execute it
  PREPARE st FROM @sqlstmt;
  EXECUTE st;
  DEALLOCATE PREPARE st;
END$$

DELIMITER ;


CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_dataTables');
CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_scheduledJobs');
CALL add_primary_key('scheduledJobs_reminders', 'id_scheduledJobs, id_dataTables');
CALL add_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_dataTables', 'id_dataTables', '`dataTables` (`id`)');        
CALL add_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_scheduledJobs', 'id_scheduledJobs', '`scheduledJobs` (`id`)');        

DROP TABLE IF EXISTS `deprecated_formActions_external`;
DROP TABLE IF EXISTS `deprecated_formActions_internal`;
DROP TABLE IF EXISTS `deprecated_user_input`;
DROP TABLE IF EXISTS `deprecated_user_input_record`;

DROP VIEW IF EXISTS view_form;
DROP VIEW IF EXISTS view_user_input;

-- DROP TABLE IF EXISTS `api_routes`;
CREATE TABLE IF NOT EXISTS `api_routes` (
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

-- Insert API routes with proper versioned controllers
INSERT IGNORE INTO `api_routes` (`route_name`,`version`,`path`,`controller`,`methods`,`requirements`,`params`) VALUES
-- Auth routes
('auth_login','v1','/auth/login','App\\Controller\\Api\\V1\\Auth\\AuthController::login','POST',NULL,JSON_OBJECT('user',JSON_OBJECT('in','body','required',true),'password',JSON_OBJECT('in','body','required',true))),
('auth_two_factor_verify','v1','/auth/two-factor-verify','App\\Controller\\Api\\V1\\Auth\\AuthController::twoFactorVerify','POST',NULL,JSON_OBJECT('code',JSON_OBJECT('in','body','required',true),'id_users',JSON_OBJECT('in','body','required',true))),
('auth_refresh_token','v1','/auth/refresh-token','App\\Controller\\Api\\V1\\Auth\\AuthController::refreshToken','POST',NULL,JSON_OBJECT('refresh_token',JSON_OBJECT('in','body','required',true))),
('auth_logout','v1','/auth/logout','App\\Controller\\Api\\V1\\Auth\\AuthController::logout','POST',NULL,JSON_OBJECT('access_token',JSON_OBJECT('in','body','required',false),'refresh_token',JSON_OBJECT('in','body','required',false))),

-- Admin routes
('admin_get_pages','v1','/admin/pages','App\\Controller\\Api\\V1\\Admin\\AdminPageController::getPages','GET',NULL,NULL),
('admin_page_fields','v1','/admin/pages/{page_keyword}/fields','App\\Controller\\Api\\V1\\Admin\\AdminPageController::getPageFields','GET',JSON_OBJECT('page_keyword','[A-Za-z0-9_-]+'),NULL),
('admin_page_sections','v1','/admin/pages/{page_keyword}/sections','App\\Controller\\Api\\V1\\Admin\\AdminPageController::getPageSections','GET',JSON_OBJECT('page_keyword','[A-Za-z0-9_-]+'),NULL);

-- Example of a v2 API route (for future use)
-- INSERT IGNORE INTO `api_routes` (`route_name`,`version`,`path`,`controller`,`methods`,`requirements`,`params`) VALUES
-- ('auth_login','v2','/auth/login','App\\Controller\\Api\\V2\\Auth\\AuthController::login','POST',NULL,JSON_OBJECT('email',JSON_OBJECT('in','body','required',true),'password',JSON_OBJECT('in','body','required',true)));

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

DELIMITER $$

DROP PROCEDURE IF EXISTS `MigrateDomainToLookups`$$
CREATE PROCEDURE `MigrateDomainToLookups`()
BEGIN
  -- 1) userStatus → lookups + users.id_status
  IF EXISTS (
    SELECT 1 FROM information_schema.TABLES
    WHERE table_schema = DATABASE() AND table_name = 'userStatus'
  ) THEN
    -- a) upsert into lookups
    INSERT IGNORE INTO lookups(type_code, lookup_code, lookup_value, lookup_description)
    SELECT 'userStatus', name, name, description
      FROM userStatus;
      
	CALL drop_foreign_key('users', 'fk_users_id_status');
    
    -- b) re-point users.id_status
    UPDATE users u
    JOIN userStatus us ON u.id_status = us.id
    JOIN lookups l    ON l.type_code   = 'userStatus'
                     AND l.lookup_code = us.name
    SET u.id_status = l.id;

    -- c) swap FKs
    
    CALL add_foreign_key('users',
                         'fk_users_id_status',
                         'id_status',
                         'lookups(id)');

    -- d) drop domain table
    DROP TABLE IF EXISTS `userStatus`;
  END IF;

  -- 2) actions → lookups + pages.id_actions
  IF EXISTS (
    SELECT 1 FROM information_schema.TABLES
    WHERE table_schema = DATABASE() AND table_name = 'actions'
  ) THEN
    INSERT IGNORE INTO lookups(type_code, lookup_code, lookup_value, lookup_description)
    SELECT 'pageActions', name, name, NULL
      FROM actions;

	CALL drop_foreign_key('pages', 'pages_fk_id_actions');

    UPDATE pages p
    JOIN actions a ON p.id_actions = a.id
    JOIN lookups l ON l.type_code   = 'pageActions'
                  AND l.lookup_code = a.name
    SET p.id_actions = l.id;
    
    CALL add_foreign_key('pages',
                         'pages_fk_id_actions',
                         'id_actions',
                         'lookups(id)');

    DROP TABLE IF EXISTS `actions`;
  END IF;

  -- 3) activityType → lookups + user_activity.id_type
  IF EXISTS (
    SELECT 1 FROM information_schema.TABLES
    WHERE table_schema = DATABASE() AND table_name = 'activityType'
  ) THEN
    INSERT IGNORE INTO lookups(type_code, lookup_code, lookup_value, lookup_description)
    SELECT 'activityType', name, name, NULL
      FROM activityType;

	CALL drop_foreign_key('user_activity', 'fk_user_activity_fk_id_type');

    UPDATE user_activity ua
    JOIN activityType at ON ua.id_type = at.id
    JOIN lookups l       ON l.type_code   = 'activityType'
                       AND l.lookup_code = at.name
    SET ua.id_type = l.id;
    
    CALL add_foreign_key('user_activity',
                         'fk_user_activity_fk_id_type',
                         'id_type',
                         'lookups(id)');

    DROP TABLE IF EXISTS `activityType`;
  END IF;

  -- 4) styleType → lookups + styles.id_type
  IF EXISTS (
    SELECT 1 FROM information_schema.TABLES
    WHERE table_schema = DATABASE() AND table_name = 'styleType'
  ) THEN
    INSERT IGNORE INTO lookups(type_code, lookup_code, lookup_value, lookup_description)
    SELECT 'styleType', name, name, NULL
      FROM styleType;

	CALL drop_foreign_key('styles', 'styles_fk_id_type');

    UPDATE styles s
    JOIN styleType st ON s.id_type = st.id
    JOIN lookups l     ON l.type_code   = 'styleType'
                      AND l.lookup_code = st.name
    SET s.id_type = l.id;
    
    CALL add_foreign_key('styles',
                         'styles_fk_id_type',
                         'id_type',
                         'lookups(id)');

    DROP TABLE IF EXISTS `styleType`;
  END IF;

END$$

DELIMITER ;

-- Install once:
CALL MigrateDomainToLookups();

-- You can safely call it repeatedly; already-migrated tables are skipped.

DROP PROCEDURE IF EXISTS `MigrateDomainToLookups`;

DROP VIEW IF EXISTS `view_styles`;
CREATE VIEW `view_styles` AS
SELECT
  CAST(s.id AS UNSIGNED) AS style_id,
  s.name AS style_name,
  s.description AS style_description,
  CAST(lst.id AS UNSIGNED) AS style_type_id,
  lst.lookup_value AS style_type,
  CAST(sg.id AS UNSIGNED) AS style_group_id,
  sg.name AS style_group,
  sg.description AS style_group_description,
  sg.position AS style_group_position
FROM styles s
LEFT JOIN lookups lst
  ON s.id_type = lst.id
  AND lst.type_code = 'styleType'
LEFT JOIN styleGroup sg
  ON s.id_group = sg.id;

DROP VIEW IF EXISTS `view_users`;
CREATE VIEW `view_users` AS
SELECT
  u.id AS id,
  u.email AS email,
  u.name AS name,
  IFNULL(
    CONCAT(
      u.last_login,
      ' (',
      TO_DAYS(NOW()) - TO_DAYS(u.last_login),
      ' days ago)'
    ),
    'never'
  ) AS last_login,
  usl.lookup_value       AS status,
  usl.lookup_description AS description,
  u.blocked              AS blocked,
  (CASE
     WHEN u.name = 'admin' THEN 'admin'
     WHEN u.name = 'tpf'   THEN 'tpf'
     ELSE IFNULL(vc.code, '-')
   END)                  AS code,
  GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS `groups`,
  ua.activity_count     AS user_activity,
  ua.distinct_url_count AS ac,
  u.intern              AS intern,
  u.id_userTypes        AS id_userTypes,
  lut.lookup_code       AS user_type_code,
  lut.lookup_value      AS user_type
FROM users u
LEFT JOIN lookups usl
  ON usl.id = u.id_status
  AND usl.type_code = 'userStatus'
LEFT JOIN users_groups ug
  ON ug.id_users = u.id
LEFT JOIN `groups` g
  ON g.id = ug.id_groups
LEFT JOIN validation_codes vc
  ON u.id = vc.id_users
JOIN lookups lut
  ON lut.id = u.id_userTypes
LEFT JOIN (
  SELECT
    ua.id_users AS id_users,
    COUNT(*)    AS activity_count,
    COUNT(DISTINCT CASE WHEN ua.id_type = 1 THEN ua.url END) AS distinct_url_count
  FROM user_activity ua
  GROUP BY ua.id_users
) AS ua
  ON ua.id_users = u.id
WHERE u.intern <> 1
  AND u.id_status > 0
GROUP BY
  u.id,
  u.email,
  u.name,
  u.last_login,
  usl.lookup_value,
  usl.lookup_description,
  u.blocked,
  vc.code,
  ua.activity_count,
  ua.distinct_url_count,
  u.intern,
  u.id_userTypes,
  lut.lookup_code,
  lut.lookup_value
ORDER BY u.email;

DELIMITER //

DROP PROCEDURE IF EXISTS get_page_fields //
CREATE PROCEDURE get_page_fields(
    IN page_id INT,
    IN language_id INT,
    IN default_language_id INT,
    IN filter_param VARCHAR(1000),
    IN order_param VARCHAR(1000)
)
READS SQL DATA
DETERMINISTIC
BEGIN  
    -- page_id = -1 returns all pages
    SET @@group_concat_max_len = 32000000;

    SELECT get_page_fields_helper(page_id, language_id, default_language_id) 
      INTO @sql;    
    
    IF @sql IS NULL THEN    
        SELECT * 
          FROM pages 
         WHERE 1=2;
    ELSE 
        BEGIN
            SET @sql = CONCAT(
                'SELECT 
                    p.id,
                    p.keyword,
                    p.url,
                    p.protocol,
                    p.id_actions,
                    "select" AS access_level,
                    p.id_navigation_section,
                    p.parent,
                    p.is_headless,
                    p.nav_position,
                    p.footer_position,
                    p.id_type,
                    p.id_pageAccessTypes,
                    a.lookup_code AS `action`, ',
                 @sql, '
                 FROM pages p
                 LEFT JOIN lookups AS a 
                   ON a.id = p.id_actions 
                  AND a.type_code = "pageActions"
                 LEFT JOIN pageType_fields AS ptf 
                   ON ptf.id_pageType = p.id_type 
                 LEFT JOIN fields AS f 
                   ON f.id = ptf.id_fields
                 WHERE (p.id = ', page_id, ' OR -1 = ', page_id, ')
                 GROUP BY 
                   p.id, p.keyword, p.url, p.protocol, p.id_actions,
                   p.id_navigation_section, p.parent, p.is_headless,
                   p.nav_position, p.footer_position, p.id_type,
                   p.id_pageAccessTypes, a.lookup_code
                 HAVING 1 ', filter_param
            );
            
            IF order_param <> '' THEN             
                SET @sql = CONCAT(
                    'SELECT * FROM (',
                    @sql,
                    ') AS t ', order_param
                );
            END IF;

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END;
    END IF;
END 
//

DELIMITER ;

INSERT IGNORE INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('pageActions', 'navigation', 'Navigation', 'Navigation section page');

CREATE TABLE IF NOT EXISTS `apiRequestLogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `route_name` VARCHAR(255) DEFAULT NULL,
  `path` VARCHAR(255) NOT NULL,
  `method` VARCHAR(10) NOT NULL,
  `status_code` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `request_time` DATETIME NOT NULL,
  `response_time` DATETIME NOT NULL,
  `duration_ms` INT NOT NULL,
  `request_params` TEXT DEFAULT NULL,
  `request_headers` TEXT DEFAULT NULL,
  `response_data` TEXT DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- shoudl remove is_fluid from container style
-- reowork all form data to use drop down for table selection. First the table should be registered by the user. Assign ACL to these dataTables.
-- remove the gender
-- pages should be moved to routes, then create link to lages, then link to pages_configurations (something else), refactor types, actions and all. Check this sql
-- check the cache page

-- for old to work and test:
INSERT IGNORE INTO `lookups` (`type_code`,`lookup_code`,`lookup_value`,`lookup_description`) VALUES ('pageActions','cms-api','cms-api',NULL);
SET @page_keyword = 'cms-api_v1_content_get_all_routes'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[content:class]/[all_routes:method]', 'GET', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 1); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Get Navigation Pages'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Get Navigation Pages');
INSERT IGNORE INTO `acl_users` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES (1, (SELECT `id` FROM `pages` WHERE `keyword` = 'home'), '1','0','0','0');
SET @page_keyword = 'cms-api_v1_content_get_page'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[content:class]/[page:method]/[slug:keyword]', 'GET', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 1); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Get Page'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Get Page');
SET @page_keyword = 'cms-api_v1_content_put_page'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[content:class]/[page:method]/[slug:keyword]', 'PUT', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 1); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Put Data to Page'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Put Data to Page');
SET @page_keyword = 'cms-api_v1_auth_login'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[auth:class]/[login:method]', 'POST', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 1); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Login'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Login');
SET @page_keyword = 'cms-api_v1_auth_two-factor-verify'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[auth:class]/[two_factor_verify:method]', 'POST', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 1); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Two Factor Authentication'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Two Factor Authentication');
SET @page_keyword = 'cms-api_v1_auth_refresh_token'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[auth:class]/[refresh_token:method]', 'POST', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 1); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Refresh Token'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Refresh Token');
SET @page_keyword = 'cms-api_v1_auth_logout'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[auth:class]/[logout:method]', 'POST', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 1); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Logout'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Logout');
SET @page_keyword = 'cms-api_v1_admin_get_access'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[access:method]', 'GET', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 0); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Admin - get access'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Admin - get access'); INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT `id` FROM `groups` WHERE `name` = 'admin'), (SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), '1','0','0','0');
SET @page_keyword = 'cms-api_v1_admin_get_pages'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[pages:method]', 'GET', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 0); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Admin - get pages'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Admin - get pages'); INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT `id` FROM `groups` WHERE `name` = 'admin'), (SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), '1','0','0','0');
SET @page_keyword = 'cms-api_v1_admin_page_fields'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[page_fields:method]/[slug:page_keyword]', 'GET', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 0); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Get Page Fields'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Get Page Fields'); INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT `id` FROM `groups` WHERE `name` = 'admin'), (SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), '1','0','0','0');
SET @page_keyword = 'cms-api_v1_admin_page_sections'; INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[page_sections:method]/[slug:page_keyword]', 'GET', (SELECT `id` FROM `lookups` WHERE `type_code` = 'pageActions' AND `lookup_code` = 'cms-api' LIMIT 1), NULL, NULL, '0', NULL, NULL, (SELECT `id` FROM `pageType` WHERE `name` = 'intern' LIMIT 1), (SELECT `id` FROM `lookups` WHERE `lookup_code` = 'mobile_and_web' LIMIT 1), 0); INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000001', 'Get Page Sections'), ((SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), get_field_id('title'), '0000000002', 'Get Page Sections'); INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT `id` FROM `groups` WHERE `name` = 'admin'), (SELECT `id` FROM `pages` WHERE `keyword` = @page_keyword), '1','0','0','0');
