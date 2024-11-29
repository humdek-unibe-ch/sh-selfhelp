-- set DB version
UPDATE version
SET version = 'v8.0.0';

CALL add_table_column('pages', 'is_open_access', 'TINYINT DEFAULT 0');

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

DELIMITER //

DROP PROCEDURE IF EXISTS get_user_acl //

CREATE PROCEDURE get_user_acl( param_user_id INT, param_page_id INT ) # when page_id is -1 then all pages
BEGIN

    SELECT ug.id_users, acl.id_pages, MAX(IFNULL(acl.acl_select, 0)) as acl_select, MAX(IFNULL(acl.acl_insert, 0)) as acl_insert,
	MAX(IFNULL(acl.acl_update, 0)) as acl_update, MAX(IFNULL(acl.acl_delete, 0)) as acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, p.id_pageAccessTypes
	FROM users u
	INNER JOIN users_groups AS ug ON (ug.id_users = u.id)
	INNER  JOIN acl_groups acl ON (acl.id_groups = ug.id_groups)
	INNER JOIN pages p ON (acl.id_pages = p.id)
	WHERE ug.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY ug.id_users, acl.id_pages, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type
    
    UNION 
    
    SELECT acl.id_users, acl.id_pages, 
	acl. acl_select, 
	acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, p.id_pageAccessTypes
	FROM acl_users acl
	INNER JOIN pages p ON (acl.id_pages = p.id)
    WHERE acl.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type
    
    UNION 
    
    -- add open access pages
    SELECT param_user_id, p.id AS id_pages, 
	1 AS acl_select, 
	0 AS acl_insert, 0 AS acl_update, 0 AS acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, p.id_pageAccessTypes
	FROM pages p
    WHERE p.is_open_access = 1;
    
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


-- shoudl remove is_fluid from container style
-- create new page onpen access use new field
-- reowork all form data to use drop down for table selection. First the table should be registered by the user. Assign ACL to these dataTables.