<?php

namespace App\Service\Cache\Core;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Service\Core\Utils;
use App\Entity\User;
use App\Entity\Page;
use App\Entity\Section;
use App\Entity\Language;
use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Asset;
use App\Entity\ScheduledJob;
use App\Entity\Action;

/**
 * ENTITY RULE
 * 
 * Core cache service for managing application-wide caching operations
 * 
 * USE THIS SERVICE DIRECTLY WHEN:
 * - You need fine-grained cache control (manual get/set/delete)
 * - You need custom invalidation logic
 * - You're building cache infrastructure
 * - You need direct pool access
 * - You're in controllers or system-level services
 * 
 * USE CacheableServiceTrait WHEN:
 * - You need simple "cache-or-callback" patterns in business services
 * - You want convenience methods for common caching scenarios
 * - You need standardized cache key generation
 * 
 * This service provides:
 * - Low-level cache operations: get, set, delete, has
 * - Category-based cache management with prefixes
 * - Entity-specific invalidation methods
 * - Pool management and TTL logic
 * - Cache statistics integration
 * 
 * Cache Categories:
 * - pages: Page entities and their data
 * - users: User entities and profiles
 * - sections: Section entities and hierarchies
 * - languages: Language entities and translations
 * - groups: Group entities and memberships
 * - roles: Role entities and permissions
 * - permissions: Permission entities and ACLs
 * - lookups: Lookup data and constants
 * - assets: Asset entities and metadata
 * - frontend_user: User-specific frontend data
 * - cms_preferences: CMS configuration preferences
 * - scheduled_jobs: Scheduled job entities
 * - actions: Actions for dataTables
 */
class CacheService
{
    // Cache category prefixes
    public const CATEGORY_PAGES = 'pages';
    public const CATEGORY_USERS = 'users';
    public const CATEGORY_SECTIONS = 'sections';
    public const CATEGORY_LANGUAGES = 'languages';
    public const CATEGORY_GROUPS = 'groups';
    public const CATEGORY_ROLES = 'roles';
    public const CATEGORY_PERMISSIONS = 'permissions';
    public const CATEGORY_LOOKUPS = 'lookups';
    public const CATEGORY_ASSETS = 'assets';
    public const CATEGORY_FRONTEND_USER = 'frontend_user';
    public const CATEGORY_CMS_PREFERENCES = 'cms_preferences';
    public const CATEGORY_SCHEDULED_JOBS = 'scheduled_jobs';
    public const CATEGORY_ACTIONS = 'actions';

    // Cache pools
    private CacheItemPoolInterface $globalCache;
    private CacheItemPoolInterface $userFrontendCache;
    private CacheItemPoolInterface $adminCache;
    private CacheItemPoolInterface $lookupsCache;
    private CacheItemPoolInterface $permissionsCache;
    private CacheItemPoolInterface $appCache;

    private ?CacheStatsService $statsService = null;

    public function __construct(
        #[Autowire(service: 'cache.global')] CacheItemPoolInterface $globalCache,
        #[Autowire(service: 'cache.user_frontend')] CacheItemPoolInterface $userFrontendCache,
        #[Autowire(service: 'cache.admin')] CacheItemPoolInterface $adminCache,
        #[Autowire(service: 'cache.lookups')] CacheItemPoolInterface $lookupsCache,
        #[Autowire(service: 'cache.permissions')] CacheItemPoolInterface $permissionsCache,
        #[Autowire(service: 'cache.app')] CacheItemPoolInterface $appCache,
        private ?LoggerInterface $logger = null
    ) {
        $this->globalCache = $globalCache;
        $this->userFrontendCache = $userFrontendCache;
        $this->adminCache = $adminCache;
        $this->lookupsCache = $lookupsCache;
        $this->permissionsCache = $permissionsCache;
        $this->appCache = $appCache;
    }

    /**
     * Set the stats service for recording statistics
     */
    public function setStatsService(CacheStatsService $statsService): void
    {
        $this->statsService = $statsService;
    }

    /**
     * Get data from cache
     */
    public function get(string $category, string $key, ?int $userId = null): mixed
    {
        $fullKey = $this->generateCacheKey($category, $key, $userId);
        $pool = $this->getCachePool($category);
        
        $item = $pool->getItem($fullKey);
        
        if ($item->isHit()) {
            $this->recordHit($category);
            $this->log('debug', 'Cache hit', ['category' => $category, 'key' => $key, 'user_id' => $userId]);
            return $item->get();
        }
        
        $this->recordMiss($category);
        $this->log('debug', 'Cache miss', ['category' => $category, 'key' => $key, 'user_id' => $userId]);
        return null;
    }

    /**
     * Set data in cache
     */
    public function set(string $category, string $key, mixed $data, ?int $ttl = null, ?int $userId = null): bool
    {
        $fullKey = $this->generateCacheKey($category, $key, $userId);
        $pool = $this->getCachePool($category);
        
        $item = $pool->getItem($fullKey);
        $data = Utils::normalizeWithSymfonySerializer($data);
        $item->set($data);
        
        if ($ttl !== null) {
            $item->expiresAfter($ttl);
        }
        
        $success = $pool->save($item);
        
        if ($success) {
            $this->recordSet($category);
            $this->log('debug', 'Cache set', ['category' => $category, 'key' => $key, 'user_id' => $userId, 'ttl' => $ttl]);
        }
        
        return $success;
    }

    /**
     * Check if cache item exists
     */
    public function has(string $category, string $key, ?int $userId = null): bool
    {
        $fullKey = $this->generateCacheKey($category, $key, $userId);
        $pool = $this->getCachePool($category);
        
        return $pool->hasItem($fullKey);
    }

    /**
     * Delete specific cache item
     */
    public function delete(string $category, string $key, ?int $userId = null): bool
    {
        $fullKey = $this->generateCacheKey($category, $key, $userId);
        $pool = $this->getCachePool($category);
        
        $success = $pool->deleteItem($fullKey);
        
        if ($success) {
            $this->recordInvalidation($category);
            $this->log('debug', 'Cache item deleted', ['category' => $category, 'key' => $key, 'user_id' => $userId]);
        }
        
        return $success;
    }

    /**
     * Invalidate entire category
     */
    public function invalidateCategory(string $category): bool
    {
        $pool = $this->getCachePool($category);
        $success = $pool->clear();
        
        if ($success) {
            $this->recordInvalidation($category, 'category');
            $this->log('info', 'Cache category invalidated', ['category' => $category]);
        }
        
        return $success;
    }

    /**
     * Invalidate all user-specific frontend caches for a user
     */
    public function invalidateUserCategory(int $userId): bool
    {
        $pattern = $this->generateCacheKey(self::CATEGORY_FRONTEND_USER, '*', $userId);
        return $this->invalidateByPattern($pattern);
    }

    /**
     * Clear all caches
     */
    public function clearAll(): bool
    {
        $pools = [
            'global' => $this->globalCache,
            'user_frontend' => $this->userFrontendCache,
            'admin' => $this->adminCache,
            'lookups' => $this->lookupsCache,
            'permissions' => $this->permissionsCache,
            'app' => $this->appCache
        ];
        
        $success = true;
        foreach ($pools as $name => $pool) {
            $result = $pool->clear();
            $success = $success && $result;
            
            if ($result) {
                $this->log('info', 'Cache pool cleared', ['pool' => $name]);
            }
        }
        
        if ($success) {
            // Reset statistics via stats service
            if ($this->statsService) {
                $this->statsService->resetStats();
            }
            $this->log('info', 'All caches cleared');
        }
        
        return $success;
    }

    /**
     * Clear API routes cache specifically
     */
    public function clearApiRoutes(): bool
    {
        $result = $this->appCache->deleteItem('api_routes_collection');
        
        if ($result) {
            $this->log('info', 'API routes cache cleared');
        }
        
        return $result;
    }

    /**
     * Clear API routes cache (alternative method name for backward compatibility)
     */
    public function clearApiRoutesCache(): bool
    {
        return $this->clearApiRoutes();
    }

    /**
     * Get TTL for different cache categories
     */
    public function getCacheTTL(string $category): int
    {
        return match ($category) {
            self::CATEGORY_LOOKUPS => 7200, // 2 hours
            self::CATEGORY_PERMISSIONS => 1800, // 30 minutes
            self::CATEGORY_FRONTEND_USER => 1800, // 30 minutes
            self::CATEGORY_LANGUAGES => 3600, // 1 hour
            self::CATEGORY_ROLES => 1800, // 30 minutes
            self::CATEGORY_GROUPS => 1800, // 30 minutes
            default => 3600 // 1 hour default
        };
    }

    /**
     * Get cache pool for a category (used by CacheStatsService)
     */
    public function getCachePool(string $category): CacheItemPoolInterface
    {
        return match ($category) {
            self::CATEGORY_FRONTEND_USER => $this->userFrontendCache,
            self::CATEGORY_LOOKUPS => $this->lookupsCache,
            self::CATEGORY_PERMISSIONS => $this->permissionsCache,            
            self::CATEGORY_ACTIONS => $this->adminCache,
            self::CATEGORY_CMS_PREFERENCES => $this->adminCache,
            self::CATEGORY_SCHEDULED_JOBS => $this->adminCache,            
            self::CATEGORY_ROLES => $this->adminCache,
            self::CATEGORY_ASSETS => $this->adminCache,
            'stats' => $this->globalCache, // Use global cache for stats storage
            default => $this->globalCache
        };
    }

    /**
     * Generic method to invalidate cache for any entity
     * This is the main entry point for entity-based invalidation
     */
    public function invalidateForEntity(object $entity, string $operation = 'update'): void
    {
        $entityClass = get_class($entity);
        
        match ($entityClass) {
            Page::class => $this->invalidatePage($entity, $operation),
            User::class => $this->invalidateUser($entity->getId(), $operation),
            Section::class => $this->invalidateSection($entity, $operation),
            Language::class => $this->invalidateLanguage($entity, $operation),
            Group::class => $this->invalidateGroup($entity, $operation),
            Role::class => $this->invalidateRole($entity, $operation),
            Asset::class => $this->invalidateAsset($entity, $operation),
            ScheduledJob::class => $this->invalidateScheduledJob($entity, $operation),
            Action::class => $this->invalidateAction($entity, $operation),
            default => $this->log('warning', 'Unknown entity type for cache invalidation', ['entity_class' => $entityClass])
        };
    }

    /**
     * Invalidate caches when a page is modified
     */
    public function invalidatePage(Page $page, string $operation = 'update'): void
    {
        $pageId = $page->getId();
        
        // Invalidate specific page cache items
        $this->delete(self::CATEGORY_PAGES, "page_{$pageId}");
        $this->delete(self::CATEGORY_PAGES, "page_sections_{$pageId}");
        $this->delete(self::CATEGORY_PAGES, "page_fields_{$pageId}");
        
        // Invalidate page category completely
        $this->invalidateCategory(self::CATEGORY_PAGES);
        
        // Invalidate all frontend user caches (page changes affect all users)
        $this->invalidateAllUserFrontendCaches();
        
        // If page is deleted, also invalidate sections that belonged to it
        if ($operation === 'delete') {
            $this->delete(self::CATEGORY_SECTIONS, "page_sections_{$pageId}");
        }
        
        $this->log('info', 'Page cache invalidated', ['page_id' => $pageId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a user is modified
     */
    public function invalidateUser(int $userId, string $operation = 'update'): void
    {
        // Invalidate specific user cache items
        $this->delete(self::CATEGORY_USERS, "user_{$userId}");
        $this->delete(self::CATEGORY_USERS, "user_profile_{$userId}");
        $this->delete(self::CATEGORY_USERS, "user_permissions_{$userId}");
        
        // Invalidate user category completely
        $this->invalidateCategory(self::CATEGORY_USERS);
        
        // Invalidate all frontend caches for this specific user
        $this->invalidateUserCategory($userId);
        
        // Invalidate permission caches
        $this->delete(self::CATEGORY_PERMISSIONS, "user_acl_{$userId}");
        
        $this->log('info', 'User cache invalidated', ['user_id' => $userId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a section is modified
     */
    public function invalidateSection(Section $section, string $operation = 'update'): void
    {
        $sectionId = $section->getId();
        
        // Invalidate specific section cache items
        $this->delete(self::CATEGORY_SECTIONS, "section_{$sectionId}");
        $this->delete(self::CATEGORY_SECTIONS, "section_children_{$sectionId}");
        $this->delete(self::CATEGORY_SECTIONS, "section_fields_{$sectionId}");
        
        // Invalidate sections category completely
        $this->invalidateCategory(self::CATEGORY_SECTIONS);
        
        // Sections affect pages too
        $this->invalidateCategory(self::CATEGORY_PAGES);
        
        // Invalidate frontend user caches (sections affect all users)
        $this->invalidateAllUserFrontendCaches();
        
        $this->log('info', 'Section cache invalidated', ['section_id' => $sectionId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when sections are reordered
     */
    public function invalidateSectionOrder(Page $page): void
    {
        $pageId = $page->getId();
        
        // Invalidate page sections cache
        $this->delete(self::CATEGORY_PAGES, "page_sections_{$pageId}");
        $this->delete(self::CATEGORY_PAGES, "page_{$pageId}");
        
        // Invalidate sections category
        $this->invalidateCategory(self::CATEGORY_SECTIONS);
        
        // Invalidate frontend user caches
        $this->invalidateAllUserFrontendCaches();
        
        $this->log('info', 'Section order cache invalidated', ['page_id' => $pageId]);
    }

    /**
     * Invalidate caches when a language is modified
     */
    public function invalidateLanguage(Language $language, string $operation = 'update'): void
    {
        $languageId = $language->getId();
        
        // Invalidate specific language cache
        $this->delete(self::CATEGORY_LANGUAGES, "language_{$languageId}");
        
        // Invalidate languages category completely
        $this->invalidateCategory(self::CATEGORY_LANGUAGES);
        
        // Invalidate frontend user caches (language changes affect all users)
        $this->invalidateAllUserFrontendCaches();
        
        $this->log('info', 'Language cache invalidated', ['language_id' => $languageId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a group is modified
     */
    public function invalidateGroup(Group $group, string $operation = 'update'): void
    {
        $groupId = $group->getId();
        
        // Invalidate specific group cache
        $this->delete(self::CATEGORY_GROUPS, "group_{$groupId}");
        $this->delete(self::CATEGORY_GROUPS, "group_users_{$groupId}");
        
        // Invalidate groups category completely
        $this->invalidateCategory(self::CATEGORY_GROUPS);
        
        // Invalidate permission caches for all users in this group
        $this->invalidateCategory(self::CATEGORY_PERMISSIONS);
        
        $this->log('info', 'Group cache invalidated', ['group_id' => $groupId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a role is modified
     */
    public function invalidateRole(Role $role, string $operation = 'update'): void
    {
        $roleId = $role->getId();
        
        // Invalidate specific role cache
        $this->delete(self::CATEGORY_ROLES, "role_{$roleId}");
        $this->delete(self::CATEGORY_ROLES, "role_permissions_{$roleId}");
        
        // Invalidate roles category completely
        $this->invalidateCategory(self::CATEGORY_ROLES);
        
        // Invalidate all permission caches (role changes affect permissions)
        $this->invalidateCategory(self::CATEGORY_PERMISSIONS);
        
        $this->log('info', 'Role cache invalidated', ['role_id' => $roleId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when an asset is modified
     */
    public function invalidateAsset(Asset $asset, string $operation = 'update'): void
    {
        $assetId = $asset->getId();
        
        // Invalidate specific asset cache
        $this->delete(self::CATEGORY_ASSETS, "asset_{$assetId}");
        
        // Invalidate assets category completely
        $this->invalidateCategory(self::CATEGORY_ASSETS);
        
        $this->log('info', 'Asset cache invalidated', ['asset_id' => $assetId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when a scheduled job is modified
     */
    public function invalidateScheduledJob(ScheduledJob $job, string $operation = 'update'): void
    {
        $jobId = $job->getId();
        
        // Invalidate specific job cache
        $this->delete(self::CATEGORY_SCHEDULED_JOBS, "job_{$jobId}");
        $this->delete(self::CATEGORY_SCHEDULED_JOBS, "job_transactions_{$jobId}");
        
        // Invalidate scheduled jobs category completely
        $this->invalidateCategory(self::CATEGORY_SCHEDULED_JOBS);
        
        $this->log('info', 'Scheduled job cache invalidated', ['job_id' => $jobId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when an action is modified
     */
    public function invalidateAction(Action $action, string $operation = 'update'): void
    {
        $actionId = $action->getId();
        
        // Invalidate specific action cache
        $this->delete(self::CATEGORY_ACTIONS, "action_{$actionId}");
        
        // Invalidate actions category completely
        $this->invalidateCategory(self::CATEGORY_ACTIONS);
        
        $this->log('info', 'Action cache invalidated', ['action_id' => $actionId, 'operation' => $operation]);
    }

    /**
     * Invalidate caches when permissions are modified
     */
    public function invalidatePermissions(?int $userId = null): void
    {
        if ($userId) {
            // Invalidate specific user permissions
            $this->delete(self::CATEGORY_PERMISSIONS, "user_acl_{$userId}");
            $this->delete(self::CATEGORY_PERMISSIONS, "user_permissions_{$userId}");
            
            // Invalidate frontend caches for this user
            $this->invalidateUserCategory($userId);
            
            $this->log('info', 'User permissions cache invalidated', ['user_id' => $userId]);
        } else {
            // Invalidate all permission caches
            $this->invalidateCategory(self::CATEGORY_PERMISSIONS);
            
            // Invalidate all frontend user caches
            $this->invalidateAllUserFrontendCaches();
            
            $this->log('info', 'All permissions cache invalidated');
        }
    }

    /**
     * Invalidate lookup caches
     */
    public function invalidateLookups(string $typeCode = null): void
    {
        if ($typeCode) {
            $this->delete(self::CATEGORY_LOOKUPS, "lookups_{$typeCode}");
            $this->log('info', 'Lookup cache invalidated', ['type_code' => $typeCode]);
        } else {
            $this->invalidateCategory(self::CATEGORY_LOOKUPS);
            $this->log('info', 'All lookup caches invalidated');
        }
    }

    /**
     * Invalidate CMS preferences cache
     */
    public function invalidateCmsPreferences(): void
    {
        $this->invalidateCategory(self::CATEGORY_CMS_PREFERENCES);
        $this->log('info', 'CMS preferences cache invalidated');
    }

    /**
     * Invalidate all caches for a specific user
     */
    public function invalidateAllUserCaches(int $userId): void
    {
        // Invalidate user-specific caches
        $this->invalidateUser($userId, 'update');
        
        // Invalidate all frontend caches for this user
        $this->invalidateUserCategory($userId);
        
        // Invalidate user permissions
        $this->invalidatePermissions($userId);
        
        $this->log('info', 'All user caches invalidated', ['user_id' => $userId]);
    }

    /**
     * Invalidate all frontend user caches (affects all users)
     */
    private function invalidateAllUserFrontendCaches(): void
    {
        $this->invalidateCategory(self::CATEGORY_FRONTEND_USER);
        $this->log('info', 'All frontend user caches invalidated');
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey(string $category, string $key, ?int $userId = null): string
    {
        $parts = [$category, $key];
        
        if ($userId !== null) {
            $parts[] = "user_{$userId}";
        }
        
        return implode('-', $parts);
    }

    /**
     * Invalidate cache by pattern (simplified version)
     */
    private function invalidateByPattern(string $pattern): bool
    {
        try {
            $pools = [
                $this->globalCache,
                $this->userFrontendCache,
                $this->adminCache,
                $this->lookupsCache,
                $this->permissionsCache
            ];
            
            $success = true;
            foreach ($pools as $pool) {
                if (method_exists($pool, 'clear')) {
                    $success = $success && $pool->clear();
                }
            }
            
            if ($success) {
                $this->recordInvalidation('pattern', 'pattern');
                $this->log('info', 'Cache invalidated by pattern', ['pattern' => $pattern]);
            }
            
            return $success;
        } catch (\Exception $e) {
            $this->log('error', 'Failed to invalidate cache by pattern', ['pattern' => $pattern, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // Simplified stats recording (delegates to CacheStatsService if available)
    private function recordHit(string $category): void
    {
        if ($this->statsService) {
            $this->statsService->recordHit($category);
        }
    }

    private function recordMiss(string $category): void
    {
        if ($this->statsService) {
            $this->statsService->recordMiss($category);
        }
    }

    private function recordSet(string $category): void
    {
        if ($this->statsService) {
            $this->statsService->recordSet($category);
        }
    }

    private function recordInvalidation(string $category, string $type = 'item'): void
    {
        if ($this->statsService) {
            $this->statsService->recordInvalidation($category, $type);
        }
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, "[Cache] {$message}", $context);
        }
    }
}