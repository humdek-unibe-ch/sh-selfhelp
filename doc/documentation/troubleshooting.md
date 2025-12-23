# Troubleshooting

This guide provides solutions to common issues and debugging strategies for SelfHelp CMS.

---

## Table of Contents

1. [Common Issues](#common-issues)
2. [Authentication Issues](#authentication-issues)
3. [Page and Navigation Issues](#page-and-navigation-issues)
4. [Form and Data Issues](#form-and-data-issues)
5. [Styling Issues](#styling-issues)
6. [Performance Issues](#performance-issues)
7. [Mobile Issues](#mobile-issues)
8. [Debugging Techniques](#debugging-techniques)

---

## Common Issues

### System Not Loading

**Symptoms:**
- Blank page
- Server error (500)
- PHP errors displayed

**Solutions:**

| Check | Solution |
|-------|----------|
| PHP version | Ensure PHP 8.2+ is installed |
| Database connection | Verify credentials in `globals_untracked.php` |
| File permissions | Set correct read/write permissions |
| Error logs | Check PHP and web server logs |
| Dependencies | Run `composer install` |

### White Screen of Death

1. Enable error display:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

2. Check `php_error.log`

3. Verify database connectivity

4. Clear cache:
   ```php
   $cache->clear_cache();
   ```

### Configuration Issues

| Issue | Solution |
|-------|----------|
| `globals_untracked.php` missing | Copy from `globals_untracked.default.php` |
| Wrong base path | Check `BASE_PATH` constant |
| Database errors | Verify `DBSERVER`, `DBNAME`, `DBUSER`, `DBPW` |

---

## Authentication Issues

### Cannot Log In

**Check user account:**
```sql
SELECT id, email, blocked, id_status 
FROM users 
WHERE email = 'user@example.com';
```

| Issue | Solution |
|-------|----------|
| Wrong password | Use password reset |
| Account blocked | Set `blocked = 0` |
| Invalid status | Check `id_status` value |
| Account not found | Verify email address |

### Session Issues

**Symptoms:**
- Random logouts
- Session not persisting
- "Not logged in" errors

**Solutions:**

1. Check session configuration:
   ```php
   // In php.ini or runtime
   session.gc_maxlifetime = 36000
   session.cookie_lifetime = 36000
   ```

2. Verify session storage:
   ```bash
   ls -la /tmp/sess_*
   ```

3. Check cookie settings:
   - SameSite attribute
   - Secure flag (HTTPS)
   - Domain configuration

### 2FA Issues

| Issue | Solution |
|-------|----------|
| Code not received | Check email configuration |
| Code expired | Request new code |
| Code invalid | Verify correct entry |

**Check 2FA codes:**
```sql
SELECT * FROM users_2fa_codes 
WHERE id_users = :user_id 
AND expires_at > NOW() 
AND is_used = 0;
```

### Password Reset Not Working

1. Verify email configuration
2. Check spam/junk folder
3. Verify validation code:
   ```sql
   SELECT * FROM validation_codes 
   WHERE id_users = :user_id 
   ORDER BY created DESC;
   ```

---

## Page and Navigation Issues

### Page Not Found (404)

**Check page exists:**
```sql
SELECT * FROM pages WHERE keyword = 'page-name';
```

| Issue | Solution |
|-------|----------|
| Page doesn't exist | Create the page |
| Wrong URL pattern | Fix URL in page config |
| Route conflict | Check for duplicate routes |
| Protocol mismatch | Verify GET/POST settings |

### Page Shows "No Access"

**Check permissions:**
```sql
-- Check user groups
SELECT g.name FROM groups g
JOIN users_groups ug ON g.id = ug.id_groups
WHERE ug.id_users = :user_id;

-- Check page ACL
SELECT * FROM acl_groups 
WHERE id_pages = :page_id;
```

| Issue | Solution |
|-------|----------|
| No ACL entry | Add group permissions |
| Wrong page type | Change to appropriate type |
| User not in group | Add user to group |

### Navigation Not Showing

| Issue | Solution |
|-------|----------|
| `nav_position` not set | Set positive integer |
| No ACL permission | Grant `acl_select` |
| Page type wrong | Use appropriate type |
| Parent not set correctly | Check hierarchy |

### Section Not Displaying

1. Check section exists in page
2. Verify condition evaluation
3. Check component style exists
4. Enable debug mode to see details

---

## Form and Data Issues

### Form Not Saving Data

**Check form configuration:**

| Check | Action |
|-------|--------|
| Form structure | Verify `formUserInput` is parent |
| Field names | Ensure `name` attribute set |
| Required fields | Check validation |
| AJAX mode | Verify endpoint accessible |

**Debug form submission:**
```php
// In FormUserInputController
var_dump($_POST);
exit;
```

### Data Not Displaying

**Check data exists:**
```sql
SELECT * FROM dataRows 
WHERE id_dataTables = :table_id
AND id_users = :user_id;
```

| Issue | Solution |
|-------|----------|
| Wrong source | Verify table name |
| Filter too restrictive | Check filter clause |
| User filter active | Check `current_user` setting |
| Data deleted | Check `deleted` flag |

### Interpolation Not Working

| Issue | Solution |
|-------|----------|
| Wrong syntax | Use `{{variable}}` format |
| Variable not available | Check data_config |
| Timing issue | Data loads after render |

**Debug interpolation:**
```
section:
  debug: true
```

### Duplicate Records

| Cause | Solution |
|-------|----------|
| Journal mode (`is_log: 1`) | Expected behaviour |
| Form resubmission | Add redirect after success |
| No validation | Add duplicate check |

---

## Styling Issues

### CSS Not Applying

| Issue | Solution |
|-------|----------|
| Class typo | Verify class name |
| Specificity | Use more specific selector |
| Cache | Clear browser cache |
| Load order | Check CSS load sequence |

### Bootstrap Not Working

1. Verify Bootstrap files loading
2. Check for JavaScript errors
3. Verify jQuery loaded first
4. Check for CSS conflicts

### Mobile Styling Issues

| Issue | Solution |
|-------|----------|
| Wrong field | Use `css_mobile` field |
| Platform detection | Verify mobile flag |
| Responsive breakpoints | Test at breakpoints |

### Custom CSS Not Loading

1. Verify CSS page exists (keyword: `css`)
2. Check page has content
3. Clear cache
4. Check for syntax errors

---

## Performance Issues

### Slow Page Load

**Identify bottleneck:**

1. Enable Clockwork debugging
2. Check query count and time
3. Review cache hit ratio
4. Analyse network requests

| Cause | Solution |
|-------|----------|
| Too many queries | Optimise, use caching |
| Large data sets | Add pagination, filters |
| Complex conditions | Simplify logic |
| No caching | Enable APCu |

### Database Performance

**Check slow queries:**
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
```

**Optimisation tips:**
- Add indexes on frequently queried columns
- Use EXPLAIN to analyse queries
- Limit result sets
- Archive old data

### Cache Issues

| Issue | Solution |
|-------|----------|
| Stale data | Clear cache |
| High miss rate | Increase cache size |
| Memory issues | Configure APCu limits |

**Clear all caches:**
```php
$db->get_cache()->clear_cache();
```

---

## Mobile Issues

### Mobile API Not Working

| Issue | Solution |
|-------|----------|
| Page access type | Set to include mobile |
| Authentication | Check X-API-Key |
| CORS | Configure headers |
| JSON format | Verify output format |

### Push Notifications Not Sending

1. Verify Firebase configuration
2. Check device token stored
3. Review notification job status
4. Check FCM console for errors

### JSON Output Wrong

**Debug mobile output:**
```php
// Force mobile output
$_POST['mobile'] = 1;
$page->output_content_mobile();
```

---

## Debugging Techniques

### Enable Debug Mode

```php
// In globals.php or globals_untracked.php
define('DEBUG', true);
```

### Component Debug

Add `debug: true` to section configuration:

```
section:
  debug: true
```

Shows:
- Field values
- Condition results
- Data config results
- Interpolation data

### Database Debugging

**Log all queries:**
```php
$db->enable_query_logging(true);
// ... operations ...
$queries = $db->get_logged_queries();
```

### Transaction Review

Check recent changes:
```sql
SELECT t.*, u.email 
FROM transactions t
LEFT JOIN users u ON t.id_users = u.id
ORDER BY transaction_time DESC
LIMIT 50;
```

### Network Debugging

1. Open browser DevTools (F12)
2. Go to Network tab
3. Reload page
4. Check for failed requests
5. Inspect response bodies

### Error Log Locations

| Log | Location |
|-----|----------|
| PHP errors | `/var/log/php_errors.log` |
| Apache | `/var/log/apache2/error.log` |
| Nginx | `/var/log/nginx/error.log` |
| Clockwork | `/data/clockwork/` |

### Useful Debug Queries

```sql
-- Check system version
SELECT version FROM version;

-- List all pages
SELECT keyword, url, id_type FROM pages ORDER BY keyword;

-- Check user groups
SELECT u.email, g.name 
FROM users u
JOIN users_groups ug ON u.id = ug.id_users
JOIN groups g ON ug.id_groups = g.id;

-- Recent jobs
SELECT * FROM view_scheduledJobs 
ORDER BY date_create DESC 
LIMIT 20;

-- Recent activity
SELECT * FROM user_activity 
ORDER BY timestamp DESC 
LIMIT 50;
```

---

## Getting Help

### Before Asking for Help

1. Check this troubleshooting guide
2. Search error message
3. Review recent changes
4. Test in different browser/device
5. Check server logs

### Information to Provide

When reporting issues, include:

- SelfHelp version
- PHP version
- Browser and version
- Error messages (full text)
- Steps to reproduce
- Recent changes made
- Relevant log entries

### Support Resources

- Project documentation
- Source code comments
- Database schema documentation
- Plugin documentation

---

*Previous: [Advanced Features](advanced-features.md) | Next: [Examples and Tutorials](examples-and-tutorials.md)*


