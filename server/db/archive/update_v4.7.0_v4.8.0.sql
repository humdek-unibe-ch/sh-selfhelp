-- set DB version
UPDATE version
SET version = 'v4.8.0';

INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES 
('0000000001', (select id from pages where keyword = 'ajax_set_user_language'), '1', '0', '0', '0'),
('0000000002', (select id from pages where keyword = 'ajax_set_user_language'), '1', '0', '0', '0'),
('0000000003', (select id from pages where keyword = 'ajax_set_user_language'), '1', '0', '0', '0');