# Permissions and Security

This guide covers the access control system, group management, and security features in SelfHelp CMS.

---

## Table of Contents

1. [Access Control Overview](#access-control-overview)
2. [Groups](#groups)
3. [ACL (Access Control List)](#acl-access-control-list)
4. [Page Permissions](#page-permissions)
5. [Section Permissions](#section-permissions)
6. [Security Features](#security-features)
7. [Best Practices](#best-practices)

---

## Access Control Overview

### Permission Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    ACCESS CONTROL SYSTEM                        │
│                                                                 │
│  ┌─────────────┐              ┌─────────────┐                  │
│  │    USERS    │◀────────────▶│   GROUPS    │                  │
│  │             │  users_groups │             │                  │
│  │  - id       │              │  - id       │                  │
│  │  - email    │              │  - name     │                  │
│  └─────────────┘              │  - requires_2fa               │
│                               └─────────────┘                  │
│                                      │                          │
│                                      │ acl_groups               │
│                                      ▼                          │
│                               ┌─────────────┐                  │
│                               │    PAGES    │                  │
│                               │             │                  │
│                               │  - acl_select                  │
│                               │  - acl_insert                  │
│                               │  - acl_update                  │
│                               │  - acl_delete                  │
│                               └─────────────┘                  │
└─────────────────────────────────────────────────────────────────┘
```

### Permission Levels

| Level | Code | Description |
|-------|------|-------------|
| **Select** | `acl_select` | View/read access |
| **Insert** | `acl_insert` | Create new content |
| **Update** | `acl_update` | Modify existing content |
| **Delete** | `acl_delete` | Remove content |

### Permission Evaluation

```
Request → Check Page Type → Check User Groups → Check ACL → Grant/Deny
```

---

## Groups

### What are Groups?

Groups are collections of users with shared permissions. Users can belong to multiple groups.

### Core Tables

| Table | Purpose |
|-------|---------|
| `groups` | Group definitions |
| `users_groups` | User memberships |
| `acl_groups` | Group-page permissions |

### Group Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | INT | Unique identifier |
| `name` | VARCHAR(100) | Unique group name |
| `description` | VARCHAR(250) | Group description |
| `id_group_types` | INT | Group type lookup |
| `requires_2fa` | BOOLEAN | 2FA requirement |

### Default Groups

| Group | Purpose |
|-------|---------|
| **admin** | Full system access |
| **experimenter** | Research features access |
| **participant** | Standard user access |
| **guest** | Public access (special) |

### Creating Groups

1. Navigate to **Admin** → **Groups**
2. Click **Add Group**
3. Enter name and description
4. Configure 2FA requirement if needed
5. Save

### Managing Memberships

**Add user to group:**
1. Open user profile in admin
2. Select groups to add
3. Save changes

**Remove user from group:**
1. Open user profile
2. Deselect groups to remove
3. Save changes

### Group Types

| Type | Description |
|------|-------------|
| **System** | Core system groups |
| **Custom** | User-defined groups |
| **Plugin** | Plugin-specific groups |

---

## ACL (Access Control List)

### How ACL Works

The ACL system connects groups to pages with specific permissions:

```sql
-- ACL entry structure
acl_groups:
  id_groups: 5
  id_pages: 10
  acl_select: 1
  acl_insert: 1
  acl_update: 0
  acl_delete: 0
```

### ACL Tables

| Table | Description |
|-------|-------------|
| `acl_groups` | Group permissions for pages |
| `acl_users` | User-specific overrides |

### Permission Check Flow

```
1. Get user's group memberships
2. Find ACL entries for those groups
3. Check if any group grants required permission
4. Apply most permissive rule
```

### ACL Views

Database views for simplified access:

| View | Purpose |
|------|---------|
| `view_acl_groups_pages` | Group permissions with page details |
| `view_acl_users_pages` | User-specific permissions |
| `view_acl_users_in_groups_pages` | Combined view |
| `view_acl_users_union` | All user permissions |

### Permission Inheritance

- Users inherit permissions from all their groups
- Most permissive permission applies
- User-specific ACL overrides group ACL

---

## Page Permissions

### Setting Page Permissions

1. Navigate to **Admin** → **Pages**
2. Select a page
3. Click **ACL** or **Permissions**
4. Configure group permissions:

| Group | Select | Insert | Update | Delete |
|-------|--------|--------|--------|--------|
| admin | ✓ | ✓ | ✓ | ✓ |
| experimenter | ✓ | ✓ | ✓ | ✗ |
| participant | ✓ | ✗ | ✗ | ✗ |

### Page Type Permissions

Page types provide default access:

| Type | Default Access |
|------|----------------|
| **Open** | Everyone (including guests) |
| **Internal** | Logged-in users only |
| **Backend** | Admin users only |

### Navigation Permissions

Pages only appear in navigation if user has `acl_select` permission.

### Dynamic Permission Check

In code, permissions are checked via the ACL service:

```php
$acl->has_access($user_id, $page_id, 'select')
$acl->has_access($user_id, $page_id, 'insert')
$acl->has_access($user_id, $page_id, 'update')
$acl->has_access($user_id, $page_id, 'delete')
```

---

## Section Permissions

### Section-Level Access

Sections can have additional visibility controls:

1. **Owner** – Section ownership (who created it)
2. **Conditions** – Display conditions
3. **Data Filtering** – Filter data by user

### Conditional Display

Use conditions to control section visibility:

```json
{
  "type": "operator",
  "field": "{{user_group}}",
  "operator": "in",
  "value": ["admin", "experimenter"]
}
```

### Data Ownership

Forms can filter data by user:

```json
{
  "table": "submissions",
  "current_user": true
}
```

This shows only the current user's data.

---

## Security Features

### Authentication Security

| Feature | Implementation |
|---------|----------------|
| **Password hashing** | Argon2ID algorithm |
| **Session security** | Regeneration, secure cookies |
| **2FA support** | Email-based codes |
| **Brute force protection** | Rate limiting, lockout |

### Input Validation

| Protection | Method |
|------------|--------|
| **SQL injection** | Parameterised queries |
| **XSS** | Output escaping |
| **CSRF** | Token validation |
| **File uploads** | Type validation, scanning |

### HTTP Security Headers

```php
// Applied in index.php
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
```

### Session Security

| Setting | Value | Purpose |
|---------|-------|---------|
| `cookie_httponly` | true | Prevent XSS access |
| `cookie_secure` | true (prod) | HTTPS only |
| `cookie_samesite` | Lax | CSRF protection |
| `use_strict_mode` | true | Reject unknown IDs |

### API Security

| Feature | Implementation |
|---------|----------------|
| **Authentication** | X-API-Key header |
| **Authorization** | ACL-based access |
| **Rate limiting** | Configurable limits |
| **Logging** | Request/response logging |

### Audit Trail

All significant actions are logged:

| Table | Content |
|-------|---------|
| `transactions` | Data changes |
| `user_activity` | Page access |
| `callbackLogs` | External callbacks |

### Transaction Logging

```php
$transaction->log_action(
    $type,         // CREATE, UPDATE, DELETE
    $table,        // Affected table
    $id,           // Record ID
    $user_id,      // Acting user
    $details       // Additional info
);
```

---

## Best Practices

### Group Organisation

1. **Clear naming** – Use descriptive group names
2. **Single purpose** – One group, one role
3. **Minimal permissions** – Grant only necessary access
4. **Regular review** – Audit memberships periodically

### Permission Design

| Principle | Implementation |
|-----------|----------------|
| **Least privilege** | Grant minimum required access |
| **Separation of duties** | Different groups for different tasks |
| **Defence in depth** | Multiple security layers |
| **Audit everything** | Log all access and changes |

### Security Checklist

**User Management:**
- [ ] Strong password requirements
- [ ] 2FA for privileged accounts
- [ ] Regular access reviews
- [ ] Account lockout policies

**Page Security:**
- [ ] Appropriate page types
- [ ] ACL configured for all pages
- [ ] Sensitive pages require authentication
- [ ] Admin pages restricted

**Data Security:**
- [ ] Data filtered by user when appropriate
- [ ] Sensitive data encrypted
- [ ] Regular backups
- [ ] Audit logging enabled

### Common Patterns

**Admin-Only Pages:**
```
Page Type: Backend
ACL: admin group only with all permissions
```

**Member Content:**
```
Page Type: Internal
ACL: participant with select
     experimenter with select, insert, update
     admin with all
```

**Public with Protected Areas:**
```
Page Type: Open
Sections: Use conditions for group-specific content
```

---

## Troubleshooting

### Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| "No Access" error | Missing ACL entry | Add group permissions |
| Page not in menu | No select permission | Grant select to user's group |
| Can't edit | No update permission | Grant update permission |
| 2FA not triggering | Group setting | Enable 2FA on group |

### Debug Steps

1. **Check user groups:**
   ```sql
   SELECT g.name FROM groups g
   JOIN users_groups ug ON g.id = ug.id_groups
   WHERE ug.id_users = :user_id
   ```

2. **Check page permissions:**
   ```sql
   SELECT * FROM acl_groups
   WHERE id_pages = :page_id
   ```

3. **View combined permissions:**
   ```sql
   SELECT * FROM view_acl_users_union
   WHERE id_users = :user_id
   ```

### Permission Testing

1. Create test user accounts
2. Assign specific groups
3. Test each permission level
4. Verify navigation shows correctly
5. Check data filtering

---

*Previous: [User Management](user-management.md) | Next: [Actions and Workflows](actions-and-workflows.md)*


