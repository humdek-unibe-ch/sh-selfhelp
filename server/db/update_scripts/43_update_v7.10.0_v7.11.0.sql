-- set DB version
UPDATE version
SET version = 'v7.11.0';

-- Index user_activity.timestamp.
-- Router::get_other_users_editing_this_page() (the "other users are editing this
-- page" warning of the CMS) only looks at the last 15 minutes of user_activity,
-- but without an index on timestamp MySQL scans the whole table. Every request
-- inserts a row, so the table only grows (~700k rows on a dev instance, where the
-- query took ~180 ms per call; with the index it takes < 1 ms).
CALL add_index('user_activity', 'idx_user_activity_timestamp', 'timestamp');
