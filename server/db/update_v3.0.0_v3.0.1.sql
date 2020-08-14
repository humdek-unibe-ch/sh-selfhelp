DROP VIEW IF EXISTS view_acl_users_in_groups_pages_modules;
CREATE VIEW view_acl_users_in_groups_pages_modules
AS
SELECT ug.id_users, acl.id_pages, MAX(IFNULL(acl_select, 0)) as acl_select, MAX(IFNULL(acl_insert, 0)) as acl_insert,
MAX(IFNULL(acl_update, 0)) as acl_update, MAX(IFNULL(acl_delete, 0)) as acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
FROM users u
INNER JOIN users_groups AS ug ON (ug.id_users = u.id)
INNER  JOIN acl_groups acl ON (acl.id_groups = ug.id_groups)
INNER JOIN pages p ON (acl.id_pages = p.id)
LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
LEFT JOIN modules m ON (m.id = mp.id_modules)
GROUP BY acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;

DROP VIEW IF EXISTS view_acl_users_union;
CREATE VIEW view_acl_users_union
AS
SELECT *
FROM view_acl_users_in_groups_pages_modules

UNION 

SELECT *
FROM view_acl_users_pages_modules;