-- set DB version
UPDATE version
SET version = 'v6.15.0';

ALTER TABLE scheduledJobs_formActions
ADD COLUMN record_id INT(10) UNSIGNED ZEROFILL NOT NULL;
