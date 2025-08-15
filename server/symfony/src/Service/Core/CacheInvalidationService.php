<?php

namespace App\Service\Core;

use App\Entity\User;
use App\Entity\Page;
use App\Entity\Section;
use App\Entity\Language;
use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Asset;
use App\Entity\ScheduledJob;
use Psr\Log\LoggerInterface;

/**
 * Service for managing cache invalidation strategies based on entity changes
 * 
 * This service defines the invalidation rules for different entities and provides
 * methods to invalidate related caches when entities are created, updated, or deleted.
 */
class CacheInvalidationService
{
    public function __construct(
        private GlobalCacheService $cacheService,
        private ?LoggerInterface $logger = null
    ) {}

    /**
     * Invalidate caches when a page is modified
     */
    public function invalidatePage(Page $page, string $operation = 'update'): void
    {
        $pageId = $page->getId();
        
        // Invalidate specific page cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, "page_{$pageId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, "page_sections_{$pageId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, "page_fields_{$pageId}");
        
        // Invalidate page list caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, 'pages_list');
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, 'pages_hierarchy');
        
        // Invalidate all frontend user caches (as page changes affect all users)
        $this->cacheService->invalidateByPattern('frontend_user:*');
        
        // If page is deleted, also invalidate sections that belonged to it
        if ($operation === 'delete') {
            $this->cacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, "page_sections_{$pageId}");
        }
        
        $this->log('info', 'Page cache invalidated', ['page_id' => $pageId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a user is modified
     * @param int $userId
     * @param string $operation
     */
    public function invalidateUser(int $userId, string $operation = 'update'): void
    {
        // Invalidate specific user cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_USERS, "user_{$userId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_USERS, "user_profile_{$userId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_USERS, "user_permissions_{$userId}");
        
        // Invalidate user list caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_USERS, 'users_list');
        
        // Invalidate all frontend caches for this specific user
        $this->cacheService->invalidateUserFrontend($userId);
        
        // Invalidate permission caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PERMISSIONS, "user_acl_{$userId}");
        
        $this->log('info', 'User cache invalidated', ['user_id' => $userId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a section is modified
     */
    public function invalidateSection(Section $section, string $operation = 'update'): void
    {
        $sectionId = $section->getId();
        
        // Invalidate specific section cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, "section_{$sectionId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, "section_children_{$sectionId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, "section_fields_{$sectionId}");
        
        // Invalidate section list caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, 'sections_list');
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, 'sections_hierarchy');
        
        // If section belongs to a page, invalidate page caches too
        // Note: We'll invalidate all pages since we can't determine specific page from section entity
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, 'pages_list');
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, 'pages_hierarchy');
        
        // Invalidate parent section caches - this would need to be determined by the calling service
        // since we don't have direct access to parent relationships in this generic method
        
        // Invalidate frontend user caches (sections affect all users)
        $this->cacheService->invalidateByPattern('frontend_user:*');
        
        $this->log('info', 'Section cache invalidated', ['section_id' => $sectionId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when sections are reordered
     */
    public function invalidateSectionOrder(Page $page): void
    {
        $pageId = $page->getId();
        
        // Invalidate page sections cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, "page_sections_{$pageId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, "page_{$pageId}");
        
        // Invalidate section hierarchy caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, 'sections_hierarchy');
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SECTIONS, 'sections_list');
        
        // Invalidate frontend user caches
        $this->cacheService->invalidateByPattern('frontend_user:*');
        
        $this->log('info', 'Section order cache invalidated', ['page_id' => $pageId]);
    }

    /**
     * Invalidate caches when a language is modified
     */
    public function invalidateLanguage(Language $language, string $operation = 'update'): void
    {
        $languageId = $language->getId();
        
        // Invalidate specific language cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_LANGUAGES, "language_{$languageId}");
        
        // Invalidate language list caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_LANGUAGES, 'languages_list');
        $this->cacheService->delete(GlobalCacheService::CATEGORY_LANGUAGES, 'active_languages');
        
        // Invalidate all translation caches
        $this->cacheService->invalidateByPattern('*translations*');
        
        // Invalidate frontend user caches (language changes affect all users)
        $this->cacheService->invalidateByPattern('frontend_user:*');
        
        $this->log('info', 'Language cache invalidated', ['language_id' => $languageId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a group is modified
     */
    public function invalidateGroup(Group $group, string $operation = 'update'): void
    {
        $groupId = $group->getId();
        
        // Invalidate specific group cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_GROUPS, "group_{$groupId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_GROUPS, "group_users_{$groupId}");
        
        // Invalidate group list caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_GROUPS, 'groups_list');
        
        // Invalidate permission caches for all users in this group
        $this->cacheService->invalidateCategory(GlobalCacheService::CATEGORY_PERMISSIONS);
        
        $this->log('info', 'Group cache invalidated', ['group_id' => $groupId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a role is modified
     */
    public function invalidateRole(Role $role, string $operation = 'update'): void
    {
        $roleId = $role->getId();
        
        // Invalidate specific role cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_ROLES, "role_{$roleId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_ROLES, "role_permissions_{$roleId}");
        
        // Invalidate role list caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_ROLES, 'roles_list');
        
        // Invalidate all permission caches (role changes affect permissions)
        $this->cacheService->invalidateCategory(GlobalCacheService::CATEGORY_PERMISSIONS);
        
        $this->log('info', 'Role cache invalidated', ['role_id' => $roleId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when permissions are modified
     */
    public function invalidatePermissions(?int $userId = null): void
    {
        if ($userId) {
            // Invalidate specific user permissions
            $this->cacheService->delete(GlobalCacheService::CATEGORY_PERMISSIONS, "user_acl_{$userId}");
            $this->cacheService->delete(GlobalCacheService::CATEGORY_PERMISSIONS, "user_permissions_{$userId}");
            
            // Invalidate frontend caches for this user
            $this->cacheService->invalidateUserFrontend($userId);
            
            $this->log('info', 'User permissions cache invalidated', ['user_id' => $userId]);
        } else {
            // Invalidate all permission caches
            $this->cacheService->invalidateCategory(GlobalCacheService::CATEGORY_PERMISSIONS);
            
            // Invalidate all frontend user caches
            $this->cacheService->invalidateByPattern('frontend_user:*');
            
            $this->log('info', 'All permissions cache invalidated');
        }
    }

    /**
     * Invalidate caches when an asset is modified
     */
    public function invalidateAsset(Asset $asset, string $operation = 'update'): void
    {
        $assetId = $asset->getId();
        
        // Invalidate specific asset cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_ASSETS, "asset_{$assetId}");
        
        // Invalidate asset list caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_ASSETS, 'assets_list');
        $this->cacheService->delete(GlobalCacheService::CATEGORY_ASSETS, 'assets_by_type');
        
        $this->log('info', 'Asset cache invalidated', ['asset_id' => $assetId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a scheduled job is modified
     */
    public function invalidateScheduledJob(ScheduledJob $job, string $operation = 'update'): void
    {
        $jobId = $job->getId();
        
        // Invalidate specific job cache
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SCHEDULED_JOBS, "job_{$jobId}");
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SCHEDULED_JOBS, "job_transactions_{$jobId}");
        
        // Invalidate job list caches
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SCHEDULED_JOBS, 'jobs_list');
        $this->cacheService->delete(GlobalCacheService::CATEGORY_SCHEDULED_JOBS, 'jobs_by_status');
        
        $this->log('info', 'Scheduled job cache invalidated', ['job_id' => $jobId, 'operation' => $operation]);
    }

    /**
     * Invalidate lookup caches
     */
    public function invalidateLookups(string $typeCode = null): void
    {
        if ($typeCode) {
            $this->cacheService->delete(GlobalCacheService::CATEGORY_LOOKUPS, "lookups_{$typeCode}");
            $this->log('info', 'Lookup cache invalidated', ['type_code' => $typeCode]);
        } else {
            $this->cacheService->invalidateCategory(GlobalCacheService::CATEGORY_LOOKUPS);
            $this->log('info', 'All lookup caches invalidated');
        }
    }

    /**
     * Invalidate CMS preferences cache
     */
    public function invalidateCmsPreferences(?int $userId = null): void
    {
        if ($userId) {
            $this->cacheService->delete(GlobalCacheService::CATEGORY_CMS_PREFERENCES, "user_preferences_{$userId}");
            $this->log('info', 'User CMS preferences cache invalidated', ['user_id' => $userId]);
        } else {
            $this->cacheService->invalidateCategory(GlobalCacheService::CATEGORY_CMS_PREFERENCES);
            $this->log('info', 'All CMS preferences cache invalidated');
        }
    }

    /**
     * Invalidate all caches for a specific user (used when user performs any action)
     */
    public function invalidateAllUserCaches(int $userId): void
    {
        // Invalidate user-specific caches
        $this->invalidateUser($userId, 'update'); // This will handle user-specific invalidation
        
        // Invalidate all frontend caches for this user
        $this->cacheService->invalidateUserFrontend($userId);
        
        // Invalidate user permissions
        $this->invalidatePermissions($userId);
        
        // Invalidate user CMS preferences
        $this->invalidateCmsPreferences($userId);
        
        $this->log('info', 'All user caches invalidated', ['user_id' => $userId]);
    }

    /**
     * Log cache invalidation operations
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, "[CacheInvalidation] {$message}", $context);
        }
    }
}
