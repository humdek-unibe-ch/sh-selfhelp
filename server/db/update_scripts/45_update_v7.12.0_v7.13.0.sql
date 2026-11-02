START TRANSACTION;

-- Bump version only with a successful migration (same transaction as INSERTs).
UPDATE version
SET version = 'v7.13.0';

-- ---------------------------------------------------------------------------
-- Field versioning (opt-in per field)
--
-- `fields.versioned = 1` makes every CMS save of that field store a snapshot
-- in `fields_versions` (only when the content changed). The current content
-- in pages_fields_translation / sections_fields_translation stays the active
-- version; a restore copies an old snapshot back and records it as a new
-- version. Only the default gender (1) of a section field is versioned.
-- Plugins switch their own fields on in their migrations.
-- Clear the CMS cache after running this migration.
-- ---------------------------------------------------------------------------

CALL add_table_column('fields', 'versioned', 'TINYINT(1) NOT NULL DEFAULT 0');

CREATE TABLE IF NOT EXISTS `fields_versions` (
    `id` INT(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `id_fields` INT(10) UNSIGNED ZEROFILL NOT NULL,
    `id_pages` INT(10) UNSIGNED ZEROFILL DEFAULT NULL,
    `id_sections` INT(10) UNSIGNED ZEROFILL DEFAULT NULL,
    `id_languages` INT(10) UNSIGNED ZEROFILL NOT NULL,
    `version_no` INT NOT NULL,
    `content` LONGTEXT NOT NULL,
    `meta` VARCHAR(10000) DEFAULT NULL,
    `content_hash` CHAR(64) NOT NULL,
    `restored_from` INT(10) UNSIGNED ZEROFILL DEFAULT NULL,
    `id_users` INT(10) UNSIGNED ZEROFILL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_fields_versions_owner` (`id_fields`, `id_pages`, `id_sections`, `id_languages`, `version_no`),
    KEY `idx_fields_versions_hash` (`content_hash`),
    CONSTRAINT `fk_fields_versions_fields` FOREIGN KEY (`id_fields`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fields_versions_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fields_versions_sections` FOREIGN KEY (`id_sections`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fields_versions_languages` FOREIGN KEY (`id_languages`) REFERENCES `languages` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fields_versions_restored_from` FOREIGN KEY (`restored_from`) REFERENCES `fields_versions` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fields_versions_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE SET NULL
);

UPDATE `fields`
SET `versioned` = 1
WHERE `name` IN ('global_values', 'custom_css');

-- Baseline: the current content of every versioned field becomes version 1
-- (system snapshot, no user).
INSERT INTO `fields_versions` (`id_fields`, `id_pages`, `id_languages`, `version_no`, `content`, `content_hash`)
SELECT pft.id_fields, pft.id_pages, pft.id_languages, 1, pft.content, SHA2(pft.content, 256)
FROM `pages_fields_translation` pft
INNER JOIN `fields` f ON f.id = pft.id_fields AND f.versioned = 1
WHERE NOT EXISTS (
    SELECT 1 FROM `fields_versions` fv
    WHERE fv.id_fields = pft.id_fields AND fv.id_pages = pft.id_pages AND fv.id_languages = pft.id_languages
);

INSERT INTO `fields_versions` (`id_fields`, `id_sections`, `id_languages`, `version_no`, `content`, `meta`, `content_hash`)
SELECT sft.id_fields, sft.id_sections, sft.id_languages, 1, sft.content, sft.meta, SHA2(sft.content, 256)
FROM `sections_fields_translation` sft
INNER JOIN `fields` f ON f.id = sft.id_fields AND f.versioned = 1
WHERE sft.id_genders = 1 AND NOT EXISTS (
    SELECT 1 FROM `fields_versions` fv
    WHERE fv.id_fields = sft.id_fields AND fv.id_sections = sft.id_sections
        AND fv.id_languages = sft.id_languages
);

-- History page of a versioned field (component cmsFieldVersions). Every group
-- that may edit in the CMS (`cmsUpdate`) gets it; the component also checks
-- update access to the page that owns the field.
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`)
VALUES ('cmsFieldVersions', '/admin/cms_field_versions/[page_field|section_field:relation]/[i:owner_id]/[i:id_fields]/[i:id_languages]/[i:version]?', 'GET|POST', '0000000002', NULL, NULL, '0', NULL, NULL, '0000000001', (SELECT id FROM lookups WHERE type_code = "pageAccessTypes" AND lookup_code = "web"));
SET @id_page = (SELECT id FROM pages WHERE keyword = 'cmsFieldVersions');

INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('label'), '0000000002', 'Field history');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, get_field_id('title'), '0000000002', 'Field history');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`)
SELECT acl.id_groups, @id_page, acl.acl_select, 0, acl.acl_update, 0
FROM `acl_groups` acl
WHERE acl.id_pages = (SELECT id FROM pages WHERE keyword = 'cmsUpdate');

COMMIT;

