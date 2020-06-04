-- function readjust with collate
DELIMITER //
DROP FUNCTION IF EXISTS get_field_type_id //

CREATE FUNCTION get_field_type_id(field_type varchar(100)) RETURNS INT
BEGIN 
	DECLARE field_type_id INT;    
	SELECT id INTO field_type_id
	FROM fieldType
	WHERE name = field_type COLLATE utf8_unicode_ci;
    RETURN field_type_id;
END
//

DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS get_field_id //

CREATE FUNCTION get_field_id(field varchar(100)) RETURNS INT
BEGIN 
	DECLARE field_id INT;    
	SELECT id INTO field_id
	FROM fields
	WHERE name = field COLLATE utf8_unicode_ci;
    RETURN field_id;
END
//

DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS get_style_id //

CREATE FUNCTION get_style_id(style varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_id INT;    
	SELECT id INTO style_id
	FROM styles
	WHERE name = style COLLATE utf8_unicode_ci;
    RETURN style_id;
END
//

DELIMITER ;

DELIMITER //
DROP FUNCTION IF EXISTS get_style_group_id //

CREATE FUNCTION get_style_group_id(style_group varchar(100)) RETURNS INT
BEGIN 
	DECLARE style_group_id INT;    
	SELECT id INTO style_group_id
	FROM styleGroup
	WHERE name = style_group COLLATE utf8_unicode_ci;
    RETURN style_group_id;
END
//

DELIMITER ;


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
VALUES (NULL, 'moduleMail', '/admin/mailQueue/[i:mqid]?', 'GET|POST', '0000000002', NULL, '0000000009', '0', '80', NULL, '0000000001');
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
  `lookup_code` VARCHAR(100) UNIQUE,
  `lookup_value` VARCHAR(200) UNIQUE,
  `lookup_description` VARCHAR(500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add notificationTypes
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('notificationTypes', 'All options', 'Notification will be sent by all availale options');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('notificationTypes', 'Email', 'The notification will be sent by email');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('notificationTypes', 'SMS', 'The notification will be sent by SMS');

-- add qualtricsProjectStageTypes
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('qualtricsProjectStageTypes', 'Baseline', 'Baselin surveys are the leadign surveys. They record the user in the contact list');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('qualtricsProjectStageTypes', 'Follow-up', 'Folloup surveys get a user from the contact list and use it.');

-- add qualtricsProjectStageTriggerType
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('qualtricsProjectStageTriggerTypes', 'Started', 'When the user start the survey');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('qualtricsProjectStageTriggerTypes', 'Finished', 'When the user finish the survey');

-- add qualtricsProjectStageAdditionalFunction
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('qualtricsProjectStageAdditionalFunction', 'Evaluate personal strengths', 'Function that will evaluate the personal strengths and it will send an email');

-- add timePeriod
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('timePeriod', 'seconds', 'seconds');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('timePeriod', 'minutes', 'minutes');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('timePeriod', 'hours', 'hours');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('timePeriod', 'days', 'days');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('timePeriod', 'weeks', 'weeks');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('timePeriod', 'months', 'months');

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
  `id_qualtricsProjectStageTriggerTypes` int(10 ) UNSIGNED ZEROFILL NOT NULL,
  `survey_response_id` VARCHAR(100) UNIQUE,  
  `started_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_on` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `qualtricsSurveysResponses`
ADD CONSTRAINT `qualtricsSurveysResponses_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsSurveysResponsesfk_id_surveys` FOREIGN KEY (`id_surveys`) REFERENCES `qualtricsSurveys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `qualtricsSurveysResponsesfk_id_qualtricsProjectStageTriggerTypes` FOREIGN KEY (`id_qualtricsProjectStageTriggerTypes`) REFERENCES `lookups` (`id`);

-- auto created user status
INSERT INTO userStatus (name, description)
VALUES ('auto_created', 'This user was auto created. The user has only code and cannot login. If the real user register later with the code the user will be activated to normal user.');

-- add table mailQueue
CREATE TABLE `mailQueue` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,
  `id_mailQueueStatus` INT(10) UNSIGNED ZEROFILL NOT NULL,  
  `date_create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  
  `date_to_be_sent` TIMESTAMP NOT NUll,
  `date_sent` TIMESTAMP,
  `from_email` VARCHAR(100) NOT NUll,
  `from_name` VARCHAR(100) NOT NUll,
  `reply_to` VARCHAR(100) NOT NUll,
  `recipient_emails` TEXT NOT NUll,
  `cc_emails` VARCHAR(1000),
  `bcc_emails` VARCHAR(1000),
  `subject` VARCHAR(1000) NOT NUll,
  `body` LONGTEXT NOT NUll,
  `is_html` INT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `mailQueue`
ADD CONSTRAINT `mailQueue_fk_id_mailQueueStatus` FOREIGN KEY (`id_mailQueueStatus`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add mailQueueStatus
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('mailQueueStatus', 'queued', 'Status for initialization. When the mail is queued it goes in this status');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('mailQueueStatus', 'deleted', 'When the queue is deleted');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('mailQueueStatus', 'sent', 'When the mail is sent');
INSERT INTO lookups (type_code, lookup_value, lookup_description) values ('mailQueueStatus', 'failed', 'When something happened and the mail sending failed');

-- view_mailQueu
DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT mq.id AS id, l_status.lookup_value AS status, date_create, date_to_be_sent, date_sent, from_email, from_name,
reply_to, recipient_emails, cc_emails, bcc_emails, subject, body, is_html
FROM mailQueue mq
INNER JOIN lookups l_status ON (l_status.id = mq.id_mailQueueStatus);

-- add mailQueueSearchDateTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('mailQueueSearchDateTypes', 'date_create', 'Entry date', 'The date that the queue record was created');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('mailQueueSearchDateTypes', 'date_to_be_sent', 'Date to be send', 'The date when the queue record should be sent');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('mailQueueSearchDateTypes', 'date_sent', 'Sent date', 'The date when the queue record was sent');


-- add table transactions
CREATE TABLE `transactions` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY  AUTO_INCREMENT,      
  `transaction_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  
  `id_transactionTypes` INT(10) UNSIGNED,
  `id_transactionBy` INT(10) UNSIGNED,
  `id_users` INT(10) UNSIGNED, -- the user who did the transaction, null if it was automated
  `table_name` varchar(100), -- the name of the table that we want to store. Later using the id we can make joins to retrieve some information
  `id_table_name` INT(10) UNSIGNED, -- the id of the record which is related to this transaction
  `transaction_log` TEXT  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `transactions`
ADD CONSTRAINT `transactions_fk_id_transactionTypes` FOREIGN KEY (`id_transactionTypes`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `transactions_fk_id_transactionBy` FOREIGN KEY (`id_transactionBy`) REFERENCES `lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `transactions_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- add transactionTypes
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'insert', 'Add new entry', 'Add new entry to a table');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'select', 'View entry', 'View entry from a table');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'update', 'Edit entry', 'Edit entry from a table');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'delete', 'Delete entry', 'Delete entry from a table');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'send_mail_ok', 'Send mail successfully', 'Send mail successfully');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'send_mail_fail', 'Send mail failed', 'Send mail failed');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionTypes', 'check_mailQueue', 'Check mail queue', 'Check mail queue and send mails if needed');

-- add transactionBy
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionBy', 'by_mail_cron', 'By mail cronjob', 'The action was done by a mail cronjob');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionBy', 'by_user', 'By user', 'The action was done by an user');
INSERT INTO lookups (type_code, lookup_code, lookup_value, lookup_description) values ('transactionBy', 'by_qualtrics_callback', 'By qualtrics callback', 'The action was done by a qualtrics callback');

-- view view_transactions
DROP VIEW IF EXISTS view_transactions;
CREATE VIEW view_transactions
AS
SELECT t.id, t.transaction_time, t.id_transactionTypes, tran_type.lookup_value AS transaction_type,
id_transactionBy, tran_by.lookup_value AS transaction_by, id_users, u.name AS user_name,
table_name, id_table_name, REPLACE(JSON_EXTRACT(transaction_log, '$.verbal_log'), '"', '') AS transaction_verbal_log
FROM transactions t
INNER JOIN lookups tran_type ON (tran_type.id = t.id_transactionTypes)
INNER JOIN lookups tran_by ON (tran_by.id = t.id_transactionBy)
LEFT JOIN users u ON (u.id = t.id_users);

-- view view_mailQueue_transactions
DROP VIEW IF EXISTS view_mailQueue_transactions;
CREATE VIEW view_mailQueue_transactions
AS
SELECT mq.id, date_create, date_to_be_sent, date_sent, t.id AS transaction_id, transaction_time, 
transaction_type, transaction_by, user_name, transaction_verbal_log
FROM mailQueue mq
INNER JOIN view_transactions t ON (t.table_name = 'mailQueue' AND t.id_table_name = mq.id)
ORDER BY mq.id ASC, t.id ASC;

-- add moduleMailComposeMail
INSERT INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`) 
VALUES (NULL, 'moduleMailComposeEmail', '/admin/mailQueue/composeEmail', 'GET|POST', '0000000002', NULL, '0000000009', '0', NULL, NULL, '0000000001');
SET @id_page = LAST_INSERT_ID();

INSERT INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page, '0000000008', '0000000001', 'Compose Mail');
INSERT INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page, '1', '0', '1', '0');

-- register moduleMailComposeEmail with page
call proc_register_module('moduleMail', 'moduleMailComposeEmail', 1);

-- add field open_registration to style register
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'open_registration', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('register'), get_field_id('open_registration'), 0, 'If checked any user can register without a registration code. The code will be automatically generated upon registration');

-- add field live_search to style select
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'live_search', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('select'), get_field_id('live_search'), 0, 'If checked the select component will have a live search text box which can filter the values');

-- add field disabled to style select
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'disabled', get_field_type_id('checkbox'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('select'), get_field_id('disabled'), 0, 'If checked the select component is disabled');


-- add field max to style select
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (get_style_id('select'), get_field_id('max'), 5, 'Set the maximum elements that can be shown in the drop down list before the scroller appears');

-- Add new field type `select-group` and field `group` in style register
INSERT INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-group', '7');
INSERT INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'group', get_field_type_id('select-group'), '0');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) 
VALUES (get_style_id('register'), get_field_id('group'), (SELECT id FROM groups WHERE name = 'subject' LIMIT 1), 'Select the default group in which evey new user is assigned.');

