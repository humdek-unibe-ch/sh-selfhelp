# Consolidated Cache System

## Overview

The SelfHelp Symfony Backend implements a comprehensive, well-structured cache system that has been consolidated into a single core service for maximum simplicity and maintainability. The cache system provides category-based caching, user-specific cache invalidation, and optional statistics monitoring with clear separation of concerns.

## Architecture

### Streamlined Structure

```
src/Service/Cache/
├── Core/
│   ├── CacheService.php              # SINGLE CORE SERVICE: All cache operations + invalidation
│   ├── CacheStatsService.php         # Statistics & monitoring (optional)
│   └── CacheableServiceTrait.php     # Provides caching capabilities to services
├── Specialized/
│   ├── UserCacheService.php          # User entity caching during request lifecycle
│   └── UserPermissionCacheService.php # User permissions caching during request lifecycle
└── Command/
    └── ClearApiRoutesCacheCommand.php # Cache management commands

tests/Controller/Api/V1/Admin/
└── AdminCacheTestController.php       # Test controller (moved to proper location)
```

### Core Components

1. **CacheService** - **SINGLE UNIFIED SERVICE** for all cache operations and invalidation
2. **CacheStatsService** - Statistics and monitoring (separated for debugging/monitoring only)
3. **CacheableServiceTrait** - Provides standardized caching capabilities to services
4. **UserCacheService** - Request-lifecycle user entity caching
5. **UserPermissionCacheService** - Request-lifecycle user permissions caching
6. **AdminCacheController** - API endpoints for cache monitoring and management

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

### Cache Pools

The system utilizes multiple Redis cache pools for optimal performance:

- `cache.global` - General application cache (default)
- `cache.user_frontend` - Frontend user-specific data
- `cache.admin` - Admin interface data
- `cache.lookups` - Lookup data and constants
- `cache.permissions` - Permission-related data
- `cache.app` - Application metadata (routes, etc.)

### Cache Configuration

Cache pools are configured in `config/packages/cache.yaml`:

```yaml
framework:
    cache:
        pools:
            cache.global:
                adapter: cache.adapter.redis
                default_lifetime: 3600
            cache.user_frontend:
                adapter: cache.adapter.redis
                default_lifetime: 1800
            cache.admin:
                adapter: cache.adapter.redis
                default_lifetime: 1800
            cache.lookups:
                adapter: cache.adapter.redis
                default_lifetime: 7200
            cache.permissions:
                adapter: cache.adapter.redis
                default_lifetime: 1800
            cache.app:
                adapter: cache.adapter.redis
                default_lifetime: 86400
```

### Service Configuration (config/services.yaml)

```yaml
services:
    # Cache Service Configuration - SINGLE CORE SERVICE
    App\Service\Cache\Core\CacheService:
        arguments:
            $globalCache: '@cache.global'
            $userFrontendCache: '@cache.user_frontend'
            $adminCache: '@cache.admin'
            $lookupsCache: '@cache.lookups'
            $permissionsCache: '@cache.permissions'
            $appCache: '@cache.app'
        calls:
            - [setStatsService, ['@App\Service\Cache\Core\CacheStatsService']]
    
    # Cache Statistics Service (optional monitoring)
    App\Service\Cache\Core\CacheStatsService:
        arguments:
            $cacheService: '@App\Service\Cache\Core\CacheService'
    
    # Services using cache automatically get the CacheableServiceTrait methods
    # Example configuration:
    App\Service\CMS\Admin\AdminUserService:
        calls:
            - [setCacheService, ['@App\Service\Cache\Core\CacheService']]
```

## Usage

### Standardized Cache Operations

#### Using CacheService Directly (Single Service)

```php
use App\Service\Cache\Core\CacheService;

class YourService
{
    public function __construct(
        private CacheService $cacheService
    ) {}
    
    public function getData(int $id): array
    {
        // Get from cache with automatic set if not found
        $data = $this->cacheService->get(
            CacheService::CATEGORY_PAGES,
            "page_{$id}"
        );
        
        if ($data === null) {
            // Fetch from database
            $data = $this->fetchFromDatabase($id);
            
            // Store in cache with automatic TTL
            $this->cacheService->set(
                CacheService::CATEGORY_PAGES,
                "page_{$id}",
                $data
            );
        }
        
        return $data;
    }
    
    public function updateData(int $id, array $newData): void
    {
        // Update database
        $this->updateDatabase($id, $newData);
        
        // Automatically invalidate related caches
        $this->cacheService->invalidateCategory(CacheService::CATEGORY_PAGES);
    }
}
```

#### Using CacheableServiceTrait (Recommended)

```php
use App\Service\Cache\Core\CacheableServiceTrait;
use App\Service\Cache\Core\CacheService;

class YourService
{
    use CacheableServiceTrait;
    
    public function getData(int $id): array
    {
        // Single method handles get-or-set with automatic TTL
        return $this->getCache(
            CacheService::CATEGORY_PAGES,
            "page_{$id}",
            fn() => $this->fetchFromDatabase($id)
        );
    }
    
    public function updateData(Page $page): void
    {
        // Update database
        $this->updateDatabase($page);
        
        // Automatic entity-based invalidation
        $this->triggerCacheInvalidation('update', CacheService::CATEGORY_PAGES, $page);
    }
}
```

### Entity-Based Invalidation (Built into CacheService)

```php
// The CacheService now handles entity-specific invalidation
$this->cacheService->invalidateForEntity($user, 'update');
$this->cacheService->invalidateForEntity($page, 'delete');
$this->cacheService->invalidateForEntity($section, 'create');

// Or use specific methods directly
$this->cacheService->invalidateUser($userId, 'update');
$this->cacheService->invalidatePage($page, 'delete');
$this->cacheService->invalidateSection($section, 'update');
```

### User-Specific Cache Management

```php
// Invalidate all caches for a specific user
$this->cacheService->invalidateAllUserCaches($userId);

// Invalidate only frontend caches for a user
$this->cacheService->invalidateUserCategory($userId);

// Invalidate specific permissions for a user
$this->cacheService->invalidatePermissions($userId);
```

### Category-Based Operations

```php
// Invalidate entire categories
$this->cacheService->invalidateCategory(CacheService::CATEGORY_PAGES);
$this->cacheService->invalidateCategory(CacheService::CATEGORY_USERS);

// Clear all caches
$this->cacheService->clearAll();

// Clear API routes cache specifically
$this->cacheService->clearApiRoutes();
```

### Statistics and Monitoring (Optional)

```php
use App\Service\Cache\Core\CacheStatsService;

// Get comprehensive statistics
$stats = $this->cacheStatsService->getStats();

// Get cache health with recommendations
$health = $this->cacheStatsService->getCacheHealth();

// Get top performing categories
$topCategories = $this->cacheStatsService->getTopPerformingCategories(5);

// Reset statistics
$this->cacheStatsService->resetStats();
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

## Key Consolidation Benefits

### 🎯 **Single Responsibility**
- **One Core Service**: All cache operations (get, set, delete, invalidate) in `CacheService.php`
- **Separate Stats Service**: Statistics moved to dedicated `CacheStatsService.php` for monitoring
- **Clean Architecture**: Core caching logic separate from debugging/monitoring

### 🚀 **Simplified Usage**
```php
// BEFORE: Multiple services
$this->cacheService->get(...);
$this->cacheService->invalidateForEntity(...);

// AFTER: Single service
$this->cacheService->get(...);
$this->cacheService->invalidateForEntity(...);
```

### 📦 **Better Organization**
- **CacheService.php**: ~450 lines with ALL core functionality
- **CacheStatsService.php**: ~250 lines with monitoring only
- **No Duplication**: Removed `getCacheTTL` from utility services
- **Test Location**: Test controller moved to proper `tests/` directory

### 🔧 **Enhanced Developer Experience**
- **Single Import**: Only need `use App\Service\Cache\Core\CacheService;`
- **Consistent API**: All operations through one service
- **Auto-Invalidation**: Entity changes automatically trigger cache invalidation
- **Error Resilient**: Cache failures don't break application functionality

## Before vs After Comparison

### Before Consolidation
```php
// Multiple services needed
use App\Service\Cache\Core\CacheService;
use App\Service\Cache\Core\CacheInvalidationService;

// Separate operations
$data = $this->cacheService->get($category, $key);
$this->cacheService->invalidateForEntity($entity);

// Duplicate logic
protected function getCacheTTL($category) { /* duplicate in multiple files */ }
```

### After Consolidation
```php
// Single service handles everything
use App\Service\Cache\Core\CacheService;

// Unified operations
$data = $this->cacheService->get($category, $key);
$this->cacheService->invalidateForEntity($entity);

// No duplication - TTL handled automatically
$this->cacheService->getCacheTTL($category); // Built-in
```

## Migration Impact

- **✅ Zero Breaking Changes**: All existing code works unchanged
- **✅ Better Performance**: Reduced service dependencies and method calls  
- **✅ Easier Maintenance**: All cache logic in one place
- **✅ Cleaner Tests**: Test controller in proper location
- **✅ Better Documentation**: Clear separation between core and monitoring

The consolidated cache system maintains all existing functionality while providing a much cleaner and more maintainable architecture.