-- set DB version
UPDATE version
SET version = 'v8.0.0';

-- N+1 Query Optimization for StylesField Repository
-- Added findDefaultValuesByStyleIds batch method to StylesFieldRepository
-- to eliminate N+1 query problem when fetching default values for multiple styles
-- in SectionUtilityService::applySectionTranslations method.
-- This reduces database queries from N individual queries to 1 batch query,
-- significantly improving performance for pages with many sections.

-- Add new features for v8.0.0: Gender API, CMS Preferences API, Asset Management API, User Validation System, Scheduled Jobs API, Section Management API

-- Scheduled Jobs API Enhancement
-- Added comprehensive scheduled jobs management API with:
-- - Full CRUD operations for scheduled jobs
-- - Advanced filtering and search capabilities
-- - Pagination support with customizable page sizes
-- - Date range filtering for different date types (create, execute, executed)
-- - Status and job type filtering
-- - Transaction logging for all job operations
-- - Job execution functionality with proper status management
-- - Soft delete functionality (status change to deleted)
-- - Related transactions viewing for audit trails
-- - Job statuses and types lookup endpoints
-- - Proper entity relationships with users and tasks
-- - Transaction service integration for data integrity
-- - JSON schema validation for all responses
-- - Admin permission system integration
-- New API routes:
-- - /admin/scheduled-jobs - List and filter scheduled jobs
-- - /admin/scheduled-jobs/{jobId} - Get job details
-- - /admin/scheduled-jobs/{jobId}/execute - Execute a job
-- - /admin/scheduled-jobs/{jobId} - Delete a job (soft delete)
-- - /admin/scheduled-jobs/{jobId}/transactions - Get job transactions
-- - /admin/scheduled-jobs/statuses - Get available job statuses
-- - /admin/scheduled-jobs/types - Get available job types

-- Add new features for v8.0.0: Gender API, CMS Preferences API, Asset Management API, User Validation System

-- User Validation System Enhancement
-- Added user validation functionality using the existing token field in users table
-- UserValidationService handles account validation with email scheduling
-- JobSchedulerService enhanced with direct email scheduling capabilities
-- Welcome emails are automatically sent after successful account validation
-- Validation tokens are stored in users.token field (32-character hex strings)

-- Transaction Logging Enhancement
-- All admin service create, edit, delete operations are now wrapped in database transactions
-- with proper rollback handling and transaction logging via TransactionService
-- This ensures data integrity and provides comprehensive audit trails for all admin operations

-- Ensure upload directory structure exists for assets
-- Note: This is handled by the AdminAssetService when creating assets

-- Asset Management API Enhancement
-- Added pagination and search functionality to assets endpoint
-- Enhanced asset upload to support multiple file uploads
-- Fixed file size error by avoiding stat calls on temporary files
-- Added proper file validation and transaction handling for asset operations
-- Added JSON schema validation for asset creation requests (both single and multiple files)

-- Section update functionality added
-- Updated API routes for section management to include page_keyword parameter
-- Added section update endpoint with proper field handling for content and property fields

-- Section Export/Import API Routes Enhancement
-- Updated section export/import functionality to properly handle:
-- - Complete field information including display, default_value, help, disabled, hidden
-- - Hierarchical structure with proper position handling
-- - Style names and language locales instead of IDs for portability
-- - Meta data for translations
-- - Recursive child section handling with proper position management
-- - Enhanced error handling and validation for style and language resolution
-- - Added position parameter support for section import operations
-- - Section names now include timestamp suffix for uniqueness during import
-- - Fixed PagesSection entity ID assignment issue for proper persistence

-- Page Title Translation Enhancement
-- Added locale parameter support for page listing endpoints
-- Enhanced page service to fetch and display page titles with translations
-- Added PagesFieldsTranslationRepository for optimized translation queries
-- Updated frontend and admin controllers to accept locale parameter
-- Added fallback mechanism for default language when translations are missing
-- New API routes:
-- - /pages/{locale} - Frontend pages with locale
-- - /admin/pages/{locale} - Admin pages with locale

DROP PROCEDURE IF EXISTS rename_index;
DELIMITER //

CREATE PROCEDURE rename_index(
  IN param_table VARCHAR(100),
  IN old_index_name VARCHAR(100),
  IN new_index_name VARCHAR(100)
)
BEGIN
  DECLARE old_exists INT DEFAULT 0;
  DECLARE new_exists INT DEFAULT 0;

  -- does old index exist?
  SELECT COUNT(*) INTO old_exists
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = param_table
    AND INDEX_NAME   = old_index_name;

  -- does new index already exist?
  SELECT COUNT(*) INTO new_exists
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = param_table
    AND INDEX_NAME   = new_index_name;

  IF new_exists > 0 THEN
    SELECT CONCAT('Index ', new_index_name, ' already exists on ', param_table) AS msg;
  ELSEIF old_exists = 0 THEN
    SELECT CONCAT('Index ', old_index_name, ' not found on ', param_table) AS msg;
  ELSE
    SET @sql := CONCAT('ALTER TABLE `', param_table, '` RENAME INDEX `', old_index_name, '` TO `', new_index_name, '`;');
    PREPARE st FROM @sql;
    EXECUTE st;
    DEALLOCATE PREPARE st;
    SELECT CONCAT('Renamed `', param_table, '`.`', old_index_name, '` -> `', new_index_name, '`') AS msg;
  END IF;
END //

DELIMITER ;


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
    IN param_page_id INT  -- -1 means "all pages"
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
        parent,
        is_headless,
        nav_position,
        footer_position,        
        id_type,
        id_pageAccessTypes,
        is_system
    FROM
    (
        -- 1) Group-based ACL
        SELECT
            ug.id_users,
            acl.id_pages,
            acl.acl_select,
            acl.acl_insert,
            acl.acl_update,
            acl.acl_delete,
            p.keyword,
            p.url,            
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,       
            id_type,     
            p.id_pageAccessTypes,
            is_system
        FROM users_groups ug
        JOIN users u             ON ug.id_users   = u.id
        JOIN acl_groups acl      ON acl.id_groups = ug.id_groups
        JOIN pages p             ON p.id           = acl.id_pages
        WHERE ug.id_users = param_user_id
          AND (param_page_id = -1 OR acl.id_pages = param_page_id)

        UNION ALL

        -- 2) User-specific ACL
        SELECT
            acl.id_users,
            acl.id_pages,
            acl.acl_select,
            acl.acl_insert,
            acl.acl_update,
            acl.acl_delete,
            p.keyword,
            p.url,          
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,     
            id_type,       
            p.id_pageAccessTypes,
            is_system
        FROM acl_users acl
        JOIN pages p ON p.id = acl.id_pages
        WHERE acl.id_users = param_user_id
          AND (param_page_id = -1 OR acl.id_pages = param_page_id)

        UNION ALL

        -- 3) Open-access pages (only all if param_page_id = -1, or just that page if it's open)
        SELECT
            param_user_id       AS id_users,
            p.id                AS id_pages,
            1                   AS acl_select,
            0                   AS acl_insert,
            0                   AS acl_update,
            0                   AS acl_delete,
            p.keyword,
            p.url,           
            p.parent,
            p.is_headless,
            p.nav_position,
            p.footer_position,  
            id_type,          
            p.id_pageAccessTypes,
            is_system
        FROM pages p
        WHERE p.is_open_access = 1
          AND (param_page_id = -1 OR p.id = param_page_id)

    ) AS combined_acl
    GROUP BY
        id_pages,
        keyword,
        url,      
        parent,
        is_headless,
        nav_position,
        footer_position,     
        id_type,   
        is_system,
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

-- add column can_have_children in the style table
CALL add_table_column('styles', 'can_have_children', 'TINYINT(1) DEFAULT 0 NOT NULL');
UPDATE styles
SET can_have_children = 1
WHERE `name` IN ("htmlTag","dataContainer","tableCell","tableRow","loop","table","conditionFailed","formUserInputRecord","formUserInputLog","refContainer","entryRecord","entryList","conditionalContainer","div","formUserInput","navigationContainer","tabs","tab","link","form","figure","card","alert","validate","jumbotron","container","profile");

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

-- drop qualtrics tables if the plugin is not installed
-- 1) Check plugin
SELECT COUNT(*) INTO @cnt
  FROM plugins
 WHERE name = 'qualtrics';

-- 2) Flag whether it's missing
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

-- 2) Flag whether it's missing
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

DELIMITER //
DROP PROCEDURE IF EXISTS add_foreign_key //
CREATE PROCEDURE add_foreign_key(param_table VARCHAR(100), fk_name VARCHAR(100), fk_column VARCHAR(100), fk_references VARCHAR(200))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) > 0,
        "SELECT 'The foreign key already exists in the table'",
        CONCAT('ALTER TABLE ', param_table, ' ADD CONSTRAINT ', fk_name, ' FOREIGN KEY (', fk_column, ') REFERENCES ', fk_references, ' ON DELETE CASCADE;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;

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
            ' ON `', 
            param_table, 
            '` (`', 
            param_index_column, 
            '`);'
        )
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;


DELIMITER //
DROP PROCEDURE IF EXISTS drop_index //
CREATE PROCEDURE drop_index(param_table VARCHAR(100), param_index_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.STATISTICS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `index_name` = param_index_name
		) > 0,        
        CONCAT('ALTER TABLE `', param_table, '` DROP INDEX ', param_index_name),
        "SELECT 'The index does not exists in the table'"
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS drop_foreign_key //
CREATE PROCEDURE drop_foreign_key(param_table VARCHAR(100), fk_name VARCHAR(100))
BEGIN	
    SET @sqlstmt = (SELECT IF(
		(
			SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS 
			WHERE `table_schema` = DATABASE()
			AND `table_name` = param_table
            AND `constraint_name` = fk_name
		) = 0,
        "SELECT 'Foreign key does not exist'",
        CONCAT('ALTER TABLE `', param_table, '` DROP FOREIGN KEY ', fk_name, ' ;')
    ));
	PREPARE st FROM @sqlstmt;
	EXECUTE st;
	DEALLOCATE PREPARE st;	
END

//

DELIMITER ;


-- --------------------------- DOCTRINE ------------------------------------------------------------------------
-- drop foreign keys
CALL drop_foreign_key('acl_groups', 'fk_acl_groups_id_groups');
CALL drop_foreign_key('acl_groups', 'fk_acl_groups_id_pages');
CALL drop_foreign_key('acl_users', 'acl_fk_id_pages');
CALL drop_foreign_key('acl_users', 'acl_fk_id_users');
CALL drop_foreign_key('assets', 'assets_fk_id_assetTypes');
CALL drop_foreign_key('codes_groups', 'fk_codes');
CALL drop_foreign_key('codes_groups', 'fk_id_groups');
CALL drop_foreign_key('dataCells', 'uploadCells_fk_id_uploadCols');
CALL drop_foreign_key('dataCells', 'uploadCells_fk_id_uploadRows');
CALL drop_foreign_key('dataCols', 'uploadCols_fk_id_uploadTables');
CALL drop_foreign_key('dataRows', 'uploadRows_fk_id_actionTriggerTypes');
CALL drop_foreign_key('dataRows', 'uploadRows_fk_id_uploadTables');
CALL drop_foreign_key('dataRows', 'uploadRows_fk_id_users');
CALL drop_foreign_key('fields', 'fields_fk_id_type');
CALL drop_foreign_key('formActions', 'formActions_id_dataTables');
CALL drop_foreign_key('genders', 'genders_fk_id_something');
CALL drop_foreign_key('groups', 'groups_fk_id_group_types');
CALL drop_foreign_key('hooks', 'hooks_fk_id_hookTypes');
CALL drop_foreign_key('logPerformance', 'logperformance_ibfk_1');
CALL drop_foreign_key('mailAttachments', 'mailAttachments_fk_id_mailQueue');
CALL drop_foreign_key('pages', 'pages_fk_id_actions');
CALL drop_foreign_key('pages', 'pages_fk_id_navigation_section');
CALL drop_foreign_key('pages', 'pages_fk_id_type');
CALL drop_foreign_key('pages', 'pages_fk_parent');
CALL drop_foreign_key('pages_fields', 'fk_page_fields_id_fields');
CALL drop_foreign_key('pages_fields', 'fk_page_fields_id_pages');
CALL drop_foreign_key('pages_fields_translation', 'pages_fields_translation_fk_id_fields');
CALL drop_foreign_key('pages_fields_translation', 'pages_fields_translation_fk_id_languages');
CALL drop_foreign_key('pages_fields_translation', 'pages_fields_translation_fk_id_pages');
CALL drop_foreign_key('pages_sections', 'pages_sections_fk_id_pages');
CALL drop_foreign_key('pages_sections', 'pages_sections_fk_id_sections');
CALL drop_foreign_key('pageType_fields', 'fk_pageType_fields_id_fields');
CALL drop_foreign_key('pageType_fields', 'fk_pageType_fields_id_pageType');
CALL drop_foreign_key('scheduledJobs', 'scheduledJobs_fk_id_jobStatus');
CALL drop_foreign_key('scheduledJobs', 'scheduledJobs_fk_id_jobTypes');
CALL drop_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_iid_formActions');
CALL drop_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_id_dataRows');
CALL drop_foreign_key('scheduledJobs_mailQueue', 'scheduledJobs_mailQueue_fk_id_mailQueue');
CALL drop_foreign_key('scheduledJobs_mailQueue', 'scheduledJobs_mailQueue_fk_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_notifications', 'scheduledJobs_notifications_fk_id_notifications');
CALL drop_foreign_key('scheduledJobs_notifications', 'scheduledJobs_notifications_fk_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_dataTables');
CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_tasks', 'scheduledJobs_tasks_fk_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_tasks', 'scheduledJobs_tasks_fk_id_tasks');
CALL drop_foreign_key('scheduledJobs_users', 'scheduledJobs_users_fk_id_users');
CALL drop_foreign_key('scheduledJobs_users', 'scheduledJobs_users_fk_scheduledJobs');
CALL drop_foreign_key('sections', 'sections_fk_id_styles');
CALL drop_foreign_key('sections_fields_translation', 'sections_fields_translation_fk_id_fields');
CALL drop_foreign_key('sections_fields_translation', 'sections_fields_translation_fk_id_genders');
CALL drop_foreign_key('sections_fields_translation', 'sections_fields_translation_fk_id_languages');
CALL drop_foreign_key('sections_fields_translation', 'sections_fields_translation_fk_id_sections');
CALL drop_foreign_key('sections_hierarchy', 'sections_hierarchy_fk_child');
CALL drop_foreign_key('sections_hierarchy', 'sections_hierarchy_fk_parent');
CALL drop_foreign_key('sections_navigation', 'sections_navigation_fk_child');
CALL drop_foreign_key('sections_navigation', 'sections_navigation_fk_id_pages');
CALL drop_foreign_key('sections_navigation', 'sections_navigation_fk_parent');
CALL drop_foreign_key('styles', 'styles_fk_id_group');
CALL drop_foreign_key('styles', 'styles_fk_id_type');
CALL drop_foreign_key('styles_fields', 'styles_fields_fk_id_fields');
CALL drop_foreign_key('styles_fields', 'styles_fields_fk_id_styles');
CALL drop_foreign_key('transactions', 'transactions_fk_id_transactionBy');
CALL drop_foreign_key('transactions', 'transactions_fk_id_transactionTypes');
CALL drop_foreign_key('transactions', 'transactions_fk_id_users');
CALL drop_foreign_key('users', 'fk_users_id_genders');
CALL drop_foreign_key('users', 'fk_users_id_languages');
CALL drop_foreign_key('users', 'fk_users_id_status');
CALL drop_foreign_key('user_activity', 'fk_user_activity_fk_id_type');
CALL drop_foreign_key('user_activity', 'fk_user_activity_fk_id_users');
CALL drop_foreign_key('users_groups', 'fk_users_groups_id_groups');
CALL drop_foreign_key('users_groups', 'fk_users_groups_id_users');
CALL drop_foreign_key('validation_codes', 'validation_codes_fk_id_users');
CALL drop_foreign_key('cmsPreferences', 'fk_cmsPreferences_language');

CALL drop_foreign_key('groups', 'groups_fk_id_group_types');
CALL drop_foreign_key('pages', 'pages_fk_id_pacgeAccessTypes');
CALL drop_foreign_key('users', 'FK_1483A5E93F6026C1');

-- drop indexes
CALL drop_index('acl_groups', 'id_pages');
CALL drop_index('acl_groups', 'id_groups');
-- CALL drop_index('api_routes', 'uniq_route_name_version');
-- CALL drop_index('api_routes', 'uniq_version_path');
CALL drop_index('assets', 'assets_fk_id_assetTypes');
CALL drop_index('assets', 'file_name');
CALL drop_index('cmsPreferences', 'fk_cmspreferences_language');
CALL drop_index('codes_groups', 'fk_id_groups');
CALL drop_index('codes_groups', 'IDX_9F20ED7677153098');
CALL drop_index('dataCells', 'idx_uploadCells_value');
CALL drop_index('dataCols', 'unique_name_id_dataTables');
CALL drop_index('dataRows', 'idx_uploadRows_timestamp');
CALL drop_index('dataRows', 'uploadRows_fk_id_actionTriggerTypes');
CALL drop_index('dataRows', 'uploadRows_fk_id_users');
CALL drop_index('dataTables', 'idx_uploadTables_name_timestamp');
CALL drop_index('dataTables', 'uploadTables_name');
CALL drop_index('fields', 'fields_name');
CALL drop_index('fields', 'id_type');
CALL drop_index('fieldType', 'fieldType_name');
CALL drop_index('groups', 'name');
CALL drop_index('hooks', 'hooks_fk_id_hookTypes');
CALL drop_index('hooks', 'name');
CALL drop_index('languages', 'language');
CALL drop_index('languages', 'locale');
CALL drop_index('libraries', 'name');
CALL drop_index('lookups', 'idx_lookups_type_code_lookup_code');
CALL drop_index('mailAttachments', 'mailAttachments_fk_id_mailQueue');
CALL drop_index('plugins', 'plugins_name');
CALL drop_index('refreshTokens', 'idx_token_hash');
CALL drop_index('scheduledJobs', 'scheduledJobs_fk_id_jobStatus');
CALL drop_index('scheduledJobs', 'scheduledJobs_fk_id_jobTypes');
CALL drop_index('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_iid_formActions');
CALL drop_index('scheduledJobs_formActions', 'scheduledJobs_formActions_id_dataRows');
CALL drop_index('scheduledJobs_formActions', 'IDX_AE5B5D0B8030BA52');
CALL drop_index('scheduledJobs_mailQueue', 'scheduledJobs_mailQueue_fk_id_mailQueue');
CALL drop_index('scheduledJobs_mailQueue', 'IDX_E560A18030BA52');
CALL drop_index('scheduledJobs_notifications', 'scheduledJobs_notifications_fk_id_notifications');
CALL drop_index('scheduledJobs_notifications', 'IDX_9879806C8030BA52');
CALL drop_index('sections', 'name');
CALL drop_index('styles_fields', 'id_fields');
CALL drop_index('styles_fields', 'id_styles');
CALL drop_index('transactions', 'idx_transactions_table_name');
CALL drop_index('users', 'id_genders');
CALL drop_index('users', 'id_languages');
CALL drop_index('users', 'id_status');
CALL drop_index('acl_users', 'id_pages');
CALL drop_index('acl_users', 'id_users');
CALL drop_index('dataCells', 'id_uploadCols');
CALL drop_index('dataCells', 'id_uploadRows');
CALL drop_index('dataCols', 'id_uploadTables');
CALL drop_index('dataRows', 'id_uploadTables');
CALL drop_index('groups', 'groups_fk_id_group_types');
CALL drop_index('pages', 'id_actions');
CALL drop_index('pages', 'id_navigation_section');
CALL drop_index('pages', 'id_type');
CALL drop_index('pages', 'keyword');
CALL drop_index('pages', 'pages_fk_id_pacgeAccessTypes');
CALL drop_index('pages', 'parent');
CALL drop_index('pages_fields', 'id_fields');
CALL drop_index('pages_fields', 'id_pages');
CALL drop_index('pages_fields_translation', 'id_fields');
CALL drop_index('pages_fields_translation', 'id_languages');
CALL drop_index('pages_fields_translation', 'id_pages');
CALL drop_index('pages_sections', 'id_pages');
CALL drop_index('pages_sections', 'id_sections');
CALL drop_index('pageType', 'pageType_name');
CALL drop_index('refreshtokens', 'idx_user_id');
CALL drop_index('scheduledJobs_tasks', 'scheduledJobs_tasks_fk_id_tasks');
CALL drop_index('scheduledJobs_users', 'scheduledJobs_users_fk_scheduledJobs');
CALL drop_index('sections', 'id_styles');
CALL drop_index('sections_fields_translation', 'id_fields');
CALL drop_index('sections_fields_translation', 'id_genders');
CALL drop_index('sections_fields_translation', 'id_languages');
CALL drop_index('sections_fields_translation', 'id_sections');
CALL drop_index('sections_hierarchy', 'child');
CALL drop_index('sections_hierarchy', 'parent');
CALL drop_index('sections_navigation', 'child');
CALL drop_index('sections_navigation', 'id_pages');
CALL drop_index('sections_navigation', 'parent');
CALL drop_index('styles', 'id_group');
CALL drop_index('styles', 'id_type');
CALL drop_index('styles', 'styles_name');
CALL drop_index('transactions', 'transactions_fk_id_transactionBy');
CALL drop_index('transactions', 'transactions_fk_id_transactionTypes');
CALL drop_index('transactions', 'transactions_fk_id_users');
CALL drop_index('users', 'email');
CALL drop_index('users', 'user_name');
CALL drop_index('user_activity', 'id_type');
CALL drop_index('user_activity', 'id_users');
CALL drop_index('users_groups', 'id_groups');
CALL drop_index('users_groups', 'id_users');
CALL drop_index('validation_codes', 'id_users');



ALTER TABLE acl_groups CHANGE id_groups id_groups INT NOT NULL, CHANGE id_pages id_pages INT NOT NULL;
ALTER TABLE acl_users CHANGE id_users id_users INT NOT NULL, CHANGE id_pages id_pages INT NOT NULL;
ALTER TABLE apiRequestLogs CHANGE request_params request_params LONGTEXT DEFAULT NULL, CHANGE request_headers request_headers LONGTEXT DEFAULT NULL, CHANGE response_data response_data LONGTEXT DEFAULT NULL, CHANGE error_message error_message LONGTEXT DEFAULT NULL;
-- ALTER TABLE api_routes CHANGE version version VARCHAR(10) NOT NULL, CHANGE params params JSON DEFAULT NULL;
ALTER TABLE assets CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_assetTypes id_assetTypes INT NOT NULL;
ALTER TABLE cmsPreferences CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE default_language_id default_language_id INT DEFAULT NULL, CHANGE anonymous_users anonymous_users INT DEFAULT 0 NOT NULL;
ALTER TABLE codes_groups CHANGE id_groups id_groups INT NOT NULL;
ALTER TABLE dataCells CHANGE id_dataRows id_dataRows INT NOT NULL, CHANGE id_dataCols id_dataCols INT NOT NULL;
ALTER TABLE dataCols CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_dataTables id_dataTables INT DEFAULT NULL;
ALTER TABLE dataRows CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_dataTables id_dataTables INT DEFAULT NULL, CHANGE timestamp timestamp DATETIME NOT NULL, CHANGE id_users id_users INT DEFAULT NULL, CHANGE id_actionTriggerTypes id_actionTriggerTypes INT DEFAULT NULL;
ALTER TABLE dataTables CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE timestamp timestamp DATETIME NOT NULL;
ALTER TABLE `fields` CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_type id_type INT NOT NULL, CHANGE display display TINYINT(1) NOT NULL;       
ALTER TABLE fieldType CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE formActions CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_formProjectActionTriggerTypes id_formProjectActionTriggerTypes INT NOT NULL, CHANGE config config LONGTEXT DEFAULT NULL, CHANGE id_dataTables id_dataTables INT DEFAULT NULL;
ALTER TABLE genders CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE `groups` CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_group_types id_group_types INT DEFAULT NULL, CHANGE requires_2fa requires_2fa TINYINT(1) NOT NULL;
ALTER TABLE hooks CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_hookTypes id_hookTypes INT NOT NULL, CHANGE priority priority INT DEFAULT 10 NOT NULL;
ALTER TABLE languages CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE locale locale VARCHAR(5) NOT NULL, CHANGE csv_separator csv_separator VARCHAR(1) NOT NULL;
ALTER TABLE libraries CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE logPerformance CHANGE id_user_activity id_user_activity INT NOT NULL;
ALTER TABLE lookups CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE mailAttachments CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_mailQueue id_mailQueue INT NOT NULL, CHANGE template_path template_path VARCHAR(1000) NOT NULL;
ALTER TABLE mailQueue CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE recipient_emails recipient_emails LONGTEXT NOT NULL, CHANGE is_html is_html TINYINT(1) NOT NULL;
ALTER TABLE notifications CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE pages CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_actions id_actions INT DEFAULT NULL, CHANGE id_navigation_section id_navigation_section INT DEFAULT NULL, CHANGE parent parent INT DEFAULT NULL, CHANGE id_type id_type INT NOT NULL, CHANGE protocol protocol VARCHAR(100) DEFAULT NULL COMMENT 'pipe separated list of HTTP Methods (GET|POST)', CHANGE id_pageAccessTypes id_pageAccessTypes INT DEFAULT NULL;
ALTER TABLE pages_fields CHANGE id_pages id_pages INT NOT NULL, CHANGE id_fields id_fields INT NOT NULL;
ALTER TABLE pages_fields_translation CHANGE id_pages id_pages INT NOT NULL, CHANGE id_fields id_fields INT NOT NULL, CHANGE id_languages id_languages INT NOT NULL;
ALTER TABLE pages_sections CHANGE id_pages id_pages INT NOT NULL, CHANGE id_sections id_sections INT NOT NULL;
ALTER TABLE pageType CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE pageType_fields CHANGE id_fields id_fields INT NOT NULL, CHANGE id_pageType id_pageType INT NOT NULL;
ALTER TABLE plugins CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE refreshTokens CHANGE id_users id_users INT NOT NULL;
ALTER TABLE scheduledJobs CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_jobTypes id_jobTypes INT NOT NULL, CHANGE id_jobStatus id_jobStatus INT NOT NULL, CHANGE date_create date_create DATETIME NOT NULL;
ALTER TABLE scheduledJobs_formActions CHANGE id_scheduledJobs id_scheduledJobs INT NOT NULL, CHANGE id_formActions id_formActions INT NOT NULL, CHANGE id_dataRows id_dataRows INT DEFAULT NULL;
ALTER TABLE scheduledJobs_mailQueue CHANGE id_scheduledJobs id_scheduledJobs INT NOT NULL, CHANGE id_mailQueue id_mailQueue INT NOT NULL;
ALTER TABLE scheduledJobs_notifications CHANGE id_notifications id_notifications INT NOT NULL, CHANGE id_scheduledJobs id_scheduledJobs INT NOT NULL;
ALTER TABLE scheduledJobs_reminders CHANGE id_scheduledJobs id_scheduledJobs INT NOT NULL, CHANGE id_dataTables id_dataTables INT NOT NULL;
ALTER TABLE scheduledJobs_tasks CHANGE id_tasks id_tasks INT NOT NULL, CHANGE id_scheduledJobs id_scheduledJobs INT NOT NULL;
ALTER TABLE scheduledJobs_users CHANGE id_users id_users INT NOT NULL, CHANGE id_scheduledJobs id_scheduledJobs INT NOT NULL;
ALTER TABLE sections CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_styles id_styles INT NOT NULL;
ALTER TABLE sections_fields_translation CHANGE id_sections id_sections INT NOT NULL, CHANGE id_fields id_fields INT NOT NULL, CHANGE id_languages id_languages INT NOT NULL, CHANGE id_genders id_genders INT NOT NULL;
ALTER TABLE sections_hierarchy CHANGE parent parent INT NOT NULL, CHANGE child child INT NOT NULL;
ALTER TABLE sections_navigation CHANGE parent parent INT NOT NULL, CHANGE child child INT NOT NULL, CHANGE id_pages id_pages INT NOT NULL;
ALTER TABLE styleGroup CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE styles CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE styles CHANGE id_type id_type INT NOT NULL, CHANGE id_group id_group INT NOT NULL;
ALTER TABLE styles_fields CHANGE id_styles id_styles INT NOT NULL;
ALTER TABLE styles_fields CHANGE id_fields id_fields INT NOT NULL, CHANGE disabled disabled TINYINT(1) NOT NULL, CHANGE hidden hidden INT DEFAULT NULL;
ALTER TABLE tasks CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE transactions CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_users id_users INT DEFAULT NULL, CHANGE transaction_time transaction_time DATETIME NOT NULL, CHANGE id_transactionTypes id_transactionTypes INT DEFAULT NULL, CHANGE id_transactionBy id_transactionBy INT DEFAULT NULL, CHANGE id_table_name id_table_name INT DEFAULT NULL, CHANGE transaction_log transaction_log LONGTEXT DEFAULT NULL;
ALTER TABLE users CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_genders id_genders INT DEFAULT NULL, CHANGE id_status id_status INT DEFAULT 1, CHANGE id_languages id_languages INT DEFAULT NULL, CHANGE id_userTypes id_userTypes INT NOT NULL;
ALTER TABLE user_activity CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_users id_users INT NOT NULL, CHANGE id_type id_type INT NOT NULL, CHANGE timestamp timestamp DATETIME NOT NULL;
ALTER TABLE users_2fa_codes CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_users id_users INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE is_used is_used TINYINT(1) NOT NULL;
ALTER TABLE users_groups CHANGE id_users id_users INT NOT NULL, CHANGE id_groups id_groups INT NOT NULL;
ALTER TABLE validation_codes ADD id_groups INT DEFAULT NULL, CHANGE id_users id_users INT DEFAULT NULL, CHANGE created created DATETIME NOT NULL;  
ALTER TABLE version CHANGE id id INT AUTO_INCREMENT NOT NULL;

-- add foreign keys
CALL add_foreign_key('acl_groups', 'FK_AB370E20D65A8C9D', 'id_groups', '`groups`(id)');
CALL add_foreign_key('acl_groups', 'FK_AB370E20CEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('acl_users', 'FK_901AE856FA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('acl_users', 'FK_901AE856CEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('dataCells', 'FK_726A5F25F3854F45', 'id_dataRows', 'dataRows(id)');
CALL add_foreign_key('dataCells', 'FK_726A5F25B216B425', 'id_dataCols', 'dataCols(id)');
CALL add_foreign_key('dataCols', 'FK_E2CD58B0E2E6A7C3', 'id_dataTables', 'dataTables(id)');
CALL add_foreign_key('dataRows', 'FK_A35EA3D0E2E6A7C3', 'id_dataTables', 'dataTables(id)');

-- add indexes
CALL add_index('acl_groups', 'IDX_AB370E20D65A8C9D', 'id_groups', FALSE);
CALL add_index('acl_groups', 'IDX_AB370E20CEF1A445', 'id_pages', FALSE);
CALL add_index('acl_users', 'IDX_901AE856FA06E4D9', 'id_users', FALSE);
CALL add_index('acl_users', 'IDX_901AE856CEF1A445', 'id_pages', FALSE);
-- CALL add_index('api_routes', 'UNIQ_B4228533F3667F83', 'route_name', TRUE);
CALL add_index('assets', 'UNIQ_79D17D8ED7DF1668', 'file_name', TRUE);
CALL add_index('cmsPreferences', 'IDX_3F26A2DF5602A942', 'default_language_id', FALSE);
CALL add_index('dataCells', 'IDX_726A5F25F3854F45', 'id_dataRows', FALSE);
CALL add_index('dataCells', 'IDX_726A5F25B216B425', 'id_dataCols', FALSE);
CALL add_index('dataCols', 'IDX_E2CD58B0E2E6A7C3', 'id_dataTables', FALSE);
CALL add_index('dataRows', 'IDX_A35EA3D0E2E6A7C3', 'id_dataTables', FALSE);

CALL add_index('styles_fields', 'IDX_4F23ED26906D4F18', 'id_styles', FALSE);
CALL add_index('styles_fields', 'IDX_4F23ED2658D25665', 'id_fields', FALSE);


-- add more foreign keys
CALL add_foreign_key('fields', 'FK_7EE5E388FF2309B7', 'id_type', 'fieldType(id)');
CALL add_foreign_key('formActions', 'FK_3128FB5E8A8FCE9D', 'id_formProjectActionTriggerTypes', 'lookups(id)');
CALL add_foreign_key('formActions', 'FK_3128FB5EE2E6A7C3', 'id_dataTables', 'dataTables(id)');
CALL add_foreign_key('logPerformance', 'FK_6D164595F2D13C3F', 'id_user_activity', 'user_activity(id)');

-- more indexes
CALL add_index('formActions', 'IDX_3128FB5E8A8FCE9D', 'id_formProjectActionTriggerTypes', FALSE);
CALL add_index('formActions', 'IDX_3128FB5EE2E6A7C3', 'id_dataTables', FALSE);

-- foreign keys for pages and related tables
CALL add_foreign_key('pages', 'FK_2074E575DBD5589F', 'id_actions', 'lookups(id)');
CALL add_foreign_key('pages', 'FK_2074E575E8D3C633', 'id_navigation_section', 'sections(id)');
CALL add_foreign_key('pages', 'FK_2074E5753D8E604F', 'parent', 'pages(id)');
CALL add_foreign_key('pages', 'FK_2074E5757FE4B2B', 'id_type', 'pageType(id)');
CALL add_foreign_key('pages_fields', 'FK_D36F9887CEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('pages_fields', 'FK_D36F988758D25665', 'id_fields', 'fields(id)');
CALL add_foreign_key('pages_fields_translation', 'FK_903943EECEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('pages_fields_translation', 'FK_903943EE58D25665', 'id_fields', 'fields(id)');
CALL add_foreign_key('pages_fields_translation', 'FK_903943EE20E4EF5E', 'id_languages', 'languages(id)');
CALL add_foreign_key('pages_sections', 'FK_6BD95A69CEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('pages_sections', 'FK_6BD95A697B4DAF0D', 'id_sections', 'sections(id)');
CALL add_foreign_key('pageType_fields', 'FK_B305C681FDE305E9', 'id_pageType', 'pageType(id)');
CALL add_foreign_key('pageType_fields', 'FK_B305C68158D25665', 'id_fields', 'fields(id)');

-- indexes for pages and related tables
CALL add_index('pages', 'UNIQ_2074E5755A93713B', 'keyword', TRUE);
CALL add_index('pages', 'IDX_2074E575DBD5589F', 'id_actions', FALSE);
CALL add_index('pages', 'IDX_2074E575E8D3C633', 'id_navigation_section', FALSE);
CALL add_index('pages', 'IDX_2074E5753D8E604F', 'parent', FALSE);
CALL add_index('pages', 'IDX_2074E5757FE4B2B', 'id_type', FALSE);
CALL add_index('pages', 'IDX_2074E57534643D90', 'id_pageAccessTypes', FALSE);
CALL add_index('pages_fields', 'IDX_D36F9887CEF1A445', 'id_pages', FALSE);
CALL add_index('pages_fields', 'IDX_D36F988758D25665', 'id_fields', FALSE);
CALL add_index('pages_fields_translation', 'IDX_903943EECEF1A445', 'id_pages', FALSE);
CALL add_index('pages_fields_translation', 'IDX_903943EE58D25665', 'id_fields', FALSE);
CALL add_index('pages_fields_translation', 'IDX_903943EE20E4EF5E', 'id_languages', FALSE);
CALL add_index('pages_sections', 'IDX_6BD95A69CEF1A445', 'id_pages', FALSE);
CALL add_index('pages_sections', 'IDX_6BD95A697B4DAF0D', 'id_sections', FALSE);
CALL add_index('pageType', 'UNIQ_AD38E97C5E237E06', 'name', TRUE);
CALL add_index('pageType_fields', 'IDX_B305C68158D25665', 'id_fields', FALSE);
CALL add_index('refreshTokens', 'IDX_BFB6788AFA06E4D9', 'id_users', FALSE);

-- add foreign keys for scheduled jobs
CALL add_foreign_key('scheduledJobs_reminders', 'FK_23156A608030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');
CALL add_foreign_key('scheduledJobs_reminders', 'FK_23156A60E2E6A7C3', 'id_dataTables', 'dataTables(id)');
CALL add_foreign_key('scheduledJobs_tasks', 'FK_96A54FA88030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');
CALL add_foreign_key('scheduledJobs_tasks', 'FK_96A54FA8BEDD24A7', 'id_tasks', 'tasks(id)');
CALL add_foreign_key('scheduledJobs_users', 'FK_D27E8FD6FA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('scheduledJobs_users', 'FK_D27E8FD68030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');

-- add foreign keys for sections
CALL add_foreign_key('sections', 'FK_2B964398906D4F18', 'id_styles', 'styles(id)');
CALL add_foreign_key('sections_fields_translation', 'FK_EC5054157B4DAF0D', 'id_sections', 'sections(id)');
CALL add_foreign_key('sections_fields_translation', 'FK_EC50541558D25665', 'id_fields', 'fields(id)');
CALL add_foreign_key('sections_fields_translation', 'FK_EC50541520E4EF5E', 'id_languages', 'languages(id)');
CALL add_foreign_key('sections_fields_translation', 'FK_EC5054155D8601CD', 'id_genders', 'genders(id)');
CALL add_foreign_key('sections_hierarchy', 'FK_A6D0AE7C3D8E604F', 'parent', 'sections(id)');
CALL add_foreign_key('sections_hierarchy', 'FK_A6D0AE7C22B35429', 'child', 'sections(id)');
CALL add_foreign_key('sections_navigation', 'FK_21BBDC413D8E604F', 'parent', 'sections(id)');
CALL add_foreign_key('sections_navigation', 'FK_21BBDC4122B35429', 'child', 'sections(id)');
CALL add_foreign_key('sections_navigation', 'FK_21BBDC41CEF1A445', 'id_pages', 'pages(id)');

-- add foreign keys for styles
CALL add_foreign_key('styles', 'FK_B65AFAF57FE4B2B', 'id_type', 'lookups(id)');
CALL add_foreign_key('styles', 'FK_B65AFAF5834505F5', 'id_group', 'styleGroup(id)');
CALL add_foreign_key('styles_fields', 'FK_4F23ED261DF44B12', 'id_fields', 'fields(id)');
CALL add_foreign_key('styles_fields', 'FK_4F23ED26D54B526F', 'id_styles', 'styles(id)');

-- add foreign keys for transactions and users
CALL add_foreign_key('transactions', 'FK_EAA81A4CC41DBD5F', 'id_transactionTypes', 'lookups(id)');
CALL add_foreign_key('transactions', 'FK_EAA81A4CFC2E5563', 'id_transactionBy', 'lookups(id)');
CALL add_foreign_key('transactions', 'FK_EAA81A4CFA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('user_activity', 'FK_4CF9ED5AFA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('user_activity', 'FK_4CF9ED5A7FE4B2B', 'id_type', 'lookups(id)');
CALL add_foreign_key('users_groups', 'FK_FF8AB7E0FA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('users_groups', 'FK_FF8AB7E0D65A8C9D', 'id_groups', '`groups`(id)');
CALL add_foreign_key('validation_codes', 'FK_DBEC45EFA06E4D9', 'id_users', 'users(id)');

-- add indexes
CALL add_index('scheduledJobs_reminders', 'IDX_23156A60E2E6A7C3', 'id_dataTables', FALSE);
CALL add_index('scheduledJobs_tasks', 'IDX_96A54FA8BEDD24A7', 'id_tasks', FALSE);
CALL add_index('scheduledJobs_users', 'IDX_D27E8FD68030BA52', 'id_scheduledJobs', FALSE);
CALL add_index('sections', 'IDX_2B964398906D4F18', 'id_styles', FALSE);
CALL add_index('sections_fields_translation', 'IDX_EC5054157B4DAF0D', 'id_sections', FALSE);
CALL add_index('sections_fields_translation', 'IDX_EC50541558D25665', 'id_fields', FALSE);
CALL add_index('sections_fields_translation', 'IDX_EC50541520E4EF5E', 'id_languages', FALSE);
CALL add_index('sections_fields_translation', 'IDX_EC5054155D8601CD', 'id_genders', FALSE);
CALL add_index('sections_hierarchy', 'IDX_A6D0AE7C3D8E604F', 'parent', FALSE);
CALL add_index('sections_hierarchy', 'IDX_A6D0AE7C22B35429', 'child', FALSE);
CALL add_index('sections_navigation', 'IDX_21BBDC413D8E604F', 'parent', FALSE);
CALL add_index('sections_navigation', 'IDX_21BBDC4122B35429', 'child', FALSE);
CALL add_index('sections_navigation', 'IDX_21BBDC41CEF1A445', 'id_pages', FALSE);
CALL add_index('styles', 'UNIQ_B65AFAF55E237E06', 'name', TRUE);
CALL add_index('styles', 'IDX_B65AFAF57FE4B2B', 'id_type', FALSE);
CALL add_index('styles', 'IDX_B65AFAF5834505F5', 'id_group', FALSE);
CALL add_index('transactions', 'IDX_EAA81A4CC41DBD5F', 'id_transactionTypes', FALSE);
CALL add_index('transactions', 'IDX_EAA81A4CFC2E5563', 'id_transactionBy', FALSE);
CALL add_index('transactions', 'IDX_EAA81A4CFA06E4D9', 'id_users', FALSE);
CALL add_index('users', 'UNIQ_1483A5E9E7927C74', 'email', TRUE);
CALL add_index('users', 'UNIQ_1483A5E924A232CF', 'user_name', TRUE);
CALL add_index('user_activity', 'IDX_4CF9ED5AFA06E4D9', 'id_users', FALSE);
CALL add_index('user_activity', 'IDX_4CF9ED5A7FE4B2B', 'id_type', FALSE);
CALL add_index('users_2fa_codes', 'IDX_65A1E404FA06E4D9', 'id_users', FALSE);
CALL add_index('users_groups', 'IDX_FF8AB7E0FA06E4D9', 'id_users', FALSE);
CALL add_index('users_groups', 'IDX_FF8AB7E0D65A8C9D', 'id_groups', FALSE);
CALL add_index('validation_codes', 'IDX_DBEC45EFA06E4D9', 'id_users', FALSE);
CALL add_index('validation_codes', 'IDX_DBEC45ED65A8C9D', 'id_groups', FALSE);
CALL add_index('users', 'IDX_1483A5E93F6026C1', 'id_userTypes', FALSE);
CALL add_index('fields', 'IDX_7EE5E3887FE4B2B', 'id_type', FALSE);


CALL add_foreign_key('users', 'FK_1483A5E93F6026C1', 'id_userTypes', 'lookups(id)');
CALL add_foreign_key('cmsPreferences', 'FK_3F26A2DF5602A942', 'default_language_id', 'languages(id)');
CALL add_foreign_key('pages', 'FK_2074E57534643D90', 'id_pageAccessTypes', 'lookups (id)');
CALL add_foreign_key('sections', 'FK_2B964398906D4F18', 'id_styles', 'styles (id)');
CALL add_foreign_key('validation_codes', 'FK_DBEC45ED65A8C9D', 'id_groups', '`groups` (id)');

CALL add_foreign_key('codes_groups', 'FK_9F20ED7677153098', 'code', 'validation_codes(code)');
CALL add_foreign_key('codes_groups', 'FK_9F20ED76D65A8C9D', 'id_groups', '`groups`(id)');
CALL add_index('codes_groups', 'IDX_9F20ED7677153098', 'code', FALSE);
CALL add_index('codes_groups', 'IDX_9F20ED76D65A8C9D', 'id_groups', FALSE);

CALL add_foreign_key('mailAttachments', 'FK_76D06F85CE570F32', 'id_mailQueue', 'mailQueue(id)');
CALL add_index('mailAttachments', 'IDX_76D06F85CE570F32', 'id_mailQueue', FALSE);

CALL add_foreign_key('scheduledJobs', 'FK_3E186B3777FD8DE1', 'id_jobStatus', 'lookups(id)');
CALL add_foreign_key('scheduledJobs', 'FK_3E186B3712C34CFB', 'id_jobTypes', 'lookups(id)');
CALL add_index('scheduledJobs', 'IDX_3E186B3777FD8DE1', 'id_jobStatus', FALSE);
CALL add_index('scheduledJobs', 'IDX_3E186B3712C34CFB', 'id_jobTypes', FALSE);

CALL add_foreign_key('scheduledJobs_formActions', 'FK_AE5B5D0B8030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');
CALL add_foreign_key('scheduledJobs_formActions', 'FK_AE5B5D0B293A36A9', 'id_formActions', 'formActions(id)');
CALL add_foreign_key('scheduledJobs_formActions', 'FK_AE5B5D0BF3854F45', 'id_dataRows', 'dataRows(id)');
CALL add_index('scheduledJobs_formActions', 'IDX_AE5B5D0B8030BA52', 'id_scheduledJobs', FALSE);
CALL add_index('scheduledJobs_formActions', 'IDX_AE5B5D0B293A36A9', 'id_formActions', FALSE);
CALL add_index('scheduledJobs_formActions', 'IDX_AE5B5D0BF3854F45', 'id_dataRows', FALSE);

CALL add_foreign_key('scheduledJobs_mailQueue', 'FK_E560A18030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');
CALL add_foreign_key('scheduledJobs_mailQueue', 'FK_E560A1CE570F32', 'id_mailQueue', 'mailQueue(id)');
CALL add_index('scheduledJobs_mailQueue', 'IDX_E560A18030BA52', 'id_scheduledJobs', FALSE);
CALL add_index('scheduledJobs_mailQueue', 'IDX_E560A1CE570F32', 'id_mailQueue', FALSE);

CALL add_foreign_key('scheduledJobs_notifications', 'FK_9879806C8030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');
CALL add_foreign_key('scheduledJobs_notifications', 'FK_9879806CDE2861B6', 'id_notifications', 'notifications(id)');
CALL add_index('scheduledJobs_notifications', 'IDX_9879806C8030BA52', 'id_scheduledJobs', FALSE);
CALL add_index('scheduledJobs_notifications', 'IDX_9879806CDE2861B6', 'id_notifications', FALSE);



CALL add_foreign_key('assets', 'FK_79D17D8E843A9330', 'id_assetTypes', 'lookups(id)');
CALL add_index('assets', 'IDX_79D17D8E843A9330', 'id_assetTypes', FALSE);

CALL add_foreign_key('users', 'FK_1483A5E95D8601CD', 'id_genders', 'genders(id)');
CALL add_index('users', 'IDX_1483A5E95D8601CD', 'id_genders', FALSE);
CALL add_foreign_key('users', 'FK_1483A5E95D37D0F1', 'id_status', 'lookups(id)');
CALL add_index('users', 'IDX_1483A5E95D37D0F1', 'id_status', FALSE);
CALL add_foreign_key('users', 'FK_1483A5E920E4EF5E', 'id_languages', 'languages(id)');
CALL add_index('users', 'IDX_1483A5E920E4EF5E', 'id_languages', FALSE);


SET @user_type_user_id = (
  SELECT id
    FROM lookups
   WHERE type_code    = 'userTypes'
     AND lookup_value = 'user'
);

-- 2) build and run an ALTER TABLE to set the DEFAULT
SET @sql = CONCAT(
  'ALTER TABLE users ',
  'MODIFY id_userTypes INT NOT NULL ',
  'DEFAULT ', @user_type_user_id, ';'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------- DOCTRINE ------------------------------------------------------------------------

-- --------------------------- API ROUTES ------------------------------------------------------------------------
-- shoudl be here
-- --------------------------- API ROUTES ------------------------------------------------------------------------


CALL add_unique_key('lookups', 'uniq_type_lookup', 'type_code,lookup_code');
CALL add_unique_key('languages', 'UNIQ_A0D153794180C698', 'locale');
CALL add_unique_key('fields', 'UNIQ_7EE5E3885E237E06', 'name');
CALL add_unique_key('fieldType', 'UNIQ_C1760DF55E237E06', 'name');

UPDATE styleGroup
SET position = 0, `description` = 'Reserved for internal system styles. Modifying or using these styles externally may cause unexpected behavior.'
WHERE `name` = 'intern';

UPDATE pages
SET id_actions = (SELECT id FROM lookups WHERE type_code = 'pageActions' AND lookup_code = 'sections')
WHERE keyword IN ('profile-link', 'logout');

UPDATE pages
SET is_system = 1
WHERE keyword IN ('logout', 'profile-link');

UPDATE pages
SET url = '/missing'
WHERE keyword = 'missing';

UPDATE pages
SET url = '/no-access'
WHERE keyword = 'no_access';

UPDATE pages
SET url = '/no-access-guest'
WHERE keyword = 'no_access_guest';

UPDATE pages
SET url = '/profile-link'
WHERE keyword = 'profile-link';

DELETE FROM pages 
WHERE keyword IN ("admin-link",
"cmsSelect",
"cmsInsert",
"cmsUpdate",
"cmsDelete",
"userSelect",
"userInsert",
"userUpdate",
"userDelete",
"groupSelect",
"groupInsert",
"groupUpdate",
"groupDelete",
"export",
"exportData",
"assetSelect",
"assetInsert",
"assetUpdate",
"assetDelete",
"userGenCode",
"email",
"exportDelete",
"groupUpdateCustom",
"data",
"cmsPreferences",
"cmsPreferencesUpdate",
"language",
"moduleScheduledJobs",
"moduleScheduledJobsCompose",
"cmsExport",
"cmsImport",
"moduleFormsActions",
"moduleFormsAction",
"ajax_get_groups",
"ajax_get_table_names",
"ajax_get_table_fields",
"ajax_search_anchor_section",
"ajax_search_data_source",
"ajax_search_user_chat",
"ajax_set_data_filter",
"ajax_set_user_language",
"ajax_get_lookups",
"ajax_get_languages",
"sh_globals",
"sh_modules",
"ajax_get_assets",
"moduleScheduledJobsCalendar",
"dataDelete",
"cms-api_v1_admin_get_access",
"cms-api_v1_admin_get_pages",
"cms-api_v1_admin_page_fields",
"cms-api_v1_admin_page_sections",
"cms-api_v1_content_get_all_routes",
"cms-api_v1_content_get_page",
"cms-api_v1_content_put_page",
"cms-api_v1_auth_login",
"cms-api_v1_auth_two-factor-verify",
"cms-api_v1_auth_refresh_token",
"cms-api_v1_auth_logout",
"callback");

CALL drop_foreign_key('pages', 'FK_2074E575E8D3C633');
CALL drop_foreign_key('pages', 'FK_2074E575DBD5589F');
CALL drop_table_column('pages', 'protocol');
CALL drop_table_column('pages', 'id_actions');
CALL drop_table_column('pages', 'id_navigation_section');

DELETE FROM lookups
WHERE type_code = 'pageActions';

-- -------------------------- add configuration pages ----------------------------------------------------------

-- add css config page
INSERT IGNORE INTO `pageType` (`name`) VALUES ('global_css');

INSERT IGNORE INTO `pages` (`keyword`, `url`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES ('sh-global-css', NULL, NULL, 0, 0, NULL, (SELECT id FROM pageType WHERE `name` = 'global_css' LIMIT 1), (SELECT id FROM lookups WHERE lookup_code = 'web'));
SET @id_page_values = (SELECT id FROM pages WHERE keyword = 'sh-global-css');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_values, '1', '0', '1', '0');

-- add new fieldType css
INSERT IGNORE INTO `fieldType` (`name`, `position`) VALUES ('css', '15');

-- add new filed `custom_css` from type JSON
INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`) VALUES ('custom_css', get_field_type_id('css'), '0');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'global_css' LIMIT 1), get_field_id('custom_css'), NULL, 'Enter your own CSS rules in this field to customize the appearance of your pages, elements, or components. Any CSS classes or styles you define here will be automatically loaded on your site. You can then use the class names you define in your content, layouts, or widgets.');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'global_css' LIMIT 1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'global_css' LIMIT 1), get_field_id('description'), NULL, 'Page description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000002', 'Custom CSS');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000003', 'Custom CSS');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('description'), '0000000002', 'Geben Sie in diesem Feld Ihre eigenen CSS-Regeln ein, um das Erscheinungsbild Ihrer Seiten, Elemente oder Komponenten individuell anzupassen. Alle hier definierten CSS-Klassen oder -Stile werden automatisch auf Ihrer Website geladen. Anschließend können Sie die von Ihnen vergebenen Klassennamen in Ihren Inhalten, Layouts oder Widgets verwenden.');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('description'), '0000000003', 'Enter your own CSS rules in this field to customize the appearance of your pages, elements, or components. Any CSS classes or styles you define here will be automatically loaded on your site. You can then use the class names you define in your content, layouts, or widgets.');

-- add page type global_values
INSERT IGNORE INTO `pageType` (`name`) VALUES ('global_values');

-- add translation page
INSERT IGNORE INTO `pages` (`keyword`, `url`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES ('sh-global-values', NULL, NULL, 0, 10, NULL, (SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_values = (SELECT id FROM pages WHERE keyword = 'sh-global-values');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_values, '1', '0', '1', '0');

-- add new filed `selfhelpTranslations` from type JSON
INSERT IGNORE INTO `fields` (`name`, `id_type`, `display`) VALUES ('global_values', get_field_type_id('json'), '1');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 1), get_field_id('global_values'), NULL, 'JSON object where can be defined global translation keys and use the key to load the proper translation based on the selected language. A key is accessed by {{key_name}}, and this will be replaced with the value for the selected language');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 1), get_field_id('description'), NULL, 'Page description');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000002', 'Global Values');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000003', 'Global Values');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('description'), '0000000002', 'JSON object where can be defined global translation keys and use the key to load the proper translation based on the selected language. A key is accessed by {{key_name}}, and this will be replaced with the value for the selected language');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('description'), '0000000003', 'JSON object where can be defined global translation keys and use the key to load the proper translation based on the selected language. A key is accessed by {{key_name}}, and this will be replaced with the value for the selected language');



-- -------------------------- add configuration pages ----------------------------------------------------------

-- drop column gender from translations
CALL drop_foreign_key('sections_fields_translation', 'FK_EC5054155D8601CD');
CALL drop_table_column('sections_fields_translation', 'id_genders');




-- Example of a v2 API route (for future use)
-- INSERT IGNORE INTO `api_routes` (`route_name`,`version`,`path`,`controller`,`methods`,`requirements`,`params`) VALUES
-- ('auth_login','v2','/auth/login','App\\Controller\\Api\\V2\\Auth\\AuthController::login','POST',NULL,JSON_OBJECT('email',JSON_OBJECT('in','body','required',true),'password',JSON_OBJECT('in','body','required',true)));


-- create page in transaction and give acl permisions to the user and to admin group, aslo to the therapsi and subject group
-- add edit page
-- add delete page
-- add tests liked to them


-- shoudl remove is_fluid from container style
-- reowork all form data to use drop down for table selection. First the table should be registered by the user. Assign ACL to these dataTables.
-- remove the gender
-- pages should be moved to routes, then create link to lages, then link to pages_configurations (something else), refactor types, actions and all. Check this sql
-- check the cache page
-- remove page protocol field; it will not be used in the new version

-- core pages: users,
-- groups,
-- export,
-- assets,
-- data,
-- cms_preferences,
-- languages,
-- scheduled_jobs,
-- actions,
-- cache,
-- clockwork


-- Page translation functionality enhancement
-- Changed from locale-based to language_id-based parameters to reduce SQL queries
-- Updated controllers and services to accept language_id directly instead of converting from locale
-- Removed unnecessary locale-to-language_id conversions in PageService::determineLanguageId()
-- Enhanced login and 2FA responses to include language_id and language_locale
-- Added proper JSON schema validation for language setting endpoint

-- User Management System Updates
-- Adding support for improved user management functionality

-- Create view for user management (replacing the old view_users)
DROP VIEW IF EXISTS `view_users_management`;
CREATE VIEW `view_users_management` AS
SELECT
    u.id AS id,
    u.email AS email,
    u.name AS name,
    u.user_name AS user_name,
    IFNULL(
        CONCAT(
            u.last_login,
            ' (',
            TO_DAYS(NOW()) - TO_DAYS(u.last_login),
            ' days ago)'
        ),
        'never'
    ) AS last_login,
    usl.lookup_value AS status,
    usl.lookup_description AS status_description,
    u.blocked AS blocked,
    (CASE
        WHEN u.name = 'admin' THEN 'admin'
        WHEN u.name = 'tpf' THEN 'tpf'
        ELSE IFNULL(vc.code, '-')
    END) AS validation_code,
    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR '; ') AS `groups`,
    GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR '; ') AS `roles`,
    COALESCE(ua.activity_count, 0) AS user_activity,
    COALESCE(ua.distinct_url_count, 0) AS distinct_url_count,
    u.intern AS intern,
    u.id_userTypes AS id_userTypes,
    ut.lookup_code AS user_type_code,
    ut.lookup_value AS user_type,
    u.id_genders AS id_genders,
    u.id_languages AS id_languages,
    u.id_status AS id_status
FROM users u
LEFT JOIN lookups usl ON usl.id = u.id_status AND usl.type_code = 'userStatus'
LEFT JOIN lookups ut ON ut.id = u.id_userTypes AND ut.type_code = 'userTypes'
LEFT JOIN users_groups ug ON ug.id_users = u.id
LEFT JOIN `groups` g ON g.id = ug.id_groups
LEFT JOIN users_roles ur ON ur.id_users = u.id
LEFT JOIN `roles` r ON r.id = ur.id_roles
LEFT JOIN validation_codes vc ON u.id = vc.id_users AND vc.consumed IS NULL
LEFT JOIN (
    SELECT
        ua.id_users AS id_users,
        COUNT(*) AS activity_count,
        COUNT(DISTINCT CASE WHEN ua.id_type = 1 THEN ua.url END) AS distinct_url_count
    FROM user_activity ua
    GROUP BY ua.id_users
) AS ua ON ua.id_users = u.id
WHERE u.intern <> 1 AND u.id_status > 0
GROUP BY
    u.id, u.email, u.name, u.user_name, u.last_login,
    usl.lookup_value, usl.lookup_description, u.blocked,
    vc.code, ua.activity_count, ua.distinct_url_count,
    u.intern, u.id_userTypes, ut.lookup_code, ut.lookup_value,
    u.id_genders, u.id_languages, u.id_status
ORDER BY u.email;

-- remove field `children`
DELETE FROM `fields`
WHERE `name` = 'children';

-- add column `title` for fileds in the style
CALL add_table_column('styles_fields', 'title', 'VARCHAR(100) NOT NULL');

UPDATE styles_fields
SET title = 'CSS'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Email'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('label_user');

UPDATE styles_fields
SET title = 'Password'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('label_pw');

UPDATE styles_fields
SET title = 'Login Button'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('label_login');

UPDATE styles_fields
SET title = 'Reset Link'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('label_pw_reset');

UPDATE styles_fields
SET title = 'Error Message'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('alert_fail');

UPDATE styles_fields
SET title = 'Login Title'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('login_title');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('login') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Alert Fail'
WHERE id_styles = get_style_id('profile') AND id_fields = get_field_id('alert_fail');

UPDATE styles_fields
SET title = 'Alert Del Fail'
WHERE id_styles = get_style_id('profile') AND id_fields = get_field_id('alert_del_fail');

UPDATE styles_fields
SET title = 'Alert Del Success'
WHERE id_styles = get_style_id('profile') AND id_fields = get_field_id('alert_del_success');

UPDATE styles_fields
SET title = 'Alert Success'
WHERE id_styles = get_style_id('profile') AND id_fields = get_field_id('alert_success');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('container') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Is Fluid'
WHERE id_styles = get_style_id('container') AND id_fields = get_field_id('is_fluid');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('container') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('container') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('container') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Export PDF'
WHERE id_styles = get_style_id('container') AND id_fields = get_field_id('export_pdf');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('container') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('jumbotron') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('jumbotron') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('jumbotron') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('jumbotron') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('jumbotron') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Heading Level'
WHERE id_styles = get_style_id('heading') AND id_fields = get_field_id('level');

UPDATE styles_fields
SET title = 'Title'
WHERE id_styles = get_style_id('heading') AND id_fields = get_field_id('title');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('heading') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('heading') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('heading') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('heading') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('heading') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('markdown') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Markdown Text'
WHERE id_styles = get_style_id('markdown') AND id_fields = get_field_id('text_md');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('markdown') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('markdown') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('markdown') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('markdown') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('markdownInline') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Inline Markdown'
WHERE id_styles = get_style_id('markdownInline') AND id_fields = get_field_id('text_md_inline');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('markdownInline') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('markdownInline') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('markdownInline') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('markdownInline') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('url');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Cancel Label'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('label_cancel');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Confirmation Title'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('confirmation_title');

UPDATE styles_fields
SET title = 'Confirmation Continue'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('confirmation_continue');

UPDATE styles_fields
SET title = 'Confirmation Message'
WHERE id_styles = get_style_id('button') AND id_fields = get_field_id('confirmation_message');

UPDATE styles_fields
SET title = 'Password Label'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('label_pw');

UPDATE styles_fields
SET title = 'Login Label'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('label_login');

UPDATE styles_fields
SET title = 'Error Message'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('alert_fail');

UPDATE styles_fields
SET title = 'Password'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('label_pw_confirm');

UPDATE styles_fields
SET title = 'Title'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('title');

UPDATE styles_fields
SET title = 'Subtitle'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('subtitle');

UPDATE styles_fields
SET title = 'Alert Success'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('alert_success');

UPDATE styles_fields
SET title = 'Username'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('label_name');

UPDATE styles_fields
SET title = 'Username'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('name_placeholder');

UPDATE styles_fields
SET title = 'Username'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('name_description');

UPDATE styles_fields
SET title = 'Label Gender'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('label_gender');

UPDATE styles_fields
SET title = 'Gender Male'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('gender_male');

UPDATE styles_fields
SET title = 'Gender Female'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('gender_female');

UPDATE styles_fields
SET title = 'Label Activate'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('label_activate');

UPDATE styles_fields
SET title = 'Password'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('pw_placeholder');

UPDATE styles_fields
SET title = 'Success'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('success');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Gender Divers'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('gender_divers');

UPDATE styles_fields
SET title = 'Page Keyword'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('page_keyword');

UPDATE styles_fields
SET title = 'Value Gender'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('value_gender');

UPDATE styles_fields
SET title = 'Username'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('value_name');

UPDATE styles_fields
SET title = 'Anonymous User Name Description'
WHERE id_styles = get_style_id('validate') AND id_fields = get_field_id('anonymous_user_name_description');

UPDATE styles_fields
SET title = 'Error Message'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('alert_fail');

UPDATE styles_fields
SET title = 'Alt'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('alt');

UPDATE styles_fields
SET title = 'Title Prefix'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('title_prefix');

UPDATE styles_fields
SET title = 'Experimenter'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('experimenter');

UPDATE styles_fields
SET title = 'Subjects'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('subjects');

UPDATE styles_fields
SET title = 'Label Submit'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('label_submit');

UPDATE styles_fields
SET title = 'Label Lobby'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('label_lobby');

UPDATE styles_fields
SET title = 'Label New'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('label_new');

UPDATE styles_fields
SET title = 'Email User'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('email_user');

UPDATE styles_fields
SET title = 'Subject User'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('subject_user');

UPDATE styles_fields
SET title = 'Is Html'
WHERE id_styles = get_style_id('chat') AND id_fields = get_field_id('is_html');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('alert') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('alert') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Is Dismissable'
WHERE id_styles = get_style_id('alert') AND id_fields = get_field_id('is_dismissable');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('alert') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('alert') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('alert') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('alert') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Title'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('title');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Is Expanded'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('is_expanded');

UPDATE styles_fields
SET title = 'Is Collapsible'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('is_collapsible');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('url_edit');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('card') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('figure') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Caption Title'
WHERE id_styles = get_style_id('figure') AND id_fields = get_field_id('caption_title');

UPDATE styles_fields
SET title = 'Caption'
WHERE id_styles = get_style_id('figure') AND id_fields = get_field_id('caption');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('figure') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('figure') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('figure') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('figure') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('url');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Cancel Label'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('label_cancel');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('url_cancel');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('form') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Title'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('title');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Is Fluid'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('is_fluid');

UPDATE styles_fields
SET title = 'Alt'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('alt');

UPDATE styles_fields
SET title = 'Img Src'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('img_src');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Height'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('height');

UPDATE styles_fields
SET title = 'Width'
WHERE id_styles = get_style_id('image') AND id_fields = get_field_id('width');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('type_input');

UPDATE styles_fields
SET title = 'Placeholder'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('placeholder');

UPDATE styles_fields
SET title = 'Is Required'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('is_required');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Value'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('value');

UPDATE styles_fields
SET title = 'Min'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('min');

UPDATE styles_fields
SET title = 'Max'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('max');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Format'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('format');

UPDATE styles_fields
SET title = 'Locked After Submit'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('locked_after_submit');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('input') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Text'
WHERE id_styles = get_style_id('plaintext') AND id_fields = get_field_id('text');

UPDATE styles_fields
SET title = 'Is Paragraph'
WHERE id_styles = get_style_id('plaintext') AND id_fields = get_field_id('is_paragraph');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('link') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('link') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('link') AND id_fields = get_field_id('url');

UPDATE styles_fields
SET title = 'Open In New Tab'
WHERE id_styles = get_style_id('link') AND id_fields = get_field_id('open_in_new_tab');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('link') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('link') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('link') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('link') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Count'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('count');

UPDATE styles_fields
SET title = 'Count Max'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('count_max');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Is Striped'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('is_striped');

UPDATE styles_fields
SET title = 'Has Label'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('has_label');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('progressBar') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Caption'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('caption');

UPDATE styles_fields
SET title = 'Label Right'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('label_right');

UPDATE styles_fields
SET title = 'Label Wrong'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('label_wrong');

UPDATE styles_fields
SET title = 'Right Content'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('right_content');

UPDATE styles_fields
SET title = 'Wrong Content'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('wrong_content');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('quiz') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('rawText') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Text'
WHERE id_styles = get_style_id('rawText') AND id_fields = get_field_id('text');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('rawText') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('rawText') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('rawText') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('rawText') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Alt'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('alt');

UPDATE styles_fields
SET title = 'Is Required'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('is_required');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Value'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('value');

UPDATE styles_fields
SET title = 'Items'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('items');

UPDATE styles_fields
SET title = 'Is Multiple'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('is_multiple');

UPDATE styles_fields
SET title = 'Max'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('max');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Live Search'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('live_search');

UPDATE styles_fields
SET title = 'Disabled'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('disabled');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Image Selector'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('image_selector');

UPDATE styles_fields
SET title = 'Locked After Submit'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('locked_after_submit');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Allow Clear'
WHERE id_styles = get_style_id('select') AND id_fields = get_field_id('allow_clear');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Value'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('value');

UPDATE styles_fields
SET title = 'Labels'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('labels');

UPDATE styles_fields
SET title = 'Min'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('min');

UPDATE styles_fields
SET title = 'Max'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('max');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Locked After Submit'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('locked_after_submit');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('slider') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Is Expanded'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('is_expanded');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Icon'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('icon');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('tab') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('tabs') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('tabs') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('tabs') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('tabs') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('tabs') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Placeholder'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('placeholder');

UPDATE styles_fields
SET title = 'Is Required'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('is_required');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Value'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('value');

UPDATE styles_fields
SET title = 'Min'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('min');

UPDATE styles_fields
SET title = 'Max'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('max');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Locked After Submit'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('locked_after_submit');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Markdown Editor'
WHERE id_styles = get_style_id('textarea') AND id_fields = get_field_id('markdown_editor');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('video') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Is Fluid'
WHERE id_styles = get_style_id('video') AND id_fields = get_field_id('is_fluid');

UPDATE styles_fields
SET title = 'Alt'
WHERE id_styles = get_style_id('video') AND id_fields = get_field_id('alt');

UPDATE styles_fields
SET title = 'Sources'
WHERE id_styles = get_style_id('video') AND id_fields = get_field_id('sources');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('video') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('video') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('video') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('video') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Title Prefix'
WHERE id_styles = get_style_id('accordionList') AND id_fields = get_field_id('title_prefix');

UPDATE styles_fields
SET title = 'Items'
WHERE id_styles = get_style_id('accordionList') AND id_fields = get_field_id('items');

UPDATE styles_fields
SET title = 'Label Root'
WHERE id_styles = get_style_id('accordionList') AND id_fields = get_field_id('label_root');

UPDATE styles_fields
SET title = 'Id Prefix'
WHERE id_styles = get_style_id('accordionList') AND id_fields = get_field_id('id_prefix');

UPDATE styles_fields
SET title = 'Id Active'
WHERE id_styles = get_style_id('accordionList') AND id_fields = get_field_id('id_active');

UPDATE styles_fields
SET title = 'Title'
WHERE id_styles = get_style_id('navigationContainer') AND id_fields = get_field_id('title');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('navigationContainer') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Markdown Text'
WHERE id_styles = get_style_id('navigationContainer') AND id_fields = get_field_id('text_md');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('navigationContainer') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('navigationContainer') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('navigationContainer') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('navigationContainer') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Is Fluid'
WHERE id_styles = get_style_id('navigationAccordion') AND id_fields = get_field_id('is_fluid');

UPDATE styles_fields
SET title = 'Title Prefix'
WHERE id_styles = get_style_id('navigationAccordion') AND id_fields = get_field_id('title_prefix');

UPDATE styles_fields
SET title = 'Label Root'
WHERE id_styles = get_style_id('navigationAccordion') AND id_fields = get_field_id('label_root');

UPDATE styles_fields
SET title = 'Label Back'
WHERE id_styles = get_style_id('navigationAccordion') AND id_fields = get_field_id('label_back');

UPDATE styles_fields
SET title = 'Label Next'
WHERE id_styles = get_style_id('navigationAccordion') AND id_fields = get_field_id('label_next');

UPDATE styles_fields
SET title = 'Has Navigation Buttons'
WHERE id_styles = get_style_id('navigationAccordion') AND id_fields = get_field_id('has_navigation_buttons');

UPDATE styles_fields
SET title = 'Has Navigation Menu'
WHERE id_styles = get_style_id('navigationAccordion') AND id_fields = get_field_id('has_navigation_menu');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Title Prefix'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('title_prefix');

UPDATE styles_fields
SET title = 'Is Expanded'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('is_expanded');

UPDATE styles_fields
SET title = 'Is Collapsible'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('is_collapsible');

UPDATE styles_fields
SET title = 'Items'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('items');

UPDATE styles_fields
SET title = 'Search Text'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('search_text');

UPDATE styles_fields
SET title = 'Id Prefix'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('id_prefix');

UPDATE styles_fields
SET title = 'Id Active'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('id_active');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('nestedList') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Is Fluid'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('is_fluid');

UPDATE styles_fields
SET title = 'Title Prefix'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('title_prefix');

UPDATE styles_fields
SET title = 'Is Expanded'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('is_expanded');

UPDATE styles_fields
SET title = 'Is Collapsible'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('is_collapsible');

UPDATE styles_fields
SET title = 'Label Back'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('label_back');

UPDATE styles_fields
SET title = 'Label Next'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('label_next');

UPDATE styles_fields
SET title = 'Has Navigation Buttons'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('has_navigation_buttons');

UPDATE styles_fields
SET title = 'Search Text'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('search_text');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('css_nav');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Has Navigation Menu'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('has_navigation_menu');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('navigationNested') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Items'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('items');

UPDATE styles_fields
SET title = 'Is Sortable'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('is_sortable');

UPDATE styles_fields
SET title = 'Is Editable'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('is_editable');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('url_delete');

UPDATE styles_fields
SET title = 'Label Add'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('label_add');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('url_add');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('sortableList') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Password Label'
WHERE id_styles = get_style_id('resetPassword') AND id_fields = get_field_id('label_pw_reset');

UPDATE styles_fields
SET title = 'Reset Link'
WHERE id_styles = get_style_id('resetPassword') AND id_fields = get_field_id('text_md');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('resetPassword') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Alert Success'
WHERE id_styles = get_style_id('resetPassword') AND id_fields = get_field_id('alert_success');

UPDATE styles_fields
SET title = 'Email'
WHERE id_styles = get_style_id('resetPassword') AND id_fields = get_field_id('placeholder');

UPDATE styles_fields
SET title = 'Email User'
WHERE id_styles = get_style_id('resetPassword') AND id_fields = get_field_id('email_user');

UPDATE styles_fields
SET title = 'Subject User'
WHERE id_styles = get_style_id('resetPassword') AND id_fields = get_field_id('subject_user');

UPDATE styles_fields
SET title = 'Is Html'
WHERE id_styles = get_style_id('resetPassword') AND id_fields = get_field_id('is_html');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Alert Success'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('alert_success');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Is Log'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('is_log');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Anchor'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('anchor');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Ajax'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('ajax');

UPDATE styles_fields
SET title = 'Redirect At End'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('redirect_at_end');

UPDATE styles_fields
SET title = 'Own Entries Only'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('own_entries_only');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Internal'
WHERE id_styles = get_style_id('formUserInput') AND id_fields = get_field_id('internal');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Is Required'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('is_required');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Value'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('value');

UPDATE styles_fields
SET title = 'Items'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('items');

UPDATE styles_fields
SET title = 'Is Inline'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('is_inline');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Locked After Submit'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('locked_after_submit');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('radio') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Delete Title'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('delete_title');

UPDATE styles_fields
SET title = 'Label Delete'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('label_delete');

UPDATE styles_fields
SET title = 'Delete Content'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('delete_content');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Is Log'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('is_log');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Anchor'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('anchor');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Data Table'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('data_table');

UPDATE styles_fields
SET title = 'Own Entries Only'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('own_entries_only');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Fields Map'
WHERE id_styles = get_style_id('showUserInput') AND id_fields = get_field_id('fields_map');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('div') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('div') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('div') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('div') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('div') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Color Background'
WHERE id_styles = get_style_id('div') AND id_fields = get_field_id('color_background');

UPDATE styles_fields
SET title = 'Color Border'
WHERE id_styles = get_style_id('div') AND id_fields = get_field_id('color_border');

UPDATE styles_fields
SET title = 'Color Text'
WHERE id_styles = get_style_id('div') AND id_fields = get_field_id('color_text');

UPDATE styles_fields
SET title = 'Email'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('label_user');

UPDATE styles_fields
SET title = 'Password Label'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('label_pw');

UPDATE styles_fields
SET title = 'Error Message'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('alert_fail');

UPDATE styles_fields
SET title = 'Title'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('title');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Alert Success'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('alert_success');

UPDATE styles_fields
SET title = 'Success'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('success');

UPDATE styles_fields
SET title = 'Label Submit'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('label_submit');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Open Registration'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('open_registration');

UPDATE styles_fields
SET title = 'Group'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('group');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label Security Question 1'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('label_security_question_1');

UPDATE styles_fields
SET title = 'Label Security Question 2'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('label_security_question_2');

UPDATE styles_fields
SET title = 'Anonymous Users Registration'
WHERE id_styles = get_style_id('register') AND id_fields = get_field_id('anonymous_users_registration');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('conditionalContainer') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('conditionalContainer') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('conditionalContainer') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('conditionalContainer') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('conditionalContainer') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('audio') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Alt'
WHERE id_styles = get_style_id('audio') AND id_fields = get_field_id('alt');

UPDATE styles_fields
SET title = 'Sources'
WHERE id_styles = get_style_id('audio') AND id_fields = get_field_id('sources');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('audio') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('audio') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('audio') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('audio') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Sources'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('sources');

UPDATE styles_fields
SET title = 'Id Prefix'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('id_prefix');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Has Controls'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('has_controls');

UPDATE styles_fields
SET title = 'Has Indicators'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('has_indicators');

UPDATE styles_fields
SET title = 'Has Crossfade'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('has_crossfade');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('carousel') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Json'
WHERE id_styles = get_style_id('json') AND id_fields = get_field_id('json');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('userProgress') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('userProgress') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('userProgress') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('userProgress') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Is Striped'
WHERE id_styles = get_style_id('userProgress') AND id_fields = get_field_id('is_striped');

UPDATE styles_fields
SET title = 'Has Label'
WHERE id_styles = get_style_id('userProgress') AND id_fields = get_field_id('has_label');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('userProgress') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('userProgress') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Placeholder'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('placeholder');

UPDATE styles_fields
SET title = 'Is Required'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('is_required');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Value'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('value');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Name Value Field'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('name_value_field');

UPDATE styles_fields
SET title = 'Callback Class'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('callback_class');

UPDATE styles_fields
SET title = 'Callback Method'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('callback_method');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('autocomplete') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Items'
WHERE id_styles = get_style_id('navigationBar') AND id_fields = get_field_id('items');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('version') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('version') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('version') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('version') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('version') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Plugin'
WHERE id_styles = get_style_id('trigger') AND id_fields = get_field_id('plugin');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Data Table'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('data_table');

UPDATE styles_fields
SET title = 'Own Entries Only'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('own_entries_only');

UPDATE styles_fields
SET title = 'Filter'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('filter');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Load As Table'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('load_as_table');

UPDATE styles_fields
SET title = 'Scope'
WHERE id_styles = get_style_id('entryList') AND id_fields = get_field_id('scope');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Data Table'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('data_table');

UPDATE styles_fields
SET title = 'Own Entries Only'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('own_entries_only');

UPDATE styles_fields
SET title = 'Filter'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('filter');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Scope'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('scope');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('entryRecord') AND id_fields = get_field_id('url_param');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Alert Success'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('alert_success');

UPDATE styles_fields
SET title = 'Cancel Label'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('label_cancel');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('url_cancel');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Is Log'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('is_log');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Anchor'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('anchor');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Ajax'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('ajax');

UPDATE styles_fields
SET title = 'Redirect At End'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('redirect_at_end');

UPDATE styles_fields
SET title = 'Own Entries Only'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('own_entries_only');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Confirmation Title'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('confirmation_title');

UPDATE styles_fields
SET title = 'Confirmation Continue'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('confirmation_continue');

UPDATE styles_fields
SET title = 'Confirmation Message'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('confirmation_message');

UPDATE styles_fields
SET title = 'Internal'
WHERE id_styles = get_style_id('formUserInputLog') AND id_fields = get_field_id('internal');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Alert Success'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('alert_success');

UPDATE styles_fields
SET title = 'Cancel Label'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('label_cancel');

UPDATE styles_fields
SET title = 'Link URL'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('url_cancel');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Is Log'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('is_log');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Anchor'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('anchor');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Ajax'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('ajax');

UPDATE styles_fields
SET title = 'Redirect At End'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('redirect_at_end');

UPDATE styles_fields
SET title = 'Own Entries Only'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('own_entries_only');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Confirmation Title'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('confirmation_title');

UPDATE styles_fields
SET title = 'Confirmation Continue'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('confirmation_continue');

UPDATE styles_fields
SET title = 'Confirmation Message'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('confirmation_message');

UPDATE styles_fields
SET title = 'Internal'
WHERE id_styles = get_style_id('formUserInputRecord') AND id_fields = get_field_id('internal');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('conditionFailed') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('conditionFailed') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('conditionFailed') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('conditionFailed') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('conditionFailed') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('table') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('table') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('table') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('table') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Column Names'
WHERE id_styles = get_style_id('table') AND id_fields = get_field_id('column_names');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('loop') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('loop') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('loop') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('loop') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Loop'
WHERE id_styles = get_style_id('loop') AND id_fields = get_field_id('loop');

UPDATE styles_fields
SET title = 'Scope'
WHERE id_styles = get_style_id('loop') AND id_fields = get_field_id('scope');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('tableRow') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('tableRow') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('tableRow') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('tableRow') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('tableCell') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Markdown Text'
WHERE id_styles = get_style_id('tableCell') AND id_fields = get_field_id('text_md');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('tableCell') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('tableCell') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('tableCell') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Is Required'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('is_required');

UPDATE styles_fields
SET title = 'Name'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('name');

UPDATE styles_fields
SET title = 'Value'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('value');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Locked After Submit'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('locked_after_submit');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Toggle Switch'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('toggle_switch');

UPDATE styles_fields
SET title = 'Checkbox Value'
WHERE id_styles = get_style_id('checkbox') AND id_fields = get_field_id('checkbox_value');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('dataContainer') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('dataContainer') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('dataContainer') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('dataContainer') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('dataContainer') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Scope'
WHERE id_styles = get_style_id('dataContainer') AND id_fields = get_field_id('scope');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('htmlTag') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('htmlTag') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('htmlTag') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('htmlTag') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Html Tag'
WHERE id_styles = get_style_id('htmlTag') AND id_fields = get_field_id('html_tag');

UPDATE styles_fields
SET title = 'Label Delete'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('label_delete');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Style Type'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('type');

UPDATE styles_fields
SET title = 'Condition'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('condition');

UPDATE styles_fields
SET title = 'Debug Info'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('debug');

UPDATE styles_fields
SET title = 'Data Config'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('data_config');

UPDATE styles_fields
SET title = 'Redirect At End'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('redirect_at_end');

UPDATE styles_fields
SET title = 'Own Entries Only'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('own_entries_only');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Confirmation Title'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('confirmation_title');

UPDATE styles_fields
SET title = 'Confirmation Continue'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('confirmation_continue');

UPDATE styles_fields
SET title = 'Confirmation Message'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('confirmation_message');

UPDATE styles_fields
SET title = 'Confirmation Cancel'
WHERE id_styles = get_style_id('entryRecordDelete') AND id_fields = get_field_id('confirmation_cancel');

UPDATE styles_fields
SET title = 'Error Message'
WHERE id_styles = get_style_id('twoFactorAuth') AND id_fields = get_field_id('alert_fail');

UPDATE styles_fields
SET title = 'Label'
WHERE id_styles = get_style_id('twoFactorAuth') AND id_fields = get_field_id('label');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('twoFactorAuth') AND id_fields = get_field_id('css');

UPDATE styles_fields
SET title = 'Markdown Text'
WHERE id_styles = get_style_id('twoFactorAuth') AND id_fields = get_field_id('text_md');

UPDATE styles_fields
SET title = 'CSS Classes'
WHERE id_styles = get_style_id('twoFactorAuth') AND id_fields = get_field_id('css_mobile');

UPDATE styles_fields
SET title = 'Label Expiration 2Fa'
WHERE id_styles = get_style_id('twoFactorAuth') AND id_fields = get_field_id('label_expiration_2fa');

-- add column `title` for fileds in the pageType
CALL add_table_column('pageType_fields', 'title', 'VARCHAR(100) NOT NULL');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'core' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'experiment' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'global_css' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'maintenance' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_global_css' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page title'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('title');

UPDATE pageType_fields
SET title = 'Page description'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'core' LIMIT 1)
  AND id_fields = get_field_id('description');

UPDATE pageType_fields
SET title = 'Page icon'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'core' LIMIT 1)
  AND id_fields = get_field_id('icon');

UPDATE pageType_fields
SET title = 'Activation email'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_activate');

UPDATE pageType_fields
SET title = 'Reminder email'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_reminder');

UPDATE pageType_fields
SET title = 'Email subject'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_subject');

UPDATE pageType_fields
SET title = 'Activate subject'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_activate_subject');

UPDATE pageType_fields
SET title = 'Reminder subject'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_reminder_subject');

UPDATE pageType_fields
SET title = 'Activate email'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_activate_email_address');

UPDATE pageType_fields
SET title = 'Delete confirm email'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_delete_profile_email_address');

UPDATE pageType_fields
SET title = 'Delete subject'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_delete_profile_subject');

UPDATE pageType_fields
SET title = 'Delete email'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_delete_profile');

UPDATE pageType_fields
SET title = 'Delete notify email'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_delete_profile_email_address_notification_copy');

UPDATE pageType_fields
SET title = '2FA subject'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_2fa_subject');

UPDATE pageType_fields
SET title = '2FA email'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'emails' LIMIT 1)
  AND id_fields = get_field_id('email_2fa');

UPDATE pageType_fields
SET title = 'Page description'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'experiment' LIMIT 1)
  AND id_fields = get_field_id('description');

UPDATE pageType_fields
SET title = 'Page icon'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'experiment' LIMIT 1)
  AND id_fields = get_field_id('icon');

UPDATE pageType_fields
SET title = 'Page description'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'global_css' LIMIT 1)
  AND id_fields = get_field_id('description');

UPDATE pageType_fields
SET title = 'Custom CSS'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'global_css' LIMIT 1)
  AND id_fields = get_field_id('custom_css');

UPDATE pageType_fields
SET title = 'Page description'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 1)
  AND id_fields = get_field_id('description');

UPDATE pageType_fields
SET title = 'Translation keys'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'global_values' LIMIT 1)
  AND id_fields = get_field_id('global_values');

UPDATE pageType_fields
SET title = 'Page description'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 1)
  AND id_fields = get_field_id('description');

UPDATE pageType_fields
SET title = 'Page icon'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 1)
  AND id_fields = get_field_id('icon');

UPDATE pageType_fields
SET title = 'Maintenance text'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'maintenance' LIMIT 1)
  AND id_fields = get_field_id('maintenance');

UPDATE pageType_fields
SET title = 'Maintenance date'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'maintenance' LIMIT 1)
  AND id_fields = get_field_id('maintenance_date');

UPDATE pageType_fields
SET title = 'Maintenance time'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'maintenance' LIMIT 1)
  AND id_fields = get_field_id('maintenance_time');

UPDATE pageType_fields
SET title = 'Custom CSS'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_global_css' LIMIT 1)
  AND id_fields = get_field_id('custom_css');

UPDATE pageType_fields
SET title = 'Enable reset'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('enable_reset_password');

UPDATE pageType_fields
SET title = 'Question 1'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_01');

UPDATE pageType_fields
SET title = 'Question 2'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_02');

UPDATE pageType_fields
SET title = 'Question 3'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_03');

UPDATE pageType_fields
SET title = 'Question 4'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_04');

UPDATE pageType_fields
SET title = 'Question 5'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_05');

UPDATE pageType_fields
SET title = 'Question 6'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_06');

UPDATE pageType_fields
SET title = 'Question 7'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_07');

UPDATE pageType_fields
SET title = 'Question 8'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_08');

UPDATE pageType_fields
SET title = 'Question 9'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_09');

UPDATE pageType_fields
SET title = 'Question 10'
WHERE id_pageType = (SELECT id FROM pageType WHERE `name` = 'sh_security_questions' LIMIT 1)
  AND id_fields = get_field_id('security_question_10');

-- add new fieldType select-css
INSERT IGNORE INTO `fieldType` (`name`, `position`) VALUES ('select-css', '8');

-- change the `css` type to `select-css`
UPDATE `fields`
SET id_type = get_field_type_id('select-css')
WHERE `name` IN ('css', 'css_mobile');

UPDATE styles_fields
SET title = 'Mobile CSS Classesf'
WHERE id_fields = get_field_id('css_mobile');

UPDATE users
SET `name` = 'Guest'
WHERE id = 1;

-- ===============================
-- Rename table `formActions` -> `actions` and update references
-- ===============================

-- Drop foreign keys and indexes referencing `formActions` in junction tables
CALL drop_foreign_key('scheduledJobs_formActions', 'FK_AE5B5D0B293A36A9');
CALL drop_index('scheduledJobs_formActions', 'IDX_AE5B5D0B293A36A9');

CALL rename_table('formActions', 'actions');
CALL rename_table('scheduledJobs_formActions', 'scheduledJobs_actions');

CALL drop_foreign_key('actions', 'FK_3128FB5E8A8FCE9D');
CALL drop_foreign_key('actions', 'FK_548F1EF4AC2316F');
CALL drop_foreign_key('actions', 'FK_3128FB5EE2E6A7C3');
CALL drop_index('actions', 'IDX_548F1EF8A8FCE9D');
CALL drop_index('actions', 'IDX_548F1EF8A8FCE9D');
CALL rename_table_column('actions', 'id_formProjectActionTriggerTypes', 'id_actionTriggerTypes');
ALTER TABLE actions CHANGE id_actionTriggerTypes id_actionTriggerTypes INT NOT NULL;
CALL add_foreign_key(
  'actions',
  'FK_548F1EF4AC2316F',
  'id_actionTriggerTypes',
  'lookups (id)'
);
CALL add_index(
  'actions',
  'IDX_548F1EF4AC2316F',
  'id_actionTriggerTypes',
  FALSE
);

ALTER TABLE actions CHANGE id_dataTables id_dataTables INT NOT NULL;

-- Recreate foreign key and index to point to the new table/column
CALL add_foreign_key('scheduledJobs_actions', 'FK_SJ_ACTIONS', 'id_actions', 'actions(id)');
CALL add_foreign_key('actions', 'FK_548F1EFE2E6A7C3', 'id_dataTables', 'dataTables(id)');
CALL add_index('scheduledJobs_actions', 'IDX_862DD4F8DBD5589F', 'id_actions', FALSE);

CALL rename_index('actions', 'idx_3128fb5e8a8fce9d', 'IDX_548F1EF8A8FCE9D');
CALL rename_index('actions', 'idx_3128fb5ee2e6a7c3', 'IDX_548F1EFE2E6A7C3');

CALL rename_index('scheduledJobs_actions', 'idx_ae5b5d0b8030ba52', 'IDX_862DD4F88030BA52');
CALL rename_index('scheduledJobs_actions', 'idx_ae5b5d0bf3854f45',  'IDX_862DD4F8F3854F45');

UPDATE pages
SET nav_position = null
WHERE keyword IN ('sh-global-css', 'sh-global-values');

-- remove old styles
DELETE FROM styles
WHERE `name` IN (
    'jumbotron',
    'markdownInline',
    'chat',
    'card',
    'form',
    'quiz',
    'rawText',
    'accordionList',
    'navigationContainer',
    'navigationAccordion',
    'nestedList',
    'navigationNested',
    'sortableList',
    'formUserInput',
    'conditionalContainer',
    'json',
    'userProgress',
    'autocomplete',
    'navigationBar',
    'trigger',
    'conditionFailed',
    'conditionBuilder',
    'dataConfigBuilder',
    'actionConfigBuilder'
);

-- remove not needed fields
DELETE
FROM `fields` 
WHERE `id` NOT IN (SELECT DISTINCT `id_fields` FROM `styles_fields`) 
AND `id` NOT IN (SELECT DISTINCT `id_fields` FROM `pages_fields`)
AND `id` NOT IN (SELECT DISTINCT `id_fields` FROM `sections_fields_translation`)
AND `id` NOT IN (SELECT DISTINCT `id_fields` FROM `pages_fields_translation`);

-- remove not needed field types
DELETE
FROM `fieldType` 
WHERE `id` NOT IN (SELECT DISTINCT `id_type` FROM `fields`);

CALL add_table_column('sections', 'debug', 'TINYINT DEFAULT 0');
CALL add_table_column('sections', 'condition', 'LONGTEXT DEFAULT NULL');
CALL add_table_column('sections', 'data_config', 'LONGTEXT DEFAULT NULL');
CALL add_table_column('sections', 'css', 'LONGTEXT DEFAULT NULL');
CALL add_table_column('sections', 'css_mobile', 'LONGTEXT DEFAULT NULL');

-- Update CSS
UPDATE sections s
JOIN sections_fields_translation sft 
    ON sft.id_sections = s.id
JOIN styles st 
    ON st.id = s.id_styles
SET s.css = sft.content
WHERE sft.id_fields = get_field_id('css');

-- Update DEBUG
UPDATE sections s
JOIN sections_fields_translation sft 
    ON sft.id_sections = s.id
JOIN styles st 
    ON st.id = s.id_styles
SET s.debug = CAST(sft.content AS UNSIGNED)
WHERE sft.id_fields = get_field_id('debug');

-- Update CONDITION
UPDATE sections s
JOIN sections_fields_translation sft 
    ON sft.id_sections = s.id
JOIN styles st 
    ON st.id = s.id_styles
SET s.`condition` = sft.content
WHERE sft.id_fields = get_field_id('condition');

-- Update DATA_CONFIG
UPDATE sections s
JOIN sections_fields_translation sft 
    ON sft.id_sections = s.id
JOIN styles st 
    ON st.id = s.id_styles
SET s.data_config = sft.content
WHERE sft.id_fields = get_field_id('data_config');

-- Remove not needed fields as they are now in the sections table
DELETE
FROM `fields` 
WHERE `name` IN ('css', 'css_mobile', 'debug', 'condition', 'data_config');

DELIMITER //

DROP PROCEDURE IF EXISTS `get_page_sections_hierarchical` //

CREATE PROCEDURE `get_page_sections_hierarchical`(IN page_id INT)
BEGIN
    WITH RECURSIVE section_hierarchy AS (
        -- Base case: get top-level sections for the page, position starts from 10
        SELECT 
            s.id,
            s.`name`,
            s.id_styles,
            st.`name` AS style_name,
            st.can_have_children,
            s.`condition`,
            s.css,
            s.css_mobile,
            s.debug,
            s.data_config,
            ps.`position` AS position,      -- Start at 10
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
            st.can_have_children,
            s.`condition`,
            s.css,
            s.css_mobile,
            s.debug,
            s.data_config,
            sh.position AS position,        -- Add 10 to each level
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
        can_have_children,
        `condition`,
		css,
		css_mobile,
		debug,
		data_config,
        position,
        `level`,
        `path`
    FROM section_hierarchy
    ORDER BY `path`, `position`;
END //

DELIMITER ;

-- Delete existing container style
DELETE FROM styles
WHERE `name` = 'container';

DELETE FROM styles
WHERE `name` IN ('tabs', 'tab');





-- Section Management API Enhancement
-- Added new section deletion capabilities:
-- - DELETE /admin/sections/unused/{section_id} - Delete single unused section (requires admin.section.delete permission)
-- - DELETE /admin/sections/unused - Delete all unused sections (requires admin.section.delete permission)
-- - DELETE /admin/pages/{page_keyword}/sections/{section_id}/force-delete - Force delete section from page (requires admin.page.delete permission)
-- - Updated existing DELETE /admin/pages/{page_keyword}/sections/{section_id} to require admin.page.delete permission
-- Added comprehensive transaction logging for all section deletion operations
-- Enhanced AdminSectionUtilityService with deletion capabilities and proper relationship cleanup
-- Added forceDeleteSection method that always deletes (never just removes from page)
-- All deletion operations are wrapped in database transactions with proper rollback handling
-- All operations properly check page access permissions before allowing section deletion

--
-- Styles Relationship System Enhancement v8.0.0
-- Added relational constraints for styles to define allowed parent-child relationships
-- This ensures that only valid style combinations can be created, preventing invalid hierarchies
--

-- Create table for defining allowed parent-child relationships between styles
-- This table enforces style-level constraints to ensure only valid combinations are allowed
-- Example: Style "tabs" can only have "tab" as children, "card-header" can only have "card" as parent
CREATE TABLE IF NOT EXISTS `styles_allowed_relationships` (
  `id_parent_style` int NOT NULL COMMENT 'ID of the parent style',
  `id_child_style` int NOT NULL COMMENT 'ID of the child style',
  PRIMARY KEY (`id_parent_style`,`id_child_style`),
  KEY `IDX_757F0414DC4D59BB` (`id_parent_style`),
  KEY `IDX_757F041478A9D70E` (`id_child_style`),
  CONSTRAINT `FK_styles_relationships_parent`
    FOREIGN KEY (`id_parent_style`) REFERENCES `styles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_styles_relationships_child`
    FOREIGN KEY (`id_child_style`) REFERENCES `styles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Defines allowed parent-child relationships between styles';
  ALTER TABLE styles_allowed_relationships CHANGE id_parent_style id_parent_style INT NOT NULL, CHANGE id_child_style id_child_style INT NOT NULL;


