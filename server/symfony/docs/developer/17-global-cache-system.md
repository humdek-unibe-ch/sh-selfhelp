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

## Entity Scope Invalidation System

### Overview

The ReworkedCacheService includes a powerful **Entity Scope Invalidation System** that allows cache invalidation based on specific entity primary keys. This system provides O(1) invalidation across all categories and cache types when any entity changes.

### Key Benefits

1. **Cross-Category Invalidation**: One entity change can invalidate cache across multiple categories
2. **O(1) Performance**: Uses generation counters, no scanning of cache entries required
3. **Precise Targeting**: Only invalidates cache that actually depends on the changed entity
4. **Automatic Integration**: Works seamlessly with existing cache operations

### Supported Entity Scopes

```php
// Core entity scopes - each represents a primary key dependency
ReworkedCacheService::ENTITY_SCOPE_PAGE           // page_id
ReworkedCacheService::ENTITY_SCOPE_PAGE_KEYWORD   // page_keyword
ReworkedCacheService::ENTITY_SCOPE_SECTION        // section_id
ReworkedCacheService::ENTITY_SCOPE_USER           // user_id
ReworkedCacheService::ENTITY_SCOPE_GROUP          // group_id
ReworkedCacheService::ENTITY_SCOPE_ROLE           // role_id
ReworkedCacheService::ENTITY_SCOPE_LANGUAGE       // language_id
ReworkedCacheService::ENTITY_SCOPE_ASSET          // asset_id
ReworkedCacheService::ENTITY_SCOPE_ACTION         // action_id
ReworkedCacheService::ENTITY_SCOPE_SCHEDULED_JOB  // scheduled_job_id
ReworkedCacheService::ENTITY_SCOPE_FIELD          // field_id
ReworkedCacheService::ENTITY_SCOPE_LOOKUP         // lookup_id
ReworkedCacheService::ENTITY_SCOPE_PERMISSION     // permission_id
ReworkedCacheService::ENTITY_SCOPE_CMS_PREFERENCE // cms_preference_id
```

### Basic Entity Scope Usage

```php
// Caching with entity dependencies
$pageContent = $this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
    ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageId)
    ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
    ->getItem('page_content', fn() => $this->buildPageContent($pageId, $userId));

// When the page changes, invalidate ALL cache depending on this page
$this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_PAGE, $pageId);

// When user permissions change, invalidate ALL cache depending on this user
$this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
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

// ENTITY SCOPE INVALIDATION - The most powerful invalidation
// Invalidates ALL cache across ALL categories that depends on this entity
$this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
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

## Multi-Entity Relationship Invalidation Patterns

### The Core Problem

When entities have relationships (User ↔ Group, User ↔ Role, Page ↔ Section), updating one entity often affects cache entries that depend on related entities. The ReworkedCacheService provides sophisticated patterns to handle these scenarios efficiently.

### Critical Rule: Entity Scope + List Invalidation

**MEMORY RULE**: When using entity scope invalidation (`invalidateEntityScope`), you **MUST** also invalidate all lists in the same category by calling `invalidateAllListsInCategory` for that category. This ensures both entity-specific cache and category lists are properly cleared.

```php
// ✅ CORRECT: Entity scope + list invalidation
$this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);

$this->cache
    ->withCategory(ReworkedCacheService::CATEGORY_USERS)
    ->invalidateAllListsInCategory();
```

### Multi-Entity Invalidation Strategies

#### Strategy 1: Primary Entity Changes
When a primary entity changes, invalidate both the entity scope AND related category lists:

```php
public function updateUser(int $userId, array $userData): array 
{
    // Update database
    $user = $this->repository->update($userId, $userData);
    
    // 1. Invalidate ALL cache that depends on this user (cross-category)
    $this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
    
    // 2. Invalidate user category lists
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->invalidateAllListsInCategory();
    
    return $this->formatUser($user);
}
```

#### Strategy 2: Relationship Changes
When relationships change (adding/removing groups from user), invalidate multiple entity scopes:

```php
public function updateUserGroups(int $userId, array $groupIds): array 
{
    // Update database relationships
    $this->updateUserGroupRelationships($userId, $groupIds);
    
    // 1. Invalidate user-specific cache
    $this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
    
    // 2. Invalidate affected groups
    if (!empty($groupIds)) {
        $this->cache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_GROUP, $groupIds);
    }
    
    // 3. Invalidate lists in both categories
    $this->cache->withCategory(ReworkedCacheService::CATEGORY_USERS)->invalidateAllListsInCategory();
    $this->cache->withCategory(ReworkedCacheService::CATEGORY_GROUPS)->invalidateAllListsInCategory();
    
    return $this->getUserGroups($userId);
}
```

#### Strategy 3: Bulk Operations
When performing bulk operations affecting multiple entities:

```php
public function bulkUpdateUsers(array $userIds, array $changes): array 
{
    // Update database
    $this->repository->bulkUpdate($userIds, $changes);
    
    // Bulk invalidate all affected users
    $this->cache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_USER, $userIds);
    
    // Invalidate user lists
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->invalidateAllListsInCategory();
    
    return ['updated_count' => count($userIds)];
}
```

### Invalidation Strategy Patterns

#### CREATE Operations
For create operations, only invalidate lists (new item doesn't exist in cache):

```php
public function createUser(array $data): array 
{
    $user = $this->repository->create($data);
    
    // Only invalidate lists - no entity scope needed for new entities
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->invalidateAllListsInCategory();
    
    return $this->formatUser($user);
}
```

#### UPDATE Operations
For updates, use entity scope invalidation + list invalidation:

```php
public function updateUser(int $userId, array $data): array 
{
    $user = $this->repository->update($userId, $data);
    
    // Entity scope invalidation (cross-category)
    $this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
    
    // Category list invalidation
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->invalidateAllListsInCategory();
    
    return $this->formatUser($user);
}
```

#### DELETE Operations
For deletes, use entity scope invalidation + list invalidation:

```php
public function deleteUser(int $userId): bool 
{
    $this->repository->delete($userId);
    
    // Entity scope invalidation (cross-category)
    $this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
    
    // Category list invalidation
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->invalidateAllListsInCategory();
    
    return true;
}
```

## Real-World Implementation Examples

### AdminUserService: Complete Cache Integration

The AdminUserService demonstrates comprehensive cache integration with entity scope invalidation, multi-entity relationships, and proper list management. Here are the key patterns:

#### Entity-Scoped Caching with Dependencies

```php
// Caching user data with entity scope dependency
public function getUserById(int $userId): array
{
    return $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
        ->getItem(
            "user_{$userId}",
            fn() => $this->formatUserForDetail($this->findUserOrThrow($userId))
        );
}

// Caching user groups with entity scope dependency
public function getUserGroups(int $userId): array
{
    return $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
        ->getItem(
            "user_groups_{$userId}",
            fn() => $this->fetchUserGroups($userId)
        );
}
```

#### Multi-Entity Relationship Invalidation

```php
// User update with relationship changes
public function updateUser(int $userId, array $userData): array
{
    return $this->executeInTransaction(function () use ($userId, $userData) {
        $user = $this->findUserOrThrow($userId);
        
        // Update user and relationships
        $this->updateUserFromData($user, $userData);
        $this->handleUserRelationships($user, $userData);
        $this->entityManager->flush();
        
        // Get fresh data BEFORE invalidating caches
        $result = $this->formatUserForDetail($user, true);
        
        // 1. Primary invalidation - user entity scope
        $this->invalidateUserCaches($userId);
        
        // 2. Conditional relationship invalidation
        if (isset($userData['group_ids']) && !empty($userData['group_ids'])) {
            $groupCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_GROUPS);
            $groupCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_GROUP, $userData['group_ids']);
            $groupCache->invalidateAllListsInCategory();
        }
        
        if (isset($userData['role_ids']) && !empty($userData['role_ids'])) {
            $roleCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_ROLES);
            $roleCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_ROLE, $userData['role_ids']);
            $roleCache->invalidateAllListsInCategory();
        }
        
        return $result;
    });
}

// Dedicated user cache invalidation method
private function invalidateUserCaches(int $userId): void
{
    // Invalidate user lists
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->invalidateAllListsInCategory();
    
    // Invalidate ALL cache that depends on this user (cross-category)
    $this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
}
```

#### Relationship-Specific Operations

```php
// Adding groups to user - affects both user and group caches
public function addGroupsToUser(int $userId, array $groupIds): array
{
    return $this->executeInTransaction(function () use ($userId, $groupIds) {
        $user = $this->findUserOrThrow($userId);
        
        $this->assignGroupsToUser($user, $groupIds, false);
        $this->entityManager->flush();
        
        // Get fresh data before invalidating caches
        $result = $this->fetchUserGroupsFromEntity($user);
        
        // Invalidate both user and group caches
        $this->invalidateUserGroupCaches($userId, $groupIds);
        
        return $result;
    });
}

// Specialized invalidation for user-group relationships
private function invalidateUserGroupCaches(int $userId, array $groupIds): void
{
    // 1. Invalidate user's group cache specifically
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
        ->invalidateItemAndLists("user_groups_{$userId}");
    
    // 2. Invalidate affected groups
    if (!empty($groupIds)) {
        $groupCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_GROUPS);
        $groupCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_GROUP, $groupIds);
        $groupCache->invalidateAllListsInCategory();
    }
}
```

#### List Caching with Pagination

```php
// Paginated user lists with complex cache keys
public function getUsers(
    int $page = 1,
    int $pageSize = 20,
    ?string $search = null,
    ?string $sort = null,
    ?string $sortDirection = 'asc'
): array {
    [$page, $pageSize, $sortDirection] = $this->validatePaginationParams($page, $pageSize, $sortDirection);
    
    // Build cache key including all parameters
    $cacheKey = $this->buildCacheKey('users_list', $page, $pageSize, $search, $sort, $sortDirection);
    
    return $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->getList(
            $cacheKey,
            fn() => $this->fetchUsersFromDatabase($page, $pageSize, $search, $sort, $sortDirection)
        );
}

// Cache key building for complex parameters
private function buildCacheKey(string $prefix, ...$params): string
{
    $hashableParams = array_slice($params, 2); // Skip page and pageSize for hash
    return $prefix . '_' . $params[0] . '_' . $params[1] . '_' . md5(implode('_', $hashableParams));
}
```

### Cache Invalidation Best Practices from AdminUserService

#### 1. **Transaction-Scoped Cache Operations**
```php
// Always get fresh data BEFORE invalidating caches
$result = $this->formatUserForDetail($user, true);

// Then invalidate caches
$this->invalidateUserCaches($userId);

return $result;
```

#### 2. **Conditional Relationship Invalidation**
```php
// Only invalidate related entities if they were actually modified
if (isset($userData['group_ids']) && is_array($userData['group_ids']) && !empty($userData['group_ids'])) {
    $groupCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_GROUPS);
    $groupCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_GROUP, $userData['group_ids']);
    $groupCache->invalidateAllListsInCategory();
}
```

#### 3. **Dedicated Cache Invalidation Methods**
```php
// Centralized cache invalidation logic
private function invalidateUserCaches(int $userId): void
{
    // List invalidation
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->invalidateAllListsInCategory();
    
    // Entity scope invalidation (cross-category)
    $this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
}

// Relationship-specific invalidation
private function invalidateUserGroupCaches(int $userId, array $groupIds): void
{
    // Specific item + lists
    $this->cache
        ->withCategory(ReworkedCacheService::CATEGORY_USERS)
        ->withEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId)
        ->invalidateItemAndLists("user_groups_{$userId}");
    
    // Related entities
    if (!empty($groupIds)) {
        $groupCache = $this->cache->withCategory(ReworkedCacheService::CATEGORY_GROUPS);
        $groupCache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_GROUP, $groupIds);
        $groupCache->invalidateAllListsInCategory();
    }
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

## Core Cache Invalidation Principles - THE CACHE CORE

### 🎯 **THE GOLDEN RULES**

These are the fundamental principles that govern all cache invalidation in the ReworkedCacheService system:

#### 1. **Entity Scope + List Invalidation Rule**
```php
// ✅ ALWAYS: When using entity scope invalidation, also invalidate lists
$this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
$this->cache->withCategory(ReworkedCacheService::CATEGORY_USERS)->invalidateAllListsInCategory();
```

#### 2. **Operation-Based Invalidation Strategy**
- **CREATE**: Only invalidate lists (new entities don't exist in cache)
- **UPDATE**: Entity scope invalidation + list invalidation
- **DELETE**: Entity scope invalidation + list invalidation

#### 3. **Multi-Entity Relationship Invalidation**
When relationships change, invalidate ALL affected entities and their categories:
```php
// User-Group relationship change
$this->cache->invalidateEntityScope(ReworkedCacheService::ENTITY_SCOPE_USER, $userId);
$this->cache->invalidateEntityScopes(ReworkedCacheService::ENTITY_SCOPE_GROUP, $groupIds);

// Invalidate lists in BOTH categories
$this->cache->withCategory(ReworkedCacheService::CATEGORY_USERS)->invalidateAllListsInCategory();
$this->cache->withCategory(ReworkedCacheService::CATEGORY_GROUPS)->invalidateAllListsInCategory();
```

#### 4. **Transaction-Scoped Cache Operations**
Always get fresh data BEFORE invalidating caches:
```php
// ✅ CORRECT ORDER
$result = $this->formatUserForDetail($user, true);  // Fresh data first
$this->invalidateUserCaches($userId);               // Then invalidate
return $result;
```

#### 5. **Conditional Relationship Invalidation**
Only invalidate related entities if they were actually modified:
```php
if (isset($userData['group_ids']) && !empty($userData['group_ids'])) {
    // Only then invalidate group caches
}
```

### 🚀 **Cache Architecture Benefits**

#### Performance Advantages
- **O(1) Invalidation**: Generation counters eliminate cache scanning/deletion
- **Cross-Category Power**: Entity scope invalidation works across all categories
- **Memory Efficient**: Old cache entries become inaccessible without deletion
- **Tag-Based Precision**: Fine-grained invalidation using cache tags

#### Developer Experience
- **Builder Pattern**: Clean, chainable API with immutable configuration
- **Self-Documenting**: Category constants and method names make intent clear
- **Automatic Statistics**: Built-in monitoring without separate services
- **Error Resilience**: Cache failures don't break application functionality

#### Advanced Features
- **Generation-Based Keys**: Cache keys include generation counters for instant invalidation
- **Entity Scope Dependencies**: Cache entries can depend on multiple entities
- **User Scoping**: User-specific cache namespacing with global kill switches
- **Dual Types**: Separate handling for lists (collections) vs items (entities)

### 📊 **Cache Key Architecture**

The cache system uses sophisticated key generation that includes:
- **Category Generation**: For category-wide invalidation
- **User Generation**: For user-specific invalidation
- **Entity Scope Generations**: For entity-dependent cache invalidation
- **Cache Type**: Distinguishes between lists and items

Example cache key format:
```
cms-users-g1-u123-g2-epage_id_456_g3-euser_id_123_g5-item-user_profile
```

This represents:
- `cms`: Prefix
- `users`: Category  
- `g1`: Category generation
- `u123-g2`: User 123 with generation 2
- `epage_id_456_g3`: Page entity dependency
- `euser_id_123_g5`: User entity dependency
- `item`: Cache type
- `user_profile`: Cache key

### 🎯 **Implementation Checklist**

When implementing cache in a new service:

1. ✅ **Inject ReworkedCacheService** in constructor
2. ✅ **Use appropriate category constants** (CATEGORY_USERS, CATEGORY_GROUPS, etc.)
3. ✅ **Apply entity scopes** for dependencies (`withEntityScope()`)
4. ✅ **Follow CRUD invalidation patterns** (create=lists, update/delete=entity+lists)
5. ✅ **Handle relationship changes** (invalidate all affected entities)
6. ✅ **Get fresh data before invalidation** in transactions
7. ✅ **Create dedicated invalidation methods** for complex scenarios
8. ✅ **Use conditional invalidation** for optional relationships

The ReworkedCacheService represents a significant advancement in cache architecture, providing both superior performance and developer experience through generation-based invalidation, entity scope dependencies, and comprehensive multi-entity relationship handling.