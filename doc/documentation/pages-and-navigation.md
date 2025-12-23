# Pages and Navigation

This guide covers everything you need to know about creating, configuring, and organising pages in SelfHelp CMS.

---

## Table of Contents

1. [Understanding Pages](#understanding-pages)
2. [Creating Pages](#creating-pages)
3. [Page Properties](#page-properties)
4. [Page Types](#page-types)
5. [Navigation System](#navigation-system)
6. [URL Routing](#url-routing)
7. [Page Hierarchy](#page-hierarchy)
8. [Best Practices](#best-practices)

---

## Understanding Pages

### What is a Page?

In SelfHelp, a **page** is a URL-addressable container that holds content sections. Each page:

- Has a unique **keyword** (identifier)
- Maps to a specific **URL** pattern
- Contains one or more **sections** (configured components)
- Has associated **access permissions**
- Can be part of a **navigation hierarchy**

### Page Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                           PAGE                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Keyword: home                                             │  │
│  │ URL: /home                                                │  │
│  │ Type: Core                                                │  │
│  │ Access: Web & Mobile                                      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Section 1: header-banner                                  │  │
│  │ Style: card                                               │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Section 2: main-content                                   │  │
│  │ Style: markdown                                           │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Section 3: footer-links                                   │  │
│  │ Style: container                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### Database Structure

Pages are stored in the `pages` table with the following key columns:

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT | Unique identifier |
| `keyword` | VARCHAR(100) | URL-friendly name |
| `url` | VARCHAR(255) | Route pattern |
| `protocol` | VARCHAR(100) | HTTP methods (GET\|POST) |
| `id_actions` | INT | Action handler reference |
| `parent` | INT | Parent page for hierarchy |
| `id_type` | INT | Page type reference |
| `id_pageAccessTypes` | INT | Platform access (web/mobile/both) |
| `nav_position` | INT | Navigation menu order |
| `footer_position` | INT | Footer menu order |
| `is_headless` | BOOLEAN | API-only mode |

---

## Creating Pages

### Using the Admin Interface

1. Navigate to the **Admin** panel
2. Select **Pages** from the menu
3. Click **Add Page** or the **+** button

*[Screenshot placeholder: Page creation form]*

### Required Fields

| Field | Description | Example |
|-------|-------------|---------|
| **Keyword** | Unique identifier, URL-friendly | `contact-us` |
| **URL** | Route pattern | `/contact-us` |
| **Protocol** | HTTP method(s) | `GET` or `GET\|POST` |
| **Page Type** | Access level | Internal |

### Optional Fields

| Field | Description | Example |
|-------|-------------|---------|
| **Parent** | Parent page for navigation | `home` |
| **Nav Position** | Order in navigation menu | `1`, `2`, `3` |
| **Footer Position** | Order in footer menu | `1`, `2` |
| **Page Access Type** | Platform restriction | Web, Mobile, Both |
| **Is Headless** | API-only mode | Yes/No |

### Example: Creating a Contact Page

```
Keyword: contact
URL: /contact
Protocol: GET|POST
Page Type: Open
Nav Position: 3
Page Access: Both (Web & Mobile)
```

---

## Page Properties

### Page Fields Configuration

Each page can have additional configuration through **page fields**:

| Field | Type | Description |
|-------|------|-------------|
| `title` | text | Page title for browser tab |
| `description` | text | Meta description for SEO |
| `keywords` | text | Meta keywords |
| `custom_css` | textarea | Page-specific CSS |
| `custom_js` | textarea | Page-specific JavaScript |

### Setting Page Fields

1. Open the page in the CMS editor
2. Click **Page Properties** or the gear icon
3. Fill in the desired fields
4. Click **Save**

### Multi-Language Support

Page fields support translations:

```
English (en-GB):
  title: Contact Us
  description: Get in touch with our team

German (de-CH):
  title: Kontakt
  description: Nehmen Sie Kontakt mit uns auf
```

---

## Page Types

### Available Page Types

SelfHelp provides several page types that control access and behaviour:

| Type ID | Name | Description |
|---------|------|-------------|
| 1 | **Core** | System pages, cannot be deleted |
| 2 | **Open** | Publicly accessible without login |
| 3 | **Internal** | Requires user authentication |
| 4 | **Experimenter** | Special research/study access |
| 5+ | **Custom** | Plugin-defined types |

### Page Type Behaviour

#### Core Pages
- Cannot be deleted through the interface
- Used for essential system functions
- Examples: `login`, `logout`, `home`, `missing`

#### Open Pages
- Accessible to everyone, including guests
- No authentication required
- Ideal for: Landing pages, public information, marketing

#### Internal Pages
- Requires user to be logged in
- Redirects to login page if not authenticated
- Ideal for: User dashboards, personal content

#### Experimenter Pages
- Special access rules for research studies
- May have different login redirect behaviour
- Ideal for: Surveys, data collection

### Page Access Types

Control which platforms can access the page:

| Type | Constant | Behaviour |
|------|----------|-----------|
| **Web Only** | `pageAccessTypes_web` | HTML output only |
| **Mobile Only** | `pageAccessTypes_mobile` | JSON output only |
| **Both** | `pageAccessTypes_web_and_mobile` | Both outputs |

---

## Navigation System

### Navigation Structure

SelfHelp uses a hierarchical navigation system:

```
Home (nav_position: 1)
├── About (nav_position: 2)
│   ├── Team (child)
│   └── History (child)
├── Services (nav_position: 3)
│   ├── Consulting (child)
│   └── Training (child)
└── Contact (nav_position: 4)
```

### Main Navigation

Pages with `nav_position` set appear in the main navigation menu:

1. Set `nav_position` to a positive integer
2. Lower numbers appear first
3. Leave empty to exclude from navigation

### Footer Navigation

Pages can appear in the footer menu:

1. Set `footer_position` to a positive integer
2. Works independently of main navigation

### Child Pages

Create sub-navigation by setting a parent page:

1. Open the child page in the editor
2. Set the **Parent** field to the parent page keyword
3. The child will appear under the parent in navigation

### Navigation Components

Use these components to display navigation:

| Component | Description |
|-----------|-------------|
| `navigation` | Standard navigation menu |
| `navigationBar` | Bootstrap navbar |
| `navigationNested` | Multi-level dropdown |
| `navigationAccordion` | Collapsible accordion menu |
| `navigationContainer` | Custom navigation wrapper |

---

## URL Routing

### Route Patterns

SelfHelp uses AltoRouter for flexible URL routing:

| Pattern | Description | Example |
|---------|-------------|---------|
| `/page` | Static route | `/home` |
| `/page/[i:id]` | Integer parameter | `/user/123` |
| `/page/[a:slug]` | Alphanumeric parameter | `/article/my-post` |
| `/page/[*:path]` | Wildcard (any path) | `/files/docs/file.pdf` |

### Route Parameters

Parameters in routes are passed to the page:

```
URL: /user/[i:id]/profile
Actual URL: /user/123/profile
Parameters: { "id": "123" }
```

Access parameters in components using `#param_name`:
```
Welcome, user #id!
```

### Special Routes

| Route | Purpose |
|-------|---------|
| `/` | Root redirect to home |
| `/login` | User authentication |
| `/logout` | Session termination |
| `/missing` | 404 error page |
| `/no_access` | Access denied page |

### Route Actions

Routes can have different actions:

| Action | Description |
|--------|-------------|
| **controller** | Standard page rendering |
| **link** | Redirect to another URL |
| **ajax** | AJAX request handler |
| **callback** | External callback handler |

---

## Page Hierarchy

### Creating Page Trees

Organise pages into a logical hierarchy:

```
┌─────────────────────────────────────────────────────────────────┐
│ Root Pages (no parent)                                          │
│ ├── home                                                        │
│ ├── about                                                       │
│ │   └── Child Pages (parent = about)                           │
│ │       ├── team                                                │
│ │       └── history                                             │
│ ├── services                                                    │
│ │   └── consulting                                              │
│ └── contact                                                     │
└─────────────────────────────────────────────────────────────────┘
```

### Breadcrumbs

Use the page hierarchy to generate breadcrumbs:

```
Home > About > Team
```

### Navigation Sections

Pages can have dedicated navigation sections that display sub-pages:

1. Create a navigation section component
2. Assign it to the page's `id_navigation_section` field
3. Child sections will be displayed when navigating

---

## Best Practices

### Page Naming Conventions

| Convention | Example | Notes |
|------------|---------|-------|
| Use lowercase | `contact-us` | Not `Contact-Us` |
| Use hyphens | `user-profile` | Not `user_profile` |
| Be descriptive | `password-reset` | Not `pwd-rst` |
| Keep it short | `settings` | Not `user-account-settings-page` |

### URL Structure

```
Good:
/products
/products/category
/products/category/item

Avoid:
/page?id=123
/p/c/i
/ProductsPageWithCategory
```

### Navigation Organisation

1. **Limit top-level items** – Keep main navigation to 5-7 items
2. **Logical grouping** – Group related pages under parent items
3. **Consistent ordering** – Use meaningful `nav_position` values
4. **Clear labels** – Use descriptive page titles

### Performance Considerations

| Practice | Benefit |
|----------|---------|
| Use page caching | Faster load times |
| Minimise sections per page | Reduced database queries |
| Optimise route patterns | Faster routing |
| Use headless mode for API-only pages | Skip HTML rendering |

### Access Control

1. **Set appropriate page types** – Don't make internal pages open
2. **Use group permissions** – Control access at the group level
3. **Test as different users** – Verify access restrictions
4. **Document access requirements** – Keep a record of who can access what

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Page not found (404) | Check keyword and URL pattern |
| Access denied | Verify page type and group permissions |
| Navigation not showing | Check `nav_position` is set |
| Wrong page displayed | Check for duplicate keywords |
| Mobile not working | Verify page access type includes mobile |

### Debugging Routes

1. Check the browser's network tab for the actual URL
2. Verify the route exists in the pages table
3. Check the router configuration for conflicts
4. Enable debug mode for detailed routing information

---

*Previous: [Getting Started](getting-started.md) | Next: [Sections and Components](sections-and-components.md)*


