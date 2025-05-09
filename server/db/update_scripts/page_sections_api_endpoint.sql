-- Register the new API endpoint for retrieving page sections
SET @page_keyword = 'cms-api_v1_admin_pages_page_sections';

-- Add the page entry for the new API endpoint
INSERT IGNORE INTO `pages` (`keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`, `is_open_access`) 
VALUES (@page_keyword, '/cms-api/v1/[admin:class]/[pages:method]/[page_sections:method]/[v:page_keyword]', 'GET', (SELECT id FROM actions WHERE `name` = 'cms-api' LIMIT 0,1), NULL, NULL, '0', NULL, NULL, (SELECT id FROM pageType WHERE `name` = 'intern' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'), 0);

-- Add translations for the page title in English
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000001', 'Get Page Sections');

-- Add translations for the page title in German
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) 
VALUES ((SELECT id FROM pages WHERE keyword = @page_keyword), get_field_id('title'), '0000000002', 'Get Page Sections');
