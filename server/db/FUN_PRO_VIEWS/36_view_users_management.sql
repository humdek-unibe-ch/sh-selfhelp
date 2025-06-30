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