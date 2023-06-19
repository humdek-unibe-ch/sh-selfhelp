DROP VIEW IF EXISTS view_users;
CREATE VIEW view_users
AS
SELECT u.id, u.email, u.`name`, 
IFNULL(CONCAT(u.last_login, ' (', DATEDIFF(NOW(), u.last_login), ' days ago)'), 'never') AS last_login, 
us.`name` AS `status`,
us.description, u.blocked, 
CASE
	WHEN u.`name` = 'admin' THEN 'admin'
    WHEN u.`name` = 'tpf' THEN 'tpf'    
    ELSE IFNULL(vc.code, '-') 
END AS code,
GROUP_CONCAT(DISTINCT g.`name` SEPARATOR '; ') AS `groups`,
(SELECT COUNT(*) AS activity FROM user_activity WHERE user_activity.id_users = u.id) AS user_activity,
(SELECT COUNT(DISTINCT url) FROM user_activity WHERE user_activity.id_users = u.id AND id_type = 1) AS ac,
u.intern, u.id_userTypes, l_user_type.lookup_code AS user_type_code, l_user_type.lookup_value AS user_type
FROM users AS u
LEFT JOIN userStatus AS us ON us.id = u.id_status
LEFT JOIN users_groups AS ug ON ug.id_users = u.id
LEFT JOIN `groups` g ON g.id = ug.id_groups
LEFT JOIN validation_codes vc ON u.id = vc.id_users
INNER JOIN lookups l_user_type ON u.id_userTypes = l_user_type.id
WHERE u.intern <> 1 AND u.id_status > 0
GROUP BY u.id, u.email, u.`name`, u.last_login, us.`name`, us.description, u.blocked, vc.`code`, user_activity
ORDER BY u.email;
