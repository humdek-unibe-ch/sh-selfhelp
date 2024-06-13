DROP VIEW IF EXISTS view_users;
CREATE VIEW view_users
AS
SELECT 
    u.id, 
    u.email, 
    u.`name`, 
    IFNULL(CONCAT(u.last_login, ' (', DATEDIFF(NOW(), u.last_login), ' days ago)'), 'never') AS last_login, 
    us.`name` AS `status`,
    us.description, 
    u.blocked, 
    CASE
        WHEN u.`email` = 'admin' THEN 'admin'
        WHEN u.`email` = 'tpf' THEN 'tpf'    
        ELSE IFNULL(vc.code, '-') 
    END AS code,
    GROUP_CONCAT(DISTINCT g.`name` SEPARATOR '; ') AS `groups`,
    user_activity.activity_count AS user_activity,
    user_activity.distinct_url_count AS ac,
    u.intern, 
    u.id_userTypes, 
    l_user_type.lookup_code AS user_type_code, 
    l_user_type.lookup_value AS user_type
FROM users AS u
LEFT JOIN userStatus AS us ON us.id = u.id_status
LEFT JOIN users_groups AS ug ON ug.id_users = u.id
LEFT JOIN `groups` g ON g.id = ug.id_groups
LEFT JOIN validation_codes vc ON u.id = vc.id_users
INNER JOIN lookups l_user_type ON u.id_userTypes = l_user_type.id
LEFT JOIN (
    SELECT 
        id_users, 
        COUNT(*) AS activity_count,
        COUNT(DISTINCT CASE WHEN id_type = 1 THEN url ELSE NULL END) AS distinct_url_count
    FROM user_activity
    GROUP BY id_users
) AS user_activity ON u.id = user_activity.id_users
WHERE u.intern <> 1 
AND u.id_status > 0
GROUP BY 
    u.id, 
    u.email, 
    u.`name`, 
    u.last_login, 
    us.`name`, 
    us.description, 
    u.blocked, 
    vc.`code`, 
    user_activity.activity_count, 
    user_activity.distinct_url_count,
    u.intern, 
    u.id_userTypes, 
    l_user_type.lookup_code, 
    l_user_type.lookup_value
ORDER BY u.email;

