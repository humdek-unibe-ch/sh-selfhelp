<?php

namespace App\Service\Cache\Core;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * ReworkedCacheService - Advanced tag-based cache service with generation-based invalidation
 *
 * Features:
 * - Builder pattern for category and prefix configuration
 * - Generation-based cache invalidation (O(1) category/user invalidation)
 * - Automatic statistics tracking per category
 * - Tag-based cache organization for fine-grained control
 * - User-scoped caching with global kill switches
 * - List and item cache types with different invalidation strategies
 *
 * Usage Patterns:
 * 1. Builder pattern: $cache->withCategory(CATEGORY_USERS)->getItem(...)
 * 2. Compute-or-get: getList/getItem with callbacks for automatic cache population
 * 3. Selective invalidation: invalidateItem, invalidateCategory, invalidateUser
 * 4. Statistics: Built-in hit/miss/set/invalidate tracking per category
 *
 * @author SelfHelp Development Team
 * @version 8.0.0
 */
class ReworkedCacheService
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

    private const ALL_CATEGORIES = [
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
    ];

    /** @var string Current cache category for this service instance */
    private string $category = 'default';
    
    /** @var string Cache key prefix for namespacing */
    private string $prefix = 'cms';

    /**
     * @param TagAwareCacheInterface $cache Tag-aware cache interface for advanced cache operations
     */
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
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
     * @param int|null $userId Optional user ID for user-scoped caching
     * @param int|null $ttlSeconds Optional TTL override (uses category default if null)
     * @return mixed The cached or computed value
     * 
     * @example 
     * $actions = $cache->withCategory(CATEGORY_ACTIONS)->getList(
     *     'actions_page_1_size_20',
     *     fn() => $this->repository->findActionsWithPagination(1, 20)
     * );
     */
    public function getList(string $key, callable $compute, ?int $userId = null, ?int $ttlSeconds = null): mixed
    {
        $cacheKey = $this->getCacheKey('list', $key, $userId);
        $miss = false;

        if (!$ttlSeconds) {
            $ttlSeconds = $this->getCategoryTTL($this->category);
        }

        $value = $this->cache->get($cacheKey, function (ItemInterface $item) use ($compute, $ttlSeconds, $userId, $key, &$miss) {
            $miss = true;
            if ($ttlSeconds) {
                $item->expiresAfter($ttlSeconds);
            }
            $tags = array_merge($this->tagsFor($userId), [$this->itemTag($key, $userId), $this->listTag()]);
            $item->tag($tags);

            $val = $compute();
            $this->recordSet($this->category);
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
     * @param int|null $userId Optional user ID for user-scoped caching
     * @param int|null $ttlSeconds Optional TTL override (uses category default if null)
     * @return mixed The cached or computed value
     * 
     * @example 
     * $action = $cache->withCategory(CATEGORY_ACTIONS)->getItem(
     *     "action_{$actionId}",
     *     fn() => $this->formatAction($this->repository->find($actionId))
     * );
     */
    public function getItem(string $key, callable $compute, ?int $userId = null, ?int $ttlSeconds = null): mixed
    {
        $cacheKey = $this->getCacheKey('item', $key, $userId);
        $miss = false;

        if (!$ttlSeconds) {
            $ttlSeconds = $this->getCategoryTTL($this->category);
        }

        $value = $this->cache->get($cacheKey, function (ItemInterface $item) use ($compute, $ttlSeconds, $userId, $key, &$miss) {
            $miss = true;
            if ($ttlSeconds) {
                $item->expiresAfter($ttlSeconds);
            }
            $tags = array_merge($this->tagsFor($userId), [$this->itemTag($key, $userId)]);
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
     * @param int|null $userId Optional user ID for user-scoped caching
     * @param mixed $value The value to store in cache
     * @param int|null $ttlSeconds Optional TTL override (uses category default if null)
     * 
     * @example $cache->withCategory(CATEGORY_ACTIONS)->setItem("action_{$id}", null, $formattedAction);
     */
    public function setItem(string $key, ?int $userId, mixed $value, ?int $ttlSeconds = null): void
    {
        $cacheKey = $this->getCacheKey('item', $key, $userId);
        $this->cache->get($cacheKey, function (ItemInterface $item) use ($value, $ttlSeconds, $userId, $key) {
            if ($ttlSeconds) {
                $item->expiresAfter($ttlSeconds);
            }
            $tags = array_merge($this->tagsFor($userId), [$this->itemTag($key, $userId)]);
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
     * @param int|null $userId Optional user ID if the item is user-scoped
     * 
     * @example $cache->withCategory(CATEGORY_ACTIONS)->invalidateItem("action_{$actionId}");
     */
    public function invalidateItem(string $key, ?int $userId = null): void
    {
        $this->cache->invalidateTags([$this->itemTag($key, $userId)]);
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
     * @param int $userId The user ID whose cache entries should be globally invalidated
     * 
     * @example $cache->invalidateUserGlobally($userId); // Clears user cache everywhere
     */
    public function invalidateUserGlobally(int $userId): void
    {
        $this->incr($this->globalUserGenKey($userId));
        // Not tied to a category; record under a synthetic bucket if you like.
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
     * @param int|null $userId Optional user ID if the item is user-scoped
     * 
     * @example $cache->withCategory(CATEGORY_ACTIONS)->invalidateItemAndLists("action_{$actionId}");
     */
    public function invalidateItemAndLists(string $key, ?int $userId = null): void
    {
        $this->invalidateItem($key, $userId);
        $this->invalidateAllListsInCategory(); // all lists in this category
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
            default => 3600, // 1h
        };
    }

    /* =========================
       Statistics Tracking (per category)
       ========================= */

    /**
     * Get cache statistics for one or all categories
     * 
     * Returns hit/miss/set/invalidate counters that are automatically tracked
     * during cache operations. Useful for monitoring cache performance.
     * 
     * @param string|null $category Specific category to get stats for, or null for all categories
     * @return array Statistics data structure with hit/miss/set/invalidate counters
     * 
     * @example 
     * $allStats = $cache->getStats(); // All categories
     * $actionStats = $cache->getStats(CATEGORY_ACTIONS); // Just actions
     */
    public function getStats(?string $category = null): array
    {
        if ($category !== null) {
            return $this->readStatsBucket($category);
        }

        $out = [];
        foreach (self::ALL_CATEGORIES as $cat) {
            $out[$cat] = $this->readStatsBucket($cat);
        }
        return $out;
    }

    private function readStatsBucket(string $category): array
    {
        return [
            'hit' => $this->getInt($this->statKey($category, 'hit'), 0),
            'miss' => $this->getInt($this->statKey($category, 'miss'), 0),
            'set' => $this->getInt($this->statKey($category, 'set'), 0),
            'invalidate' => $this->getInt($this->statKey($category, 'invalidate'), 0),
        ];
    }

    private function recordHit(string $category): void
    {
        $this->incr($this->statKey($category, 'hit'));
    }
    private function recordMiss(string $category): void
    {
        $this->incr($this->statKey($category, 'miss'));
    }
    private function recordSet(string $category): void
    {
        $this->incr($this->statKey($category, 'set'));
    }
    private function recordInvalidation(string $category): void
    {
        $this->incr($this->statKey($category, 'invalidate'));
    }

    /* =========================
       Internal Implementation (generation-based cache keys & statistics)
       ========================= */

    /**
     * Generate a cache key with generation-based invalidation support
     * 
     * Creates cache keys that include generation counters for O(1) invalidation.
     * The key format includes category generation, user generation, and global user generation
     * to support selective invalidation without scanning existing cache entries.
     * 
     * @param string $type Cache entry type ('list' or 'item')
     * @param string $plainKey The base key identifier
     * @param int|null $userId Optional user ID for user-scoped caching
     * @return string Complete cache key with generation counters
     */
    private function getCacheKey(string $type, string $plainKey, ?int $userId = null): string
    {
        $catGen = $this->getInt($this->catGenKey(), 1);
        $parts = [$this->prefix, $this->category, 'g' . $catGen];

        if ($userId !== null) {
            $userGen = $this->getInt($this->userGenKey($userId), 1);
            $global = $this->getInt($this->globalUserGenKey($userId), 1); // global kill switch
            $parts[] = 'u' . $userId;
            $parts[] = 'g' . max($userGen, $global);
        }

        $parts[] = $type;
        $parts[] = $this->normalizeKey($plainKey);

        return implode('-', $parts);
    }

    private function listTag(): string
    {
        return "list-{$this->category}";
    }

    private function tagsFor(?int $userId): array
    {
        $tags = ["cat-{$this->category}"];
        if ($userId !== null) {
            $tags[] = "user-{$userId}";
            $tags[] = "cat-{$this->category}-user-{$userId}";
        }
        return $tags;
    }

    private function itemTag(string $key, ?int $userId = null): string
    {
        $base = "item-{$this->category}-" . $this->normalizeKey($key);
        return $userId === null ? $base : $base . "-u{$userId}";
    }

    private function normalizeKey(string $k): string
    {
        return strlen($k) <= 60 ? $k : (substr($k, 20) ? substr($k, 0, 20) : $k) . ':' . md5($k);
    }

    private function catGenKey(): string
    {
        return "{$this->prefix}-{$this->category}-gen";
    }

    private function userGenKey(int $userId): string
    {
        return "{$this->prefix}-{$this->category}-user-{$userId}-gen";
    }

    private function globalUserGenKey(int $userId): string
    {
        return "{$this->prefix}-user-{$userId}-gen";
    }

    private function statKey(string $category, string $metric): string
    {
        return "{$this->prefix}-stats-{$category}-{$metric}";
    }

    private function getInt(string $key, int $default): int
    {
        // CacheInterface::get callback runs on miss; we store the default.
        return (int) $this->cache->get($key, function (ItemInterface $item) use ($default) {
            // No expiry for namespace tokens & stats
            return $default;
        });
    }

    private function incr(string $key): void
    {
        $this->cache->get($key, function (ItemInterface $item) {
            $current = (int) $item->get();
            return $current + 1;
        });
    }
}
