-- set DB version
UPDATE version
SET version = 'v3.10.0';

UPDATE pages
SET url = '/admin/qualtrics/sync/[i:pid]/[i:aid]?'
WHERE keyword = 'moduleQualtricsSync';
