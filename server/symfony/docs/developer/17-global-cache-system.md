# Global Cache System

## Overview

The SelfHelp Symfony Backend implements a comprehensive global cache system that provides category-based caching, user-specific cache invalidation, and extensive monitoring capabilities. The cache system is designed to improve performance while maintaining data consistency through intelligent invalidation strategies.

## Architecture

### Core Components

1. **GlobalCacheService** - Main cache management service
2. **CacheInvalidationService** - Handles cache invalidation strategies
3. **CacheableServiceTrait** - Provides caching capabilities to services
4. **AdminCacheController** - API endpoints for cache monitoring and management

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
- `actions` - actions for dataTables

### Cache Pools

The system utilizes multiple Redis cache pools for different data types:

- **cache.global** - Main cache for entities and API responses (TTL: 1 hour)
- **cache.user_frontend** - User-specific frontend data (TTL: 30 minutes)
- **cache.admin** - Admin interface data (TTL: 15 minutes)
- **cache.lookups** - Lookup data with longer TTL (TTL: 2 hours)
- **cache.permissions** - Permissions and ACL data (TTL: 30 minutes)

## Configuration

### Cache Configuration (config/packages/cache.yaml)

```yaml
framework:
    cache:
        prefix_seed: selfhelp_app
        app: cache.adapter.redis
        default_redis_provider: '%env(REDIS_URL)%'
        
        pools:
            cache.global:
                adapter: cache.adapter.redis
                default_lifetime: 3600
                
            cache.user_frontend:
                adapter: cache.adapter.redis
                default_lifetime: 1800
                
            cache.admin:
                adapter: cache.adapter.redis
                default_lifetime: 900
                
            cache.lookups:
                adapter: cache.adapter.redis
                default_lifetime: 7200
                
            cache.permissions:
                adapter: cache.adapter.redis
                default_lifetime: 1800
```

### Service Configuration (config/services.yaml)

```yaml
services:
    # Global Cache Service Configuration
    App\Service\Core\GlobalCacheService:
        arguments:
            $globalCache: '@cache.global'
            $userFrontendCache: '@cache.user_frontend'
            $adminCache: '@cache.admin'
            $lookupsCache: '@cache.lookups'
            $permissionsCache: '@cache.permissions'
    
    # Cache Invalidation Service
    App\Service\Core\CacheInvalidationService:
        arguments:
            $cacheService: '@App\Service\Core\GlobalCacheService'
```

## Usage

### Basic Cache Operations

#### Using GlobalCacheService Directly

```php
use App\Service\Core\GlobalCacheService;

class YourService
{
    public function __construct(
        private GlobalCacheService $cacheService
    ) {}
    
    public function getData(int $id): array
    {
        // Try to get from cache
        $data = $this->cacheService->get(
            GlobalCacheService::CATEGORY_PAGES,
            "page_{$id}"
        );
        
        if ($data === null) {
            // Fetch from database
            $data = $this->fetchFromDatabase($id);
            
            // Store in cache
            $this->cacheService->set(
                GlobalCacheService::CATEGORY_PAGES,
                "page_{$id}",
                $data,
                3600 // TTL in seconds
            );
        }
        
        return $data;
    }
}
```

#### Using CacheableServiceTrait

```php
use App\Service\Core\CacheableServiceTrait;
use App\Service\Core\GlobalCacheService;

class YourService
{
    use CacheableServiceTrait;
    
    public function getPageData(int $pageId): array
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_PAGES,
            "page_{$pageId}",
            function() use ($pageId) {
                return $this->fetchPageFromDatabase($pageId);
            },
            3600 // TTL
        );
    }
    
    public function getUserSpecificData(int $userId, string $type): array
    {
        return $this->cacheUserData($userId, $type, function() use ($userId, $type) {
            return $this->fetchUserData($userId, $type);
        });
    }
}
```

### Cache Invalidation

#### Entity-Based Invalidation

```php
use App\Service\Core\CacheInvalidationService;

class AdminPageService
{
    public function __construct(
        private CacheInvalidationService $cacheInvalidationService
    ) {}
    
    public function updatePage(Page $page): Page
    {
        // Update page logic...
        
        // Invalidate related caches
        $this->cacheInvalidationService->invalidatePage($page, 'update');
        
        return $page;
    }
    
    public function deleteUser(User $user): void
    {
        // Delete user logic...
        
        // Invalidate all user-related caches
        $this->cacheInvalidationService->invalidateAllUserCaches($user->getId());
    }
}
```

#### Manual Invalidation

```php
// Invalidate specific cache item
$this->cacheService->delete(GlobalCacheService::CATEGORY_PAGES, 'page_123');

// Invalidate entire category
$this->cacheService->invalidateCategory(GlobalCacheService::CATEGORY_USERS);

// Invalidate all frontend caches for a user
$this->cacheService->invalidateUserFrontend(456);

// Clear all caches
$this->cacheService->clearAll();
```

## Invalidation Strategies

### Automatic Invalidation Triggers

The system automatically invalidates caches when:

1. **Pages**: Created, updated, deleted, or sections reordered
2. **Users**: Created, updated, deleted, or permissions changed
3. **Sections**: Created, updated, deleted, or moved between pages
4. **Languages**: Created, updated, or deleted
5. **Groups**: Created, updated, deleted, or ACLs changed
6. **Roles**: Created, updated, deleted, or permissions changed
7. **Permissions**: Any permission changes
8. **User Actions**: Any action performed by a user invalidates their frontend caches


### Cascading Invalidation

When certain entities change, related caches are also invalidated:

- **Page changes** → Invalidate page caches + all user frontend caches
- **Section changes** → Invalidate section + parent page + user frontend caches
- **Permission changes** → Invalidate permissions + affected user caches
- **Group/Role changes** → Invalidate permissions + all related user caches

## API Endpoints

### Cache Monitoring

#### GET /admin/cache/stats
Get comprehensive cache statistics and monitoring data.

**Response:**
```json
{
    "status": "success",
    "data": {
        "cache_stats": {
            "global_stats": {
                "hits": 1250,
                "misses": 180,
                "sets": 320,
                "invalidations": 45,
                "hit_rate": 87.41
            },
            "category_stats": {
                "pages": {
                    "hits": 450,
                    "misses": 32,
                    "sets": 85,
                    "invalidations": 12
                }
            }
        },
        "cache_categories": [...],
        "cache_pools": {...},
        "timestamp": "2024-01-15T10:30:00+00:00"
    }
}
```

#### GET /admin/cache/health
Get cache health status and recommendations.

**Response:**
```json
{
    "status": "success",
    "data": {
        "status": "excellent",
        "color": "green",
        "hit_rate": 87.41,
        "total_operations": 1430,
        "recommendations": [
            {
                "type": "performance",
                "message": "Cache performance is optimal",
                "priority": "low"
            }
        ],
        "timestamp": "2024-01-15T10:30:00+00:00"
    }
}
```

### Cache Management

#### POST /admin/cache/clear/all
Clear all caches across all pools.

#### POST /admin/cache/clear/category
Clear specific cache category.

**Request Body:**
```json
{
    "category": "pages"
}
```

#### POST /admin/cache/clear/user
Clear all caches for a specific user.

**Request Body:**
```json
{
    "user_id": 123
}
```

#### POST /admin/cache/stats/reset
Reset cache statistics.

## Integration Examples

### Frontend Page Service

```php
class PageService extends UserContextAwareService
{
    use CacheableServiceTrait;
    
    public function getAllAccessiblePagesForUser(string $mode, bool $admin, ?int $language_id = null): array
    {
        $user = $this->getCurrentUser();
        $userId = $user ? $user->getId() : 1;
        $languageId = $this->determineLanguageId($language_id);
        
        // Try cache first
        $cacheKey = "pages_{$mode}_{$admin}_{$languageId}";
        $cachedPages = $this->getCachedUserData($userId, $cacheKey);
        if ($cachedPages !== null) {
            return $cachedPages;
        }
        
        // Fetch and process pages...
        $pages = $this->processPages($mode, $admin, $languageId);
        
        // Cache the result
        $this->cacheUserData(
            $userId, 
            $cacheKey, 
            $pages, 
            $this->getCacheTTL(GlobalCacheService::CATEGORY_FRONTEND_USER)
        );
        
        return $pages;
    }
}
```

### Admin User Service with Caching

```php
class AdminUserService extends UserContextAwareService
{
    use CacheableServiceTrait;
    
    public function getUsers(int $page = 1, int $pageSize = 20, ?string $search = null, ?string $sort = null, ?string $sortDirection = 'asc'): array
    {
        // Create cache key based on parameters
        $cacheKey = "users_list_{$page}_{$pageSize}_" . md5(($search ?? '') . ($sort ?? '') . $sortDirection);
        
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_USERS,
            $cacheKey,
            function() use ($page, $pageSize, $search, $sort, $sortDirection) {
                return $this->fetchUsersFromDatabase($page, $pageSize, $search, $sort, $sortDirection);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_USERS)
        );
    }
    
    public function getUserById(int $userId): array
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_USERS,
            "user_{$userId}",
            function() use ($userId) {
                $user = $this->userRepository->find($userId);
                if (!$user) {
                    throw new ServiceException('User not found', Response::HTTP_NOT_FOUND);
                }
                return $this->formatUserForDetail($user);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_USERS)
        );
    }
    
    public function createUser(array $userData): array
    {
        // ... creation logic ...
        
        $this->entityManager->commit();

        // Invalidate user caches after successful creation
        if ($this->cacheInvalidationService) {
            $this->cacheInvalidationService->invalidateUser($user, 'create');
            $this->cacheInvalidationService->invalidatePermissions();
        }

        return $this->formatUserForDetail($user);
    }
}
```

### Admin Group Service with Caching

```php
class AdminGroupService extends UserContextAwareService
{
    use CacheableServiceTrait;
    
    public function getGroups(int $page = 1, int $pageSize = 20, ?string $search = null, ?string $sort = null, ?string $sortDirection = 'asc'): array
    {
        // Create cache key based on parameters
        $cacheKey = "groups_list_{$page}_{$pageSize}_" . md5(($search ?? '') . ($sort ?? '') . $sortDirection);
        
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_GROUPS,
            $cacheKey,
            function() use ($page, $pageSize, $search, $sort, $sortDirection) {
                return $this->fetchGroupsFromDatabase($page, $pageSize, $search, $sort, $sortDirection);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_GROUPS)
        );
    }
}
```

### Admin Section Service with Caching

```php
class AdminSectionService extends UserContextAwareService
{
    use CacheableServiceTrait;
    
    public function getSection(?string $page_keyword, int $section_id): array
    {
        $cacheKey = "section_{$section_id}_" . ($page_keyword ?? 'auto');
        
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_SECTIONS,
            $cacheKey,
            function() use ($page_keyword, $section_id) {
                return $this->fetchSectionFromDatabase($page_keyword, $section_id);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_SECTIONS)
        );
    }
}
```

### Admin Asset Service with Caching

```php
class AdminAssetService extends BaseService
{
    use CacheableServiceTrait;
    
    public function getAllAssets(int $page = 1, int $pageSize = 100, ?string $search = null, ?string $folder = null): array
    {
        // Create cache key based on parameters
        $cacheKey = "assets_list_{$page}_{$pageSize}_" . md5(($search ?? '') . ($folder ?? ''));
        
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_ASSETS,
            $cacheKey,
            function() use ($page, $pageSize, $search, $folder) {
                return $this->fetchAssetsFromDatabase($page, $pageSize, $search, $folder);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_ASSETS)
        );
    }
}
```

### Admin Action Service with Caching

```php
class AdminActionService extends BaseService
{
    use CacheableServiceTrait;
    
    public function getActions(int $page = 1, int $pageSize = 20, ?string $search = null, ?string $sort = null, string $sortDirection = 'asc'): array
    {
        // Create cache key based on parameters
        $cacheKey = "actions_list_{$page}_{$pageSize}_" . md5(($search ?? '') . ($sort ?? '') . $sortDirection);
        
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_ACTIONS,
            $cacheKey,
            function() use ($page, $pageSize, $search, $sort, $sortDirection) {
                return $this->actionRepository->findActionsWithPagination($page, $pageSize, $search, $sort, $sortDirection);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_ACTIONS)
        );
    }
    
    public function getActionById(int $actionId): array
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_ACTIONS,
            "action_{$actionId}",
            function() use ($actionId) {
                $action = $this->entityManager->find(Action::class, $actionId);
                if (!$action instanceof Action) {
                    throw new ServiceException('Action not found', Response::HTTP_NOT_FOUND);
                }
                return $this->formatAction($action);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_ACTIONS)
        );
    }
}
```

### Lookup Service with Caching

```php
class LookupService extends BaseService
{
    use CacheableServiceTrait;
    
    public function getLookups(string $typeCode): array
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_LOOKUPS,
            "lookups_{$typeCode}",
            function() use ($typeCode) {
                return $this->lookupRepository->findByTypeCode($typeCode);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_LOOKUPS)
        );
    }
    
    public function findByTypeAndValue(string $typeCode, string $value): ?Lookup
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_LOOKUPS,
            "lookup_{$typeCode}_{$value}",
            function() use ($typeCode, $value) {
                return $this->lookupRepository->findByTypeAndValue($typeCode, $value);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_LOOKUPS)
        );
    }
}
```

### Admin Gender Service with Caching

```php
class AdminGenderService extends UserContextAwareService
{
    use CacheableServiceTrait;
    
    public function getAllGenders(): array
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_LOOKUPS,
            'all_genders',
            function() {
                $genders = $this->genderRepository->findAllGenders();
                
                if (!$genders) {
                    throw new ServiceException('Genders not found', Response::HTTP_NOT_FOUND);
                }

                return array_map(function($gender) {
                    return [
                        'id' => $gender->getId(),
                        'gender' => $gender->getGender()
                    ];
                }, $genders);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_LOOKUPS)
        );
    }
}
```

### CMS Preferences Service with Caching

```php
class AdminCmsPreferenceService extends UserContextAwareService
{
    use CacheableServiceTrait;
    
    public function getCmsPreferences(): array
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_CMS_PREFERENCES,
            'cms_preferences',
            function() {
                $preferences = $this->cmsPreferenceRepository->getCmsPreferences();
                
                if (!$preferences) {
                    throw new ServiceException('CMS preferences not found', Response::HTTP_NOT_FOUND);
                }

                return [
                    'id' => $preferences->getId(),
                    'callback_api_key' => $preferences->getCallbackApiKey(),
                    'default_language_id' => $preferences->getDefaultLanguage()?->getId(),
                    'default_language' => $preferences->getDefaultLanguage() ? [
                        'id' => $preferences->getDefaultLanguage()->getId(),
                        'locale' => $preferences->getDefaultLanguage()->getLocale(),
                        'language' => $preferences->getDefaultLanguage()->getLanguage()
                    ] : null,
                    'anonymous_users' => $preferences->getAnonymousUsers(),
                    'firebase_config' => $preferences->getFirebaseConfig()
                ];
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_CMS_PREFERENCES)
        );
    }
}
```

### Admin Service with Cache Invalidation

```php
class AdminPageService extends UserContextAwareService
{
    use CacheableServiceTrait;
    
    public function createPage(string $keyword, ...): Page
    {
        $this->entityManager->beginTransaction();
        
        try {
            // Create page logic...
            $page = new Page();
            // ... set properties
            
            $this->entityManager->persist($page);
            $this->entityManager->flush();
            
            // Set up ACLs...
            
            $this->entityManager->commit();
            
            // Invalidate caches after successful creation
            if ($this->cacheInvalidationService) {
                $this->cacheInvalidationService->invalidatePage($page, 'create');
                $this->cacheInvalidationService->invalidatePermissions();
            }
            
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
        
        return $page;
    }
}
```

## Performance Considerations

### Cache Key Design

- Use consistent naming patterns: `{category}_{entity_id}_{suffix}`
- Include relevant context: user ID, language ID, etc.
- Keep keys reasonably short but descriptive

### TTL Strategy

- **Static data** (lookups): 2 hours
- **Dynamic data** (pages, sections): 1 hour
- **User-specific data**: 30 minutes
- **Admin data**: 15 minutes

### Memory Usage

- Monitor Redis memory usage
- Use appropriate eviction policies
- Consider data compression for large objects

## Monitoring and Debugging

### Cache Statistics

The system tracks detailed statistics:

- **Hit/Miss ratios** per category
- **Cache effectiveness** measurements
- **Invalidation patterns**
- **Performance recommendations**

### Logging

Cache operations are logged with context:

```php
// Cache hit
[GlobalCache] Cache hit {"category":"pages","key":"page_123","user_id":456}

// Cache invalidation
[CacheInvalidation] Page cache invalidated {"page_id":123,"operation":"update"}
```

### Health Monitoring

The system provides health indicators:

- **Excellent** (80%+ hit rate)
- **Good** (60-79% hit rate)
- **Fair** (40-59% hit rate)
- **Poor** (<40% hit rate)

## Best Practices

### Service Integration

1. **Use CacheableServiceTrait** for consistent caching patterns
2. **Implement cache invalidation** in all CUD operations
3. **Use appropriate TTLs** based on data volatility
4. **Cache at the right level** (entity vs. aggregated data)

### Key Management

1. **Use category prefixes** for organization
2. **Include relevant context** in keys
3. **Avoid overly complex keys**
4. **Document key patterns**

### Invalidation

1. **Invalidate immediately** after successful operations
2. **Use cascading invalidation** for related data
3. **Consider user-specific invalidation** for frontend caches
4. **Test invalidation strategies** thoroughly

### Monitoring

1. **Track cache effectiveness** regularly
2. **Monitor memory usage**
3. **Review invalidation patterns**
4. **Act on performance recommendations**

## Troubleshooting

### Common Issues

#### Low Hit Rate
- Review invalidation frequency
- Check TTL settings
- Analyze cache key patterns
- Consider data access patterns

#### High Memory Usage
- Review cached data size
- Implement data compression
- Adjust TTL values
- Use cache size limits

#### Stale Data
- Verify invalidation triggers
- Check cascading invalidation
- Review cache key consistency
- Test invalidation scenarios

### Debug Commands

```bash
# Check Redis connection
redis-cli ping

# Monitor Redis operations
redis-cli monitor

# Check memory usage
redis-cli info memory

# List all keys (development only)
redis-cli keys "*"
```

## Migration and Deployment

### Deployment Considerations

1. **Redis availability** during deployment
2. **Cache warming** strategies
3. **Gradual rollout** of cache changes
4. **Monitoring** during deployment

### Version Compatibility

- Cache keys include version context
- Graceful degradation when cache unavailable
- Backward compatibility for cache structures

## Implemented Services

The following services have been fully integrated with the global cache system:

### Frontend Services
- **PageService** - Caches user-specific page data with automatic invalidation

### Core Services
- **LookupService** - Caches lookup data by type and value
- **JobSchedulerService** - Caches job scheduling data
- **UserPermissionCacheService** - Enhanced with global cache integration

### Admin Services (Complete Implementation)
- **AdminUserService** - User lists, individual users, groups, roles with invalidation
- **AdminPageService** - Page management with cache invalidation on CUD operations
- **AdminSectionService** - Section data with automatic cache invalidation
- **AdminGroupService** - Group lists and details with invalidation
- **AdminRoleService** - Role management with cache invalidation
- **AdminGenderService** - Gender lookup data caching
- **AdminAssetService** - Asset lists with search and folder filtering
- **AdminScheduledJobService** - Job management with caching
- **AdminActionService** - Action management with caching
- **AdminCmsPreferenceService** - CMS configuration caching

### Cache Categories Implemented
All predefined cache categories are now actively used:

- ✅ `pages` - Page entities and their data
- ✅ `users` - User entities and profiles  
- ✅ `sections` - Section entities and hierarchies
- ✅ `languages` - Language entities and translations
- ✅ `genders` - Gender entities
- ✅ `groups` - Group entities and memberships
- ✅ `roles` - Role entities and permissions
- ✅ `permissions` - Permission entities and ACLs
- ✅ `lookups` - Lookup data and constants
- ✅ `assets` - Asset entities and metadata
- ✅ `frontend_user` - User-specific frontend data
- ✅ `cms_preferences` - CMS configuration preferences
- ✅ `scheduled_jobs` - Scheduled job entities
- ✅ `actions` - Actions for dataTables

### Invalidation Triggers Implemented
The system automatically invalidates caches when:

1. ✅ **Pages**: Created, updated, deleted, or sections reordered
2. ✅ **Users**: Created, updated, deleted, or permissions changed
3. ✅ **Sections**: Created, updated, deleted, or moved between pages
4. ✅ **Languages**: Created, updated, or deleted
5. ✅ **Groups**: Created, updated, deleted, or ACLs changed
6. ✅ **Roles**: Created, updated, deleted, or permissions changed
7. ✅ **Permissions**: Any permission changes
8. ✅ **User Actions**: Any action performed by a user invalidates their frontend caches
9. ✅ **Assets**: Created, updated, or deleted
10. ✅ **Scheduled Jobs**: Created, updated, or deleted
11. ✅ **Actions**: Created, updated, or deleted
12. ✅ **CMS Preferences**: Updated or modified

## Future Enhancements

### Planned Features

1. **Cache warming** strategies
2. **Distributed cache invalidation**
3. **Advanced analytics**
4. **Cache preloading**
5. **Multi-tier caching**

### Performance Optimizations

1. **Compression** for large objects
2. **Batch operations** for related cache items
3. **Predictive caching** based on usage patterns
4. **Cache partitioning** for better scalability
