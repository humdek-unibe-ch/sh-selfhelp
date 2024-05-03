-- set DB version
UPDATE version
SET version = 'v6.14.0';

UPDATE pages
SET id_type = (SELECT id FROM pageType WHERE `name` = 'core')
WHERE keyword = 'ajax_set_user_language';