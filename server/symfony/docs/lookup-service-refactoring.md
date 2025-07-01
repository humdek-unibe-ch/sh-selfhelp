# LookupService Refactoring

## Overview

This document describes the refactoring of lookup data access from direct repository usage to service-based access, following Symfony best practices.

## Problem Statement

Previously, services throughout the application were directly injecting and using `LookupRepository`, which violated the separation of concerns principle and made the codebase harder to maintain.

## Solution

Refactored the `LookupService` to be the primary interface for all lookup operations, encapsulating the repository logic and providing:

1. **Centralized lookup access** - Single point of entry for all lookup operations
2. **Business logic layer** - Can add validation, caching, logging in the future
3. **Consistent interface** - Standardized methods for common lookup patterns
4. **Better testability** - Easier to mock service than repository

## Changes Made

### 1. Enhanced LookupService

Added all repository methods to `LookupService`:

- `findByTypeAndValue(string $typeCode, string $lookupValue): ?Lookup`
- `findByTypeAndCode(string $typeCode, string $lookupCode): ?Lookup`
- `getDefaultUserType(): ?Lookup`
- `getLookups(string $typeCode): array`
- `getLookupIdByValue(string $typeCode, string $lookupValue): ?int`
- `getLookupIdByCode(string $typeCode, string $lookupCode): ?int`
- `getAllLookups(): array`
- `existsByTypeAndValue(string $typeCode, string $lookupValue): bool`
- `existsByTypeAndCode(string $typeCode, string $lookupCode): bool`
- `getLookupValueByCode(string $typeCode, string $lookupCode): ?string`
- `findById(int $id): ?Lookup`
- `findOneBy(array $criteria): ?Lookup`

### 2. Updated Services

Replaced `LookupRepository` injection with `LookupService` in:

- `TransactionService`
- `AdminUserService`
- `AdminPageService`
- `PageService` (Frontend)

### 3. Updated Tests

Modified `AdminUserServiceTest` to use the service container for dependency injection instead of manual construction.

## Best Practices Applied

### Repository vs Service Usage

**Use Services as the primary interface:**
- Controllers should inject services, not repositories
- Services should inject other services, not repositories directly
- Repositories should only be used within their corresponding services

**Benefits:**
- **Business Logic Layer**: Services can add validation, caching, transaction management
- **Abstraction**: Services can combine multiple repositories or add complex logic
- **Consistency**: Single point of access for domain operations
- **Testability**: Easier to mock services than repositories

### Service Design Patterns

1. **Encapsulation**: Repository is private to the service
2. **Delegation**: Service methods delegate to repository methods
3. **Enhancement**: Service can add business logic around repository calls
4. **Constants**: Lookup type and code constants are co-located with methods

## Usage Examples

### Before (Bad Practice)
```php
// Directly injecting repository
public function __construct(
    private readonly LookupRepository $lookupRepository
) {}

// Using repository directly
$userType = $this->lookupRepository->findOneBy([
    'typeCode' => 'userTypes',
    'lookupCode' => 'user'
]);
```

### After (Best Practice)
```php
// Injecting service
public function __construct(
    private readonly LookupService $lookupService
) {}

// Using service method with constants
$userType = $this->lookupService->getDefaultUserType();
// or
$userType = $this->lookupService->findByTypeAndCode(
    LookupService::USER_TYPES,
    LookupService::USER_TYPES_USER
);
```

## Migration Guidelines

When refactoring other services:

1. **Replace repository injection** with service injection
2. **Update method calls** to use service methods instead of repository methods
3. **Use service constants** instead of magic strings
4. **Test thoroughly** to ensure functionality remains the same
5. **Update dependency injection** in tests to use the container

## Future Enhancements

With the service layer in place, we can easily add:

- **Caching**: Cache frequently accessed lookups
- **Validation**: Validate lookup operations
- **Logging**: Log lookup access for audit trails
- **Performance monitoring**: Track lookup performance
- **Multi-tenancy**: Support for tenant-specific lookups

## Summary

This refactoring establishes a clean architecture where:
- **Repositories** handle data access
- **Services** handle business logic and provide clean interfaces
- **Controllers** use services for domain operations
- **Tests** use the container for proper dependency injection

This pattern should be followed for all entity operations throughout the application. 