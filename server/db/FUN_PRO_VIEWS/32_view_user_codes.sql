DROP VIEW IF EXISTS view_user_codes;
CREATE VIEW view_user_codes
AS
SELECT u.id, u.email, u.name, u.blocked, 
CASE
	WHEN u.name = 'admin' THEN 'admin'
    WHEN u.name = 'tpf' THEN 'tpf'    
    ELSE IFNULL(vc.code, '-') 
END AS code,
u.intern
FROM users AS u
LEFT JOIN validation_codes vc ON u.id = vc.id_users
WHERE u.intern <> 1 AND u.id_status > 0;
