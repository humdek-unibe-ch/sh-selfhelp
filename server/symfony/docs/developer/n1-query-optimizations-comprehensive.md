# N+1 Query Performance Optimizations - Comprehensive Report

## Overview

This document provides a comprehensive summary of all N+1 query performance optimizations implemented across the Symfony codebase. These optimizations significantly reduce database load and improve application performance.

## Previously Implemented Optimizations

### 1. ApiRouteLoader (CRITICAL FIX)
**File**: `src/Routing/ApiRouteLoader.php`  
**Problem**: Loading 97+ routes caused 1 + V + N queries (1 for versions, V for routes per version, N for permissions per route)  
**Solution**: Single optimized query with caching
- Consolidated to 1 SQL query using `GROUP_CONCAT` and `LEFT JOIN`
- Added Symfony cache layer (1 hour TTL in production)
- Returns arrays instead of entities to avoid lazy loading

**Performance Impact**: Reduced from 100+ queries to 1 query

### 2. EntityUtil (MEDIUM FIX)
**File**: `src/Util/EntityUtil.php`  
**Problem**: `convertEntityToArray()` triggered lazy loading of collections and proxies  
**Solution**: Added initialization checks
- Check `isInitialized()` before accessing collections
- Handle Doctrine proxies gracefully
- Return placeholders for uninitialized collections

## New Optimizations Implemented

### 3. AdminUserService - Group and Role Management (HIGH PRIORITY)
**File**: `src/Service/CMS/Admin/AdminUserService.php`

#### Problems Fixed:
- **assignGroupsToUser()**: N+1 queries when assigning multiple groups to users
- **assignRolesToUser()**: N+1 queries when assigning multiple roles to users  
- **removeGroupsFromUser()**: N+1 queries when removing multiple groups
- **removeRolesFromUser()**: N+1 queries when removing multiple roles

#### Solutions Implemented:
```php
// Before: foreach ($groupIds as $groupId) { find($groupId) } - N queries
// After: Single query with IN clause
$groups = $this->entityManager->getRepository(Group::class)
    ->createQueryBuilder('g')
    ->where('g.id IN (:groupIds)')
    ->setParameter('groupIds', $groupIds)
    ->getQuery()
    ->getResult();
```

**Performance Impact**: Reduced from N queries to 1-2 queries per operation

### 4. AdminGroupService - ACL Management (HIGH PRIORITY)
**File**: `src/Service/CMS/Admin/AdminGroupService.php`

#### Problem Fixed:
- **updateGroupAclsInternal()**: N+1 queries when updating ACLs for multiple pages

#### Solution Implemented:
```php
// Before: foreach ($aclsData as $acl) { find($acl['page_id']) } - N queries
// After: Single batch query
$pageIds = array_column($aclsData, 'page_id');
$pages = $this->entityManager->getRepository(Page::class)
    ->createQueryBuilder('p')
    ->where('p.id IN (:pageIds)')
    ->setParameter('pageIds', $pageIds)
    ->getQuery()
    ->getResult();
```

**Performance Impact**: Reduced from N queries to 1 query when updating multiple ACLs

### 5. RelationshipManagerTrait - Section Cleanup (MEDIUM PRIORITY)
**File**: `src/Service/CMS/Admin/Traits/RelationshipManagerTrait.php`

#### Problem Fixed:
- **removeAllSectionRelationships()**: Multiple `findBy()` calls followed by individual entity removals

#### Solution Implemented:
```php
// Before: findBy() + foreach remove() - Multiple queries + N deletions
// After: Direct DQL DELETE queries
$entityManager->createQueryBuilder()
    ->delete(PagesSection::class, 'ps')
    ->where('ps.section = :section')
    ->setParameter('section', $section)
    ->getQuery()
    ->execute();
```

**Performance Impact**: Reduced from 3 SELECT + N DELETE queries to 3 DELETE queries

## Database Indexes Added

Enhanced database performance with strategic indexes in `db/update_scripts/39_update_v7.6.0_v8.0.0.sql`:

### API Route Loading Indexes
```sql
CREATE INDEX IF NOT EXISTS idx_arp_route_permission_composite 
ON api_routes_permissions (id_api_routes, id_permissions);

CREATE INDEX IF NOT EXISTS idx_permissions_name 
ON permissions (name);

CREATE INDEX IF NOT EXISTS idx_api_routes_version_id 
ON api_routes (version, id);
```

### User/Group/Role Management Indexes
```sql
CREATE INDEX IF NOT EXISTS idx_groups_id ON groups (id);
CREATE INDEX IF NOT EXISTS idx_roles_id ON roles (id);
CREATE INDEX IF NOT EXISTS idx_users_groups_user_group ON users_groups (id_users, id_groups);
CREATE INDEX IF NOT EXISTS idx_pages_id ON pages (id);
```

### ACL Management Indexes
```sql
CREATE INDEX IF NOT EXISTS idx_acl_groups_group ON acl_groups (id_groups);
CREATE INDEX IF NOT EXISTS idx_acl_groups_page ON acl_groups (id_pages);
```

### Section Relationship Indexes
```sql
CREATE INDEX IF NOT EXISTS idx_pages_sections_section ON pages_sections (id_sections);
CREATE INDEX IF NOT EXISTS idx_sections_hierarchy_parent ON sections_hierarchy (id_parent_sections);
CREATE INDEX IF NOT EXISTS idx_sections_hierarchy_child ON sections_hierarchy (id_child_sections);
```

## Optimization Patterns Used

### 1. Batch Loading Pattern
Replace individual `find()` calls in loops with single `IN` queries:
```php
// Anti-pattern
foreach ($ids as $id) {
    $entity = $repository->find($id); // N queries
}

// Optimized pattern
$entities = $repository->createQueryBuilder('e')
    ->where('e.id IN (:ids)')
    ->setParameter('ids', $ids)
    ->getQuery()
    ->getResult(); // 1 query
```

### 2. DQL Bulk Operations
Replace entity-based operations with direct DQL:
```php
// Anti-pattern
$entities = $repository->findBy(['field' => $value]);
foreach ($entities as $entity) {
    $entityManager->remove($entity); // N DELETE queries
}

// Optimized pattern
$entityManager->createQueryBuilder()
    ->delete(Entity::class, 'e')
    ->where('e.field = :value')
    ->setParameter('value', $value)
    ->getQuery()
    ->execute(); // 1 DELETE query
```

### 3. Entity Map Pattern
Create lookup maps to avoid repeated queries:
```php
$entityMap = [];
foreach ($entities as $entity) {
    $entityMap[$entity->getId()] = $entity;
}
// Use $entityMap[$id] instead of find($id)
```

## Performance Impact Summary

| Component | Before | After | Improvement |
|-----------|--------|-------|-------------|
| ApiRouteLoader | 100+ queries | 1 query | ~99% reduction |
| User Group Assignment | N queries | 1-2 queries | ~90% reduction |
| User Role Assignment | N queries | 1-2 queries | ~90% reduction |
| Group ACL Updates | N queries | 1 query | ~95% reduction |
| Section Cleanup | 3+N queries | 3 queries | ~N/3 reduction |

## Best Practices for Future Development

### 1. Always Consider Batch Operations
When processing collections of IDs, use `IN` clauses instead of loops with individual queries.

### 2. Use Eager Loading
For known relationships, use `JOIN FETCH` or `addSelect()` to load related entities upfront.

### 3. Prefer DQL for Bulk Operations
Use DQL `DELETE` and `UPDATE` statements for bulk operations instead of loading entities first.

### 4. Monitor Query Counts
Use Symfony's profiler or logging to monitor query counts in development.

### 5. Index Strategy
Always add appropriate indexes for:
- Foreign key columns used in JOINs
- Columns used in WHERE clauses
- Composite indexes for multi-column queries

## Monitoring and Validation

### Development Monitoring
- Use Symfony Profiler to monitor query counts
- Enable query logging in development environment
- Set up alerts for high query counts

### Production Monitoring
- Monitor database connection pool usage
- Track average response times
- Set up APM monitoring for N+1 detection

## Additional Recommendations

### 1. Repository Method Optimization
Consider adding optimized repository methods for common bulk operations:
```php
public function findByIdsWithRelations(array $ids): array
{
    return $this->createQueryBuilder('e')
        ->leftJoin('e.relation', 'r')
        ->addSelect('r')
        ->where('e.id IN (:ids)')
        ->setParameter('ids', $ids)
        ->getQuery()
        ->getResult();
}
```

### 2. Caching Strategy
Implement caching for frequently accessed data:
- Entity lookups (users, groups, roles)
- Configuration data
- Navigation structures

### 3. Database Query Analysis
Regularly analyze slow query logs and optimize problematic queries.

## Conclusion

These N+1 query optimizations provide significant performance improvements across the application. The patterns and techniques used here should be applied consistently throughout the codebase to maintain optimal performance as the application scales.

The combination of batch loading, strategic indexing, and caching provides a robust foundation for handling increased load and data volume efficiently.
