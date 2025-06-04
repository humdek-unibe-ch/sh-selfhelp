<?php

namespace App\Service\Core;

use App\Repository\LookupRepository;

/**
 * Lookup type and code constants for use throughout the application.
 * (Auto-generate or update as needed when DB lookups change.)
 */
final class LookupService
{
    // Lookup Types
    public const NOTIFICATION_TYPES = 'notificationTypes';
    public const ACTION_SCHEDULE_TYPES = 'actionScheduleTypes';
    public const ACTION_TRIGGER_TYPES = 'actionTriggerTypes';
    public const TIME_PERIOD = 'timePeriod';
    public const WEEKDAYS = 'weekdays';
    public const SCHEDULED_JOBS_STATUS = 'scheduledJobsStatus';
    public const SCHEDULED_JOBS_SEARCH_DATE_TYPES = 'scheduledJobsSearchDateTypes';
    public const TRANSACTION_TYPES = 'transactionTypes';
    public const TRANSACTION_BY = 'transactionBy';
    public const JOB_TYPES = 'jobTypes';
    public const PAGE_ACCESS_TYPES = 'pageAccessTypes';
    public const HOOK_TYPES = 'hookTypes';
    public const ASSET_TYPES = 'assetTypes';
    public const GROUP_TYPES = 'groupTypes';
    public const USER_TYPES = 'userTypes';
    public const USER_STATUS = 'userStatus';
    public const PAGE_ACTIONS = 'pageActions';
    public const STYLE_TYPE = 'styleType';
    public const PLUGINS = 'plugins';

    // Lookup Codes
    // notificationTypes
    public const NOTIFICATION_TYPES_EMAIL = 'email';
    public const NOTIFICATION_TYPES_PUSH_NOTIFICATION = 'push_notification';

    // actionScheduleTypes
    public const ACTION_SCHEDULE_TYPES_IMMEDIATELY = 'immediately';
    public const ACTION_SCHEDULE_TYPES_ON_FIXED_DATETIME = 'on_fixed_datetime';
    public const ACTION_SCHEDULE_TYPES_AFTER_PERIOD = 'after_period';
    public const ACTION_SCHEDULE_TYPES_AFTER_PERIOD_ON_DAY_AT_TIME = 'after_period_on_day_at_time';

    // actionTriggerTypes
    public const ACTION_TRIGGER_TYPES_STARTED = 'started';
    public const ACTION_TRIGGER_TYPES_FINISHED = 'finished';
    public const ACTION_TRIGGER_TYPES_DELETED = 'deleted';
    public const ACTION_TRIGGER_TYPES_UPDATED = 'updated';

    // timePeriod
    public const TIME_PERIOD_SECONDS = 'seconds';
    public const TIME_PERIOD_MINUTES = 'minutes';
    public const TIME_PERIOD_HOURS = 'hours';
    public const TIME_PERIOD_DAYS = 'days';
    public const TIME_PERIOD_WEEKS = 'weeks';
    public const TIME_PERIOD_MONTHS = 'months';

    // weekdays
    public const WEEKDAYS_MONDAY = 'monday';
    public const WEEKDAYS_TUESDAY = 'tuesday';
    public const WEEKDAYS_WEDNESDAY = 'wednesday';
    public const WEEKDAYS_THURSDAY = 'thursday';
    public const WEEKDAYS_FRIDAY = 'friday';
    public const WEEKDAYS_SATURDAY = 'saturday';
    public const WEEKDAYS_SUNDAY = 'sunday';

    // scheduledJobsStatus
    public const SCHEDULED_JOBS_STATUS_QUEUED = 'queued';
    public const SCHEDULED_JOBS_STATUS_DELETED = 'deleted';
    public const SCHEDULED_JOBS_STATUS_DONE = 'done';
    public const SCHEDULED_JOBS_STATUS_FAILED = 'failed';

    // scheduledJobsSearchDateTypes
    public const SCHEDULED_JOBS_SEARCH_DATE_TYPES_DATE_CREATE = 'date_create';
    public const SCHEDULED_JOBS_SEARCH_DATE_TYPES_DATE_TO_BE_EXECUTED = 'date_to_be_executed';
    public const SCHEDULED_JOBS_SEARCH_DATE_TYPES_DATE_EXECUTED = 'date_executed';

    // transactionTypes
    public const TRANSACTION_TYPES_INSERT = 'insert';
    public const TRANSACTION_TYPES_SELECT = 'select';
    public const TRANSACTION_TYPES_UPDATE = 'update';
    public const TRANSACTION_TYPES_DELETE = 'delete';
    public const TRANSACTION_TYPES_STATUS_CHANGE = 'status_change';
    public const TRANSACTION_TYPES_SEND_MAIL_OK = 'send_mail_ok';
    public const TRANSACTION_TYPES_SEND_MAIL_FAIL = 'send_mail_fail';
    public const TRANSACTION_TYPES_SEND_NOTIFICATION_OK = 'send_notification_ok';
    public const TRANSACTION_TYPES_SEND_NOTIFICATION_FAIL = 'send_notification_fail';
    public const TRANSACTION_TYPES_EXECUTE_TASK_OK = 'execute_task_ok';
    public const TRANSACTION_TYPES_EXECUTE_TASK_FAIL = 'execute_task_fail';
    public const TRANSACTION_TYPES_CHECK_SCHEDULEDJOBS = 'check_scheduledJobs';

    // transactionBy
    public const TRANSACTION_BY_BY_CRON_JOB = 'by_cron_job';
    public const TRANSACTION_BY_BY_USER = 'by_user';
    public const TRANSACTION_BY_BY_ANONYMOUS_USER = 'by_anonymous_user';
    public const TRANSACTION_BY_BY_SYSTEM = 'by_system';
    public const TRANSACTION_BY_BY_SYSTEM_USER = 'by_system_user';

    // jobTypes
    public const JOB_TYPES_EMAIL = 'email';
    public const JOB_TYPES_NOTIFICATION = 'notification';
    public const JOB_TYPES_TASK = 'task';

    // pageAccessTypes
    public const PAGE_ACCESS_TYPES_MOBILE = 'mobile';
    public const PAGE_ACCESS_TYPES_WEB = 'web';
    public const PAGE_ACCESS_TYPES_MOBILE_AND_WEB = 'mobile_and_web';

    // hookTypes
    public const HOOK_TYPES_HOOK_OVERWRITE_RETURN = 'hook_overwrite_return';
    public const HOOK_TYPES_HOOK_ON_FUNCTION_EXECUTE = 'hook_on_function_execute';

    // assetTypes
    public const ASSET_TYPES_CSS = 'css';
    public const ASSET_TYPES_ASSET = 'asset';
    public const ASSET_TYPES_STATIC = 'static';

    // groupTypes
    public const GROUP_TYPES_DB_ROLE = 'db_role';
    public const GROUP_TYPES_GROUP = 'group';

    // userTypes
    public const USER_TYPES_USER = 'user';
    public const USER_TYPES_ADMIN = 'admin'; // If exists

    // userStatus
    public const USER_STATUS_INVITED = 'invited';
    public const USER_STATUS_ACTIVE = 'active';
    public const USER_STATUS_LOCKED = 'locked';

    // pageActions
    public const PAGE_ACTIONS_BACKEND = 'backend';
    public const PAGE_ACTIONS_NAVIGATION = 'navigation';
    public const PAGE_ACTIONS_SECTIONS = 'sections';
    public const PAGE_ACTIONS_COMPONENT = 'component';
    public const PAGE_ACTIONS_AJAX = 'ajax';
    public const PAGE_ACTIONS_CMS_API = 'cms-api';
    public const PAGE_ACTIONS_EXPERIMENT = 'experiment';
    public const PAGE_ACTIONS_EXPORT = 'export';

    // styleType
    public const STYLE_TYPE_VIEW = 'view';
    public const STYLE_TYPE_COMPONENT = 'component';

    // plugins
    public const PLUGINS_CALC_SLEEP_EFFICIENCY = 'calc_sleep_efficiency';

    public function __construct(
        private readonly LookupRepository $lookupRepository
    ) {}

    /**
     * Get all lookups
     */
    public function getAllLookups(): array
    {
        return $this->lookupRepository->getAllLookups();
    }
}
