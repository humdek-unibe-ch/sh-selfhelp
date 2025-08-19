<?php

namespace App\Service\Cache\Core;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


class ReworkedCacheService
{
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

    private string $category = 'default';
    private string $prefix = 'cms';

    public function __construct(
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    /* =========================
       Builder-style config
       ========================= */
    public function withCategory(string $category): self
    {
        $cl = clone $this;
        $cl->category = $category;
        return $cl;
    }

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
     * Compute-or-get a LIST entry (optionally user-scoped).
     * Records hit/miss + set stats automatically.
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
     * Compute-or-get an ITEM (optionally user-scoped).
     * Records hit/miss + set stats automatically.
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

    /** Force-set an ITEM (prefer getItem with callback when possible). */
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

    /** Invalidate one ITEM (by per-item tag). */
    public function invalidateItem(string $key, ?int $userId = null): void
    {
        $this->cache->invalidateTags([$this->itemTag($key, $userId)]);
        $this->recordInvalidation($this->category);
    }

    /* =========================
       Invalidation (no scans)
       ========================= */

    /** Invalidate every LIST/ITEM in this category (O(1)): bump category gen. */
    public function invalidateCategory(): void
    {
        $this->incr($this->catGenKey());
        $this->recordInvalidation($this->category);
    }

    /** Invalidate all cache for this USER within this category: bump user gen. */
    public function invalidateUser(int $userId): void
    {
        $this->incr($this->userGenKey($userId));
        $this->recordInvalidation($this->category);
    }

    /** Invalidate ALL categories for this user (global kill switch). */
    public function invalidateUserGlobally(int $userId): void
    {
        $this->incr($this->globalUserGenKey($userId));
        // Not tied to a category; record under a synthetic bucket if you like.
    }

    /** Optional: invalidate all LISTs in category via list tag (if you prefer tags). */
    public function invalidateAllListsInCategory(): void
    {
        $this->cache->invalidateTags([$this->listTag()]);
        $this->recordInvalidation($this->category);
    }

    /** Invalidate one ITEM and all LISTs in this category (O(1)): bump category gen. */
    public function invalidateItemAndLists(string $key, ?int $userId = null): void
    {
        $this->invalidateItem($key, $userId);
        $this->invalidateAllListsInCategory(); // all lists in this category
    }


    /* =========================
       TTLs
       ========================= */

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
       Stats (per category)
       ========================= */

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
       Internals
       ========================= */

    /**
     * Generate a cache key
     * @param string $type
     * @param string $plainKey
     * @param int|null $userId
     * @return string
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
