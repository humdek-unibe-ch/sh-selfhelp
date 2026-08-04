-- set DB version
UPDATE version
SET version = 'v7.9.0';

-- =====================================================
-- VIDEO WATCH TRACKING
-- Log play/heartbeat/ended events into user_activity
-- using a dedicated activityType so admin activity counts
-- are not inflated by video heartbeats.
-- =====================================================

INSERT IGNORE INTO `activityType` (`name`)
SELECT 'video' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `activityType` WHERE `name` = 'video');

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`)
VALUES (NULL, 'track_interval_seconds', get_field_type_id('number'), '0');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (
    get_style_id('video'),
    get_field_id('track_interval_seconds'),
    '0',
    'Seconds between watch-progress heartbeats while the video is playing. Set to `0` to disable tracking. When enabled, `play` and `ended` are logged immediately (not delayed by the interval). Requires a logged-in user.'
);

INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`)
VALUES (
    NULL,
    'ajax_video_track',
    '/request/[AjaxVideoTrack:class]/[track:method]',
    'GET|POST',
    (SELECT id FROM actions WHERE `name` = 'ajax' LIMIT 1),
    NULL,
    NULL,
    '0',
    NULL,
    NULL,
    '0000000001',
    (SELECT id FROM lookups WHERE type_code = 'pageAccessTypes' AND lookup_code = 'mobile_and_web')
);

INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`)
VALUES ('0000000001', (SELECT id FROM pages WHERE keyword = 'ajax_video_track'), '1', '0', '0', '0');

INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`)
VALUES ('0000000002', (SELECT id FROM pages WHERE keyword = 'ajax_video_track'), '1', '0', '0', '0');

INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`)
VALUES ('0000000003', (SELECT id FROM pages WHERE keyword = 'ajax_video_track'), '1', '0', '0', '0');

-- Exclude video activity rows from the admin user activity counter
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
        SUM(CASE WHEN id_type <> (SELECT id FROM activityType WHERE `name` = 'video' LIMIT 1) THEN 1 ELSE 0 END) AS activity_count,
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
