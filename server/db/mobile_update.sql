-- set DB version
UPDATE version
SET version = 'v4.0.0';

-- add transactionBy
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionBy', 'by_system', 'By Selfhelp', 'By Selfhelp');

-- device_id field in table users
ALTER TABLE users
ADD COLUMN device_id VARCHAR(100);