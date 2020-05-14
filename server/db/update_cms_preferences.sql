-- add cms prefeences page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'cmsPreferences', '/admin/cms_preferences', 'GET|POST', '0000000002', NULL, '0000000009', '0', '1000', NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'CMS Preferecnes');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- add cms prefeences update page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'cmsPreferencesUpdate', '/admin/cms_preferences_update', 'GET|POST|PATCH', '0000000002', NULL, '0000000009', '0', null, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'CMS Preferecnes Update');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- add insert language page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'language', '/admin/language/[i:lid]?', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Create Language');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '1', '1', '1');

-- local and language should be unique
ALTER TABLE languages ADD UNIQUE (locale);
ALTER TABLE languages ADD UNIQUE (language);

-- add table cms_preferences
CREATE TABLE `cmsPreferences` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `callback_api_key` varchar(500),
  `default_language_id` int(10) UNSIGNED ZEROFILL,
  CONSTRAINT fk_cmsPreferences_language FOREIGN KEY (default_language_id) REFERENCES languages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add default row for cms_preferences
-- set default language German
INSERT INTO cmsPreferences(callback_api_key, default_language_id)
VALUES (NULL, 2); 

-- add Qualtrics module page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'moduleQualtrics', '/admin/qualtrics', 'GET|POST', '0000000002', NULL, '0000000009', '0', '90', NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Module Qualtrics');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- add Qualtrics module page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'moduleMail', '/admin/module_mail', 'GET|POST', '0000000002', NULL, '0000000009', '0', '80', NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Module Mail');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- add table modules
CREATE TABLE `modules` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `module_name` varchar(500),
  `enabled` int default 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add table modules_pages
CREATE TABLE `modules_pages` (
  `id_modules` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_pages` int(10) UNSIGNED ZEROFILL NOT NULL  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `modules_pages`
  ADD PRIMARY KEY (`id_modules`,`id_pages`),
  ADD KEY `id_modules` (`id_modules`),
  ADD KEY `id_pages` (`id_pages`);
  
--
-- Constraints for table `uploadCells`
--
ALTER TABLE `modules_pages`
  ADD CONSTRAINT `modules_pages_fk_id_modules` FOREIGN KEY (`id_modules`) REFERENCES `modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `modules_pages_fk_id_pages` FOREIGN KEY (`id_pages`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
-- procedure for registering modules and pages

DELIMITER //

DROP PROCEDURE IF EXISTS proc_register_module //

CREATE PROCEDURE proc_register_module( 
	p_module_name VARCHAR(500), 
    p_page_name VARCHAR(100), 
    p_enabled INT )
-- send module name, page name and enabled disabled;
-- if module does not exists, it will be created, then the page will be added to the module if it exists. First we check if the page exist, if it doesnt exist we throw error. 
-- enabled is assigned to the module.
BEGIN
	SET @page_id = NULL;
    SET @module_id = NULL;
    SET @result = '';
	SELECT id INTO @page_id FROM pages WHERE keyword = p_page_name COLLATE utf8_unicode_ci;
    
    IF (@page_id IS NULL) THEN
		SET @result = CONCAT('Page name ', p_page_name, ' does not exist;');
	ELSE

		SELECT id INTO @module_id FROM modules WHERE module_name = p_module_name COLLATE utf8_unicode_ci;
		IF (@module_id IS NULL) THEN
			INSERT INTO modules (module_name, enabled) VALUES (p_module_name, p_enabled); 
			SET @module_id = LAST_INSERT_ID();
            SET @result = CONCAT(@result, 'Module ', p_module_name, ' was created!;');            
		ELSE
			UPDATE modules SET enabled = p_enabled WHERE id = @module_id;
            SET @result = CONCAT(@result, 'The status enabled of Module ', p_module_name, ' was was changed to ', p_enabled, ';');
            
		END IF;
        INSERT INTO modules_pages (id_modules, id_pages) VALUES (@module_id, @page_id); 
		SET @result = CONCAT(@result, 'Page ', p_page_name, ' was added to module ', p_module_name);
        
	END IF;
    
    SELECT @result AS result;

END
//

DELIMITER ;

-- register moduleQualtrics with page
call proc_register_module('moduleQualtrics', 'moduleQualtrics', 1);

-- register moduleMail with page
call proc_register_module('moduleMail', 'moduleMail', 1);

-- register chatModule
call proc_register_module('moduleChat', 'contact', 1);
call proc_register_module('moduleChat', 'chatAdminDelete', 1);
call proc_register_module('moduleChat', 'chatAdminInsert', 1);
call proc_register_module('moduleChat', 'chatAdminSelect', 1);
call proc_register_module('moduleChat', 'chatAdminUpdate', 1);

DROP VIEW IF EXISTS view_acl_groups_pages_modules;
CREATE VIEW view_acl_groups_pages_modules
AS
SELECT acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
FROM acl_groups acl
INNER JOIN pages p ON (acl.id_pages = p.id)
LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
LEFT JOIN modules m ON (m.id = mp.id_modules)
GROUP BY acl.id_groups, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;

DROP VIEW IF EXISTS view_acl_users_pages_modules;
CREATE VIEW view_acl_users_pages_modules
AS
SELECT acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword,
p.url, p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position,
p.id_type, MAX(IFNULL(m.enabled, 1)) AS enabled
FROM acl_users acl
INNER JOIN pages p ON (acl.id_pages = p.id)
LEFT JOIN modules_pages mp ON (mp.id_pages = p.id)
LEFT JOIN modules m ON (m.id = mp.id_modules)
GROUP BY acl.id_users, acl.id_pages, acl.acl_select, acl.acl_insert, acl.acl_update, acl.acl_delete, p.keyword, p.url, 
p.protocol, p.id_actions, p.id_navigation_section, p.parent, p.is_headless, p.nav_position,p.footer_position, p.id_type;


-- add insert qualtrics projects page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'moduleQualtricsProject', '/admin/qualtrics/project/[select|update|insert|delete:mode]?/[i:pid]?', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Qualtrics Projects');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '1', '1', '1');

-- register page qualtrics projects to moduleQualtrics
call proc_register_module('moduleQualtrics', 'moduleQualtricsProject', 1);

-- add insert qualtrics survey page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'moduleQualtricsSurvey', '/admin/qualtrics/survey/[select|update|insert|delete:mode]?/[i:sid]?', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Qualtrics Survey');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '1', '1', '1');

-- register page qualtrics projects to moduleQualtrics
call proc_register_module('moduleQualtrics', 'moduleQualtricsSurvey', 1);


-- Add internal style navigationBar
INSERT INTO `styles` (`id`, `name`, `id_type`, `id_group`, `description`) VALUES (NULL, 'navigationBar', '0000000001', '0000000001', 'Provides a navigation bar style');
SET @id_style = LAST_INSERT_ID();
-- Assign fields to style navigationBar
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'items');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, NULL, 'JSON structure for the navigation bar');


-- add table qualtricsProjects
CREATE TABLE `qualtricsProjects` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `description` VARCHAR(1000),
  `qualtrics_api` VARCHAR(100),
  `api_library_id` VARCHAR(100),
  `api_mailing_group_id` VARCHAR(100),
  `participant_variable` VARCHAR(100),
  `created_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_on` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add table qualtricsSurveys
CREATE TABLE `qualtricsSurveys` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `description` VARCHAR(1000),
  `qualtrics_survey_id` VARCHAR(100) UNIQUE,  
  `group_variable` INT DEFAULT 0,
  `created_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_on` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add stage to project
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'moduleQualtricsProjectStage', '/admin/qualtrics/stage/[i:pid]/[select|update|insert|delete:mode]?/[i:sid]?', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Qualtrics Project Stage');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '1', '1', '1');

-- add table lookups
CREATE TABLE `lookups` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `type_code` VARCHAR(100) NOT NULL,
  `lookup_code` VARCHAR(100),
  `lookup_value` VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add notificationTypes
INSERT INTO lookups (type_code, lookup_value) values ('notificationTypes', 'All options');
INSERT INTO lookups (type_code, lookup_value) values ('notificationTypes', 'Email');
INSERT INTO lookups (type_code, lookup_value) values ('notificationTypes', 'SMS');

-- add qualtricsProjectStageTypes
INSERT INTO lookups (type_code, lookup_value) values ('qualtricsProjectStageTypes', 'Baseline');
INSERT INTO lookups (type_code, lookup_value) values ('qualtricsProjectStageTypes', 'Follow-up');

-- add qualtricsProjectStageTriggerType
INSERT INTO lookups (type_code, lookup_value) values ('qualtricsProjectStageTriggerTypes', 'Started');
INSERT INTO lookups (type_code, lookup_value) values ('qualtricsProjectStageTriggerTypes', 'Finished');

-- add qualtricsProjectStageAdditionalFunction
INSERT INTO lookups (type_code, lookup_value) values ('qualtricsProjectStageAdditionalFunction', 'Evaluate personal strengths');

-- add timePeriod
INSERT INTO lookups (type_code, lookup_value) values ('timePeriod', 'seconds');
INSERT INTO lookups (type_code, lookup_value) values ('timePeriod', 'minutes');
INSERT INTO lookups (type_code, lookup_value) values ('timePeriod', 'hours');
INSERT INTO lookups (type_code, lookup_value) values ('timePeriod', 'days');
INSERT INTO lookups (type_code, lookup_value) values ('timePeriod', 'weeks');
INSERT INTO lookups (type_code, lookup_value) values ('timePeriod', 'months');

-- add table lookups
CREATE TABLE `qualtricsStages` (
	`id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
	`id_qualtricsProjects` int(10) UNSIGNED ZEROFILL NOT NULL,
	`id_qualtricsSurveys` int(10) UNSIGNED ZEROFILL NOT NULL, 
	`name` varchar(200) NOT NULL,
    `id_qualtricsProjectStageTypes` int(10 ) UNSIGNED ZEROFILL NOT NULL,
    `id_qualtricsProjectStageTriggerTypes` int(10 ) UNSIGNED ZEROFILL NOT NULL,
    `notification` text,
	`reminder` text    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `qualtricsStages`
ADD CONSTRAINT `qualtricsStages_fk_id_qualtricsProjects` FOREIGN KEY (`id_qualtricsProjects`) REFERENCES `qualtricsProjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsStages_fk_id_qualtricsSurveys` FOREIGN KEY (`id_qualtricsSurveys`) REFERENCES `qualtricsSurveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsStages_fk_id_qualtricsProjectStageTypese` FOREIGN KEY (`id_qualtricsProjectStageTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsStages_fk_id_lookups_qualtricsProjectStageTriggerType` FOREIGN KEY (`id_qualtricsProjectStageTriggerTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add table qualtricsStages_groups
CREATE TABLE `qualtricsStages_groups` (	
	`id_qualtricsStages` int(10) UNSIGNED ZEROFILL NOT NULL,
	`id_groups` int(10) UNSIGNED ZEROFILL NOT NULL	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `qualtricsStages_groups`
ADD PRIMARY KEY (`id_qualtricsStages`,`id_groups`),
ADD KEY `id_qualtricsStages` (`id_qualtricsStages`),
ADD KEY `id_groups` (`id_groups`);

ALTER TABLE `qualtricsStages_groups`
ADD CONSTRAINT `qualtricsStages_groups_fk_id_qualtricsStages` FOREIGN KEY (`id_qualtricsStages`) REFERENCES `qualtricsStages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsStages_groups_fk_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add table qualtricsStages_functions
CREATE TABLE `qualtricsStages_functions` (	
	`id_qualtricsStages` int(10) UNSIGNED ZEROFILL NOT NULL,
	`id_lookups` int(10) UNSIGNED ZEROFILL NOT NULL	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `qualtricsStages_functions`
ADD PRIMARY KEY (`id_qualtricsStages`,`id_lookups`),
ADD KEY `id_qualtricsStages` (`id_qualtricsStages`),
ADD KEY `id_lookups` (`id_lookups`);

ALTER TABLE `qualtricsStages_functions`
ADD CONSTRAINT `qualtricsStages_functions_fk_id_qualtricsStages` FOREIGN KEY (`id_qualtricsStages`) REFERENCES `qualtricsStages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsStages_functions_fk_id_lookups` FOREIGN KEY (`id_lookups`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- view view_qualtricsStages
DROP VIEW IF EXISTS view_qualtricsStages;
CREATE VIEW view_qualtricsStages
AS
SELECT st.id as id, st.name as stage_name, st.id_qualtricsProjects as project_id, p.name as project_name, p.qualtrics_api, p.participant_variable, p.api_mailing_group_id,
st.id_qualtricsSurveys as survey_id, s.qualtrics_survey_id, s.name as survey_name, id_qualtricsProjectStageTypes, group_variable, typ.lookup_value as stage_type, 
id_qualtricsProjectStageTriggerTypes, trig.lookup_value as trigger_type,
GROUP_CONCAT(DISTINCT g.name SEPARATOR '; ') AS groups, 
GROUP_CONCAT(DISTINCT g.id SEPARATOR '; ') AS id_groups, 
GROUP_CONCAT(DISTINCT l.lookup_value SEPARATOR '; ') AS functions,
GROUP_CONCAT(DISTINCT l.id SEPARATOR '; ') AS id_functions,
notification, reminder 
FROM qualtricsStages st 
INNER JOIN qualtricsProjects p ON (st.id_qualtricsProjects = p.id)
INNER JOIN qualtricsSurveys s ON (st.id_qualtricsSurveys = s.id)
INNER JOIN lookups typ ON (typ.id = st.id_qualtricsProjectStageTypes)
INNER JOIN lookups trig ON (trig.id = st.id_qualtricsProjectStageTriggerTypes)
LEFT JOIN qualtricsStages_groups sg on (sg.id_qualtricsStages = st.id)
LEFT JOIN groups g on (sg.id_groups = g.id)
LEFT JOIN qualtricsStages_functions f on (f.id_qualtricsStages = st.id)
LEFT JOIN lookups l on (f.id_lookups = l.id)
GROUP BY st.id, st.name, st.id_qualtricsProjects, p.name,
st.id_qualtricsSurveys, s.name, id_qualtricsProjectStageTypes, typ.lookup_value, 
id_qualtricsProjectStageTriggerTypes, trig.lookup_value;

-- add qualtricsSync page
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'moduleQualtricsSync', '/admin/qualtrics/sync/[i:pid]', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Qualtrics Synchronization');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '0', '0');

-- add table qualtricsSurveysResponses
CREATE TABLE `qualtricsSurveysResponses` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `id_users` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_surveys` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_qualtricsProjectStageTriggerType` int(10 ) UNSIGNED ZEROFILL NOT NULL,
  `survey_response_id` VARCHAR(100) UNIQUE,  
  `started_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_on` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `qualtricsSurveysResponses`
ADD CONSTRAINT `qualtricsSurveysResponses_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsSurveysResponsesfk_id_surveys` FOREIGN KEY (`id_surveys`) REFERENCES `qualtricsSurveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsSurveysResponsesfk_id_qualtricsProjectStageTriggerType` FOREIGN KEY (`id_qualtricsProjectStageTriggerType`) REFERENCES `lookups` (`id`);

-- auto created user status
INSERT INTO userStatus (name, description)
VALUES ('auto_created', 'This user was auto created. The user has only code and cannot login. If the real user register later with the code the user will be activated to normal user.');

