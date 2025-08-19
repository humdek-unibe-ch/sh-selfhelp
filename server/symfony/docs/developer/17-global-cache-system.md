# ReworkedCacheService - Generation-Based Cache System

## Overview

The SelfHelp Symfony Backend implements an advanced generation-based cache system built around the `ReworkedCacheService`. This system provides O(1) cache invalidation through generation counters, builder pattern configuration, and automatic statistics tracking. The architecture eliminates the need for cache scanning or deletion operations while providing precise control over cache invalidation.

## Architecture

### Core Structure

```
src/Service/Cache/Core/
└── ReworkedCacheService.php          # MAIN CACHE SERVICE: Generation-based caching
```

### Key Features

1. **ReworkedCacheService** - Advanced tag-based cache service with generation-based invalidation
2. **Builder Pattern** - Immutable service instances with `withCategory()` and `withPrefix()` methods
3. **Generation-Based Invalidation** - O(1) cache invalidation using generation counters
4. **Dual Cache Types** - Separate handling for lists (collections) and items (individual entities)
5. **Automatic Statistics** - Built-in hit/miss/set/invalidate tracking per category
6. **Tag-Based Operations** - Fine-grained invalidation using cache tags

### Architecture Benefits

- **O(1) Invalidation**: No cache scanning or deletion required
- **Immutable Configuration**: Builder pattern prevents configuration conflicts
- **Automatic TTL Management**: Category-based TTL configuration
- **Built-in Monitoring**: Statistics tracking without separate services
- **Memory Efficient**: Generation counters instead of cache entry deletion

### Cache Categories

The system uses predefined cache categories with specific prefixes:

- `pages` - Page entities and their data
- `users` - User entities and profiles
- `sections` - Section entities and hierarchies
- `languages` - Language entities and translations
- `genders` - Gender entities
- `groups` - Group entities and memberships
- `roles` - Role entities and permissions
- `permissions` - Permission entities and ACLs
- `lookups` - Lookup data and constants
- `assets` - Asset entities and metadata
- `frontend_user` - User-specific frontend data
- `cms_preferences` - CMS configuration preferences
- `scheduled_jobs` - Scheduled job entities
- `actions` - Actions for dataTables

### Cache Configuration

ReworkedCacheService uses a single tag-aware cache pool for all operations:

Cache configuration in `config/packages/cache.yaml`:

```yaml
framework:
    cache:
        pools:
            # Single tag-aware cache pool for ReworkedCacheService
            cache.app:
                adapter: cache.adapter.redis_tag_aware
                default_lifetime: 3600
```

### Service Configuration (config/services.yaml)

```yaml
services:
    # ReworkedCacheService - Single cache service with generation-based invalidation
    App\Service\Cache\Core\ReworkedCacheService:
        arguments:
            $cache: '@cache.app'
    
    # Services can inject ReworkedCacheService directly
    # Example configuration:
    App\Service\CMS\Admin\AdminActionService:
        arguments:
            $cache: '@App\Service\Cache\Core\ReworkedCacheService'
```

## Usage

### Builder Pattern with Generation-Based Caching

#### Basic Usage Pattern

```php
use App\Service\Cache\Core\ReworkedCacheService;

class YourService
{
    public function __construct(
        private ReworkedCacheService $cache
    ) {}
    
    public function getActions(int $page, int $pageSize): array
    {
        // Builder pattern with automatic cache-or-compute
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
            ->getList(
                "actions_page_{$page}_size_{$pageSize}",
                fn() => $this->repository->findActionsWithPagination($page, $pageSize)
            );
    }
    
    public function getAction(int $actionId): array
    {
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
            ->getItem(
                "action_{$actionId}",
                fn() => $this->formatAction($this->repository->find($actionId))
            );
    }
    
    public function updateAction(int $actionId, array $data): array
    {
        // Update database
        $action = $this->repository->update($actionId, $data);
        
        // Invalidate both the specific item and all lists
        $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
            ->invalidateItemAndLists("action_{$actionId}");
        
        return $this->formatAction($action);
    }
}
```

#### Advanced Usage Patterns

```php
// User-scoped caching
$userSpecificData = $this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_FRONTEND_USER)
    ->getItem("user_preferences_{$userId}", 
        fn() => $this->fetchUserPreferences($userId),
        $userId  // User-scoped cache key
    );

// Custom TTL override
$shortLivedData = $this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_LOOKUPS)
    ->getList("temp_lookups",
        fn() => $this->fetchTempLookups(),
        null,
        300  // 5 minutes TTL override
    );

// Multiple categories with different prefixes
$apiCache = $this->cache->withPrefix('api')->withCategory(ReworkedCacheService::CATEGORY_USERS);
$adminCache = $this->cache->withPrefix('admin')->withCategory(ReworkedCacheService::CATEGORY_USERS);
```

### Generation-Based Invalidation (O(1) Operations)

```php
// Invalidate entire category - all lists and items (O(1))
$this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
    ->invalidateCategory();

// Invalidate specific item only
$this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
    ->invalidateItem("action_{$actionId}");

// Invalidate specific item AND all lists in category
$this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
    ->invalidateItemAndLists("action_{$actionId}");

// Invalidate all lists in category (keep items)
$this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
    ->invalidateAllListsInCategory();
```

### User-Specific Cache Management

```php
// Invalidate all user cache within specific category (O(1))
$this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_FRONTEND_USER)
    ->invalidateUser($userId);

// Global user invalidation - ALL categories for this user (O(1))
$this->cache->invalidateUserGlobally($userId);

// User-scoped item invalidation
$this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_USERS)
    ->invalidateItem("user_profile_{$userId}", $userId);
```

### Invalidation Strategy Patterns

```php
// CREATE operations - only invalidate lists (new item doesn't exist in cache)
public function createAction(array $data): array 
{
    $action = $this->repository->create($data);
    
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
        ->invalidateAllListsInCategory();
    
    return $this->formatAction($action);
}

// UPDATE operations - invalidate specific item and all lists
public function updateAction(int $id, array $data): array 
{
    $action = $this->repository->update($id, $data);
    
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
        ->invalidateItemAndLists("action_{$id}");
    
    return $this->formatAction($action);
}

// DELETE operations - invalidate specific item and all lists
public function deleteAction(int $id): bool 
{
    $this->repository->delete($id);
    
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
        ->invalidateItemAndLists("action_{$id}");
    
    return true;
}
```

### Built-in Statistics Tracking

```php
// Get statistics for all categories
$allStats = $this->cache->getStats();

// Get statistics for specific category
$actionStats = $this->cache->getStats(ReworkedCacheService::CATEGORY_ACTIONS);

// Example stats structure:
// [
//     'actions' => [
//         'hit' => 1250,
//         'miss' => 180,
//         'set' => 180,
//         'invalidate' => 45
//     ],
//     'users' => [...],
//     ...
// ]

// Statistics are automatically tracked during all operations:
// - Hits: Successful cache retrievals
// - Misses: Cache misses that triggered compute callbacks
// - Sets: New values stored in cache
// - Invalidations: Cache invalidation operations
```

## API Endpoints

### Cache Management
- `GET /admin/cache/stats` - Get comprehensive cache statistics
- `POST /admin/cache/clear` - Clear all caches
- `POST /admin/cache/clear-category` - Clear specific cache category
- `POST /admin/cache/clear-user` - Clear caches for specific user
- `POST /admin/cache/clear-api-routes` - Clear API routes cache
- `GET /admin/cache/health` - Get cache health status with recommendations
- `GET /admin/cache/category/{category}` - Get statistics for specific category
- `POST /admin/cache/reset-stats` - Reset cache statistics

## Key ReworkedCacheService Benefits

### 🚀 **Performance Advantages**
- **O(1) Invalidation**: Generation counters eliminate cache scanning/deletion
- **Memory Efficient**: Old cache entries become inaccessible without deletion
- **Tag-Based Precision**: Fine-grained invalidation using cache tags
- **Single Pool**: Simplified Redis configuration with tag-aware caching

### 🎯 **Developer Experience**
- **Builder Pattern**: Clean, chainable API: `$cache->withCategory(CATEGORY_ACTIONS)->getList(...)`
- **Immutable Config**: No state conflicts between service usages
- **Automatic Statistics**: Built-in monitoring without separate services
- **Intelligent TTL**: Category-based TTL with override support

### 📊 **Advanced Features**
- **Generation-Based Keys**: Cache keys include generation counters for instant invalidation
- **User Scoping**: User-specific cache namespacing with global kill switches  
- **Dual Types**: Separate handling for lists (collections) vs items (entities)
- **Strategic Invalidation**: Different patterns for create/update/delete operations

## Architecture Comparison

### Traditional Cache Systems
```php
// Manual cache management
$key = "actions_page_{$page}";
$data = $cache->get($key);
if ($data === null) {
    $data = $repository->getData();
    $cache->set($key, $data, 3600);
}

// Manual invalidation with scanning
$cache->delete($key);
foreach ($cache->getAllKeys('actions_*') as $cacheKey) {
    $cache->delete($cacheKey); // Expensive operation
}
```

### ReworkedCacheService Approach
```php
// Automatic cache-or-compute with builder pattern
$data = $this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
    ->getList("actions_page_{$page}", 
        fn() => $repository->getData()
    );

// O(1) invalidation with generation bump
$this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_ACTIONS)
    ->invalidateCategory(); // Instant, no scanning required
```

## Migration Benefits

- **🎯 Cleaner API**: Builder pattern eliminates configuration conflicts
- **⚡ Better Performance**: O(1) invalidation instead of cache scanning
- **📊 Built-in Monitoring**: Automatic statistics without separate services
- **🔄 Smart Invalidation**: Different strategies for different operation types
- **🛡️ Error Resilience**: Cache failures don't break application functionality
- **📝 Self-Documenting**: Category constants and method names make intent clear

The ReworkedCacheService represents a significant advancement in cache architecture, providing both superior performance and developer experience.