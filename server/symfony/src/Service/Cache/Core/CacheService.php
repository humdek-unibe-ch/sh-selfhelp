<?php

namespace App\Service\Cache\Core;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Psr\Log\LoggerInterface;

/**
 * ReworkedCacheService - Advanced tag-based cache service with generation-based invalidation
 *
 * Features:
 * - Builder pattern for category, prefix, and user configuration
 * - Generation-based cache invalidation (O(1) category/user invalidation)
 * - Automatic statistics tracking per category
 * - Tag-based cache organization for fine-grained control
 * - User-scoped caching with global kill switches
 * - List and item cache types with different invalidation strategies
 *
 * Usage Patterns:
 * 1. Builder pattern: $cache->withCategory(CATEGORY_USERS)->withUser($userId)->getItem(...)
 * 2. Compute-or-get: getList/getItem with callbacks for automatic cache population
 * 3. Selective invalidation: invalidateItem, invalidateCategory, invalidateUser
 * 4. User-scoped operations: withUser() for cleaner user-specific cache operations
 * 5. Statistics: Built-in hit/miss/set/invalidate tracking per category
 *
 * New User-Scoped Examples:
 * - $cache->withCategory(CATEGORY_USERS)->withUser($userId)->getItem('profile', $callback)
 * - $cache->withCategory(CATEGORY_ACTIONS)->withUser($userId)->invalidateCurrentUser()
 * - $cache->withUser($userId)->invalidateUserGlobally() // All categories for user
 *
 * @author SelfHelp Development Team
 * @version 8.0.0
 */
class CacheService
{
    // Cache category constants - Define logical groupings for cache invalidation
    /** @var string Cache category for page entities and related data */
    public const CATEGORY_PAGES = 'pages';
    /** @var string Cache category for user entities and profiles */
    public const CATEGORY_USERS = 'users';
    /** @var string Cache category for section entities and hierarchies */
    public const CATEGORY_SECTIONS = 'sections';
    /** @var string Cache category for language entities and translations */
    public const CATEGORY_LANGUAGES = 'languages';
    /** @var string Cache category for group entities and memberships */
    public const CATEGORY_GROUPS = 'groups';
    /** @var string Cache category for role entities and permissions */
    public const CATEGORY_ROLES = 'roles';
    /** @var string Cache category for permission entities and ACLs */
    public const CATEGORY_PERMISSIONS = 'permissions';
    /** @var string Cache category for lookup data and constants */
    public const CATEGORY_LOOKUPS = 'lookups';
    /** @var string Cache category for asset entities and metadata */
    public const CATEGORY_ASSETS = 'assets';
    /** @var string Cache category for user-specific frontend data */
    public const CATEGORY_FRONTEND_USER = 'frontend_user';
    /** @var string Cache category for CMS configuration preferences */
    public const CATEGORY_CMS_PREFERENCES = 'cms_preferences';
    /** @var string Cache category for scheduled job entities */
    public const CATEGORY_SCHEDULED_JOBS = 'scheduled_jobs';
    /** @var string Cache category for action entities and configurations */
    public const CATEGORY_ACTIONS = 'actions';

    /** @var string Cache category for API routes */
    public const CATEGORY_API_ROUTES = 'api_routes';

    /** @var string Cache category for data tables */
    public const CATEGORY_DATA_TABLES = 'data_tables';

    /** @var string Cache category for API routes */
    public const CATEGORY_DEFAULT = 'default';

    public const ALL_CATEGORIES = [
        self::CATEGORY_PAGES,
        self::CATEGORY_USERS,
        self::CATEGORY_SECTIONS,
        self::CATEGORY_LANGUAGES,
        self::CATEGORY_GROUPS,
        self::CATEGORY_ROLES,
        self::CATEGORY_PERMISSIONS,
        self::CATEGORY_LOOKUPS,
        self::CATEGORY_ASSETS,
        self::CATEGORY_FRONTEND_USER,
        self::CATEGORY_CMS_PREFERENCES,
        self::CATEGORY_SCHEDULED_JOBS,
        self::CATEGORY_ACTIONS,
        self::CATEGORY_API_ROUTES,
        self::CATEGORY_DATA_TABLES,
        self::CATEGORY_DEFAULT,
    ];

    /** @var string Current cache category for this service instance */
    private string $category = self::CATEGORY_DEFAULT;

    /** @var string Cache key prefix for namespacing */
    private string $prefix = 'cms';

    /** @var int|null Current user ID for user-scoped operations */
    private ?int $userId = null;

    /**
     * @param TagAwareCacheInterface $cache Tag-aware cache interface for advanced cache operations
     * @param LoggerInterface|null $logger Optional logger for debugging and monitoring
     */
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /* =========================
       Builder-style config
       ========================= */

    /**
     * Create a new service instance with a specific cache category
     * 
     * Uses immutable builder pattern - returns a new instance without modifying current one.
     * This allows for clean, chainable cache operations scoped to specific categories.
     * 
     * @param string $category One of the CATEGORY_* constants (e.g., CATEGORY_USERS, CATEGORY_PAGES)
     * @return self New service instance configured for the specified category
     * 
     * @example $cache->withCategory(ReworkedCacheService::CATEGORY_USERS)->getItem('user_123', $callback)
     */
    public function withCategory(string $category): self
    {
        $cl = clone $this;
        $cl->category = $category;
        return $cl;
    }

    /**
     * Create a new service instance with a specific cache key prefix
     * 
     * Uses immutable builder pattern - returns a new instance without modifying current one.
     * Useful for creating separate cache namespaces for different application areas.
     * 
     * @param string $prefix Cache key prefix (default: 'cms')
     * @return self New service instance configured with the specified prefix
     * 
     * @example $cache->withPrefix('api')->withCategory(CATEGORY_USERS)->getItem(...)
     */
    public function withPrefix(string $prefix): self
    {
        $cl = clone $this;
        $cl->prefix = $prefix;
        return $cl;
    }

    /**
     * Create a new service instance with a specific user ID for user-scoped operations
     * 
     * Uses immutable builder pattern - returns a new instance without modifying current one.
     * This allows for clean, chainable cache operations scoped to a specific user.
     * When a user is set, all cache operations will automatically use this user ID.
     * 
     * @param int $userId The user ID to scope cache operations to
     * @return self New service instance configured for the specified user
     * 
     * @example $cache->withCategory(CATEGORY_USERS)->withUser($userId)->getItem('profile', $callback)
     */
    public function withUser(int $userId): self
    {
        $cl = clone $this;
        $cl->userId = $userId;
        return $cl;
    }

    /* =========================
       Public API (lists & items)
       ========================= */

    /**
     * Compute-or-get a LIST entry with automatic caching and statistics tracking
     * 
     * Lists are collections of data that can be invalidated together. Use this for
     * paginated results, filtered lists, or any data that should be invalidated as a group.
     * Automatically records cache hit/miss and set statistics.
     * 
     * @param string $key Unique identifier for this cache entry within the category
     * @param callable $compute Callback function to compute the value if not cached: fn() => mixed
     * @param int|null $userId Optional user ID for user-scoped caching (overrides withUser() if provided)
     * @param int|null $ttlSeconds Optional TTL override (uses category default if null)
     * @return mixed The cached or computed value
     * 
     * @example 
     * $actions = $cache->withCategory(CATEGORY_ACTIONS)->getList(
     *     'actions_page_1_size_20',
     *     fn() => $this->repository->findActionsWithPagination(1, 20)
     * );
     * 
     * @example With user scoping:
     * $userActions = $cache->withCategory(CATEGORY_ACTIONS)->withUser($userId)->getList(
     *     'user_actions',
     *     fn() => $this->repository->findActionsByUser($userId)
     * );
     */
    public function getList(string $key, callable $compute, ?int $userId = null, ?int $ttlSeconds = null): mixed
    {
        // Use provided userId parameter, or fall back to the instance's userId
        $effectiveUserId = $userId ?? $this->userId;
        $cacheKey = $this->getCacheKey('list', $key, $effectiveUserId);
        $miss = false;

        if (!$ttlSeconds) {
            $ttlSeconds = $this->getCategoryTTL($this->category);
        }

        $value = $this->cache->get($cacheKey, function (ItemInterface $item) use ($compute, $ttlSeconds, $effectiveUserId, $key, &$miss) {
            $miss = true;
            if ($ttlSeconds) {
                $item->expiresAfter($ttlSeconds);
            }
            $tags = array_merge($this->tagsFor($effectiveUserId), [$this->itemTag($key, $effectiveUserId), $this->listTag()]);
            $item->tag($tags);
            $this->recordSet($this->category);

            $val = $compute();
            return $val;
        });

        $miss ? $this->recordMiss($this->category) : $this->recordHit($this->category);
        return $value;
    }

    /**
     * Compute-or-get an ITEM entry with automatic caching and statistics tracking
     * 
     * Items are individual data entries that can be invalidated independently. Use this for
     * single entities, configuration values, or any data that changes independently.
     * Automatically records cache hit/miss and set statistics.
     * 
     * @param string $key Unique identifier for this cache entry within the category
     * @param callable $compute Callback function to compute the value if not cached: fn() => mixed
     * @param int|null $userId Optional user ID for user-scoped caching (overrides withUser() if provided)
     * @param int|null $ttlSeconds Optional TTL override (uses category default if null)
     * @return mixed The cached or computed value
     * 
     * @example 
     * $action = $cache->withCategory(CATEGORY_ACTIONS)->getItem(
     *     "action_{$actionId}",
     *     fn() => $this->formatAction($this->repository->find($actionId))
     * );
     * 
     * @example With user scoping:
     * $user = $cache->withCategory(CATEGORY_USERS)->withUser($userId)->getItem(
     *     'user_profile',
     *     fn() => $this->repository->findOneBy(['id' => $userId])
     * );
     */
    public function getItem(string $key, callable $compute, ?int $userId = null, ?int $ttlSeconds = null): mixed
    {
        // Use provided userId parameter, or fall back to the instance's userId
        $effectiveUserId = $userId ?? $this->userId;
        $cacheKey = $this->getCacheKey('item', $key, $effectiveUserId);
        $miss = false;

        if (!$ttlSeconds) {
            $ttlSeconds = $this->getCategoryTTL($this->category);
        }

        $value = $this->cache->get($cacheKey, function (ItemInterface $item) use ($compute, $ttlSeconds, $effectiveUserId, $key, &$miss) {
            $miss = true;
            if ($ttlSeconds) {
                $item->expiresAfter($ttlSeconds);
            }
            $tags = array_merge($this->tagsFor($effectiveUserId), [$this->itemTag($key, $effectiveUserId)]);
            $item->tag($tags);

            $val = $compute();
            $this->recordSet($this->category);
            return $val;
        });

        $miss ? $this->recordMiss($this->category) : $this->recordHit($this->category);
        return $value;
    }

    /**
     * Force-set an ITEM value directly (prefer getItem with callback when possible)
     * 
     * Directly stores a value in cache without the compute-or-get pattern.
     * Use this when you have a computed value that you want to cache immediately,
     * or when updating cache after a database operation.
     * 
     * @param string $key Unique identifier for this cache entry within the category
     * @param int|null $userId Optional user ID for user-scoped caching (overrides withUser() if provided)
     * @param mixed $value The value to store in cache
     * @param int|null $ttlSeconds Optional TTL override (uses category default if null)
     * 
     * @example $cache->withCategory(CATEGORY_ACTIONS)->setItem("action_{$id}", null, $formattedAction);
     * @example $cache->withCategory(CATEGORY_ACTIONS)->withUser($userId)->setItem("user_action", null, $formattedAction);
     */
    public function setItem(string $key, ?int $userId, mixed $value, ?int $ttlSeconds = null): void
    {
        // Use provided userId parameter, or fall back to the instance's userId
        $effectiveUserId = $userId ?? $this->userId;
        $cacheKey = $this->getCacheKey('item', $key, $effectiveUserId);
        $this->cache->delete($cacheKey); // ensure callback runs
        $this->cache->get($cacheKey, function (ItemInterface $item) use ($value, $ttlSeconds, $effectiveUserId, $key) {
            if ($ttlSeconds) {
                $item->expiresAfter($ttlSeconds);
            }
            $tags = array_merge($this->tagsFor($effectiveUserId), [$this->itemTag($key, $effectiveUserId)]);
            $item->tag($tags);
            $this->recordSet($this->category);
            return $value;
        });
    }

    /**
     * Invalidate a specific ITEM from cache using tag-based invalidation
     * 
     * Removes a single cached item without affecting other cache entries.
     * Uses the item's unique tag for precise invalidation.
     * 
     * @param string $key The cache key of the item to invalidate
     * @param int|null $userId Optional user ID if the item is user-scoped (overrides withUser() if provided)
     * 
     * @example $cache->withCategory(CATEGORY_ACTIONS)->invalidateItem("action_{$actionId}");
     * @example $cache->withCategory(CATEGORY_ACTIONS)->withUser($userId)->invalidateItem("user_action");
     */
    public function invalidateItem(string $key, ?int $userId = null): void
    {
        // Use provided userId parameter, or fall back to the instance's userId
        $effectiveUserId = $userId ?? $this->userId;
        $this->cache->invalidateTags([$this->itemTag($key, $effectiveUserId)]);
        $this->recordInvalidation($this->category);
    }

    /* =========================
       Invalidation (O(1) generation-based)
       ========================= */

    /**
     * Invalidate ALL cache entries in this category using generation-based invalidation
     * 
     * This is an O(1) operation that bumps the category generation counter, effectively
     * making all existing cache keys for this category obsolete without scanning or deleting.
     * New cache operations will use the new generation and won't find old entries.
     * 
     * @example $cache->withCategory(CATEGORY_ACTIONS)->invalidateCategory();
     */
    public function invalidateCategory(): void
    {
        $this->incr($this->catGenKey());
        $this->recordInvalidation($this->category);
    }

    /**
     * Invalidate all cache entries for a specific user within this category
     * 
     * Uses generation-based invalidation to make all user-scoped cache entries
     * in this category obsolete. This is an O(1) operation.
     * 
     * @param int $userId The user ID whose cache entries should be invalidated
     * 
     * @example $cache->withCategory(CATEGORY_USERS)->invalidateUser($userId);
     */
    public function invalidateUser(int $userId): void
    {
        $this->incr($this->userGenKey($userId));
        $this->recordInvalidation($this->category);
    }

    /**
     * Invalidate ALL cache entries for a user across ALL categories (global kill switch)
     * 
     * Uses the global user generation counter to invalidate user-scoped cache
     * entries in every category. This is the nuclear option for user cache invalidation.
     * 
     * @param int|null $userId The user ID whose cache entries should be globally invalidated (uses withUser() userId if null)
     * 
     * @example $cache->invalidateUserGlobally($userId); // Clears user cache everywhere
     * @example $cache->withUser($userId)->invalidateUserGlobally(); // Using builder pattern
     */
    public function invalidateUserGlobally(?int $userId = null): void
    {
        $effectiveUserId = $userId ?? $this->userId;
        if ($effectiveUserId === null) {
            throw new \LogicException('No user specified. Provide userId parameter or use withUser() first');
        }
        $this->incr($this->globalUserGenKey($effectiveUserId));
        // Not tied to a category; record under a synthetic bucket if you like.
    }

    /**
     * Invalidate all cache entries for the current user across ALL categories
     * 
     * Convenience method when using withUser() builder pattern.
     * This is the nuclear option - clears all user cache across every category.
     * 
     * @throws \LogicException If no user is set via withUser()
     * 
     * @example $cache->withUser($userId)->invalidateCurrentUserGlobally();
     */
    public function invalidateCurrentUserGlobally(): void
    {
        if ($this->userId === null) {
            throw new \LogicException('No user set. Use withUser() before calling invalidateCurrentUserGlobally()');
        }
        $this->invalidateUserGlobally($this->userId);
    }

    /**
     * Invalidate all LIST entries in this category using tag-based invalidation
     * 
     * Alternative to generation-based invalidation when you only want to invalidate
     * list-type cache entries and keep item-type entries intact.
     * 
     * @example $cache->withCategory(CATEGORY_ACTIONS)->invalidateAllListsInCategory();
     */
    public function invalidateAllListsInCategory(): void
    {
        $this->cache->invalidateTags([$this->listTag()]);
        $this->recordInvalidation($this->category);
    }

    /**
     * Invalidate a specific ITEM and all LIST entries in this category
     * 
     * Useful when updating a single item that might affect multiple list results.
     * For example, updating an action should invalidate both the specific action cache
     * and all paginated action lists.
     * 
     * @param string $key The cache key of the item to invalidate
     * @param int|null $userId Optional user ID if the item is user-scoped (overrides withUser() if provided)
     * 
     * @example $cache->withCategory(CATEGORY_ACTIONS)->invalidateItemAndLists("action_{$actionId}");
     * @example $cache->withCategory(CATEGORY_ACTIONS)->withUser($userId)->invalidateItemAndLists("user_action");
     */
    public function invalidateItemAndLists(string $key, ?int $userId = null): void
    {
        // Use provided userId parameter, or fall back to the instance's userId
        $effectiveUserId = $userId ?? $this->userId;
        $this->invalidateItem($key, $effectiveUserId);
        $this->invalidateAllListsInCategory(); // all lists in this category
    }

    /**
     * Invalidate all cache entries for the current user in the current category
     * 
     * Convenience method when using withUser() builder pattern.
     * Invalidates all cache entries scoped to the user set via withUser().
     * 
     * @throws \LogicException If no user is set via withUser()
     * 
     * @example $cache->withCategory(CATEGORY_USERS)->withUser($userId)->invalidateCurrentUser();
     */
    public function invalidateCurrentUser(): void
    {
        if ($this->userId === null) {
            throw new \LogicException('No user set. Use withUser() before calling invalidateCurrentUser()');
        }
        $this->invalidateUser($this->userId);
    }

    /**
     * Invalidate all cache entries for a specific user in the current category
     * 
     * Enhanced version that works with both the builder pattern and direct parameter passing.
     * 
     * @param int|null $userId User ID to invalidate (uses withUser() userId if null)
     * 
     * @example $cache->withCategory(CATEGORY_USERS)->invalidateUserInCategory($userId);
     * @example $cache->withCategory(CATEGORY_USERS)->withUser($userId)->invalidateUserInCategory();
     */
    public function invalidateUserInCategory(?int $userId = null): void
    {
        $effectiveUserId = $userId ?? $this->userId;
        if ($effectiveUserId === null) {
            throw new \LogicException('No user specified. Provide userId parameter or use withUser() first');
        }
        $this->invalidateUser($effectiveUserId);
    }

    /* =========================
       Entity-Scoped Cache Operations (NEW)
       ========================= */

    /**
     * ENTITY SCOPE INVALIDATION SYSTEM
     * 
     * This system allows cache invalidation based on specific entity primary keys.
     * When an entity changes (e.g., a page, section, user, group), you can invalidate
     * ALL cache entries that depend on that entity across all categories.
     * 
     * Key Benefits:
     * 1. O(1) Invalidation: Uses generation counters, no scanning of cache entries
     * 2. Cross-Category: One entity change can invalidate cache across multiple categories
     * 3. Precise Targeting: Only invalidates cache that actually depends on the changed entity
     * 4. Automatic Integration: Works seamlessly with existing cache operations
     * 
     * How It Works:
     * - Each entity type (page, section, user, etc.) has its own generation counter
     * - Cache keys include the generation number of all entities they depend on
     * - When an entity changes, we increment its generation counter
     * - All cache entries with the old generation become invalid automatically
     * 
     * Usage Examples:
     * 
     * 1. Caching with entity dependencies:
     *    $cache->withCategory(CATEGORY_PAGES)
     *          ->withEntityScope('page_id', $pageId)
     *          ->withEntityScope('section_id', $sectionId)
     *          ->getItem('page_content', $callback);
     * 
     * 2. Invalidating when entity changes:
     *    $cache->invalidateEntityScope('page_id', $pageId); // Invalidates ALL cache depending on this page
     *    $cache->invalidateEntityScope('section_id', $sectionId); // Invalidates ALL cache depending on this section
     * 
     * 3. Chaining multiple entity scopes:
     *    $cache->withCategory(CATEGORY_SECTIONS)
     *          ->withEntityScope('page_id', $pageId)     // This section depends on a page
     *          ->withEntityScope('user_id', $userId)     // And on a user's permissions
     *          ->withEntityScope('group_id', $groupId)   // And on a group's settings
     *          ->getList('filtered_sections', $callback);
     * 
     * Supported Entity Types (can be extended):
     * - page_id: For page-dependent cache entries
     * - section_id: For section-dependent cache entries  
     * - user_id: For user-dependent cache entries (different from user-scoped cache)
     * - group_id: For group-dependent cache entries
     * - role_id: For role-dependent cache entries
     * - language_id: For language-dependent cache entries
     * - asset_id: For asset-dependent cache entries
     * - action_id: For action-dependent cache entries
     * - scheduled_job_id: For scheduled job-dependent cache entries
     * - field_id: For field-dependent cache entries
     * - lookup_id: For lookup-dependent cache entries
     * 
     * Real-World Examples:
     * 
     * 1. Page Content Cache:
     *    When caching rendered page content, it might depend on:
     *    - The page itself (page_id)
     *    - Sections within the page (section_id)
     *    - User permissions (user_id)
     *    - Language settings (language_id)
     * 
     * 2. Navigation Menu Cache:
     *    When caching navigation menus, it might depend on:
     *    - User permissions (user_id)
     *    - Group memberships (group_id)
     *    - Language settings (language_id)
     *    - Page visibility (multiple page_ids)
     * 
     * 3. Form Field Cache:
     *    When caching form field configurations, it might depend on:
     *    - The specific field (field_id)
     *    - User permissions (user_id)
     *    - Group settings (group_id)
     *    - Language translations (language_id)
     * 
     * Performance Notes:
     * - Entity scope generation counters are cached permanently (no TTL)
     * - Cache key generation is very fast (just string concatenation)
     * - Invalidation is O(1) regardless of how many cache entries are affected
     * - Works alongside existing user-scoped and category-based invalidation
     */

    /** @var array<string, int> Current entity scopes for this service instance */
    private array $entityScopes = [];

    /**
     * SUPPORTED ENTITY SCOPE TYPES
     * 
     * These constants define the entity types that can be used for scoped caching.
     * Each represents a primary key from a database entity that cache entries can depend on.
     * 
     * When you add new entity types to your system, add corresponding constants here
     * to enable scoped caching for those entities.
     */
    public const ENTITY_SCOPE_PAGE = 'page_id';
    public const ENTITY_SCOPE_SECTION = 'section_id';
    public const ENTITY_SCOPE_USER = 'user_id';
    public const ENTITY_SCOPE_GROUP = 'group_id';
    public const ENTITY_SCOPE_ROLE = 'role_id';
    public const ENTITY_SCOPE_LANGUAGE = 'language_id';
    public const ENTITY_SCOPE_ASSET = 'asset_id';
    public const ENTITY_SCOPE_ACTION = 'action_id';
    public const ENTITY_SCOPE_SCHEDULED_JOB = 'scheduled_job_id';
    public const ENTITY_SCOPE_FIELD = 'field_id';
    public const ENTITY_SCOPE_LOOKUP = 'lookup_id';
    public const ENTITY_SCOPE_PERMISSION = 'permission_id';
    public const ENTITY_SCOPE_CMS_PREFERENCE = 'cms_preference_id';
    public const ENTITY_SCOPE_DATA_TABLE = 'data_table_id';

    /**
     * Array of all supported entity scope types for validation
     */
    public const ALL_ENTITY_SCOPES = [
        self::ENTITY_SCOPE_PAGE,
        self::ENTITY_SCOPE_SECTION,
        self::ENTITY_SCOPE_USER,
        self::ENTITY_SCOPE_GROUP,
        self::ENTITY_SCOPE_ROLE,
        self::ENTITY_SCOPE_LANGUAGE,
        self::ENTITY_SCOPE_ASSET,
        self::ENTITY_SCOPE_ACTION,
        self::ENTITY_SCOPE_SCHEDULED_JOB,
        self::ENTITY_SCOPE_FIELD,
        self::ENTITY_SCOPE_LOOKUP,
        self::ENTITY_SCOPE_PERMISSION,
        self::ENTITY_SCOPE_CMS_PREFERENCE,
        self::ENTITY_SCOPE_DATA_TABLE,
    ];

    /**
     * Create a new service instance with an additional entity scope dependency
     * 
     * BUILDER PATTERN - ENTITY SCOPING
     * 
     * This method allows you to specify that cache entries depend on specific entities.
     * When those entities change, all cache entries that declared a dependency on them
     * will be automatically invalidated through generation-based cache keys.
     * 
     * Uses immutable builder pattern - returns a new instance without modifying current one.
     * This allows for clean, chainable cache operations with multiple entity dependencies.
     * 
     * HOW ENTITY SCOPING WORKS:
     * 
     * 1. Declaration: You declare that your cache entry depends on specific entities
     * 2. Key Generation: The cache key includes generation numbers for all dependent entities
     * 3. Invalidation: When an entity changes, increment its generation counter
     * 4. Automatic Cleanup: All cache entries with old generation numbers become invalid
     * 
     * @param string $entityType One of the ENTITY_SCOPE_* constants (e.g., ENTITY_SCOPE_PAGE, ENTITY_SCOPE_SECTION)
     * @param int $entityId The primary key value of the entity this cache depends on
     * @return self New service instance configured with the additional entity scope
     * 
     * @throws \InvalidArgumentException If entityType is not supported
     * 
     * @example Single entity dependency:
     * $cache->withCategory(CATEGORY_PAGES)
     *       ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageId)
     *       ->getItem('page_content', $callback);
     * 
     * @example Multiple entity dependencies (chainable):
     * $cache->withCategory(CATEGORY_SECTIONS)
     *       ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageId)
     *       ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
     *       ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_LANGUAGE, $languageId)
     *       ->getList('localized_page_sections', $callback);
     * 
     * @example Real-world navigation cache:
     * $navigationCache = $cache->withCategory(CATEGORY_FRONTEND_USER)
     *                          ->withEntityScope(ENTITY_SCOPE_USER, $userId)      // User permissions
     *                          ->withEntityScope(ENTITY_SCOPE_GROUP, $groupId)    // Group access
     *                          ->withEntityScope(ENTITY_SCOPE_LANGUAGE, $langId)  // Language
     *                          ->getItem('user_navigation_menu', function() {
     *                              return $this->buildNavigationForUser();
     *                          });
     */
    public function withEntityScope(string $entityType, int $entityId): self
    {
        // Validate entity type is supported
        if (!in_array($entityType, self::ALL_ENTITY_SCOPES, true)) {
            throw new \InvalidArgumentException(
                "Unsupported entity scope type: {$entityType}. " .
                "Supported types: " . implode(', ', self::ALL_ENTITY_SCOPES)
            );
        }

        // Validate entity ID is positive
        if ($entityId <= 0) {
            throw new \InvalidArgumentException("Entity ID must be positive, got: {$entityId}");
        }

        // Clone and add the entity scope
        $clone = clone $this;
        $clone->entityScopes[$entityType] = $entityId;
        return $clone;
    }

    /**
     * GLOBAL ENTITY INVALIDATION - The Power Method
     * 
     * This is the main invalidation method for entity-scoped caching.
     * When an entity changes (create, update, delete), call this method to invalidate
     * ALL cache entries across ALL categories that depend on this specific entity.
     * 
     * PERFORMANCE: This is an O(1) operation regardless of how many cache entries
     * are affected. It simply increments a generation counter.
     * 
     * SCOPE: This invalidation works across:
     * - All cache categories (CATEGORY_PAGES, CATEGORY_USERS, etc.)
     * - All cache types (lists and items)
     * - All user-scoped and non-user-scoped entries
     * - Any cache entry that declared a dependency on this entity
     * 
     * @param string $entityType One of the ENTITY_SCOPE_* constants
     * @param int $entityId The primary key value of the entity that changed
     * 
     * @throws \InvalidArgumentException If entityType is not supported
     * 
     * @example When a page is updated:
     * $cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageId);
     * // This invalidates ALL cache entries that depend on this page:
     * // - Page content cache
     * // - Navigation menus that include this page
     * // - Breadcrumb caches
     * // - SEO metadata cache
     * // - Any other cache that declared dependency on this page
     * 
     * @example When a user's permissions change:
     * $cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
     * // This invalidates ALL cache entries that depend on this user:
     * // - User-specific page content
     * // - Permission-based navigation
     * // - User dashboard data
     * // - Access-controlled content cache
     * 
     * @example When a section is modified:
     * $cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_SECTION, $sectionId);
     * // This invalidates ALL cache entries that depend on this section:
     * // - Pages containing this section
     * // - Section hierarchy caches
     * // - Form field configurations using this section
     * // - Any computed data based on this section
     * 
     * @example Integration with service methods:
     * public function updatePage(int $pageId, array $data): Page
     * {
     *     $page = $this->pageRepository->find($pageId);
     *     // ... update page logic ...
     *     $this->entityManager->flush();
     *     
     *     // Invalidate ALL cache that depends on this page
     *     $this->cache->invalidateEntityScope(
     *         ReworkedCacheService::ENTITY_SCOPE_PAGE, 
     *         $pageId
     *     );
     *     
     *     return $page;
     * }
     */
    public function invalidateEntityScope(string $entityType, int $entityId): void
    {
        // Validate entity type is supported
        if (!in_array($entityType, self::ALL_ENTITY_SCOPES, true)) {
            throw new \InvalidArgumentException(
                "Unsupported entity scope type: {$entityType}. " .
                "Supported types: " . implode(', ', self::ALL_ENTITY_SCOPES)
            );
        }

        // Validate entity ID is positive
        if ($entityId <= 0) {
            throw new \InvalidArgumentException("Entity ID must be positive, got: {$entityId}");
        }

        // Increment the generation counter for this entity
        // This makes all cache entries depending on this entity invalid
        $this->incr($this->entityScopeGenKey($entityType, $entityId));

        // Log the invalidation for debugging and monitoring
        if ($this->logger) {
            $this->logger->info('Entity scope invalidated', [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'generation_key' => $this->entityScopeGenKey($entityType, $entityId)
            ]);
        }
    }

    /**
     * BULK ENTITY INVALIDATION - For Mass Updates
     * 
     * When you need to invalidate multiple entities of the same type at once,
     * this method provides an efficient way to do so. Useful for bulk operations
     * like batch updates, imports, or cascading changes.
     * 
     * @param string $entityType One of the ENTITY_SCOPE_* constants
     * @param array $entityIds Array of entity IDs to invalidate
     * 
     * @throws \InvalidArgumentException If entityType is not supported or entityIds is empty
     * 
     * @example Bulk page invalidation after import:
     * $cache->invalidateEntityScopes(
     *     ReworkedCacheService::ENTITY_SCOPE_PAGE, 
     *     [101, 102, 103, 104, 105]
     * );
     * 
     * @example Invalidate all sections in a page:
     * $sectionIds = $this->sectionRepository->findIdsByPageId($pageId);
     * $cache->invalidateEntityScopes(
     *     ReworkedCacheService::ENTITY_SCOPE_SECTION,
     *     $sectionIds
     * );
     */
    public function invalidateEntityScopes(string $entityType, array $entityIds): void
    {
        if (empty($entityIds)) {
            throw new \InvalidArgumentException('Entity IDs array cannot be empty');
        }

        foreach ($entityIds as $entityId) {
            $this->invalidateEntityScope($entityType, $entityId);
        }

        if ($this->logger) {
            $this->logger->info('Bulk entity scope invalidation completed', [
                'entity_type' => $entityType,
                'count' => count($entityIds),
                'entity_ids' => $entityIds
            ]);
        }
    }

    /**
     * GET CURRENT ENTITY SCOPES - For Debugging and Monitoring
     * 
     * Returns the current entity scopes configured for this service instance.
     * Useful for debugging cache key generation and understanding dependencies.
     * 
     * @return array<string, int> Array of entity_type => entity_id mappings
     * 
     * @example Debugging cache dependencies:
     * $scopes = $cache->withCategory(CATEGORY_PAGES)
     *                 ->withEntityScope(ENTITY_SCOPE_PAGE, 123)
     *                 ->withEntityScope(ENTITY_SCOPE_USER, 456)
     *                 ->getCurrentEntityScopes();
     * // Returns: ['page_id' => 123, 'user_id' => 456]
     */
    public function getCurrentEntityScopes(): array
    {
        return $this->entityScopes;
    }

    /**
     * CHECK IF ENTITY SCOPE IS SET - For Conditional Logic
     * 
     * Check if a specific entity scope is currently configured.
     * Useful for conditional cache operations or validation.
     * 
     * @param string $entityType One of the ENTITY_SCOPE_* constants
     * @return bool True if the entity scope is set
     * 
     * @example Conditional caching:
     * if ($cache->hasEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER)) {
     *     // Use user-specific cache key
     * } else {
     *     // Use global cache key
     * }
     */
    public function hasEntityScope(string $entityType): bool
    {
        return isset($this->entityScopes[$entityType]);
    }

    /**
     * GET SPECIFIC ENTITY SCOPE ID - For Dynamic Operations
     * 
     * Get the entity ID for a specific entity type if it's configured.
     * Returns null if the entity scope is not set.
     * 
     * @param string $entityType One of the ENTITY_SCOPE_* constants
     * @return int|null The entity ID, or null if not set
     * 
     * @example Dynamic cache key generation:
     * $userId = $cache->getEntityScopeId(ReworkedCacheService::ENTITY_SCOPE_USER);
     * if ($userId) {
     *     $cacheKey = "user_specific_{$userId}";
     * } else {
     *     $cacheKey = "global";
     * }
     */
    public function getEntityScopeId(string $entityType): ?int
    {
        return $this->entityScopes[$entityType] ?? null;
    }


    /* =========================
       TTL Configuration
       ========================= */

    /**
     * Get the default TTL (Time To Live) for a specific cache category
     * 
     * Different categories have different TTL values based on how frequently
     * the data changes and how expensive it is to recompute.
     * 
     * @param string $category One of the CATEGORY_* constants
     * @return int TTL in seconds
     */
    public function getCategoryTTL(string $category): int
    {
        // Tweak per your needs
        return match ($category) {
            self::CATEGORY_LOOKUPS => 7200, // 2h
            self::CATEGORY_PERMISSIONS => 1800, // 30m
            self::CATEGORY_FRONTEND_USER => 1800, // 30m
            self::CATEGORY_LANGUAGES => 3600, // 1h
            self::CATEGORY_ROLES => 1800, // 30m
            self::CATEGORY_GROUPS => 1800, // 30m
            self::CATEGORY_DATA_TABLES => 1800, // 30m
            default => 3600, // 1h
        };
    }





    /* =========================
       Internal Implementation (generation-based cache keys & statistics)
       ========================= */

    /**
     * Generate a cache key with generation-based invalidation support
     * 
     * ENHANCED WITH ENTITY SCOPE SUPPORT
     * 
     * Creates cache keys that include generation counters for O(1) invalidation.
     * The key format includes category generation, user generation, global user generation,
     * and entity scope generations to support selective invalidation without scanning existing cache entries.
     * 
     * Entity Scope Integration:
     * - If entity scopes are configured via withEntityScope(), their generation numbers are included
     * - Each entity scope adds its generation counter to the cache key
     * - When any entity changes, its generation counter increments, invalidating all dependent cache
     * - Entity scopes are sorted by type for consistent key generation
     * 
     * Cache Key Format:
     * prefix-category-g{catGen}[-u{userId}-g{userGen}][-e{entityType}_{entityId}_g{entityGen}...][-type-normalizedKey]
     * 
     * Examples:
     * - Basic: cms-pages-g1-item-page_content
     * - With user: cms-pages-g1-u123-g2-item-user_page_content  
     * - With entity scopes: cms-pages-g1-epage_id_456_g3-esection_id_789_g1-item-page_content
     * - Complex: cms-pages-g1-u123-g2-epage_id_456_g3-euser_id_123_g5-item-complex_content
     * 
     * @param string $type Cache entry type ('list' or 'item')
     * @param string $plainKey The base key identifier
     * @param int|null $userId Optional user ID for user-scoped caching
     * @return string Complete cache key with generation counters
     */
    private function getCacheKey(string $type, string $plainKey, ?int $userId = null): string
    {
        // Start with basic category generation
        $catGen = $this->getInt($this->catGenKey());
        $parts = [$this->prefix, $this->category, 'g' . $catGen];

        // Add user-scoped generation if applicable
        if ($userId !== null) {
            $userGen = $this->getInt($this->userGenKey($userId));
            $global = $this->getInt($this->globalUserGenKey($userId)); // global kill switch
            $parts[] = 'u' . $userId;
            $parts[] = 'g' . max($userGen, $global);
        }

        // Add entity scope generations if configured
        // Sort by entity type for consistent key generation regardless of withEntityScope() call order
        if (!empty($this->entityScopes)) {
            $sortedScopes = $this->entityScopes;
            ksort($sortedScopes); // Sort by entity type for consistency
            
            foreach ($sortedScopes as $entityType => $entityId) {
                $entityGen = $this->getInt($this->entityScopeGenKey($entityType, $entityId));
                $parts[] = 'e' . $entityType . '_' . $entityId . '_g' . $entityGen;
            }
        }

        // Add cache type and normalized key
        $parts[] = $type;
        $parts[] = $this->normalizeKey($plainKey);

        return implode('-', $parts);
    }

    /**
     * Generate a tag for a list cache entry
     */
    private function listTag(): string
    {
        return "list-{$this->category}";
    }

    /**
     * Generate tags for a user cache entry
     */
    private function tagsFor(?int $userId): array
    {
        $tags = ["cat-{$this->category}"];
        if ($userId !== null) {
            $tags[] = "user-{$userId}";
            $tags[] = "cat-{$this->category}-user-{$userId}";
        }
        return $tags;
    }

    /**
     * Generate a tag for an item cache entry
     */
    private function itemTag(string $key, ?int $userId = null): string
    {
        $base = "item-{$this->category}-" . $this->normalizeKey($key);
        return $userId === null ? $base : $base . "-u{$userId}";
    }

    /**
     * Normalize a key to a maximum length of 60 characters
     */
    private function normalizeKey(string $k): string
    {
        return strlen($k) <= 60 ? $k : (substr($k, 20) ? substr($k, 0, 20) : $k) . '_' . md5($k);
    }

    /**
     * Generate a key for the category generation
     */
    private function catGenKey(): string
    {
        return "{$this->prefix}-{$this->category}-gen";
    }

    /**
     * Generate a key for the user generation
     */
    private function userGenKey(int $userId): string
    {
        return "{$this->prefix}-{$this->category}-user-{$userId}-gen";
    }

    /**
     * Generate a key for the global user generation
     */
    private function globalUserGenKey(int $userId): string
    {
        return "{$this->prefix}-user-{$userId}-gen";
    }

    /**
     * Generate a key for entity scope generation counter
     * 
     * ENTITY SCOPE GENERATION KEY FORMAT
     * 
     * Creates generation keys for specific entity instances that can be used
     * for O(1) cache invalidation. Each entity type + entity ID combination
     * gets its own generation counter.
     * 
     * Key Format: {prefix}-entity-{entityType}-{entityId}-gen
     * 
     * Examples:
     * - Page entity: cms-entity-page_id-123-gen
     * - Section entity: cms-entity-section_id-456-gen  
     * - User entity: cms-entity-user_id-789-gen
     * - Group entity: cms-entity-group_id-101-gen
     * 
     * When an entity changes:
     * 1. This generation key gets incremented
     * 2. All cache keys containing the old generation become invalid
     * 3. New cache operations use the new generation number
     * 4. Old cache entries are effectively invisible (O(1) invalidation)
     * 
     * @param string $entityType One of the ENTITY_SCOPE_* constants
     * @param int $entityId The primary key value of the entity
     * @return string The generation key for this specific entity instance
     */
    private function entityScopeGenKey(string $entityType, int $entityId): string
    {
        return "{$this->prefix}-entity-{$entityType}-{$entityId}-gen";
    }

    /**
     * Get an integer value from the cache
     */
    protected function getInt(string $key, int $default = 0): int
    {
        // CacheInterface::get callback runs on miss; we store the default.
        return (int) $this->cache->get($key, function (ItemInterface $item) use ($default) {
            // No expiry for namespace tokens & stats
            $item->expiresAfter(null); // never expire namespace tokens & stats
            return $default;
        });
    }

    /**
     * Increment a value in the cache
     * // DO NOT CHANGE THIS CODE
     */
    private function incr(string $key): void
    {
         // DO NOT CHANGE THIS CODE
         $item = $this->cache->getItem($key);  // ✅ fetch cache item

         if(str_contains($key, 'entity')){
            $r = '';
         }

         if ($item->isHit()) {
             $current = (int) $item->get();
         } else {
             $current = -1; // so first store becomes 0
         }
 
         $item->set($current + 1);
         $item->expiresAfter(null); // keep forever
         $this->cache->save($item);
         // DO NOT CHANGE THIS CODE
    }

    /**
     * Record cache set for statistics
     */
    private function recordSet(string $category): void
    {
        $this->incr($this->statKey($category, 'set'));
    }

    /**
     * Generate a key for the statistics
     */
    protected function statKey(string $category, string $metric): string
    {
        return "{$this->prefix}-stats-{$category}-{$metric}";
    }

    /**
     * Record cache hit for statistics
     */
    private function recordHit(string $category): void
    {
        $this->incr($this->statKey($category, 'hit'));
    }

    /**
     * Record cache miss for statistics
     */
    private function recordMiss(string $category): void
    {
        $this->incr($this->statKey($category, 'miss'));
    }

    /**
     * Record cache invalidation for statistics
     */
    private function recordInvalidation(string $category): void
    {
        $this->incr($this->statKey($category, 'invalidate'));
    }

    /* =========================
       ENTITY SCOPE USAGE EXAMPLES AND INTEGRATION GUIDE
       ========================= */

    /**
     * COMPREHENSIVE USAGE EXAMPLES FOR ENTITY SCOPE SYSTEM
     * 
     * This section provides real-world examples of how to use the entity scope system
     * in your Symfony services. Copy and adapt these patterns for your use cases.
     * 
     * ================================
     * EXAMPLE 1: PAGE SERVICE CACHING
     * ================================
     * 
     * When caching page content that depends on multiple entities:
     * 
     * ```php
     * class PageService {
     *     public function getPageContent(int $pageId, int $userId, int $languageId): array
     *     {
     *         return $this->cache
     *             ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageId)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_LANGUAGE, $languageId)
     *             ->getItem("page_content_{$pageId}", function() use ($pageId, $userId, $languageId) {
     *                 return $this->buildPageContent($pageId, $userId, $languageId);
     *             });
     *     }
     * 
     *     public function updatePage(int $pageId, array $data): void
     *     {
     *         // Update page in database
     *         $this->pageRepository->update($pageId, $data);
     *         
     *         // Invalidate ALL cache entries that depend on this page
     *         // This automatically invalidates cache across all categories and users
     *         $this->cache->invalidateEntityScope(
     *             ReworkedCacheService::ENTITY_SCOPE_PAGE, 
     *             $pageId
     *         );
     *     }
     * }
     * ```
     * 
     * ================================
     * EXAMPLE 2: SECTION SERVICE CACHING
     * ================================
     * 
     * When caching section data that depends on page and user permissions:
     * 
     * ```php
     * class SectionService {
     *     public function getSectionHierarchy(int $pageId, int $userId): array
     *     {
     *         return $this->cache
     *             ->withCategory(ReworkedCacheService::CATEGORY_SECTIONS)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageId)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
     *             ->getList("section_hierarchy_{$pageId}", function() use ($pageId, $userId) {
     *                 return $this->buildSectionHierarchy($pageId, $userId);
     *             });
     *     }
     * 
     *     public function updateSection(int $sectionId, array $data): void
     *     {
     *         // Update section in database
     *         $this->sectionRepository->update($sectionId, $data);
     *         
     *         // Get the page this section belongs to
     *         $pageId = $this->sectionRepository->getPageId($sectionId);
     *         
     *         // Invalidate section-specific cache
     *         $this->cache->invalidateEntityScope(
     *             ReworkedCacheService::ENTITY_SCOPE_SECTION, 
     *             $sectionId
     *         );
     *         
     *         // Also invalidate page cache since page content includes sections
     *         $this->cache->invalidateEntityScope(
     *             ReworkedCacheService::ENTITY_SCOPE_PAGE, 
     *             $pageId
     *         );
     *     }
     * }
     * ```
     * 
     * ================================
     * EXAMPLE 3: USER PERMISSION CACHING
     * ================================
     * 
     * When caching user-specific data that depends on group and role:
     * 
     * ```php
     * class UserService {
     *     public function getUserPermissions(int $userId): array
     *     {
     *         $user = $this->userRepository->find($userId);
     *         
     *         return $this->cache
     *             ->withCategory(ReworkedCacheService::CATEGORY_PERMISSIONS)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_GROUP, $user->getGroup()->getId())
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_ROLE, $user->getRole()->getId())
     *             ->getItem("user_permissions_{$userId}", function() use ($userId) {
     *                 return $this->calculateUserPermissions($userId);
     *             });
     *     }
     * 
     *     public function updateUserRole(int $userId, int $newRoleId): void
     *     {
     *         // Update user role in database
     *         $this->userRepository->updateRole($userId, $newRoleId);
     *         
     *         // Invalidate all cache that depends on this user
     *         $this->cache->invalidateEntityScope(
     *             ReworkedCacheService::ENTITY_SCOPE_USER, 
     *             $userId
     *         );
     *         
     *         // Also invalidate role-based cache
     *         $this->cache->invalidateEntityScope(
     *             ReworkedCacheService::ENTITY_SCOPE_ROLE, 
     *             $newRoleId
     *         );
     *     }
     * }
     * ```
     * 
     * ================================
     * EXAMPLE 4: NAVIGATION MENU CACHING
     * ================================
     * 
     * Complex navigation that depends on multiple entities:
     * 
     * ```php
     * class NavigationService {
     *     public function getNavigationMenu(int $userId, int $languageId): array
     *     {
     *         $user = $this->userRepository->find($userId);
     *         
     *         return $this->cache
     *             ->withCategory(ReworkedCacheService::CATEGORY_FRONTEND_USER)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_GROUP, $user->getGroup()->getId())
     *             ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_LANGUAGE, $languageId)
     *             ->getItem("navigation_menu_{$userId}_{$languageId}", function() use ($userId, $languageId) {
     *                 return $this->buildNavigationMenu($userId, $languageId);
     *             });
     *     }
     * 
     *     public function invalidateNavigationForLanguage(int $languageId): void
     *     {
     *         // When language translations change, invalidate all navigation using that language
     *         $this->cache->invalidateEntityScope(
     *             ReworkedCacheService::ENTITY_SCOPE_LANGUAGE, 
     *             $languageId
     *         );
     *     }
     * }
     * ```
     * 
     * ================================
     * EXAMPLE 5: BULK OPERATIONS
     * ================================
     * 
     * When performing bulk operations that affect multiple entities:
     * 
     * ```php
     * class BulkOperationService {
     *     public function importPages(array $pagesData): void
     *     {
     *         $affectedPageIds = [];
     *         
     *         foreach ($pagesData as $pageData) {
     *             $pageId = $this->pageRepository->createOrUpdate($pageData);
     *             $affectedPageIds[] = $pageId;
     *         }
     *         
     *         // Bulk invalidation for all affected pages
     *         $this->cache->invalidateEntityScopes(
     *             ReworkedCacheService::ENTITY_SCOPE_PAGE,
     *             $affectedPageIds
     *         );
     *     }
     * 
     *     public function reorganizeSections(int $pageId, array $newSectionOrder): void
     *     {
     *         $affectedSectionIds = [];
     *         
     *         foreach ($newSectionOrder as $position => $sectionId) {
     *             $this->sectionRepository->updatePosition($sectionId, $position);
     *             $affectedSectionIds[] = $sectionId;
     *         }
     *         
     *         // Invalidate the page and all affected sections
     *         $this->cache->invalidateEntityScope(
     *             ReworkedCacheService::ENTITY_SCOPE_PAGE,
     *             $pageId
     *         );
     *         
     *         $this->cache->invalidateEntityScopes(
     *             ReworkedCacheService::ENTITY_SCOPE_SECTION,
     *             $affectedSectionIds
     *         );
     *     }
     * }
     * ```
     * 
     * ================================
     * INTEGRATION WITH DOCTRINE EVENTS
     * ================================
     * 
     * Automatic cache invalidation using Doctrine lifecycle events:
     * 
     * ```php
     * class CacheInvalidationListener {
     *     public function __construct(private ReworkedCacheService $cache) {}
     * 
     *     public function postUpdate(LifecycleEventArgs $args): void
     *     {
     *         $entity = $args->getObject();
     *         
     *         match (true) {
     *             $entity instanceof Page => $this->cache->invalidateEntityScope(
     *                 ReworkedCacheService::ENTITY_SCOPE_PAGE, 
     *                 $entity->getId()
     *             ),
     *             $entity instanceof Section => $this->cache->invalidateEntityScope(
     *                 ReworkedCacheService::ENTITY_SCOPE_SECTION, 
     *                 $entity->getId()
     *             ),
     *             $entity instanceof User => $this->cache->invalidateEntityScope(
     *                 ReworkedCacheService::ENTITY_SCOPE_USER, 
     *                 $entity->getId()
     *             ),
     *             default => null
     *         };
     *     }
     * }
     * ```
     * 
     * ================================
     * PERFORMANCE CONSIDERATIONS
     * ================================
     * 
     * 1. Entity Scope Ordering:
     *    - Entity scopes are automatically sorted for consistent keys
     *    - Order of withEntityScope() calls doesn't matter for performance
     * 
     * 2. Memory Usage:
     *    - Each entity scope adds minimal overhead to cache keys
     *    - Generation counters are stored permanently but are very small
     * 
     * 3. Cache Key Length:
     *    - Complex entity scopes create longer cache keys
     *    - Keys are automatically normalized if they exceed limits
     * 
     * 4. Invalidation Performance:
     *    - All invalidations are O(1) regardless of cache size
     *    - Bulk invalidations process each entity individually but efficiently
     * 
     * ================================
     * DEBUGGING AND MONITORING
     * ================================
     * 
     * Use these methods to debug entity scope configurations:
     * 
     * ```php
     * // Check current entity scopes
     * $scopes = $cache->withCategory(CATEGORY_PAGES)
     *                 ->withEntityScope(ENTITY_SCOPE_PAGE, 123)
     *                 ->getCurrentEntityScopes();
     * 
     * // Check if specific scope is set
     * if ($cache->hasEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER)) {
     *     $userId = $cache->getEntityScopeId(ReworkedCacheService::ENTITY_SCOPE_USER);
     * }
     * ```
     */

}
