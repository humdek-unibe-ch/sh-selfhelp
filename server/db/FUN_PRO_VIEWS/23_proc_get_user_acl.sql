DELIMITER //

DROP PROCEDURE IF EXISTS get_user_acl //

CREATE PROCEDURE get_user_acl( param_user_id INT, param_page_id INT ) # when page_id is -1 then all pages
BEGIN

    SELECT ug.id_users, acl.id_pages, MAX(IFNULL(acl.acl_select, 0)) as acl_select, MAX(IFNULL(acl.acl_insert, 0)) as acl_insert,
	MAX(IFNULL(acl.acl_update, 0)) as acl_update, MAX(IFNULL(acl.acl_delete, 0)) as acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
	FROM users u
	INNER JOIN users_groups AS ug ON (ug.id_users = u.id)
	INNER  JOIN acl_groups acl ON (acl.id_groups = ug.id_groups)
	INNER JOIN pages p ON (acl.id_pages = p.id)
	LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
	LEFT JOIN modules m ON (m.id = mp.id_modules)
	WHERE ug.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY ug.id_users, acl.id_pages, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type
    HAVING enabled = 1
    
    UNION 
    
    SELECT acl.id_users, acl.id_pages, 
	CASE
		WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
		ELSE acl.acl_select
	END AS acl_select, 
	acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
	p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
	p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
	FROM acl_users acl
	INNER JOIN pages p ON (acl.id_pages = p.id)
	LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
	LEFT JOIN modules m ON (m.id = mp.id_modules)
    WHERE acl.id_users = param_user_id AND acl.id_pages = (CASE WHEN param_page_id = -1 THEN acl.id_pages ELSE param_page_id END)
	GROUP BY acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
	p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type
    HAVING enabled = 1;
    
END
//

DELIMITER ;
