DROP TABLE IF EXISTS `roles_permissions`;
DROP TABLE IF EXISTS `users_roles`;
DROP TABLE IF EXISTS `api_routes_permissions`;

-- 1. Roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id`          INT AUTO_INCREMENT NOT NULL,
  `name`        VARCHAR(50)    NOT NULL,
  `description` VARCHAR(255)   NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B63E2EC75E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Permissions
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id`          INT AUTO_INCREMENT NOT NULL,
  `name`        VARCHAR(100)   NOT NULL,
  `description` VARCHAR(255)   NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2DEDCC6F5E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Role ↔ Permission
CREATE TABLE IF NOT EXISTS `roles_permissions` (
  `id_permissions` INT NOT NULL,
  `id_roles`       INT NOT NULL,
  PRIMARY KEY (`id_permissions`, `id_roles`),
  KEY `IDX_CEC2E04358BB6FF7`       (`id_roles`),
  CONSTRAINT `FK_CEC2E04335FF0198` FOREIGN KEY (`id_permissions`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CEC2E04358BB6FF7` FOREIGN KEY (`id_roles`)       REFERENCES `roles`       (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. User ↔ Role
CREATE TABLE IF NOT EXISTS `users_roles` (
  `id_users` INT NOT NULL,
  `id_roles` INT NOT NULL,
  PRIMARY KEY (`id_users`, `id_roles`),
  KEY `IDX_51498A8EFA06E4D9` (`id_users`),
  KEY `IDX_51498A8E58BB6FF7` (`id_roles`),
  CONSTRAINT `FK_51498A8EFA06E4D9` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_51498A8E58BB6FF7` FOREIGN KEY (`id_roles`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1) Create the "admin" role
INSERT IGNORE INTO `roles` (`name`, `description`)
VALUES
  ('admin', 'Administrator role with full access');

-- 2) Create the needed permissions
INSERT IGNORE INTO `permissions` (`name`, `description`)
VALUES
  ('admin.access', 'Can view and enter the admin/backend area'),
  ('admin.page.read',   'Can read existing pages'),
  ('admin.page.create',   'Can create new pages'),
  ('admin.page.update',   'Can edit existing pages'),
  ('admin.page.delete',   'Can delete pages'),
  ('admin.page.insert',   'Can insert content into pages'),
  ('admin.page.export',   'Can export sections from pages'),
  ('admin.settings',   'Full access to CMS settings'),
  ('admin.user.read',   'Can read existing users'),
  ('admin.user.create',   'Can create new users'),
  ('admin.user.update',   'Can edit existing users'),
  ('admin.user.delete',   'Can delete users'),
  ('admin.user.block',   'Can block users'),
  ('admin.user.unblock',   'Can unblock users'),
  ('admin.user.impersonate',   'Can impersonate users');

-- 3) Grant those permissions to the admin role
INSERT IGNORE INTO `roles_permissions` (`id_roles`, `id_permissions`)
SELECT (SELECT id FROM roles WHERE `name` = 'admin'), id FROM permissions;

-- 4) Assign the admin role to the admin group users
INSERT IGNORE INTO `users_roles` (`id_users`, `id_roles`)
SELECT
  ug.`id_users`,
  r.`id`
FROM `users_groups` ug
INNER JOIN `groups` g
  ON ug.`id_groups` = g.`id`
  AND g.`name` = 'admin'
INNER JOIN `roles` r
  ON r.`name` = 'admin';

DROP TABLE IF EXISTS `api_routes_permissions`;
DROP TABLE IF EXISTS `api_routes`;
CREATE TABLE IF NOT EXISTS `api_routes` (
  `id`           INT             NOT NULL AUTO_INCREMENT,
  `route_name`   VARCHAR(100)    NOT NULL,
  `version`      VARCHAR(10)     NOT NULL DEFAULT 'v1',
  `path`         VARCHAR(255)    NOT NULL,
  `controller`   VARCHAR(255)    NOT NULL,
  `methods`      VARCHAR(50)     NOT NULL,
  `requirements` JSON            NULL,
  `params`       JSON            NULL COMMENT 'Expected parameters: name → {in: body|query, required: bool}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_route_name_version` (`route_name`, `version`),
  UNIQUE KEY `uniq_version_path_methods` (`version`, `path`, `methods`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_routes_permissions` (
  `id_api_routes`  INT NOT NULL,
  `id_permissions` INT NOT NULL,
  PRIMARY KEY (`id_api_routes`,`id_permissions`),
  KEY `IDX_487141C411A805E4`    (`id_api_routes`),
  KEY `IDX_487141C435FF0198`   (`id_permissions`),
  CONSTRAINT `FK_arp_api_routes`
    FOREIGN KEY (`id_api_routes`)
    REFERENCES `api_routes` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `FK_arp_permissions`
    FOREIGN KEY (`id_permissions`)
    REFERENCES `permissions` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert API routes with proper versioned controllers
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
-- Auth routes
('auth_login', 'v1', '/auth/login', 'App\\Controller\\Api\\V1\\Auth\\AuthController::login', 'POST', NULL, NULL),
('auth_2fa_verify', 'v1', '/auth/two-factor-verify', 'App\\Controller\\Api\\V1\\Auth\\AuthController::twoFactorVerify', 'POST', NULL, NULL),
('auth_refresh_token', 'v1', '/auth/refresh-token', 'App\\Controller\\Api\\V1\\Auth\\AuthController::refreshToken', 'POST', NULL, NULL),
('auth_logout', 'v1', '/auth/logout', 'App\\Controller\\Api\\V1\\Auth\\AuthController::logout', 'POST', NULL, NULL),
('auth_set_language', 'v1', '/auth/set-language', 'App\\Controller\\Api\\V1\\Auth\\AuthController::setUserLanguage', 'POST', NULL, NULL),

('user_validate_token', 'v1', '/validate/{user_id}/{token}', 'App\\Controller\\Api\\V1\\Auth\\UserValidationController::validateToken', 'GET', JSON_OBJECT(
    'user_id', '[0-9]+',
    'token', '[a-f0-9]{32}'
), NULL),

-- Admin routes
('admin_lookups', 'v1', '/admin/lookups', 'App\\Controller\\Api\\V1\\Admin\\Common\\LookupController::getAllLookups', 'GET', NULL, NULL),
('admin_pages_get_all', 'v1', '/admin/pages', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::getPages', 'GET', NULL, NULL),
('admin_pages_get_all_with_language', 'v1', '/admin/pages/{language_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::getPages', 'GET', JSON_OBJECT(
    'language_id', '[0-9]+'
), NULL),
('admin_pages_get_one', 'v1', '/admin/pages/{page_keyword}', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::getPage', 'GET', JSON_OBJECT(
    'page_keyword', '[A-Za-z0-9_-]+'
), NULL),
('admin_pages_create', 'v1', '/admin/pages', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::createPage', 'POST', NULL, JSON_OBJECT(
    'keyword', JSON_OBJECT('in', 'body', 'required', true),
    'pageAccessTypeCode', JSON_OBJECT('in', 'body', 'required', true),
    'headless', JSON_OBJECT('in', 'body', 'required', false),
    'openAccess', JSON_OBJECT('in', 'body', 'required', false),
    'url', JSON_OBJECT('in', 'body', 'required', false),
    'navPosition', JSON_OBJECT('in', 'body', 'required', false),
    'footerPosition', JSON_OBJECT('in', 'body', 'required', false),
    'parent', JSON_OBJECT('in', 'body', 'required', false)
)),
('admin_pages_update', 'v1', '/admin/pages/{page_keyword}', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::updatePage', 'PUT', JSON_OBJECT(
    'page_keyword', '[A-Za-z0-9_-]+'
), JSON_OBJECT(
    'pageData', JSON_OBJECT('in', 'body', 'required', true, 'type', 'object'),
    'fields', JSON_OBJECT('in', 'body', 'required', true, 'type', 'array')
)),
('admin_pages_delete', 'v1', '/admin/pages/{page_keyword}', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::deletePage', 'DELETE', JSON_OBJECT(
    'page_keyword', '[A-Za-z0-9_-]+'
), NULL),
('admin_pages_sections_get', 'v1', '/admin/pages/{page_keyword}/sections', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::getPageSections', 'GET', JSON_OBJECT(
    'page_keyword', '[A-Za-z0-9_-]+'
), NULL),

('admin_languages_get_all', 'v1', '/admin/languages', 'App\\Controller\\Api\\V1\\Admin\\AdminLanguageController::getAllLanguages', 'GET', NULL, NULL),
('admin_languages_get_one', 'v1', '/admin/languages/{id}', 'App\\Controller\\Api\\V1\\Admin\\AdminLanguageController::getLanguage', 'GET', JSON_OBJECT(
    'id', '[0-9]+'
), NULL),
('admin_languages_create', 'v1', '/admin/languages', 'App\\Controller\\Api\\V1\\Admin\\AdminLanguageController::createLanguage', 'POST', NULL, JSON_OBJECT(
    'locale', JSON_OBJECT('in', 'body', 'required', true),
    'language', JSON_OBJECT('in', 'body', 'required', true),
    'csv_separator', JSON_OBJECT('in', 'body', 'required', false)
)),
('admin_languages_update', 'v1', '/admin/languages/{id}', 'App\\Controller\\Api\\V1\\Admin\\AdminLanguageController::updateLanguage', 'PUT', JSON_OBJECT(
    'id', '[0-9]+'
), JSON_OBJECT(
    'locale', JSON_OBJECT('in', 'body', 'required', false),
    'language', JSON_OBJECT('in', 'body', 'required', false),
    'csv_separator', JSON_OBJECT('in', 'body', 'required', false)
)),
('admin_languages_delete', 'v1', '/admin/languages/{id}', 'App\\Controller\\Api\\V1\\Admin\\AdminLanguageController::deleteLanguage', 'DELETE', JSON_OBJECT(
    'id', '[0-9]+'
), NULL),

-- Admin Styles 
('admin_styles_get', 'v1', '/admin/styles', 'App\\Controller\\Api\\V1\\Admin\\AdminStyleController::getStyles', 'GET', NULL, NULL),

-- Admin Page Sections 
('admin_pages_create_section', 'v1', '/admin/pages/{page_keyword}/sections/create', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::createPageSection', 'POST', JSON_OBJECT(
    'page_keyword', '[\\w-]+'
), JSON_OBJECT(
    'styleId', JSON_OBJECT('in', 'body', 'required', true),
    'position', JSON_OBJECT('in', 'body', 'required', true)
)),
('admin_pages_add_section', 'v1', '/admin/pages/{page_keyword}/sections', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::addSectionToPage', 'PUT', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+'
), JSON_OBJECT(
    'sectionId', JSON_OBJECT('in', 'body', 'required', true, 'type', 'integer'),
    'position', JSON_OBJECT('in', 'body', 'required', false, 'type', 'integer'),
    'oldParentSectionId', JSON_OBJECT('in', 'body', 'required', false, 'type', 'integer')
)),
('admin_pages_remove_section', 'v1', '/admin/pages/{page_keyword}/sections/{section_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::removeSectionFromPage', 'DELETE', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'section_id', '[0-9]+'
), NULL),

-- Admin Section in Section 
('admin_sections_create_child', 'v1', '/admin/pages/{page_keyword}/sections/{parent_section_id}/sections/create', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::createChildSection', 'POST', JSON_OBJECT(
    'parent_section_id', '\\d+'
), JSON_OBJECT(
    'styleId', JSON_OBJECT('in', 'body', 'required', true),
    'position', JSON_OBJECT('in', 'body', 'required', true)
)),
('admin_sections_add', 'v1', '/admin/pages/{page_keyword}/sections/{parent_section_id}/sections', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::addSectionToSection', 'PUT', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'parent_section_id', '[0-9]+'
), JSON_OBJECT(
    'childSectionId', JSON_OBJECT('in', 'body', 'required', true, 'type', 'integer'),
    'position', JSON_OBJECT('in', 'body', 'required', false, 'type', 'integer'),
    'oldParentPageId', JSON_OBJECT('in', 'body', 'required', false, 'type', 'integer'),
    'oldParentSectionId', JSON_OBJECT('in', 'body', 'required', false, 'type', 'integer')
)),
('admin_sections_remove', 'v1', '/admin/pages/{page_keyword}/sections/{parent_section_id}/sections/{child_section_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::removeSectionFromSection', 'DELETE', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'parent_section_id', '[0-9]+',
    'child_section_id', '[0-9]+'
), NULL),

-- Admin Section
('admin_sections_update', 'v1', '/admin/pages/{page_keyword}/sections/{section_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::updateSection', 'PUT', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'section_id', '[0-9]+'
), JSON_OBJECT(
    'sectionId', JSON_OBJECT('in', 'body', 'required', true, 'type', 'integer'),
    'sectionName', JSON_OBJECT('in', 'body', 'required', true, 'type', 'string'),
    'contentFields', JSON_OBJECT('in', 'body', 'required', true, 'type', 'array'),
    'propertyFields', JSON_OBJECT('in', 'body', 'required', true, 'type', 'array')
)),
('admin_sections_delete', 'v1', '/admin/pages/{page_keyword}/sections/{section_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::deleteSection', 'DELETE', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'section_id', '[0-9]+'
), NULL),
('admin_sections_get', 'v1', '/admin/pages/{page_keyword}/sections/{section_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::getSection', 'GET', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'section_id', '[0-9]+'
), NULL),
('admin_sections_get_children_sections', 'v1', '/admin/pages/{page_keyword}/sections/{parent_section_id}/sections', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::getChildrenSections', 'GET', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'parent_section_id', '[0-9]+'
), NULL),

-- Section Export/Import routes
('admin_sections_export_page', 'v1', '/admin/pages/{page_keyword}/sections/export', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::exportPageSections', 'GET', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+'
), NULL),
('admin_sections_export_section', 'v1', '/admin/pages/{page_keyword}/sections/{section_id}/export', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::exportSection', 'GET', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'section_id', '[0-9]+'
), NULL),
('admin_sections_import_to_page', 'v1', '/admin/pages/{page_keyword}/sections/import', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::importSectionsToPage', 'POST', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+'
), JSON_OBJECT(
    'sections', JSON_OBJECT('in', 'body', 'required', true, 'type', 'array'),
    'position', JSON_OBJECT('in', 'body', 'required', false, 'type', 'integer')
)),
('admin_sections_import_to_section', 'v1', '/admin/pages/{page_keyword}/sections/{parent_section_id}/import', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::importSectionsToSection', 'POST', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'parent_section_id', '[0-9]+'
), JSON_OBJECT(
    'sections', JSON_OBJECT('in', 'body', 'required', true, 'type', 'array'),
    'position', JSON_OBJECT('in', 'body', 'required', false, 'type', 'integer')
)),

-- Public pages route
('pages_get_all', 'v1', '/pages', 'App\\Controller\\Api\\V1\\Frontend\\PageController::getPages', 'GET', NULL, NULL),
('pages_get_all_with_language', 'v1', '/pages/{language_id}', 'App\\Controller\\Api\\V1\\Frontend\\PageController::getPages', 'GET', JSON_OBJECT(
    'language_id', '[0-9]+'
), NULL),
('pages_get_one', 'v1', '/pages/{page_keyword}', 'App\\Controller\\Api\\V1\\Frontend\\PageController::getPage', 'GET', NULL, NULL),
('languages_get_all', 'v1', '/languages', 'App\\Controller\\Api\\V1\\Frontend\\LanguageController::getAllLanguages', 'GET', NULL, NULL),

-- Form submission routes (public access)
('form_submit', 'v1', '/forms/submit', 'App\\Controller\\Api\\V1\\Frontend\\FormController::submitForm', 'POST', NULL, JSON_OBJECT(
    'page_id', JSON_OBJECT('in', 'body', 'required', true),
    'form_id', JSON_OBJECT('in', 'body', 'required', true),
    'form_data', JSON_OBJECT('in', 'body', 'required', true)
)),
('form_update', 'v1', '/forms/update', 'App\\Controller\\Api\\V1\\Frontend\\FormController::updateForm', 'PUT', NULL, JSON_OBJECT(
    'page_id', JSON_OBJECT('in', 'body', 'required', true),
    'form_id', JSON_OBJECT('in', 'body', 'required', true),
    'form_data', JSON_OBJECT('in', 'body', 'required', true),
    'update_based_on', JSON_OBJECT('in', 'body', 'required', false)
)),
('form_delete', 'v1', '/forms/delete', 'App\\Controller\\Api\\V1\\Frontend\\FormController::deleteForm', 'DELETE', NULL, JSON_OBJECT(
    'record_id', JSON_OBJECT('in', 'query', 'required', true)
));

-- add `admin.page.read` requirements to routes
INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.page.read'
WHERE ar.`route_name` IN (
  'admin_pages_get_all',
  'admin_pages_get_all_with_language',
  'admin_pages_get_one',
  'admin_pages_sections_get'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.access'
WHERE ar.`route_name` IN (
  'admin_lookups'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.page.create'
WHERE ar.`route_name` IN (
  'admin_pages_create'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.page.delete'
WHERE ar.`route_name` IN (
  'admin_pages_delete'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.page.update'
WHERE ar.`route_name` IN (
	'admin_pages_update',
	'admin_pages_create_section',
	'admin_pages_add_section',
	'admin_pages_remove_section',
	'admin_sections_create_child',
	'admin_sections_add',
	'admin_sections_remove',
	'admin_sections_update',
    'admin_sections_delete'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.settings'
WHERE ar.`route_name` IN (
  'admin_languages_get_all','admin_languages_get_one','admin_languages_create','admin_languages_update', 'admin_languages_delete'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.page.export'
WHERE ar.`route_name` IN (
  'admin_sections_export_page','admin_sections_export_section','admin_sections_import_to_page','admin_sections_import_to_section'
);

-- User Management API Routes

-- Get users with pagination
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_get_all_v1', 'v1', '/admin/users', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::getUsers', 'GET', NULL, JSON_OBJECT(
    'page', JSON_OBJECT('in', 'query', 'required', false),
    'pageSize', JSON_OBJECT('in', 'query', 'required', false),
    'search', JSON_OBJECT('in', 'query', 'required', false),
    'sort', JSON_OBJECT('in', 'query', 'required', false),
    'sortDirection', JSON_OBJECT('in', 'query', 'required', false)
));

-- Get single user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_get_one_v1', 'v1', '/admin/users/{userId}', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::getUserById', 'GET', JSON_OBJECT('userId', '[0-9]+'), NULL);

-- Create user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_create_v1', 'v1', '/admin/users', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::createUser', 'POST', NULL, JSON_OBJECT(
    'email', JSON_OBJECT('in', 'body', 'required', true),
    'name', JSON_OBJECT('in', 'body', 'required', false),
    'user_name', JSON_OBJECT('in', 'body', 'required', false),
    'password', JSON_OBJECT('in', 'body', 'required', false),
    'user_type_id', JSON_OBJECT('in', 'body', 'required', false),
    'blocked', JSON_OBJECT('in', 'body', 'required', false),
    'id_genders', JSON_OBJECT('in', 'body', 'required', false),
    'id_languages', JSON_OBJECT('in', 'body', 'required', false),
    'validation_code', JSON_OBJECT('in', 'body', 'required', true),
    'group_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', false),
    'role_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', false)
));

-- Update user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_update_v1', 'v1', '/admin/users/{userId}', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::updateUser', 'PUT', JSON_OBJECT('userId', '[0-9]+'), JSON_OBJECT(
    'email', JSON_OBJECT('in', 'body', 'required', false),
    'name', JSON_OBJECT('in', 'body', 'required', false),
    'user_name', JSON_OBJECT('in', 'body', 'required', false),
    'password', JSON_OBJECT('in', 'body', 'required', false),
    'user_type_id', JSON_OBJECT('in', 'body', 'required', false),
    'blocked', JSON_OBJECT('in', 'body', 'required', false),
    'id_genders', JSON_OBJECT('in', 'body', 'required', false),
    'id_languages', JSON_OBJECT('in', 'body', 'required', false)
));

-- Delete user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_delete_v1', 'v1', '/admin/users/{userId}', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::deleteUser', 'DELETE', JSON_OBJECT('userId', '[0-9]+'), NULL);

-- Block/Unblock user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_block_v1', 'v1', '/admin/users/{userId}/block', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::toggleUserBlock', 'PATCH', JSON_OBJECT('userId', '[0-9]+'), JSON_OBJECT(
    'blocked', JSON_OBJECT('in', 'body', 'required', true)
));

-- Get user groups
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_groups_get_v1', 'v1', '/admin/users/{userId}/groups', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::getUserGroups', 'GET', JSON_OBJECT('userId', '[0-9]+'), NULL);

-- Add groups to user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_groups_add_v1', 'v1', '/admin/users/{userId}/groups', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::addGroupsToUser', 'POST', JSON_OBJECT('userId', '[0-9]+'), JSON_OBJECT(
    'group_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', true)
));

-- Remove groups from user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_groups_remove_v1', 'v1', '/admin/users/{userId}/groups', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::removeGroupsFromUser', 'DELETE', JSON_OBJECT('userId', '[0-9]+'), JSON_OBJECT(
    'group_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', true)
));

-- Get user roles
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_roles_get_v1', 'v1', '/admin/users/{userId}/roles', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::getUserRoles', 'GET', JSON_OBJECT('userId', '[0-9]+'), NULL);

-- Add roles to user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_roles_add_v1', 'v1', '/admin/users/{userId}/roles', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::addRolesToUser', 'POST', JSON_OBJECT('userId', '[0-9]+'), JSON_OBJECT(
    'role_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', true)
));

-- Remove roles from user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_roles_remove_v1', 'v1', '/admin/users/{userId}/roles', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::removeRolesFromUser', 'DELETE', JSON_OBJECT('userId', '[0-9]+'), JSON_OBJECT(
    'role_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', true)
));

-- Send activation mail
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_send_activation_v1', 'v1', '/admin/users/{userId}/send-activation-mail', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::sendActivationMail', 'POST', JSON_OBJECT('userId', '[0-9]+'), NULL);

-- Clean user data
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_clean_data_v1', 'v1', '/admin/users/{userId}/clean-data', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::cleanUserData', 'POST', JSON_OBJECT('userId', '[0-9]+'), NULL);

-- Impersonate user
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_users_impersonate_v1', 'v1', '/admin/users/{userId}/impersonate', 'App\\Controller\\Api\\V1\\Admin\\AdminUserController::impersonateUser', 'POST', JSON_OBJECT('userId', '[0-9]+'), NULL);



INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.user.read'
WHERE ar.`route_name` IN (
  'admin_users_get_all_v1',
  'admin_users_get_one_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.user.create'
WHERE ar.`route_name` IN (
  'admin_users_create_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.user.update'
WHERE ar.`route_name` IN (
  'admin_users_update_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.user.delete'
WHERE ar.`route_name` IN (
  'admin_users_delete_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.user.block'
WHERE ar.`route_name` IN (
  'admin_users_block_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.user.unblock'
WHERE ar.`route_name` IN (
  'admin_users_unblock_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.user.impersonate'
WHERE ar.`route_name` IN (
  'admin_users_impersonate_v1'
);

-- Group Management API Routes

-- Get groups with pagination
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_groups_get_all_v1', 'v1', '/admin/groups', 'App\\Controller\\Api\\V1\\Admin\\AdminGroupController::getGroups', 'GET', NULL, JSON_OBJECT(
    'page', JSON_OBJECT('in', 'query', 'required', false),
    'pageSize', JSON_OBJECT('in', 'query', 'required', false),
    'search', JSON_OBJECT('in', 'query', 'required', false),
    'sort', JSON_OBJECT('in', 'query', 'required', false),
    'sortDirection', JSON_OBJECT('in', 'query', 'required', false)
));

-- Get single group
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_groups_get_one_v1', 'v1', '/admin/groups/{groupId}', 'App\\Controller\\Api\\V1\\Admin\\AdminGroupController::getGroupById', 'GET', JSON_OBJECT('groupId', '[0-9]+'), NULL);

-- Create group
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_groups_create_v1', 'v1', '/admin/groups', 'App\\Controller\\Api\\V1\\Admin\\AdminGroupController::createGroup', 'POST', NULL, JSON_OBJECT(
    'name', JSON_OBJECT('in', 'body', 'required', true),
    'description', JSON_OBJECT('in', 'body', 'required', false),
    'id_group_types', JSON_OBJECT('in', 'body', 'required', false),
    'requires_2fa', JSON_OBJECT('in', 'body', 'required', false),
    'acls', JSON_OBJECT('in', 'body', 'type', 'array', 'required', false)
));

-- Update group
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_groups_update_v1', 'v1', '/admin/groups/{groupId}', 'App\\Controller\\Api\\V1\\Admin\\AdminGroupController::updateGroup', 'PUT', JSON_OBJECT('groupId', '[0-9]+'), JSON_OBJECT(
    'name', JSON_OBJECT('in', 'body', 'required', false),
    'description', JSON_OBJECT('in', 'body', 'required', false),
    'id_group_types', JSON_OBJECT('in', 'body', 'required', false),
    'requires_2fa', JSON_OBJECT('in', 'body', 'required', false),
    'acls', JSON_OBJECT('in', 'body', 'type', 'array', 'required', false)
));

-- Delete group
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_groups_delete_v1', 'v1', '/admin/groups/{groupId}', 'App\\Controller\\Api\\V1\\Admin\\AdminGroupController::deleteGroup', 'DELETE', JSON_OBJECT('groupId', '[0-9]+'), NULL);

-- Get group ACLs
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_groups_acls_get_v1', 'v1', '/admin/groups/{groupId}/acls', 'App\\Controller\\Api\\V1\\Admin\\AdminGroupController::getGroupAcls', 'GET', JSON_OBJECT('groupId', '[0-9]+'), NULL);

-- Update group ACLs
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_groups_acls_update_v1', 'v1', '/admin/groups/{groupId}/acls', 'App\\Controller\\Api\\V1\\Admin\\AdminGroupController::updateGroupAcls', 'PUT', JSON_OBJECT('groupId', '[0-9]+'), JSON_OBJECT(
    'acls', JSON_OBJECT('in', 'body', 'type', 'array', 'required', true)
));

-- Role Management API Routes

-- Get roles with pagination
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_get_all_v1', 'v1', '/admin/roles', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::getRoles', 'GET', NULL, JSON_OBJECT(
    'page', JSON_OBJECT('in', 'query', 'required', false),
    'pageSize', JSON_OBJECT('in', 'query', 'required', false),
    'search', JSON_OBJECT('in', 'query', 'required', false),
    'sort', JSON_OBJECT('in', 'query', 'required', false),
    'sortDirection', JSON_OBJECT('in', 'query', 'required', false)
));

-- Get single role
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_get_one_v1', 'v1', '/admin/roles/{roleId}', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::getRoleById', 'GET', JSON_OBJECT('roleId', '[0-9]+'), NULL);

-- Create role
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_create_v1', 'v1', '/admin/roles', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::createRole', 'POST', NULL, JSON_OBJECT(
    'name', JSON_OBJECT('in', 'body', 'required', true),
    'description', JSON_OBJECT('in', 'body', 'required', false),
    'permission_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', false)
));

-- Update role
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_update_v1', 'v1', '/admin/roles/{roleId}', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::updateRole', 'PUT', JSON_OBJECT('roleId', '[0-9]+'), JSON_OBJECT(
    'name', JSON_OBJECT('in', 'body', 'required', false),
    'description', JSON_OBJECT('in', 'body', 'required', false),
    'permission_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', false)
));

-- Delete role
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_delete_v1', 'v1', '/admin/roles/{roleId}', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::deleteRole', 'DELETE', JSON_OBJECT('roleId', '[0-9]+'), NULL);

-- Get role permissions
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_permissions_get_v1', 'v1', '/admin/roles/{roleId}/permissions', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::getRolePermissions', 'GET', JSON_OBJECT('roleId', '[0-9]+'), NULL);

-- Add permissions to role
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_permissions_add_v1', 'v1', '/admin/roles/{roleId}/permissions', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::addPermissionsToRole', 'POST', JSON_OBJECT('roleId', '[0-9]+'), JSON_OBJECT(
    'permission_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', true)
));

-- Remove permissions from role
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_permissions_remove_v1', 'v1', '/admin/roles/{roleId}/permissions', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::removePermissionsFromRole', 'DELETE', JSON_OBJECT('roleId', '[0-9]+'), JSON_OBJECT(
    'permission_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', true)
));

-- Update role permissions (bulk replace)
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_roles_permissions_update_v1', 'v1', '/admin/roles/{roleId}/permissions', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::updateRolePermissions', 'PUT', JSON_OBJECT('roleId', '[0-9]+'), JSON_OBJECT(
    'permission_ids', JSON_OBJECT('in', 'body', 'type', 'array', 'required', true)
));

-- Get all permissions
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_permissions_get_all_v1', 'v1', '/admin/permissions', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::getAllPermissions', 'GET', NULL, NULL);

-- Create permissions for groups and roles management
INSERT IGNORE INTO `permissions` (`name`, `description`)
VALUES
  ('admin.group.read', 'Can read existing groups'),
  ('admin.group.create', 'Can create new groups'),
  ('admin.group.update', 'Can edit existing groups'),
  ('admin.group.delete', 'Can delete groups'),
  ('admin.group.acl', 'Can manage group ACLs'),
  ('admin.role.read', 'Can read existing roles'),
  ('admin.role.create', 'Can create new roles'),
  ('admin.role.update', 'Can edit existing roles'),
  ('admin.role.delete', 'Can delete roles'),
  ('admin.role.permissions', 'Can manage role permissions'),
  ('admin.permission.read', 'Can read all available permissions');

-- Grant group permissions to admin role
INSERT IGNORE INTO `roles_permissions` (`id_roles`, `id_permissions`)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'admin' AND p.name IN (
  'admin.group.read', 'admin.group.create', 'admin.group.update', 'admin.group.delete', 'admin.group.acl'
);

-- Grant role permissions to admin role
INSERT IGNORE INTO `roles_permissions` (`id_roles`, `id_permissions`)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'admin' AND p.name IN (
  'admin.role.read', 'admin.role.create', 'admin.role.update', 'admin.role.delete', 'admin.role.permissions', 'admin.permission.read'
);

-- Link group routes to permissions
INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.group.read'
WHERE ar.`route_name` IN (
  'admin_groups_get_all_v1',
  'admin_groups_get_one_v1',
  'admin_groups_acls_get_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.group.create'
WHERE ar.`route_name` IN (
  'admin_groups_create_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.group.update'
WHERE ar.`route_name` IN (
  'admin_groups_update_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.group.delete'
WHERE ar.`route_name` IN (
  'admin_groups_delete_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.group.acl'
WHERE ar.`route_name` IN (
  'admin_groups_acls_update_v1'
);

-- Link role routes to permissions
INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.role.read'
WHERE ar.`route_name` IN (
  'admin_roles_get_all_v1',
  'admin_roles_get_one_v1',
  'admin_roles_permissions_get_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.role.create'
WHERE ar.`route_name` IN (
  'admin_roles_create_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.role.update'
WHERE ar.`route_name` IN (
  'admin_roles_update_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.role.delete'
WHERE ar.`route_name` IN (
  'admin_roles_delete_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.role.permissions'
WHERE ar.`route_name` IN (
  'admin_roles_permissions_add_v1',
  'admin_roles_permissions_remove_v1',
  'admin_roles_permissions_update_v1'
);

-- Link permissions endpoint to permission
INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.permission.read'
WHERE ar.`route_name` IN (
  'admin_permissions_get_all_v1'
);


-- Admin Gender routes
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_genders_get_all_v1', 'v1', '/admin/genders', 'App\\Controller\\Api\\V1\\Admin\\AdminGenderController::getAllGenders', 'GET', NULL, NULL);

-- Admin CMS Preferences routes
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_cms_preferences_get_v1', 'v1', '/admin/cms-preferences', 'App\\Controller\\Api\\V1\\Admin\\AdminCmsPreferenceController::getCmsPreferences', 'GET', NULL, NULL);

INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_cms_preferences_update_v1', 'v1', '/admin/cms-preferences', 'App\\Controller\\Api\\V1\\Admin\\AdminCmsPreferenceController::updateCmsPreferences', 'PUT', NULL, JSON_OBJECT(
    'callback_api_key', JSON_OBJECT('in', 'body', 'required', false),
    'default_language_id', JSON_OBJECT('in', 'body', 'required', false),
    'anonymous_users', JSON_OBJECT('in', 'body', 'required', false),
    'firebase_config', JSON_OBJECT('in', 'body', 'required', false)
));

-- Admin Asset routes
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_assets_get_all_v1', 'v1', '/admin/assets', 'App\\Controller\\Api\\V1\\Admin\\AdminAssetController::getAllAssets', 'GET', NULL, JSON_OBJECT(
    'page', JSON_OBJECT('in', 'query', 'required', false),
    'pageSize', JSON_OBJECT('in', 'query', 'required', false),
    'search', JSON_OBJECT('in', 'query', 'required', false),
    'folder', JSON_OBJECT('in', 'query', 'required', false)
));

INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_assets_get_one_v1', 'v1', '/admin/assets/{assetId}', 'App\\Controller\\Api\\V1\\Admin\\AdminAssetController::getAssetById', 'GET', JSON_OBJECT(
    'assetId', '[0-9]+'
), NULL);

INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_assets_create_v1', 'v1', '/admin/assets', 'App\\Controller\\Api\\V1\\Admin\\AdminAssetController::createAsset', 'POST', NULL, JSON_OBJECT(
    'file', JSON_OBJECT('in', 'form', 'required', false),
    'files', JSON_OBJECT('in', 'form', 'required', false),
    'folder', JSON_OBJECT('in', 'form', 'required', false),
    'file_name', JSON_OBJECT('in', 'form', 'required', false),
    'file_names', JSON_OBJECT('in', 'form', 'required', false),
    'overwrite', JSON_OBJECT('in', 'form', 'required', false)
));

INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_assets_delete_v1', 'v1', '/admin/assets/{assetId}', 'App\\Controller\\Api\\V1\\Admin\\AdminAssetController::deleteAsset', 'DELETE', JSON_OBJECT(
    'assetId', '[0-9]+'
), NULL);

-- Update API routes for assets to support pagination and search
UPDATE `api_routes` 
SET `params` = JSON_OBJECT(
    'page', JSON_OBJECT('in', 'query', 'required', false),
    'pageSize', JSON_OBJECT('in', 'query', 'required', false),
    'search', JSON_OBJECT('in', 'query', 'required', false),
    'folder', JSON_OBJECT('in', 'query', 'required', false)
)
WHERE `route_name` = 'admin_assets_get_all_v1';

-- Update asset creation route to support multiple files
UPDATE `api_routes` 
SET `params` = JSON_OBJECT(
    'file', JSON_OBJECT('in', 'form', 'required', false),
    'files', JSON_OBJECT('in', 'form', 'required', false),
    'folder', JSON_OBJECT('in', 'form', 'required', false),
    'file_name', JSON_OBJECT('in', 'form', 'required', false),
    'file_names', JSON_OBJECT('in', 'form', 'required', false),
    'overwrite', JSON_OBJECT('in', 'form', 'required', false)
)
WHERE `route_name` = 'admin_assets_create_v1';

-- Create permissions for new features
INSERT IGNORE INTO `permissions` (`name`, `description`)
VALUES
  ('admin.gender.read', 'Can read genders'),
  ('admin.cms_preferences.read', 'Can read CMS preferences'),
  ('admin.cms_preferences.update', 'Can update CMS preferences'),
  ('admin.asset.read', 'Can read assets'),
  ('admin.asset.create', 'Can create assets'),
  ('admin.asset.delete', 'Can delete assets');

-- Grant new permissions to admin role
INSERT IGNORE INTO `roles_permissions` (`id_roles`, `id_permissions`)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'admin' AND p.name IN (
  'admin.gender.read', 'admin.cms_preferences.read', 'admin.cms_preferences.update',
  'admin.asset.read', 'admin.asset.create', 'admin.asset.delete'
);

-- Link new routes to permissions
INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.gender.read'
WHERE ar.`route_name` IN (
  'admin_genders_get_all_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.cms_preferences.read'
WHERE ar.`route_name` IN (
  'admin_cms_preferences_get_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.cms_preferences.update'
WHERE ar.`route_name` IN (
  'admin_cms_preferences_update_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.asset.read'
WHERE ar.`route_name` IN (
  'admin_assets_get_all_v1',
  'admin_assets_get_one_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.asset.create'
WHERE ar.`route_name` IN (
  'admin_assets_create_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.asset.delete'
WHERE ar.`route_name` IN (
  'admin_assets_delete_v1'
);

-- Add permissions API route
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_permissions_get_all_v1', 'v1', '/admin/permissions', 'App\\Controller\\Api\\V1\\Admin\\AdminRoleController::getAllPermissions', 'GET', NULL, NULL);

-- Add permission for reading all permissions
INSERT IGNORE INTO `permissions` (`name`, `description`)
VALUES ('admin.permission.read', 'Can read all available permissions');

-- Add permissions for scheduled jobs
INSERT IGNORE INTO `permissions` (`name`, `description`)
VALUES 
  ('admin.scheduled_job.read', 'Can read scheduled jobs'),
  ('admin.scheduled_job.execute', 'Can execute scheduled jobs'),
  ('admin.scheduled_job.delete', 'Can delete scheduled jobs');

-- Grant permission to admin role
INSERT IGNORE INTO `roles_permissions` (`id_roles`, `id_permissions`)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'admin' AND p.name IN (
  'admin.permission.read',
  'admin.scheduled_job.read',
  'admin.scheduled_job.execute',
  'admin.scheduled_job.delete'
);

-- Add CSS classes API route (open access - no authentication required)
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('frontend_css_classes_get_all', 'v1', '/frontend/css-classes', 'App\\Controller\\CssController::getCssClasses', 'GET', NULL, NULL);

-- Add scheduled jobs API routes
INSERT IGNORE INTO `api_routes` (`route_name`, `version`, `path`, `controller`, `methods`, `requirements`, `params`) VALUES
('admin_scheduled_jobs_get_all_v1', 'v1', '/admin/scheduled-jobs', 'App\\Controller\\Api\\V1\\Admin\\AdminScheduledJobController::getScheduledJobs', 'GET', NULL, NULL),
('admin_scheduled_jobs_get_one_v1', 'v1', '/admin/scheduled-jobs/{jobId}', 'App\\Controller\\Api\\V1\\Admin\\AdminScheduledJobController::getScheduledJobById', 'GET', '{"jobId": "[0-9]+"}', NULL),
('admin_scheduled_jobs_execute_v1', 'v1', '/admin/scheduled-jobs/{jobId}/execute', 'App\\Controller\\Api\\V1\\Admin\\AdminScheduledJobController::executeScheduledJob', 'POST', '{"jobId": "[0-9]+"}', NULL),
('admin_scheduled_jobs_delete_v1', 'v1', '/admin/scheduled-jobs/{jobId}', 'App\\Controller\\Api\\V1\\Admin\\AdminScheduledJobController::deleteScheduledJob', 'DELETE', '{"jobId": "[0-9]+"}', NULL),
('admin_scheduled_jobs_transactions_v1', 'v1', '/admin/scheduled-jobs/{jobId}/transactions', 'App\\Controller\\Api\\V1\\Admin\\AdminScheduledJobController::getJobTransactions', 'GET', '{"jobId": "[0-9]+"}', NULL);

-- Link permissions endpoint to permission
INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.permission.read'
WHERE ar.`route_name` IN (
  'admin_permissions_get_all_v1'
);

-- Link scheduled jobs routes to permissions
INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.scheduled_job.read'
WHERE ar.`route_name` IN (
  'admin_scheduled_jobs_get_all_v1',
  'admin_scheduled_jobs_get_one_v1',
  'admin_scheduled_jobs_transactions_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.scheduled_job.execute'
WHERE ar.`route_name` IN (
  'admin_scheduled_jobs_execute_v1'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'admin.scheduled_job.delete'
WHERE ar.`route_name` IN (
  'admin_scheduled_jobs_delete_v1'
);

-- allways last
-- give role admin to all users who had group admins
INSERT IGNORE INTO users_roles (id_users, id_roles)
SELECT ug.id_users, r.id
FROM users_groups ug
INNER JOIN `groups` g ON ug.id_groups = g.id
INNER JOIN roles  r ON r.name = 'admin'
WHERE g.name = 'admin';