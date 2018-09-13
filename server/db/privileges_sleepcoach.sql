GRANT USAGE ON *.* TO 'sleepcoach'@'localhost';

GRANT SELECT ON `sleepcoach`.* TO 'sleepcoach'@'localhost';

GRANT INSERT, DELETE ON `sleepcoach`.`sections` TO 'sleepcoach'@'localhost';

GRANT INSERT, DELETE ON `sleepcoach`.`users_groups` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (acl_select, acl_delete, acl_update, acl_insert) ON `sleepcoach`.`acl_users` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (position), DELETE ON `sleepcoach`.`pages_sections` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (content) ON `sleepcoach`.`sections_fields_translation` TO 'sleepcoach'@'localhost';

GRANT INSERT ON `sleepcoach`.`user_activity` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (acl_select, acl_delete, acl_update, acl_insert) ON `sleepcoach`.`acl_groups` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (content) ON `sleepcoach`.`pages_fields_translation` TO 'sleepcoach'@'localhost';

GRANT INSERT, DELETE ON `sleepcoach`.`groups` TO 'sleepcoach'@'localhost';

GRANT INSERT ON `sleepcoach`.`user_input` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (position), DELETE ON `sleepcoach`.`sections_hierarchy` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (nav_position), DELETE ON `sleepcoach`.`pages` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (position), DELETE ON `sleepcoach`.`sections_navigation` TO 'sleepcoach'@'localhost';

GRANT INSERT, UPDATE (email, blocked, token, id_genders, name, password), DELETE ON `sleepcoach`.`users` TO 'sleepcoach'@'localhost';
