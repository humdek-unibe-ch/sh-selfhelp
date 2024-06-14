DROP TABLE IF EXISTS `dta_selected_target_groups`;
DROP TABLE IF EXISTS `dta_jobs_groups`;
DROP TABLE IF EXISTS `dta_randomizers`;
DROP TABLE IF EXISTS `dta_repeaters_until_date`;
DROP TABLE IF EXISTS `dta_repeaters`;
DROP TABLE IF EXISTS `dta_reminders`;
DROP TABLE IF EXISTS `dta_notifications`;
DROP TABLE IF EXISTS `dta_jobs`;
DROP TABLE IF EXISTS `dta_schedule_time`;
DROP TABLE IF EXISTS `dta_blocks`;
DROP TABLE IF EXISTS `dta_actions`;
DROP TABLE IF EXISTS `dta_conditions`;

CREATE TABLE `dta_conditions` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `jsonLogic` JSON,
  `builder` JSON,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_actions` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `id_formProjectActionTriggerTypes` int(10) unsigned zerofill NOT NULL,
  `id_conditions` int(10) unsigned zerofill DEFAULT NULL,
  `id_dataTables` int(10) unsigned zerofill DEFAULT NULL,
  `target_groups` BOOLEAN NOT NULL DEFAULT false,
  PRIMARY KEY (`id`),
  CONSTRAINT `actions_dataTables` FOREIGN KEY (`id_dataTables`) REFERENCES `dataTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `actions_id_conditions` FOREIGN KEY (`id_conditions`) REFERENCES `dta_conditions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_selected_target_groups` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_dta_actions` int(10) unsigned zerofill NOT NULL,
  `id_groups` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  KEY `selected_target_groups_id_dta_actions` (`id_dta_actions`),
  KEY `selected_target_groups_id_id_groups` (`id_groups`),
  CONSTRAINT `selected_target_groups_id_dta_actions` FOREIGN KEY (`id_dta_actions`) REFERENCES `dta_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `selected_target_groups_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_blocks` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,  
  `id_conditions` int(10) unsigned zerofill NOT NULL,
  `block_name` varchar(255) NOT NULL,
  `randomization_count` int(11) NOT NULL DEFAULT 0,
  `id_dta_actions` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `blocks_id_conditions` FOREIGN KEY (`id_conditions`) REFERENCES `dta_conditions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blocks_id_dta_actions` FOREIGN KEY (`id_dta_actions`) REFERENCES `dta_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_schedule_time` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `job_schedule_types` varchar(255) NOT NULL,
  `send_after` int(11) DEFAULT NULL,
  `send_after_type` varchar(50) DEFAULT NULL,
  `send_on` varchar(50) DEFAULT NULL,
  `send_on_day` varchar(50) DEFAULT NULL,
  `send_on_day_at` time DEFAULT NULL,
  `custom_time` datetime DEFAULT NULL,  
  PRIMARY KEY (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_jobs` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_blocks` int(10) unsigned zerofill NOT NULL,
  `id_conditions` int(10) unsigned zerofill NOT NULL,
  `id_conditions_on_execute` int(10) unsigned zerofill NOT NULL,
  `id_schedule_time` int(10) unsigned zerofill NOT NULL,
  `job_name` varchar(255) NOT NULL DEFAULT 'Job',
  `job_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_block_id` (`id_blocks`),
  CONSTRAINT `jobs_id_blocks` FOREIGN KEY (`id_blocks`) REFERENCES `dta_blocks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jobs_id_conditions` FOREIGN KEY (`id_conditions`) REFERENCES `dta_conditions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jobs_id_conditions_on_execute` FOREIGN KEY (`id_conditions_on_execute`) REFERENCES `dta_conditions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jobs_id_schedule_time` FOREIGN KEY (`id_schedule_time`) REFERENCES `dta_schedule_time` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_randomizers` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `even_presentation` boolean NOT NULL DEFAULT false,
  `random_elements` int(11) NOT NULL DEFAULT 1,
  `id_dta_actions` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  KEY `randomizers_id_dta_actions` (`id_dta_actions`),
  CONSTRAINT `randomizers_id_dta_actions` FOREIGN KEY (`id_dta_actions`) REFERENCES `dta_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


CREATE TABLE `dta_repeaters` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `occurrences` int(11) NOT NULL DEFAULT 1,
  `frequency` varchar(50) NOT NULL,
  `id_dta_actions` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  KEY `repeaters_id_dta_actions` (`id_dta_actions`),
  CONSTRAINT `repeaters_id_dta_actions` FOREIGN KEY (`id_dta_actions`) REFERENCES `dta_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


CREATE TABLE `dta_repeaters_until_date` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `deadline` datetime NOT NULL,
  `schedule_at` time NOT NULL,
  `repeat_every` int(11) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `daysOfWeek` JSON,
  `daysOfMonth` JSON,
  `id_dta_actions` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  KEY `repeater_until_date_id_dta_actions` (`id_dta_actions`),
  CONSTRAINT `repeater_until_date_id_dta_actions` FOREIGN KEY (`id_dta_actions`) REFERENCES `dta_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_notifications` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `notification_type` varchar(50) NOT NULL DEFAULT 'email',
  `redirect_url` varchar(255) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `reply_to` varchar(255) DEFAULT NULL,
  `recipient` varchar(255) DEFAULT NULL,
  `attachments` JSON DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `id_jobs` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),  
  CONSTRAINT `notifications_id_jobs` FOREIGN KEY (`id_jobs`) REFERENCES `dta_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_reminders` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `id_conditions` int(10) unsigned zerofill NOT NULL,
  `id_jobs` int(10) unsigned zerofill NOT NULL,
  `schedule_time_id` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reminders_id_conditions` (`id_conditions`),
  KEY `reminders_id_jobs` (`id_jobs`),
  KEY `reminders_schedule_time_id` (`schedule_time_id`),
  CONSTRAINT `reminders_id_conditions` FOREIGN KEY (`id_conditions`) REFERENCES `dta_conditions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reminders_id_jobs` FOREIGN KEY (`id_jobs`) REFERENCES `dta_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reminders_schedule_time_id` FOREIGN KEY (`schedule_time_id`) REFERENCES `dta_schedule_time` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `dta_jobs_groups` (
  `id_jobs` int(10) unsigned zerofill NOT NULL,
  `id_groups` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id_jobs`, `id_groups`),
  CONSTRAINT `dta_jobs_groups_id_jobs` FOREIGN KEY (`id_jobs`) REFERENCES `dta_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dta_jobs_groups_id_groups` FOREIGN KEY (`id_groups`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;