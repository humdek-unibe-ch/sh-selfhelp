# Caching and Performance

## Overview

SelfHelp uses APCu (Alternative PHP Cache User) as its primary caching mechanism. The system implements a simple but effective caching strategy focused on database query results and frequently accessed lookup data. Unlike complex caching systems, SelfHelp uses direct APCu operations with a straightforward key-based approach.

## Cache Architecture

### Simple APCu-Based Caching

The caching system is built around direct APCu operations without complex abstractions:

```php
class Cache {
    // Direct APCu operations
    public function get($key) {
        return apcu_fetch($key);
    }

    public function set($key, $value, $ttl = 0) {
        return apcu_store($key, $value, $ttl);
    }

    public function clear_cache($type = null, $id = null) {
        if (!$type) {
            return apcu_clear_cache();
        }
        // Pattern-based clearing using iterators
    }
}
```

### Cache Key Structure

Keys follow a simple prefixed pattern:
```
selfhelp-{TYPE}-{ID}-{PARAMETERS}
```

Examples:
- `selfhelp-LOOKUPS-get_lookup_id_by_value-user_type`
- `selfhelp-LOOKUPS-get_lookup_id_by_code-admin-user_type`

### Cache Types

The system defines specific cache categories:

```php
const CACHE_TYPE_PAGES = 'CACHE_PAGES';
const CACHE_TYPE_SECTIONS = 'CACHE_SECTIONS';
const CACHE_TYPE_FIELDS = 'CACHE_FIELDS';
const CACHE_TYPE_STYLES = 'CACHE_STYLES';
const CACHE_TYPE_HOOKS = 'HOOKS';
const CACHE_TYPE_USER_INPUT = 'USER_INPUT';
const CACHE_TYPE_CONDITION = 'CONDITION';
const CACHE_TYPE_LOOKUPS = 'LOOKUPS';
```

## Implementation Details

### Lookup Caching

The most heavily cached operations are lookup table queries:

```php
public function get_lookup_id_by_value($type, $value) {
    $key = $this->generate_key(self::CACHE_TYPE_LOOKUPS, $value,
                              [__FUNCTION__, $type]);

    $cached = $this->get($key);
    if ($cached !== false) {
        return $cached;
    }

    // Database query
    $result = $this->query_db_first(
        'SELECT id FROM lookups WHERE lookup_value = :value AND type_code = :type',
        [':value' => $value, ':type_code' => $type]
    );

    $value = $result ? $result['id'] : null;
    $this->set($key, $value);

    return $value;
}
```

### Cache Key Generation

Simple key generation with parameters:

```php
public function generate_key($type, $id, $extra_params = []) {
    $key = PROJECT_NAME . '-' . $type . '-' . $id;
    if (!empty($extra_params)) {
        $key .= '-' . implode('-', $extra_params);
    }
    return $key;
}
```

### Cache Clearing Strategy

Pattern-based cache invalidation:

```php
public function clear_cache($type = null, $id = null) {
    if (!$type) {
        return apcu_clear_cache();
    }

    $pattern = PROJECT_NAME . '-' . $type;
    if ($id) {
        $pattern .= '-' . $id;
    }

    $iterator = new APCUIterator('#^' . preg_quote($pattern) . '#', APC_ITER_KEY);
    foreach ($iterator as $entry) {
        apcu_delete($entry['key']);
    }
}
```

## Performance Characteristics

### Cache Hit Scenarios

1. **Lookup Values**: Frequently accessed, cached indefinitely
2. **User Permissions**: Cached per user, cleared on permission changes
3. **Configuration Data**: Cached until manually cleared

### Cache Miss Scenarios

1. **New Lookups**: First access to new lookup values
2. **Cache Clears**: Administrative actions clearing caches
3. **Memory Pressure**: APCu evicting entries due to memory limits

### Transaction Rollback

Automatic cache clearing on database transaction rollback:

```php
public function rollback() {
    $this->dbh->rollback();
    $this->cache->clear_cache(); // Clear all caches on rollback
}
```

## Configuration

### APCu Settings

Required APCu configuration in `php.ini`:

```ini
apcu.enabled=1
apcu.shm_size=256M
apcu.enable_cli=1
```

### Memory Management

- **Shared Memory**: 256MB allocated for cache
- **No TTL by Default**: Most entries cached indefinitely until cleared
- **CLI Access**: Cache available in command-line scripts

## Monitoring

### Basic Cache Statistics

```php
// Get APCu statistics
$cache_info = apcu_cache_info();
$sma_info = apcu_sma_info();

$stats = [
    'hits' => $cache_info['num_hits'],
    'misses' => $cache_info['num_misses'],
    'hit_rate' => $cache_info['num_hits'] /
                 ($cache_info['num_hits'] + $cache_info['num_misses']),
    'memory_used' => $cache_info['mem_size'],
    'memory_available' => $sma_info['avail_mem'],
    'entries' => $cache_info['num_entries']
];
```

### Health Checks

Simple cache health monitoring:

```php
function check_cache_health() {
    // Test basic cache operations
    $test_key = 'health_check_' . time();
    $test_value = 'ok';

    // Test set
    if (!apcu_store($test_key, $test_value, 60)) {
        return 'Cache write failed';
    }

    // Test get
    if (apcu_fetch($test_key) !== $test_value) {
        return 'Cache read failed';
    }

    // Clean up
    apcu_delete($test_key);

    return 'Cache operational';
}
```

## Limitations and Considerations

### Simple Design Benefits

1. **Low Overhead**: Direct APCu calls with minimal abstraction
2. **Predictable Behavior**: Simple key structure and clearing patterns
3. **Memory Efficient**: No complex object serialization overhead
4. **Easy Debugging**: Direct APCu inspection and manipulation

### Trade-offs

1. **No Advanced Features**: No cache tagging, complex invalidation rules
2. **Manual Key Management**: Developers must manage cache keys manually
3. **No Compression**: Data stored as-is, no automatic compression
4. **Memory Limits**: Subject to APCu memory constraints

### Best Practices

1. **Consistent Key Naming**: Use descriptive, consistent key patterns
2. **Appropriate TTL**: Set TTL for time-sensitive data
3. **Clear on Changes**: Clear relevant caches when data changes
4. **Monitor Usage**: Regularly check cache hit rates and memory usage

## Troubleshooting

### Common Issues

#### High Cache Miss Rate

```php
// Check if cache is being cleared too frequently
$stats = apcu_cache_info();
$hit_rate = $stats['num_hits'] / ($stats['num_hits'] + $stats['num_misses']);

if ($hit_rate < 0.5) {
    // Investigate cache clearing patterns
    // Check for memory pressure
    // Review TTL settings
}
```

#### Memory Issues

```php
// Check memory usage
$sma_info = apcu_sma_info();
$used_percentage = ($sma_info['seg_size'] - $sma_info['avail_mem']) /
                   $sma_info['seg_size'];

if ($used_percentage > 0.8) {
    // Consider increasing apcu.shm_size
    // Implement selective cache clearing
    // Review cached data size
}
```

#### Stale Data Problems

```php
// Force cache refresh for specific data
public function refresh_cache_entry($type, $id) {
    $this->clear_cache($type, $id);
    // Reload data will be cached on next access
}
```

### Debug Commands

```bash
# Check APCu status
php -r "var_dump(apcu_cache_info());"

# Clear all caches
php -r "apcu_clear_cache();"

# List cache entries matching pattern
php -r "
$iterator = new APCUIterator('#^selfhelp-#', APC_ITER_KEY);
foreach ($iterator as $entry) {
    echo $entry['key'] . PHP_EOL;
}
"
```

## Performance Benchmarks

### Typical Performance

- **Cache Hit**: ~0.01ms (microseconds)
- **Cache Miss + DB Query**: ~5-20ms depending on query complexity
- **Cache Set**: ~0.02ms
- **Cache Clear Pattern**: ~0.1-1ms depending on entries matched

### Optimization Opportunities

1. **Query Optimization**: Focus on reducing database queries rather than complex caching
2. **Memory Tuning**: Adjust APCu memory based on application needs
3. **Cache Warming**: Pre-populate frequently accessed data on application start
4. **Selective Clearing**: Clear only affected cache entries instead of broad clears