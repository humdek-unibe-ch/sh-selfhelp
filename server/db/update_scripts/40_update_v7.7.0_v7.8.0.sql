-- set DB version
UPDATE version
SET version = 'v7.8.0';

-- =====================================================
-- REFRESH EVENTS (Global polling mechanism)
-- Generic mechanism for notifying users of background
-- task completions and triggering partial page refreshes.
-- =====================================================

CREATE TABLE IF NOT EXISTS `refresh_events` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_users` INT(10) UNSIGNED ZEROFILL NOT NULL,
    `event_type` VARCHAR(50) DEFAULT 'background_task_completed',
    `event_data` TEXT DEFAULT NULL,
    `consumed` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user_consumed` (`id_users`, `consumed`),
    CONSTRAINT `fk_refresh_events_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `refresh_events_sections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_refresh_events` INT UNSIGNED NOT NULL,
    `id_sections` INT(10) UNSIGNED ZEROFILL NOT NULL,
    CONSTRAINT `fk_refresh_sections_events` FOREIGN KEY (`id_refresh_events`) REFERENCES `refresh_events` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_refresh_sections_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- EVENT LISTENER PAGE FIELDS
-- Generic fields that can be attached to any page type
-- to enable client-side polling for refresh events.
-- =====================================================

INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES
(NULL, 'enable_event_listener', get_field_type_id('checkbox'), '0'),
(NULL, 'event_listener_interval', get_field_type_id('number'), '0');

-- =====================================================
-- ATTACH EVENT LISTENER FIELDS TO PAGE TYPES
-- Every experimental page (and core/email pages) can be
-- configured to poll for refresh events. When enabled,
-- the page automatically loads event-listener.js and
-- refreshes specified sections on background task completion.
-- Currently only LLM scripts generate these events, but
-- the mechanism is generic and can be extended.
-- Page types:  experiment (3), open (4)
-- =====================================================

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES
('0000000003', get_field_id('enable_event_listener'), '0', 'Enable polling for refresh events on this page. When enabled, the page will automatically check for background task completions and refresh the specified sections. Default: disabled.'),
('0000000003', get_field_id('event_listener_interval'), '5', 'Polling interval in seconds for checking refresh events. Lower values mean faster updates but increase server load. Default: 5 seconds.'),
('0000000004', get_field_id('enable_event_listener'), '0', 'Enable polling for refresh events on this page. When enabled, the page will automatically check for background task completions and refresh the specified sections. Default: disabled.'),
('0000000004', get_field_id('event_listener_interval'), '5', 'Polling interval in seconds for checking refresh events. Lower values mean faster updates but increase server load. Default: 5 seconds.');

-- =====================================================
-- AJAX ENDPOINT for refresh events polling
-- =====================================================

INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`)
VALUES (NULL, 'ajax_refresh_events_check', '/request/[AjaxRefreshEvents:class]/[check:method]', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'ajax' LIMIT 1), NULL, NULL, '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE type_code = 'pageAccessTypes' AND lookup_code = 'mobile_and_web'));

INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`)
VALUES ('0000000001', (SELECT id FROM pages WHERE keyword = 'ajax_refresh_events_check'), '1', '0', '0', '0');

-- =====================================================
-- LAST USER PAGE TRACKING
-- Add second_last_url to track the previous page so
-- #last_user_page links can avoid pointing to the
-- current page.
-- =====================================================

CALL add_table_column('users', 'second_last_url', 'VARCHAR(100) DEFAULT NULL AFTER `last_url`');
