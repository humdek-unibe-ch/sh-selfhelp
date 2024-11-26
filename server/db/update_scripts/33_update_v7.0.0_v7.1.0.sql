-- set DB version
UPDATE version
SET version = 'v7.1.0';

ALTER TABLE formActions
MODIFY COLUMN config LONGTEXT;
