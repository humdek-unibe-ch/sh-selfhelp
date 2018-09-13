GRANT USAGE ON *.* TO 'sleepcoach_test'@'localhost';

GRANT SELECT ON `sleepcoach_test`.* TO 'sleepcoach_test'@'localhost';

GRANT INSERT, DELETE ON `sleepcoach_test`.`sections` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, DELETE ON `sleepcoach_test`.`users_groups` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (acl_select, acl_delete, acl_update, acl_insert) ON `sleepcoach_test`.`acl_users` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (position), DELETE ON `sleepcoach_test`.`pages_sections` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (content) ON `sleepcoach_test`.`sections_fields_translation` TO 'sleepcoach_test'@'localhost';

GRANT INSERT ON `sleepcoach_test`.`user_activity` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (acl_select, acl_delete, acl_update, acl_insert) ON `sleepcoach_test`.`acl_groups` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (content) ON `sleepcoach_test`.`pages_fields_translation` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, DELETE ON `sleepcoach_test`.`groups` TO 'sleepcoach_test'@'localhost';

GRANT INSERT ON `sleepcoach_test`.`user_input` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (position), DELETE ON `sleepcoach_test`.`sections_hierarchy` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (nav_position), DELETE ON `sleepcoach_test`.`pages` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (position), DELETE ON `sleepcoach_test`.`sections_navigation` TO 'sleepcoach_test'@'localhost';

GRANT INSERT, UPDATE (email, blocked, token, id_genders, name, password), DELETE ON `sleepcoach_test`.`users` TO 'sleepcoach_test'@'localhost';
