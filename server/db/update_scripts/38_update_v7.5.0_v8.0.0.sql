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
VALUES (@page_keyword, '/cms-api/v1/[content:class]/[page:method]/[v:keyword]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Page');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Page');

-- add page api_v1_content_put_page
--  Used to update an existing resource or create a resource if it does not exist.
SET @page_keyword = 'cms-api_v1_content_put_page';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[content:class]/[page:method]/[v:keyword]', 'PUT', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 1);
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
CREATE PROCEDURE get_user_acl(param_user_id INT, param_page_id INT) 
BEGIN

    SELECT
        param_user_id AS id_users,
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
            -- UNION part 1: users_groups and acl_groups
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
            FROM
                users u
            INNER JOIN users_groups AS ug ON ug.id_users = u.id
            INNER JOIN acl_groups acl ON acl.id_groups = ug.id_groups
            INNER JOIN pages p ON acl.id_pages = p.id
            WHERE
                ug.id_users = param_user_id
                AND (param_page_id = -1 OR acl.id_pages = param_page_id)

            UNION ALL

            -- UNION part 2: acl_users
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
            FROM
                acl_users acl
            INNER JOIN pages p ON acl.id_pages = p.id
            WHERE
                acl.id_users = param_user_id
                AND (param_page_id = -1 OR acl.id_pages = param_page_id)

            UNION ALL

            -- UNION part 3: open access pages
            SELECT
                param_user_id AS id_users,
                p.id AS id_pages,
                1 AS acl_select,
                0 AS acl_insert,
                0 AS acl_update,
                0 AS acl_delete,
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
            FROM
                pages p
            WHERE
                p.is_open_access = 1
        ) AS combined_acl
    GROUP BY
        param_user_id,
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
VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[page_fields:method]/[v:page_keyword]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 0);

-- Add translations for the page title in English
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Page Fields');

-- Add translations for the page title in German
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Page Fields');

INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ((SELECT id FROM `groups` WHERE `name` = 'admin'), (SELECT id FROM pages WHERE keyword = @page_keyword), '1', '0', '0', '0');



-- shoudl remove is_fluid from container style
-- create new page onpen access use new field
-- reowork all form data to use drop down for table selection. First the table should be registered by the user. Assign ACL to these dataTables.