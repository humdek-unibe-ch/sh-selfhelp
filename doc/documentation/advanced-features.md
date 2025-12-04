# Advanced Features

This guide covers advanced functionality including plugins, mobile support, API integration, and performance optimisation.

---

## Table of Contents

1. [Plugin System](#plugin-system)
2. [Mobile Platform Support](#mobile-platform-support)
3. [Hook System](#hook-system)
4. [Caching System](#caching-system)
5. [External Integrations](#external-integrations)
6. [Performance Optimisation](#performance-optimisation)
7. [Debugging Tools](#debugging-tools)

---

## Plugin System

### Plugin Architecture

SelfHelp's plugin system allows extending functionality without modifying core code:

```
server/plugins/
├── sh-shp-api/           # REST API plugin
├── sh-shp-qualtrics/     # Qualtrics integration
├── sh-shp-r_serve/       # R statistical computing
├── sh-shp-survey_js/     # Survey.js forms
├── sh-shp-formula_parser/# Formula calculations
└── sh-shp-llm/           # LLM/AI integration
```

### Plugin Structure

Each plugin follows a standard structure:

```
sh-shp-plugin-name/
├── server/
│   ├── component/
│   │   ├── PluginHooks.php      # Hook implementations
│   │   └── style/               # Custom components
│   ├── service/
│   │   └── globals.php          # Configuration
│   ├── api/                     # API endpoints
│   └── db/                      # Database migrations
├── css/ext/                     # Stylesheets
├── js/ext/                      # JavaScript
├── README.md
└── CHANGELOG.md
```

### Plugin Loading

Plugins are auto-loaded during initialisation:

1. System scans `server/plugins/` directory
2. Each plugin's `globals.php` is loaded
3. Hooks are registered from database
4. Components become available

### Available Plugins

| Plugin | Purpose |
|--------|---------|
| **sh-shp-api** | REST API endpoints |
| **sh-shp-qualtrics** | Qualtrics survey integration |
| **sh-shp-r_serve** | R statistical computing |
| **sh-shp-formula_parser** | Mathematical calculations |
| **sh-shp-survey_js** | Advanced survey forms |
| **sh-shp-llm** | AI/LLM integration |

### Using Plugin Components

Plugin components are used like core components:

```
section:
  style: qualtricsSurvey
  fields:
    survey_id: "SV_xxxxx"
    config: {...}
```

---

## Mobile Platform Support

### Dual Rendering Architecture

SelfHelp automatically generates content for both web and mobile:

```
┌─────────────────────────────────────────────────────────────────┐
│                    DUAL RENDERING                               │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    Component                             │   │
│  │                                                          │   │
│  │  output_content()        output_content_mobile()        │   │
│  │       │                          │                       │   │
│  │       ▼                          ▼                       │   │
│  │    HTML Output              JSON Output                  │   │
│  │    (Bootstrap)              (Ionic/Angular)              │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### Page Access Types

Configure page visibility by platform:

| Type | Constant | Behaviour |
|------|----------|-----------|
| Web Only | `pageAccessTypes_web` | HTML only |
| Mobile Only | `pageAccessTypes_mobile` | JSON only |
| Both | `pageAccessTypes_web_and_mobile` | Both outputs |

### Mobile API Access

Mobile apps access content via:

```http
POST /endpoint
Content-Type: application/x-www-form-urlencoded

mobile=1&page=home
```

### JSON Output Format

Mobile output structure:

```json
{
  "page": "home",
  "title": "Home Page",
  "sections": [
    {
      "style_name": "card",
      "css": "custom-card",
      "title": { "content": "Welcome" },
      "children": [...]
    }
  ]
}
```

### Mobile-Specific Styling

Use separate CSS for mobile:

| Field | Platform |
|-------|----------|
| `css` | Web browser |
| `css_mobile` | Mobile app |

### Push Notifications

Send notifications to mobile devices:

```json
{
  "type": "notification",
  "title": "New Message",
  "body": "You have a new notification",
  "url": "/messages"
}
```

---

## Hook System

### Hook Architecture

Hooks allow plugins to modify core behaviour:

```
┌─────────────────────────────────────────────────────────────────┐
│                    HOOK SYSTEM                                  │
│                                                                 │
│  Core Method Call                                               │
│        │                                                        │
│        ▼                                                        │
│  Check for Hooks                                                │
│        │                                                        │
│  ┌─────┴─────┐                                                 │
│  ▼           ▼                                                  │
│  Execute    Override                                            │
│  Hook       Return                                              │
│  (on_execute) (overwrite)                                      │
│        │                                                        │
│        ▼                                                        │
│  Original/Modified Result                                       │
└─────────────────────────────────────────────────────────────────┘
```

### Hook Types

| Type | Constant | Behaviour |
|------|----------|-----------|
| **Execute** | `hook_on_function_execute` | Run alongside method |
| **Override** | `hook_overwrite_return` | Replace return value |

### Hook Registration

Hooks are registered in the `hooks` table:

```sql
INSERT INTO hooks (
  id_hookTypes, name, class, function, 
  exec_class, exec_function, priority
) VALUES (
  1, 'my-custom-hook', 
  'TargetClass', 'targetMethod',
  'MyPluginHooks', 'myHookMethod', 
  10
);
```

### Hook Implementation

```php
class MyPluginHooks extends BaseHooks
{
    public function myHookMethod($args)
    {
        $hookedInstance = $args['hookedClassInstance'];
        $originalParams = $args['original_parameters'];
        
        // Custom logic here
        
        return $modifiedResult;
    }
}
```

### Hook Priority

Lower numbers execute first. For override hooks, only highest priority executes.

---

## Caching System

### APCu Caching

SelfHelp uses APCu for performance:

```
┌─────────────────────────────────────────────────────────────────┐
│                    CACHE LAYERS                                 │
│                                                                 │
│  Request → Check Cache → Hit: Return cached data               │
│                │                                                │
│                └──────→ Miss: Query DB → Cache result → Return │
└─────────────────────────────────────────────────────────────────┘
```

### Cache Categories

| Category | Purpose |
|----------|---------|
| `CACHE_TYPE_PAGES` | Page configurations |
| `CACHE_TYPE_SECTIONS` | Section data |
| `CACHE_TYPE_FIELDS` | Field definitions |
| `CACHE_TYPE_STYLES` | Style configurations |
| `CACHE_TYPE_LOOKUPS` | Lookup values |
| `CACHE_TYPE_USER_INPUT` | User data |

### Cache Operations

```php
// Get from cache
$value = $cache->get($key);

// Set in cache
$cache->set($key, $value, $ttl);

// Clear cache
$cache->clear_cache($type, $id);
```

### Cache Key Structure

Keys follow the pattern:
```
{PROJECT_NAME}-{TYPE}-{ID}-{PARAMETERS}
```

Example: `selfhelp-LOOKUPS-get_lookup_id_by_value-user_type`

### Cache Invalidation

Cache is cleared when:
- Data is updated
- Admin makes changes
- Transaction rollback occurs
- Manual clear requested

---

## External Integrations

### API Integration

SelfHelp can connect to external APIs:

```php
$data = [
    'URL' => 'https://api.example.com/endpoint',
    'request_type' => 'POST',
    'header' => ['Content-Type: application/json'],
    'post_params' => json_encode($payload)
];

$response = BaseModel::execute_curl_call($data);
```

### Qualtrics Integration

Survey integration features:

- Survey management
- Response synchronisation
- Automated workflows
- PDF report generation

### R Statistical Computing

Execute R scripts for:

- Data analysis
- Statistical calculations
- Report generation
- Machine learning models

### Callback System

External services can call back:

```http
POST /callback/{service_name}
X-API-Key: your-api-key
Content-Type: application/json

{
  "event": "response_complete",
  "data": {...}
}
```

---

## Performance Optimisation

### Database Optimisation

| Strategy | Implementation |
|----------|----------------|
| **Indexing** | Foreign keys indexed |
| **Query optimisation** | Use EXPLAIN to analyse |
| **Connection pooling** | PDO persistent connections |
| **Batch operations** | Combine similar queries |

### Frontend Optimisation

| Strategy | Implementation |
|----------|----------------|
| **Asset minification** | Gulp build process |
| **Lazy loading** | Load components on demand |
| **Browser caching** | Cache headers |
| **CDN usage** | Bootstrap from CDN |

### Caching Strategy

1. **Query caching** – APCu for database results
2. **Component caching** – Cache rendered output
3. **Page caching** – Full page cache for static content

### Performance Monitoring

Use Clockwork for profiling:

```php
$clockwork->startEvent('my-operation');
// ... operation ...
$clockwork->endEvent('my-operation');
```

### Resource Guidelines

| Resource | Recommendation |
|----------|----------------|
| PHP memory | 256MB minimum |
| MySQL connections | Configure pooling |
| APCu size | 128MB recommended |
| Session storage | File or Redis |

---

## Debugging Tools

### Clockwork Debugger

Integrated performance debugging:

```
http://your-site/__clockwork/
```

Features:
- Request timeline
- Database queries
- Cache operations
- Custom events

### Debug Mode

Enable debug mode in configuration:

```php
define('DEBUG', true);
```

This enables:
- Detailed error messages
- SQL query logging
- Cache debugging
- SSL skip for local development

### Component Debugging

Enable debug output on components:

```
section:
  debug: true
```

Displays:
- Field values
- Condition results
- Data configuration
- Interpolation data

### Transaction Logging

View all database changes:

```sql
SELECT * FROM transactions 
ORDER BY transaction_time DESC 
LIMIT 100;
```

### Activity Logging

Track user activity:

```sql
SELECT * FROM user_activity 
WHERE id_users = :user_id 
ORDER BY timestamp DESC;
```

### Common Debug Queries

```sql
-- Check page configuration
SELECT * FROM view_pages WHERE keyword = 'page_name';

-- View section structure
SELECT * FROM view_sections_fields WHERE section_name = 'my-section';

-- Check user permissions
SELECT * FROM view_acl_users_union WHERE id_users = :user_id;

-- View scheduled jobs
SELECT * FROM view_scheduledJobs WHERE id_jobStatus != 'deleted';
```

---

## Best Practices

### Plugin Development

1. **Follow naming conventions** – `sh-shp-` prefix
2. **Use hooks appropriately** – Don't modify core code
3. **Document thoroughly** – README and CHANGELOG
4. **Test extensively** – Unit and integration tests

### Mobile Support

1. **Test on devices** – Real device testing
2. **Optimise JSON** – Minimise payload size
3. **Handle offline** – Graceful degradation
4. **Version API** – Maintain compatibility

### Performance

1. **Profile first** – Identify bottlenecks
2. **Cache wisely** – Balance freshness vs speed
3. **Optimise queries** – Use EXPLAIN
4. **Monitor regularly** – Track metrics

### Security

1. **Validate input** – Never trust external data
2. **Sanitise output** – Prevent XSS
3. **Use HTTPS** – Encrypt all traffic
4. **Rate limit** – Prevent abuse

---

*Previous: [Actions and Workflows](actions-and-workflows.md) | Next: [Troubleshooting](troubleshooting.md)*

