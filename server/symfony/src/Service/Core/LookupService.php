<?php

namespace App\Service\Core;

use App\Entity\Lookup;
use App\Repository\LookupRepository;
use App\Service\Cache\Core\CacheableServiceTrait;
use App\Service\Cache\Core\CacheService;

/**
 * Lookup service providing access to lookup data and constants.
 * This service encapsulates all lookup operations and should be used
 * instead of accessing the repository directly.
 */
final class LookupService
{
    use CacheableServiceTrait;

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

    // styleType
    public const STYLE_TYPE_VIEW = 'view';
    public const STYLE_TYPE_COMPONENT = 'component';

    // plugins
    public const PLUGINS_CALC_SLEEP_EFFICIENCY = 'calc_sleep_efficiency';

    public function __construct(
        private readonly LookupRepository $lookupRepository
    ) {}

    /**
     * Find a lookup by type and value
     * 
     * @param string $typeCode The type code to search for
     * @param string $lookupValue The lookup value to search for
     * @return Lookup|null The lookup if found, null otherwise
     */
    public function findByTypeAndValue(string $typeCode, string $lookupValue): ?Lookup
    {
        return $this->getCache(
            CacheService::CATEGORY_LOOKUPS,
            "lookup_{$typeCode}_{$lookupValue}",
            function() use ($typeCode, $lookupValue) {
                return $this->lookupRepository->findByTypeAndValue($typeCode, $lookupValue);
            },
null
        );
    }

    /**
     * Get the default user type (used for new users)
     * 
     * @return Lookup|null The default user type lookup
     */
    public function getDefaultUserType(): ?Lookup
    {
        return $this->lookupRepository->getDefaultUserType();
    }

    /**
     * Get all lookups for a given type.
     *
     * @param string $typeCode
     * @return Lookup[]
     */
    public function getLookups(string $typeCode): array
    {
        return $this->getCache(
            CacheService::CATEGORY_LOOKUPS,
            "lookups_{$typeCode}",
            function() use ($typeCode) {
                return $this->lookupRepository->getLookups($typeCode);
            },
null
        );
    }

    /**
     * Get the ID of a lookup by value.
     *
     * @param string $typeCode
     * @param string $lookupValue
     * @return int|null
     */
    public function getLookupIdByValue(string $typeCode, string $lookupValue): ?int
    {
        return $this->lookupRepository->getLookupIdByValue($typeCode, $lookupValue);
    }

    /**
     * Get the ID of a lookup by code.
     *
     * @param string $typeCode
     * @param string $lookupCode
     * @return int|null
     */
    public function getLookupIdByCode(string $typeCode, string $lookupCode): ?int
    {
        return $this->lookupRepository->getLookupIdByCode($typeCode, $lookupCode);
    }

    /**
     * Get all lookups
     *
     * @return Lookup[]
     */
    public function getAllLookups(): array
    {
        return $this->lookupRepository->getAllLookups();
    }

    /**
     * Check if a lookup exists by type and value
     *
     * @param string $typeCode
     * @param string $lookupValue
     * @return bool
     */
    public function existsByTypeAndValue(string $typeCode, string $lookupValue): bool
    {
        return $this->findByTypeAndValue($typeCode, $lookupValue) !== null;
    }

    /**
     * Check if a lookup exists by type and code
     *
     * @param string $typeCode
     * @param string $lookupCode
     * @return bool
     */
    public function existsByTypeAndCode(string $typeCode, string $lookupCode): bool
    {
        return $this->getLookupIdByCode($typeCode, $lookupCode) !== null;
    }

    /**
     * Find a lookup by type and code
     *
     * @param string $typeCode
     * @param string $lookupCode
     * @return Lookup|null
     */
    public function findByTypeAndCode(string $typeCode, string $lookupCode): ?Lookup
    {
        return $this->lookupRepository->createQueryBuilder('l')
            ->where('l.typeCode = :typeCode')
            ->andWhere('l.lookupCode = :lookupCode')
            ->setParameter('typeCode', $typeCode)
            ->setParameter('lookupCode', $lookupCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get lookup value by type and code
     *
     * @param string $typeCode
     * @param string $lookupCode
     * @return string|null
     */
    public function getLookupValueByCode(string $typeCode, string $lookupCode): ?string
    {
        $lookup = $this->findByTypeAndCode($typeCode, $lookupCode);
        return $lookup ? $lookup->getLookupValue() : null;
    }

    /**
     * Find a lookup by ID
     *
     * @param int $id
     * @return Lookup|null
     */
    public function findById(int $id): ?Lookup
    {
        return $this->lookupRepository->find($id);
    }

    /**
     * Find a lookup by type and code (alias for findByTypeAndCode)
     *
     * @param array $criteria
     * @return Lookup|null
     */
    public function findOneBy(array $criteria): ?Lookup
    {
        return $this->lookupRepository->findOneBy($criteria);
    }

    /**
     * Get the lookup value by ID
     *
     * @param int $id
     * @return string|null
     */
    public function getLookupValueById(int $id): ?string
    {
        $lookup = $this->findById($id);
        return $lookup ? $lookup->getLookupValue() : null;
    }

    /**
     * Get the lookup code by ID
     *
     * @param int $id
     * @return string|null
     */
    public function getLookupCodeById(int $id): ?string
    {
        $lookup = $this->findById($id);
        return $lookup ? $lookup->getLookupCode() : null;
    }
}
