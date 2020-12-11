DROP VIEW IF EXISTS view_acl_groups_pages_modules;
CREATE VIEW view_acl_groups_pages_modules
AS
SELECT acl.id_groups, acl.id_pages, 
CASE
	WHEN p.id_type = 4 then 1 -- the page is open all grousp should has access for select
	ELSE acl.acl_select
END AS acl_select, 
acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
FROM acl_groups acl
INNER JOIN pages p ON (acl.id_pages = p.id or (p.id_type = 4 and acl.id_pages = null)) -- add all open pages although that there is no specific ACL
LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
LEFT JOIN modules m ON (m.id = mp.id_modules)
GROUP BY acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;
