-- set DB version
UPDATE version
SET version = 'v7.2.0';

-- change the config column in formActions to json
ALTER TABLE formActions
MODIFY COLUMN config JSON;

-- replace `delete_scheduled` property in the config to `clear_existing_jobs_for_record_and_action`
UPDATE formActions
SET config = JSON_SET(
                JSON_REMOVE(config, '$.delete_scheduled'),
                '$.clear_existing_jobs_for_record_and_action',
                JSON_EXTRACT(config, '$.delete_scheduled')
             )
WHERE JSON_EXTRACT(config, '$.delete_scheduled') IS NOT NULL;

