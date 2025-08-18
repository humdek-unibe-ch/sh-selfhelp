# Cache Service vs CacheableServiceTrait: Usage Guide

## Overview

The caching system has been refactored to provide clear distinction between low-level cache operations and high-level convenience patterns.

## When to Use What

### Use `CacheService` Directly

**Inject the service and use it directly when you need:**

- **Fine-grained cache control** - Manual get/set/delete operations
- **Custom invalidation logic** - Complex cache invalidation patterns  
- **Cache infrastructure** - Building cache-related functionality
- **Direct pool access** - Working with specific cache pools
- **Controllers** - Direct cache operations in API endpoints
- **System-level services** - Low-level infrastructure services

**Example Usage:**
```php
class PageFieldService extends UserContextAwareService
{
    public function __construct(
        private readonly CacheService $cacheService,
        // ... other dependencies
    ) {}

    public function getPageWithFields(string $pageKeyword): array
    {
        // Manual cache handling
        $cacheKey = "page_with_fields_{$pageKeyword}";
        $cachedResult = $this->cacheService->get(CacheService::CATEGORY_PAGES, $cacheKey);
        
        if ($cachedResult !== null) {
            return $cachedResult;
        }
        
        // Fetch data...
        $result = $this->fetchPageData($pageKeyword);
        
        // Manual cache storage
        $this->cacheService->set(CacheService::CATEGORY_PAGES, $cacheKey, $result, 1800);
        
        return $result;
    }
}
```

### Use `CacheableServiceTrait`

**Add the trait to your service when you need:**

- **Simple "cache-or-callback" patterns** - Most common caching scenario
- **Convenience methods** - Standardized caching patterns
- **Business services** - Domain logic services that need simple caching
- **Standard key generation** - Consistent cache key formats

**Example Usage:**
```php
class AdminUserService extends UserContextAwareService
{
    use CacheableServiceTrait;
    
    public function getUsers(int $page, int $pageSize, ?string $search = null): array
    {
        $cacheKey = "users_list_{$page}_{$pageSize}_" . md5($search ?? '');
        
        // Simple cache-or-callback pattern
        return $this->getCache(
            CacheService::CATEGORY_USERS,
            $cacheKey,
            function() use ($page, $pageSize, $search) {
                return $this->fetchUsersFromDatabase($page, $pageSize, $search);
            }
        );
    }
    
    public function updateUser(int $id, array $data): User
    {
        // ... update logic ...
        
        // Simple invalidation after change
        $this->invalidateAfterChange('update', CacheService::CATEGORY_USERS, $user);
        
        return $user;
    }
}
```

## Configuration

### Services Using the Trait

Services using `CacheableServiceTrait` must have `CacheService` injected via `services.yaml`:

```yaml
App\Service\CMS\Admin\AdminUserService:
    calls:
        - [setCacheService, ['@App\Service\Cache\Core\CacheService']]
```

### Services Using Direct Injection

Services needing direct control inject `CacheService` in constructor:

```php
public function __construct(
    private readonly CacheService $cacheService,
    // ... other dependencies
) {}
```

## Method Comparison

### CacheService (Direct Usage)
- `get(category, key, userId?)` - Get cache item
- `set(category, key, data, ttl?, userId?)` - Set cache item  
- `delete(category, key, userId?)` - Delete cache item
- `has(category, key, userId?)` - Check if exists
- `invalidateCategory(category)` - Clear entire category
- `invalidateForEntity(entity, operation)` - Entity-specific invalidation
- `invalidateUserCategory(userId)` - Clear user caches

### CacheableServiceTrait (Convenience Usage)
- `getCache(category, key, callback, ttl?, userId?)` - **Main method**: cache-or-callback pattern
- `invalidateAfterChange(operation, category, entity?, userId?)` - Simple invalidation
- `getEntityCacheKey(type, id, suffix?)` - Standard entity keys
- `getListCacheKey(type, filters, suffix?)` - Standard list keys  
- `getUserCacheKey(userId, dataType, suffix?)` - Standard user keys
- `cacheEntity(category, entity, ttl?)` - Cache entity with standard key
- `getCachedEntity(category, type, id)` - Get entity with standard key
- `cacheList(category, type, data, filters, ttl?)` - Cache list with standard key
- `getCachedList(category, type, filters)` - Get list with standard key
- `cacheUserData(userId, dataType, data, ttl?)` - Cache user data
- `getCachedUserData(userId, dataType)` - Get user data

## Best Practices

1. **Default to the trait** for business services that need simple caching
2. **Use direct injection** when you need custom logic or fine control
3. **Always handle cache failures gracefully** - both approaches do this automatically
4. **Use standardized cache keys** provided by the trait methods
5. **Call invalidation after CUD operations** using `invalidateAfterChange()`

## Migration Notes

If you were using the old trait methods that were removed:
- `setCache()`, `deleteCache()`, `hasCache()` → Use `CacheService` directly instead
- `invalidateCache()`, `invalidateUserCache()` → Use `CacheService` directly instead  
- `triggerCacheInvalidation()` → Use `invalidateAfterChange()` instead

The trait now focuses purely on convenience patterns and delegates all actual cache operations to `CacheService`.
