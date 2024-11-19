-- set DB version
UPDATE version
SET version = 'v8.0.0';

-- add action `cms-api` for api calls
INSERT IGNORE INTO `actions`(`name`) VALUES('cms-api');

-- add page api_get_external
SET @page_keyword = 'cms-api_get_nav_pages';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (@page_keyword, '/cms-api/[nav:class]/[pages:method]/[web|mobile:mode]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Navigation Pages');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Navigation Pages');
-- open access page
INSERT IGNORE INTO `acl_users` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES (1, (SELECT id FROM pages WHERE keyword = @page_keyword), '1', '0', '0', '0');

-- make `home` page open access
INSERT IGNORE INTO `acl_users` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES (1, (SELECT id FROM pages WHERE keyword = 'home'), '1', '0', '0', '0');


-- add page api_get_page_content
SET @page_keyword = 'cms-api_get_webpage';
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (@page_keyword, '/cms-api/[webPage:class]/[page:method]/[v:keyword]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Web Page');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Web Page');
-- open access page
INSERT IGNORE INTO `acl_users` (`id_users`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES (1, (SELECT id FROM pages WHERE keyword = @page_keyword), '1', '0', '0', '0');

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
	CASE
		WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
		ELSE acl.acl_select
	END AS acl_select, 
	acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, p.id_pageAccessTypes
	FROM acl_users acl
	INNER JOIN pages p ON (acl.id_pages = p.id)
    WHERE acl.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
    
END
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS `log_performance` (
  `id_user_activity` int(10) UNSIGNED NOT NULL,
  `log` LONGTEXT,
  PRIMARY KEY (`id_user_activity`),
  FOREIGN KEY (`id_user_activity`) REFERENCES `user_activity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
