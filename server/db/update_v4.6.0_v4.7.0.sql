-- set DB version
UPDATE version
SET version = 'v4.7.0';

-- add keyword set_user_language as open page request
INSERT INTO pages (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'ajax_set_user_language', '/request/[AjaxLanguage:class]/[ajax_set_user_language:method]', 'GET|POST', '0000000005', NULL, NULL, '0', NULL, NULL, '0000000004', (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));