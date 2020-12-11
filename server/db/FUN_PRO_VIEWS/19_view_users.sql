DROP VIEW IF EXISTS view_users;
CREATE VIEW view_users
AS
SELECT u.id, u.email, u.name, u.last_login, us.name AS status,
us.description, u.blocked, vc.code,
GROUP_CONCAT(DISTINCT g.id*1 SEPARATOR ', ') AS groups_ids,
GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS groups,
GROUP_CONCAT(DISTINCT ch.name SEPARATOR '; ') AS chat_rooms_names
FROM users AS u
LEFT JOIN userStatus AS us ON us.id = u.id_status
LEFT JOIN users_groups AS ug ON ug.id_users = u.id
LEFT JOIN groups g ON g.id = ug.id_groups
LEFT JOIN chatRoom_users chu ON u.id = chu.id_users
LEFT JOIN chatRoom ch ON ch.id = chu.id_chatRoom
LEFT JOIN validation_codes vc ON u.id = vc.id_users
WHERE u.intern <> 1 AND u.id_status > 0
GROUP BY u.id, u.email, u.name, u.last_login, us.name, us.description, u.blocked, vc.code
ORDER BY u.email;
