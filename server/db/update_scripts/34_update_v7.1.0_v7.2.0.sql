-- set DB version
UPDATE version
SET version = 'v7.2.0';

-- change the config column in formActions to json
ALTER TABLE formActions
MODIFY COLUMN config JSON;
