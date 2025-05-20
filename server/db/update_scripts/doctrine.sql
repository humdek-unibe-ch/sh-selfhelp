-- drop foreign keys
CALL drop_foreign_key('acl_groups', 'fk_acl_groups_id_groups');
CALL drop_foreign_key('acl_groups', 'fk_acl_groups_id_pages');
CALL drop_foreign_key('acl_users', 'acl_fk_id_pages');
CALL drop_foreign_key('acl_users', 'acl_fk_id_users');
CALL drop_foreign_key('assets', 'assets_fk_id_assetTypes');
CALL drop_foreign_key('codes_groups', 'fk_codes');
CALL drop_foreign_key('codes_groups', 'fk_id_groups');
CALL drop_foreign_key('dataCells', 'uploadCells_fk_id_uploadCols');
CALL drop_foreign_key('dataCells', 'uploadCells_fk_id_uploadRows');
CALL drop_foreign_key('dataCols', 'uploadCols_fk_id_uploadTables');
CALL drop_foreign_key('dataRows', 'uploadRows_fk_id_actionTriggerTypes');
CALL drop_foreign_key('dataRows', 'uploadRows_fk_id_uploadTables');
CALL drop_foreign_key('dataRows', 'uploadRows_fk_id_users');
CALL drop_foreign_key('fields', 'fields_fk_id_type');
CALL drop_foreign_key('formActions', 'formActions_id_dataTables');
CALL drop_foreign_key('genders', 'genders_fk_id_something');
CALL drop_foreign_key('groups', 'groups_fk_id_group_types');
CALL drop_foreign_key('hooks', 'hooks_fk_id_hookTypes');
CALL drop_foreign_key('logPerformance', 'logperformance_ibfk_1');
CALL drop_foreign_key('mailAttachments', 'mailAttachments_fk_id_mailQueue');
CALL drop_foreign_key('pages', 'pages_fk_id_actions');
CALL drop_foreign_key('pages', 'pages_fk_id_navigation_section');
CALL drop_foreign_key('pages', 'pages_fk_id_type');
CALL drop_foreign_key('pages', 'pages_fk_parent');
CALL drop_foreign_key('pages_fields', 'fk_page_fields_id_fields');
CALL drop_foreign_key('pages_fields', 'fk_page_fields_id_pages');
CALL drop_foreign_key('pages_fields_translation', 'pages_fields_translation_fk_id_fields');
CALL drop_foreign_key('pages_fields_translation', 'pages_fields_translation_fk_id_languages');
CALL drop_foreign_key('pages_fields_translation', 'pages_fields_translation_fk_id_pages');
CALL drop_foreign_key('pages_sections', 'pages_sections_fk_id_pages');
CALL drop_foreign_key('pages_sections', 'pages_sections_fk_id_sections');
CALL drop_foreign_key('pageType_fields', 'fk_pageType_fields_id_fields');
CALL drop_foreign_key('pageType_fields', 'fk_pageType_fields_id_pageType');
CALL drop_foreign_key('scheduledJobs', 'scheduledJobs_fk_id_jobStatus');
CALL drop_foreign_key('scheduledJobs', 'scheduledJobs_fk_id_jobTypes');
CALL drop_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_iid_formActions');
CALL drop_foreign_key('scheduledJobs_formActions', 'scheduledJobs_formActions_id_dataRows');
CALL drop_foreign_key('scheduledJobs_mailQueue', 'scheduledJobs_mailQueue_fk_id_mailQueue');
CALL drop_foreign_key('scheduledJobs_mailQueue', 'scheduledJobs_mailQueue_fk_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_notifications', 'scheduledJobs_notifications_fk_id_notifications');
CALL drop_foreign_key('scheduledJobs_notifications', 'scheduledJobs_notifications_fk_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_dataTables');
CALL drop_foreign_key('scheduledJobs_reminders', 'scheduledJobs_reminders_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_tasks', 'scheduledJobs_tasks_fk_id_scheduledJobs');
CALL drop_foreign_key('scheduledJobs_tasks', 'scheduledJobs_tasks_fk_id_tasks');
CALL drop_foreign_key('scheduledJobs_users', 'scheduledJobs_users_fk_id_users');
CALL drop_foreign_key('scheduledJobs_users', 'scheduledJobs_users_fk_scheduledJobs');
CALL drop_foreign_key('sections', 'sections_fk_id_styles');
CALL drop_foreign_key('sections_fields_translation', 'sections_fields_translation_fk_id_fields');
CALL drop_foreign_key('sections_fields_translation', 'sections_fields_translation_fk_id_genders');
CALL drop_foreign_key('sections_fields_translation', 'sections_fields_translation_fk_id_languages');
CALL drop_foreign_key('sections_fields_translation', 'sections_fields_translation_fk_id_sections');
CALL drop_foreign_key('sections_hierarchy', 'sections_hierarchy_fk_child');
CALL drop_foreign_key('sections_hierarchy', 'sections_hierarchy_fk_parent');
CALL drop_foreign_key('sections_navigation', 'sections_navigation_fk_child');
CALL drop_foreign_key('sections_navigation', 'sections_navigation_fk_id_pages');
CALL drop_foreign_key('sections_navigation', 'sections_navigation_fk_parent');
CALL drop_foreign_key('styles', 'styles_fk_id_group');
CALL drop_foreign_key('styles', 'styles_fk_id_type');
CALL drop_foreign_key('styles_fields', 'styles_fields_fk_id_fields');
CALL drop_foreign_key('styles_fields', 'styles_fields_fk_id_styles');
CALL drop_foreign_key('transactions', 'transactions_fk_id_transactionBy');
CALL drop_foreign_key('transactions', 'transactions_fk_id_transactionTypes');
CALL drop_foreign_key('transactions', 'transactions_fk_id_users');
CALL drop_foreign_key('users', 'fk_users_id_genders');
CALL drop_foreign_key('users', 'fk_users_id_languages');
CALL drop_foreign_key('users', 'fk_users_id_status');
CALL drop_foreign_key('user_activity', 'fk_user_activity_fk_id_type');
CALL drop_foreign_key('user_activity', 'fk_user_activity_fk_id_users');
CALL drop_foreign_key('users_groups', 'fk_users_groups_id_groups');
CALL drop_foreign_key('users_groups', 'fk_users_groups_id_users');
CALL drop_foreign_key('validation_codes', 'validation_codes_fk_id_users');

-- drop indexes
CALL drop_index('api_routes', 'uniq_route_name_version');
CALL drop_index('api_routes', 'uniq_version_path');
CALL drop_index('assets', 'assets_fk_id_assetTypes');
CALL drop_index('assets', 'file_name');
CALL drop_index('cmsPreferences', 'fk_cmspreferences_language');
CALL drop_index('codes_groups', 'fk_id_groups');
CALL drop_index('codes_groups', 'IDX_9F20ED7677153098');
CALL drop_index('dataCells', 'idx_uploadCells_value');
CALL drop_index('dataCols', 'unique_name_id_dataTables');
CALL drop_index('dataRows', 'idx_uploadRows_timestamp');
CALL drop_index('dataRows', 'uploadRows_fk_id_actionTriggerTypes');
CALL drop_index('dataRows', 'uploadRows_fk_id_users');
CALL drop_index('dataTables', 'idx_uploadTables_name_timestamp');
CALL drop_index('dataTables', 'uploadTables_name');
CALL drop_index('fields', 'fields_name');
CALL drop_index('fields', 'id_type');
CALL drop_index('fieldType', 'fieldType_name');
CALL drop_index('groups', 'name');
CALL drop_index('hooks', 'hooks_fk_id_hookTypes');
CALL drop_index('hooks', 'name');
CALL drop_index('languages', 'language');
CALL drop_index('languages', 'locale');
CALL drop_index('libraries', 'name');
CALL drop_index('lookups', 'idx_lookups_type_code_lookup_code');
CALL drop_index('mailAttachments', 'mailAttachments_fk_id_mailQueue');
CALL drop_index('plugins', 'plugins_name');
CALL drop_index('refreshTokens', 'idx_token_hash');
CALL drop_index('scheduledJobs', 'scheduledJobs_fk_id_jobStatus');
CALL drop_index('scheduledJobs', 'scheduledJobs_fk_id_jobTypes');
CALL drop_index('scheduledJobs_formActions', 'scheduledJobs_formActions_fk_iid_formActions');
CALL drop_index('scheduledJobs_formActions', 'scheduledJobs_formActions_id_dataRows');
CALL drop_index('scheduledJobs_formActions', 'IDX_AE5B5D0B8030BA52');
CALL drop_index('scheduledJobs_mailQueue', 'scheduledJobs_mailQueue_fk_id_mailQueue');
CALL drop_index('scheduledJobs_mailQueue', 'IDX_E560A18030BA52');
CALL drop_index('scheduledJobs_notifications', 'scheduledJobs_notifications_fk_id_notifications');
CALL drop_index('scheduledJobs_notifications', 'IDX_9879806C8030BA52');
CALL drop_index('sections', 'name');
CALL drop_index('styles_fields', 'id_fields');
CALL drop_index('styles_fields', 'id_styles');
CALL drop_index('styles_fields', 'primary');
CALL drop_index('transactions', 'idx_transactions_table_name');
CALL drop_index('users', 'id_genders');
CALL drop_index('users', 'id_languages');
CALL drop_index('users', 'id_status');

-- add foreign keys
CALL add_foreign_key('acl_groups', 'FK_AB370E20D65A8C9D', 'id_groups', '`groups`(id)');
CALL add_foreign_key('acl_groups', 'FK_AB370E20CEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('acl_users', 'FK_901AE856FA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('acl_users', 'FK_901AE856CEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('dataCells', 'FK_726A5F25F3854F45', 'id_dataRows', 'dataRows(id)');
CALL add_foreign_key('dataCells', 'FK_726A5F25B216B425', 'id_dataCols', 'dataCols(id)');
CALL add_foreign_key('dataCols', 'FK_E2CD58B0E2E6A7C3', 'id_dataTables', 'dataTables(id)');
CALL add_foreign_key('dataRows', 'FK_A35EA3D0E2E6A7C3', 'id_dataTables', 'dataTables(id)');

-- add indexes
CALL add_index('acl_groups', 'IDX_AB370E20D65A8C9D', 'id_groups', FALSE);
CALL add_index('acl_groups', 'IDX_AB370E20CEF1A445', 'id_pages', FALSE);
CALL add_index('acl_users', 'IDX_901AE856FA06E4D9', 'id_users', FALSE);
CALL add_index('acl_users', 'IDX_901AE856CEF1A445', 'id_pages', FALSE);
CALL add_index('api_routes', 'UNIQ_B4228533F3667F83', 'route_name', TRUE);
CALL add_index('assets', 'UNIQ_79D17D8ED7DF1668', 'file_name', TRUE);
CALL add_index('cmsPreferences', 'IDX_3F26A2DF5602A942', 'default_language_id', FALSE);
CALL add_index('dataCells', 'IDX_726A5F25F3854F45', 'id_dataRows', FALSE);
CALL add_index('dataCells', 'IDX_726A5F25B216B425', 'id_dataCols', FALSE);
CALL add_index('dataCols', 'IDX_E2CD58B0E2E6A7C3', 'id_dataTables', FALSE);
CALL add_index('dataRows', 'IDX_A35EA3D0E2E6A7C3', 'id_dataTables', FALSE);


-- add more foreign keys
CALL add_foreign_key('fields', 'FK_7EE5E388FF2309B7', 'id_type', 'fieldType(id)');
CALL add_foreign_key('formActions', 'FK_3128FB5E8A8FCE9D', 'id_formProjectActionTriggerTypes', 'lookups(id)');
CALL add_foreign_key('formActions', 'FK_3128FB5EE2E6A7C3', 'id_dataTables', 'dataTables(id)');
CALL add_foreign_key('logPerformance', 'FK_6D164595F2D13C3F', 'id_user_activity', 'user_activity(id)');

-- more indexes
CALL add_index('fields', 'IDX_7EE5E388FF2309B7', 'id_type', FALSE);
CALL add_index('formActions', 'IDX_3128FB5E8A8FCE9D', 'id_formProjectActionTriggerTypes', FALSE);
CALL add_index('formActions', 'IDX_3128FB5EE2E6A7C3', 'id_dataTables', FALSE);

-- foreign keys for pages and related tables
CALL add_foreign_key('pages', 'FK_2074E575DBD5589F', 'id_actions', 'lookups(id)');
CALL add_foreign_key('pages', 'FK_2074E575E8D3C633', 'id_navigation_section', 'sections(id)');
CALL add_foreign_key('pages', 'FK_2074E5753D8E604F', 'parent', 'pages(id)');
CALL add_foreign_key('pages', 'FK_2074E5757FE4B2B', 'id_type', 'pageType(id)');
CALL add_foreign_key('pages_fields', 'FK_D36F9887CEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('pages_fields', 'FK_D36F988758D25665', 'id_fields', 'fields(id)');
CALL add_foreign_key('pages_fields_translation', 'FK_903943EECEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('pages_fields_translation', 'FK_903943EE58D25665', 'id_fields', 'fields(id)');
CALL add_foreign_key('pages_fields_translation', 'FK_903943EE20E4EF5E', 'id_languages', 'languages(id)');
CALL add_foreign_key('pages_sections', 'FK_6BD95A69CEF1A445', 'id_pages', 'pages(id)');
CALL add_foreign_key('pages_sections', 'FK_6BD95A697B4DAF0D', 'id_sections', 'sections(id)');
CALL add_foreign_key('pageType_fields', 'FK_B305C681FDE305E9', 'id_pageType', 'pageType(id)');
CALL add_foreign_key('pageType_fields', 'FK_B305C68158D25665', 'id_fields', 'fields(id)');

-- indexes for pages and related tables
CALL add_index('pages', 'UNIQ_2074E5755A93713B', 'keyword', TRUE);
CALL add_index('pages', 'IDX_2074E575DBD5589F', 'id_actions', FALSE);
CALL add_index('pages', 'IDX_2074E575E8D3C633', 'id_navigation_section', FALSE);
CALL add_index('pages', 'IDX_2074E5753D8E604F', 'parent', FALSE);
CALL add_index('pages', 'IDX_2074E5757FE4B2B', 'id_type', FALSE);
CALL add_index('pages', 'IDX_2074E57534643D90', 'id_pageAccessTypes', FALSE);
CALL add_index('pages_fields', 'IDX_D36F9887CEF1A445', 'id_pages', FALSE);
CALL add_index('pages_fields', 'IDX_D36F988758D25665', 'id_fields', FALSE);
CALL add_index('pages_fields_translation', 'IDX_903943EECEF1A445', 'id_pages', FALSE);
CALL add_index('pages_fields_translation', 'IDX_903943EE58D25665', 'id_fields', FALSE);
CALL add_index('pages_fields_translation', 'IDX_903943EE20E4EF5E', 'id_languages', FALSE);
CALL add_index('pages_sections', 'IDX_6BD95A69CEF1A445', 'id_pages', FALSE);
CALL add_index('pages_sections', 'IDX_6BD95A697B4DAF0D', 'id_sections', FALSE);
CALL add_index('pageType', 'UNIQ_AD38E97C5E237E06', 'name', TRUE);
CALL add_index('pageType_fields', 'IDX_B305C68158D25665', 'id_fields', FALSE);
CALL add_index('refreshTokens', 'IDX_BFB6788AFA06E4D9', 'id_users', FALSE);

-- add foreign keys for scheduled jobs
CALL add_foreign_key('scheduledJobs_reminders', 'FK_23156A608030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');
CALL add_foreign_key('scheduledJobs_reminders', 'FK_23156A60E2E6A7C3', 'id_dataTables', 'dataTables(id)');
CALL add_foreign_key('scheduledJobs_tasks', 'FK_96A54FA88030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');
CALL add_foreign_key('scheduledJobs_tasks', 'FK_96A54FA8BEDD24A7', 'id_tasks', 'tasks(id)');
CALL add_foreign_key('scheduledJobs_users', 'FK_D27E8FD6FA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('scheduledJobs_users', 'FK_D27E8FD68030BA52', 'id_scheduledJobs', 'scheduledJobs(id)');

-- add foreign keys for sections
CALL add_foreign_key('sections', 'FK_2B964398906D4F18', 'id_styles', 'styles(id)');
CALL add_foreign_key('sections_fields_translation', 'FK_EC5054157B4DAF0D', 'id_sections', 'sections(id)');
CALL add_foreign_key('sections_fields_translation', 'FK_EC50541558D25665', 'id_fields', 'fields(id)');
CALL add_foreign_key('sections_fields_translation', 'FK_EC50541520E4EF5E', 'id_languages', 'languages(id)');
CALL add_foreign_key('sections_fields_translation', 'FK_EC5054155D8601CD', 'id_genders', 'genders(id)');
CALL add_foreign_key('sections_hierarchy', 'FK_A6D0AE7C3D8E604F', 'parent', 'sections(id)');
CALL add_foreign_key('sections_hierarchy', 'FK_A6D0AE7C22B35429', 'child', 'sections(id)');
CALL add_foreign_key('sections_navigation', 'FK_21BBDC413D8E604F', 'parent', 'sections(id)');
CALL add_foreign_key('sections_navigation', 'FK_21BBDC4122B35429', 'child', 'sections(id)');
CALL add_foreign_key('sections_navigation', 'FK_21BBDC41CEF1A445', 'id_pages', 'pages(id)');

-- add foreign keys for styles
CALL add_foreign_key('styles', 'FK_B65AFAF57FE4B2B', 'id_type', 'lookups(id)');
CALL add_foreign_key('styles', 'FK_B65AFAF5834505F5', 'id_group', 'styleGroup(id)');
CALL add_foreign_key('styles_fields', 'FK_4F23ED261DF44B12', 'id_fields', 'fields(id)');
CALL add_foreign_key('styles_fields', 'FK_4F23ED26D54B526F', 'id_styles', 'styles(id)');

-- add foreign keys for transactions and users
CALL add_foreign_key('transactions', 'FK_EAA81A4CC41DBD5F', 'id_transactionTypes', 'lookups(id)');
CALL add_foreign_key('transactions', 'FK_EAA81A4CFC2E5563', 'id_transactionBy', 'lookups(id)');
CALL add_foreign_key('transactions', 'FK_EAA81A4CFA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('user_activity', 'FK_4CF9ED5AFA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('user_activity', 'FK_4CF9ED5A7FE4B2B', 'id_type', 'lookups(id)');
CALL add_foreign_key('users_groups', 'FK_FF8AB7E0FA06E4D9', 'id_users', 'users(id)');
CALL add_foreign_key('users_groups', 'FK_FF8AB7E0D65A8C9D', 'id_groups', '`groups`(id)');
CALL add_foreign_key('validation_codes', 'FK_DBEC45EFA06E4D9', 'id_users', 'users(id)');

-- add indexes
CALL add_index('scheduledJobs_reminders', 'IDX_23156A60E2E6A7C3', 'id_dataTables', FALSE);
CALL add_index('scheduledJobs_tasks', 'IDX_96A54FA8BEDD24A7', 'id_tasks', FALSE);
CALL add_index('scheduledJobs_users', 'IDX_D27E8FD68030BA52', 'id_scheduledJobs', FALSE);
CALL add_index('sections', 'IDX_2B964398906D4F18', 'id_styles', FALSE);
CALL add_index('sections_fields_translation', 'IDX_EC5054157B4DAF0D', 'id_sections', FALSE);
CALL add_index('sections_fields_translation', 'IDX_EC50541558D25665', 'id_fields', FALSE);
CALL add_index('sections_fields_translation', 'IDX_EC50541520E4EF5E', 'id_languages', FALSE);
CALL add_index('sections_fields_translation', 'IDX_EC5054155D8601CD', 'id_genders', FALSE);
CALL add_index('sections_hierarchy', 'IDX_A6D0AE7C3D8E604F', 'parent', FALSE);
CALL add_index('sections_hierarchy', 'IDX_A6D0AE7C22B35429', 'child', FALSE);
CALL add_index('sections_navigation', 'IDX_21BBDC413D8E604F', 'parent', FALSE);
CALL add_index('sections_navigation', 'IDX_21BBDC4122B35429', 'child', FALSE);
CALL add_index('sections_navigation', 'IDX_21BBDC41CEF1A445', 'id_pages', FALSE);
CALL add_index('styles', 'UNIQ_B65AFAF55E237E06', 'name', TRUE);
CALL add_index('styles', 'IDX_B65AFAF57FE4B2B', 'id_type', FALSE);
CALL add_index('styles', 'IDX_B65AFAF5834505F5', 'id_group', FALSE);
CALL add_index('styles_fields', 'IDX_4F23ED261DF44B12', 'id_fields', FALSE);
CALL add_index('styles_fields', 'IDX_4F23ED26D54B526F', 'id_styles', FALSE);
CALL add_primary_key('styles_fields', 'id_fields, id_styles');
CALL add_index('transactions', 'IDX_EAA81A4CC41DBD5F', 'id_transactionTypes', FALSE);
CALL add_index('transactions', 'IDX_EAA81A4CFC2E5563', 'id_transactionBy', FALSE);
CALL add_index('transactions', 'IDX_EAA81A4CFA06E4D9', 'id_users', FALSE);
CALL add_index('users', 'UNIQ_1483A5E9E7927C74', 'email', TRUE);
CALL add_index('users', 'UNIQ_1483A5E924A232CF', 'user_name', TRUE);
CALL add_index('user_activity', 'IDX_4CF9ED5AFA06E4D9', 'id_users', FALSE);
CALL add_index('user_activity', 'IDX_4CF9ED5A7FE4B2B', 'id_type', FALSE);
CALL add_index('users_2fa_codes', 'IDX_65A1E404FA06E4D9', 'id_users', FALSE);
CALL add_index('users_groups', 'IDX_FF8AB7E0FA06E4D9', 'id_users', FALSE);
CALL add_index('users_groups', 'IDX_FF8AB7E0D65A8C9D', 'id_groups', FALSE);
CALL add_index('validation_codes', 'IDX_DBEC45EFA06E4D9', 'id_users', FALSE);
