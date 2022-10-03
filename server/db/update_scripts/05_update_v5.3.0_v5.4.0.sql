-- set DB version
UPDATE version
SET version = 'v5.4.0';

UPDATE pages
SET id_actions = (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1)
WHERE keyword = 'admin-link';