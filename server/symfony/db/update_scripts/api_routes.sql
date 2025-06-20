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
  ('admin.settings',   'Full access to CMS settings');

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
('auth_login', 'v1', '/auth/login', 'App\\Controller\\Api\\V1\\Auth\\AuthController::login', 'POST', NULL, JSON_OBJECT(
    'user', JSON_OBJECT('in', 'body', 'required', true),
    'password', JSON_OBJECT('in', 'body', 'required', true)
)),
('auth_two_factor_verify', 'v1', '/auth/two-factor-verify', 'App\\Controller\\Api\\V1\\Auth\\AuthController::twoFactorVerify', 'POST', NULL, JSON_OBJECT(
    'code', JSON_OBJECT('in', 'body', 'required', true),
    'id_users', JSON_OBJECT('in', 'body', 'required', true)
)),
('auth_refresh_token', 'v1', '/auth/refresh-token', 'App\\Controller\\Api\\V1\\Auth\\AuthController::refreshToken', 'POST', NULL, JSON_OBJECT(
    'refresh_token', JSON_OBJECT('in', 'body', 'required', true)
)),
('auth_logout', 'v1', '/auth/logout', 'App\\Controller\\Api\\V1\\Auth\\AuthController::logout', 'POST', NULL, JSON_OBJECT(
    'access_token', JSON_OBJECT('in', 'body', 'required', false),
    'refresh_token', JSON_OBJECT('in', 'body', 'required', false)
)),

-- Admin routes
('admin_lookups', 'v1', '/admin/lookups', 'App\\Controller\\Api\\V1\\Admin\\Common\\LookupController::getAllLookups', 'GET', NULL, NULL),
('admin_pages_get_all', 'v1', '/admin/pages', 'App\\Controller\\Api\\V1\\Admin\\AdminPageController::getPages', 'GET', NULL, NULL),
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
('admin_sections_create_child', 'v1', '/admin/sections/{parent_section_id}/child-sections/create', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::createChildSection', 'POST', JSON_OBJECT(
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
('admin_sections_update', 'v1', '/admin/sections/{section_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::updateSection', 'PUT', JSON_OBJECT(
    'section_id', '[0-9]+'
), JSON_OBJECT(
    'position', JSON_OBJECT('in', 'body', 'required', false, 'type', 'integer')
)),
('admin_sections_delete', 'v1', '/admin/sections/{section_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::deleteSection', 'DELETE', JSON_OBJECT(
    'section_id', '[0-9]+'
), NULL),
('admin_sections_get', 'v1', '/admin/sections/{section_id}', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::getSection', 'GET', JSON_OBJECT(
    'section_id', '[0-9]+'
), NULL),
('admin_sections_get_children_sections', 'v1', '/admin/pages/{page_keyword}/sections/{parent_section_id}/sections', 'App\\Controller\\Api\\V1\\Admin\\AdminSectionController::getChildrenSections', 'GET', JSON_OBJECT(
    'page_keyword', '[a-zA-Z0-9_-]+',
    'parent_section_id', '[0-9]+'
), NULL),

-- Public pages route
('pages_get_all', 'v1', '/pages', 'App\\Controller\\Api\\V1\\Frontend\\PageController::getPages', 'GET', NULL, NULL),
('pages_get_one', 'v1', '/pages/{page_keyword}', 'App\\Controller\\Api\\V1\\Frontend\\PageController::getPage', 'GET', NULL, NULL),
('languages_get_all', 'v1', '/languages', 'App\\Controller\\Api\\V1\\Frontend\\LanguageController::getAllLanguages', 'GET', NULL, NULL);

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
  ON p.`name` = 'page.create'
WHERE ar.`route_name` IN (
  'admin_pages_create'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'page.delete'
WHERE ar.`route_name` IN (
  'admin_pages_delete'
);

INSERT IGNORE INTO `api_routes_permissions` (`id_api_routes`, `id_permissions`)
SELECT
  ar.`id`      AS id_api_routes,
  p.`id`       AS id_permissions
FROM `api_routes`     AS ar
JOIN `permissions`   AS p
  ON p.`name` = 'page.update'
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

-- give role admin to all users who had group admins
INSERT IGNORE INTO users_roles (id_users, id_roles)
SELECT ug.id_users, r.id
FROM users_groups ug
INNER JOIN `groups` g ON ug.id_groups = g.id
INNER JOIN roles  r ON r.name = 'admin'
WHERE g.name = 'admin';
