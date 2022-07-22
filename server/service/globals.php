<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/globals_untracked.php";

define('CSS_FOLDER', 'css');
define('CSS_PATH', BASE_PATH . '/' . CSS_FOLDER);
define('CSS_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . CSS_FOLDER);
define('JS_FOLDER', 'js');
define('JS_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . JS_FOLDER);
define('ASSET_FOLDER', 'assets');
define('ASSET_PATH', BASE_PATH . '/' . ASSET_FOLDER);
define('ASSET_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . ASSET_FOLDER);
define('STATIC_FOLDER', 'static');
define('STATIC_PATH', BASE_PATH . '/' . STATIC_FOLDER);
define('STATIC_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . STATIC_FOLDER);
define('STYLE_PATH', '/server/component/style');
define('STYLE_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . STYLE_PATH);
define('PLUGIN_PATH', '/server/plugins');
define('PLUGIN_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . PLUGIN_PATH);
define('SERVICE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/server/service');
define('EMAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/server/email');
define('NAME_PATTERN', '[a-zA-Z0-9_-]+'); // pattern used for naming

define('MAX_USER_COUNT', 100000);

define('ENTRY_RECORD_ID', 'record_id');

/* Static DB Content */
define('GUEST_USER_ID', 1);
define('ADMIN_USER_ID', 2);
define('ADMIN_GROUP_ID', 1);

define('NAVIGATION_STYLE_ID', 33);
define('NAVIGATION_CONTAINER_STYLE_ID', 30);

define('LABEL_FIELD_ID', 8);
define('NAME_FIELD_ID', 57);
define('TYPE_INPUT_FIELD_ID', 54);
define('EMAIL_TYPE_ID', 11);

define('STYLE_GROUP_INTERN_ID', 1);

define('MALE_GENDER_ID', 1);
define('ALL_LANGUAGE_ID', 1);

define('EXPERIMENTER_GROUP_ID', 2);
define('SUBJECT_GROUP_ID', 3);

define('INTERNAL_PAGE_ID', 1);
define('CORE_PAGE_ID', 2);
define('EXPERIMENT_PAGE_ID', 3);
define('OPEN_PAGE_ID', 4);

/* User Status code from table userStatus */
define('USER_STATUS_INTERESTED', 1);
define('USER_STATUS_INVITED', 2);
define('USER_STATUS_ACTIVE', 3);

/* Form types */
define('FORM_DYNAMIC', 'dynamic');
define('FORM_STATIC', 'static');

/* Callback status */
define('CALLBACK_NEW', 'callback_new');
define('CALLBACK_ERROR', 'callback_error');
define('CALLBACK_SUCCESS', 'callback_success');

/* Entry modes */
define('INSERT', 'insert');
define('UPDATE', 'update');
define('DELETE', 'delete');
define('SELECT', 'select');

/* relations */
define('RELATION_PAGE_FIELD', 'page_field');
define('RELATION_SECTION_FIELD', 'section_field');
define('RELATION_SECTION_CHILDREN', 'section_children');
define('RELATION_PAGE_CHILDREN', 'page_children');
define('RELATION_PAGE_NAV', 'page_nav');
define('RELATION_SECTION_NAV', 'section_nav');
define('RELATION_PAGE', 'page'); // used when we work with page columns/fields from the `page` table in the DB
define('RELATION_SECTION', 'section'); // used when we work with section columns/fields from the `section` table in the DB

/* mail separator */
define('MAIL_SEPARATOR', ';');

/*Lookup types */
define('actionScheduleJobs', 'actionScheduleJobs');
define('scheduledJobsSearchDateTypes', 'scheduledJobsSearchDateTypes');
define('transactionTypes', 'transactionTypes');
define('transactionBy', 'transactionBy');
define('weekdays', 'weekdays');
define('actionScheduleTypes', 'actionScheduleTypes');
define('scheduledJobsStatus', 'scheduledJobsStatus');
define('actionTriggerTypes', 'actionTriggerTypes');
define('timePeriod', 'timePeriod');
define('notificationTypes', 'notificationTypes');
define('plugins', 'plugins');
define('jobTypes', 'jobTypes');
define('pageAccessTypes', 'pageAccessTypes');
define('hookTypes', 'hookTypes');

/* Lookup codes */
/* Auto generate them from DB when new comes or a change is made*/
define('actionScheduleTypes_immediately', 'immediately');
define('actionScheduleTypes_on_fixed_datetime', 'on_fixed_datetime');
define('actionScheduleTypes_after_period', 'after_period');
define('actionScheduleTypes_after_period_on_day_at_time', 'after_period_on_day_at_time');
define('scheduledJobsSearchDateTypes_date_create', 'date_create');
define('scheduledJobsSearchDateTypes_date_to_be_executed', 'date_to_be_executed');
define('scheduledJobsSearchDateTypes_date_executed', 'date_executed');
define('transactionTypes_insert', 'insert');
define('transactionTypes_select', 'select');
define('transactionTypes_update', 'update');
define('transactionTypes_delete', 'delete');
define('transactionTypes_status_change', 'status_change');
define('transactionTypes_send_mail_ok', 'send_mail_ok');
define('transactionTypes_send_mail_fail', 'send_mail_fail');
define('transactionTypes_send_notification_ok', 'send_notification_ok');
define('transactionTypes_send_notification_fail', 'send_notification_fail');
define('transactionTypes_execute_task_ok', 'execute_task_ok');
define('transactionTypes_execute_task_fail', 'execute_task_fail');
define('transactionTypes_check_scheduledJobse', 'check_scheduledJobs');
define('transactionBy_by_cron_job', 'by_cron_job');
define('transactionBy_by_user', 'by_user');
define('transactionBy_by_anonymous_user', 'by_anonymous_user');
define('transactionBy_by_system', 'by_system');
define('weekdays_monday', 'monday');
define('weekdays_tuesday', 'tuesday');
define('weekdays_wednesday', 'wednesday');
define('weekdays_thursday', 'thursday');
define('weekdays_friday', 'friday');
define('weekdays_saturday', 'saturday');
define('weekdays_sunday', 'sunday');
define('actionScheduleJobs_nothing', 'nothing');
define('actionScheduleJobs_notification', 'notification');
define('actionScheduleJobs_reminder', 'reminder');
define('actionScheduleJobs_task', 'task');
define('scheduledJobsStatus_queued', 'queued');
define('scheduledJobsStatus_deleted', 'deleted');
define('scheduledJobsStatus_done', 'done');
define('scheduledJobsStatus_failed', 'failed');
define('actionTriggerTypes_started', 'started');
define('actionTriggerTypes_finished', 'finished');
define('timePeriod_seconds', 'seconds');
define('timePeriod_minutes', 'minutes');
define('timePeriod_hours', 'hours');
define('timePeriod_days', 'days');
define('timePeriod_weeks', 'weeks');
define('timePeriod_months', 'months');
define('notificationTypes_email', 'email');
define('notificationTypes_push_notification', 'push_notification');
define('notificationTypes_sms', 'sms');
define('plugins_calc_sleep_efficiency', 'calc_sleep_efficiency');
define('jobTypes_email', 'email');
define('jobTypes_notification', 'notification');
define('jobTypes_task', 'task');
define('pageAccessTypes_mobile', 'mobile');
define('pageAccessTypes_web', 'web');
define('pageAccessTypes_mobile_and_web', 'mobile_and_web');
define('hookTypes_hook_overwrite_return', 'hook_overwrite_return');
define('hookTypes_hook_on_function_execute', 'hook_on_function_execute');
?>
