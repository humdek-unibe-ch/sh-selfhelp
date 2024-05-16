-- set DB version
UPDATE version
SET version = 'v6.16.0';

DROP VIEW IF EXISTS view_form;
CREATE VIEW view_form
AS
SELECT DISTINCT cast(form.id AS UNSIGNED) form_id, sft_if.content AS form_name, IFNULL(sft_intern.content, 0) AS internal
FROM user_input_record record 
INNER JOIN sections form ON (record.id_sections = form.id)
LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = record.id_sections AND sft_if.id_fields = 57
LEFT JOIN sections_fields_translation AS sft_intern ON sft_intern.id_sections = record.id_sections AND sft_intern.id_fields = (SELECT id
FROM `fields`
WHERE `name` = 'internal');

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
        WHEN u.`name` = 'admin' THEN 'admin'
        WHEN u.`name` = 'tpf' THEN 'tpf'    
        ELSE IFNULL(vc.code, '-') 
    END AS code,
    GROUP_CONCAT(DISTINCT g.`name` SEPARATOR '; ') AS `groups`,
    user_activity.activity_count,
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

