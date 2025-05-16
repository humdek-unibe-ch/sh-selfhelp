DROP VIEW IF EXISTS `view_users`;
CREATE VIEW `view_users` AS
SELECT
  u.id AS id,
  u.email AS email,
  u.name AS name,
  IFNULL(
    CONCAT(
      u.last_login,
      ' (',
      TO_DAYS(NOW()) - TO_DAYS(u.last_login),
      ' days ago)'
    ),
    'never'
  ) AS last_login,
  usl.lookup_value       AS status,
  usl.lookup_description AS description,
  u.blocked              AS blocked,
  (CASE
     WHEN u.name = 'admin' THEN 'admin'
     WHEN u.name = 'tpf'   THEN 'tpf'
     ELSE IFNULL(vc.code, '-')
   END)                  AS code,
  GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS `groups`,
  ua.activity_count     AS user_activity,
  ua.distinct_url_count AS ac,
  u.intern              AS intern,
  u.id_userTypes        AS id_userTypes,
  lut.lookup_code       AS user_type_code,
  lut.lookup_value      AS user_type
FROM users u
LEFT JOIN lookups usl
  ON usl.id = u.id_status
  AND usl.type_code = 'userStatus'
LEFT JOIN users_groups ug
  ON ug.id_users = u.id
LEFT JOIN `groups` g
  ON g.id = ug.id_groups
LEFT JOIN validation_codes vc
  ON u.id = vc.id_users
JOIN lookups lut
  ON lut.id = u.id_userTypes
LEFT JOIN (
  SELECT
    ua.id_users AS id_users,
    COUNT(*)    AS activity_count,
    COUNT(DISTINCT CASE WHEN ua.id_type = 1 THEN ua.url END) AS distinct_url_count
  FROM user_activity ua
  GROUP BY ua.id_users
) AS ua
  ON ua.id_users = u.id
WHERE u.intern <> 1
  AND u.id_status > 0
GROUP BY
  u.id,
  u.email,
  u.name,
  u.last_login,
  usl.lookup_value,
  usl.lookup_description,
  u.blocked,
  vc.code,
  ua.activity_count,
  ua.distinct_url_count,
  u.intern,
  u.id_userTypes,
  lut.lookup_code,
  lut.lookup_value
ORDER BY u.email;
