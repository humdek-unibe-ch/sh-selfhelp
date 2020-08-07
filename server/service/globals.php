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
define('SERVICE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/server/service');
define('EMAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/server/email');
define('NAME_PATTERN', '[a-zA-Z0-9_-]+'); // pattern used for naming

define('MAX_USER_COUNT', 100000);

/* Static DB Content */
define('GUEST_USER_ID', 1);
define('ADMIN_USER_ID', 2);
define('ADMIN_GROUP_ID', 1);

define('NAVIGATION_STYLE_ID', 33);
define('NAVIGATION_CONTAINER_STYLE_ID', 30);

define('CSS_FIELD_ID', 23);
define('LABEL_FIELD_ID', 8);
define('NAME_FIELD_ID', 57);
define('TYPE_INPUT_FIELD_ID', 54);
define('EMAIL_TYPE_ID', 11);

define('STYLE_GROUP_INTERN_ID', 1);

define('MALE_GENDER_ID', 1);
define('ALL_LANGUAGE_ID', 1);

define('EXPERIMENTER_GROUP_ID', 2);
define('SUBJECT_GROUP_ID', 3);

define('GLOBAL_CHAT_ROOM_ID', 1);

define('INTERNAL_PAGE_ID', 1);
define('CORE_PAGE_ID', 2);
define('EXPERIMENT_PAGE_ID', 3);
define('OPEN_PAGE_ID', 4);

/* User Status code from table userStatus */
define('USER_STATUS_INTERESTED', 1);
define('USER_STATUS_INVITED', 2);
define('USER_STATUS_ACTIVE', 3);

/* Callback status */
define('CALLBACK_NEW', 'callback_new');
define('CALLBACK_ERROR', 'callback_error');
define('CALLBACK_SUCCESS', 'callback_success');

/* Emtry modes */
define('INSERT', 'insert');
define('UPDATE', 'update');
define('DELETE', 'delete');
define('SELECT', 'select');

/* mail separator */
define('MAIL_SEPARATOR', ';');

/*Lookup types */
define('qualtricScheduleTypes', 'qualtricScheduleTypes');
define('mailQueueSearchDateTypes', 'mailQueueSearchDateTypes');
define('transactionTypes', 'transactionTypes');
define('transactionBy', 'transactionBy');
define('weekdays', 'weekdays');
define('qualtricsActionScheduleTypes', 'qualtricsActionScheduleTypes');
define('mailQueueStatus', 'mailQueueStatus');
define('qualtricsSurveyTypes', 'qualtricsSurveyTypes');
define('qualtricsProjectActionTriggerTypes', 'qualtricsProjectActionTriggerTypes');
define('qualtricsProjectActionAdditionalFunction', 'qualtricsProjectActionAdditionalFunction');
define('timePeriod', 'timePeriod');
define('notificationTypes', 'notificationTypes');

/* Lookup codes */
/* Auto generate them from DB when new comes or a change is made*/
define('qualtricScheduleTypes_immediately', 'immediately');
define('qualtricScheduleTypes_on_fixed_datetime', 'on_fixed_datetime');
define('qualtricScheduleTypes_after_period', 'after_period');
define('qualtricScheduleTypes_after_period_on_day_at_time', 'after_period_on_day_at_time');
define('mailQueueSearchDateTypes_date_create', 'date_create');
define('mailQueueSearchDateTypes_date_to_be_sent', 'date_to_be_sent');
define('mailQueueSearchDateTypes_date_sent', 'date_sent');
define('transactionTypes_insert', 'insert');
define('transactionTypes_select', 'select');
define('transactionTypes_update', 'update');
define('transactionTypes_delete', 'delete');
define('transactionTypes_send_mail_ok', 'send_mail_ok');
define('transactionTypes_send_mail_fail', 'send_mail_fail');
define('transactionTypes_check_mailQueue', 'check_mailQueue');
define('transactionBy_by_mail_cron', 'by_mail_cron');
define('transactionBy_by_user', 'by_user');
define('transactionBy_by_qualtrics_callback', 'by_qualtrics_callback');
define('weekdays_monday', 'monday');
define('weekdays_tuesday', 'tuesday');
define('weekdays_wednesday', 'wednesday');
define('weekdays_thursday', 'thursday');
define('weekdays_friday', 'friday');
define('weekdays_saturday', 'saturday');
define('weekdays_sunday', 'sunday');
define('qualtricsActionScheduleTypes_nothing', 'nothing');
define('qualtricsActionScheduleTypes_notification', 'notification');
define('qualtricsActionScheduleTypes_reminder', 'reminder');
define('mailQueueStatus_queued', 'queued');
define('mailQueueStatus_deleted', 'deleted');
define('mailQueueStatus_sent', 'sent');
define('mailQueueStatus_failed', 'failed');
define('qualtricsSurveyTypes_baseline', 'baseline');
define('qualtricsSurveyTypes_follow_up', 'follow_up');
define('qualtricsProjectActionTriggerTypes_started', 'started');
define('qualtricsProjectActionTriggerTypes_finished', 'finished');
define('qualtricsProjectActionAdditionalFunction_workwell_evaluate_personal_strenghts', 'workwell_evaluate_personal_strenghts');
define('timePeriod_seconds', 'seconds');
define('timePeriod_minutes', 'minutes');
define('timePeriod_hours', 'hours');
define('timePeriod_days', 'days');
define('timePeriod_weeks', 'weeks');
define('timePeriod_months', 'months');
define('notificationTypes_email', 'email');
define('notificationTypes_sms', 'sms');
?>
