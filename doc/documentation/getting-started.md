# Getting Started with SelfHelp CMS

This guide will help you understand the basics of SelfHelp CMS and get you started with creating your first web application.

---

## Table of Contents

1. [System Overview](#system-overview)
2. [First Login](#first-login)
3. [Understanding the Interface](#understanding-the-interface)
4. [Basic Concepts](#basic-concepts)
5. [Creating Your First Page](#creating-your-first-page)
6. [Next Steps](#next-steps)

---

## System Overview

### What is SelfHelp CMS?

SelfHelp CMS is a database-driven Content Management System that allows you to build web applications using a component-based approach. Rather than writing code, you configure components through the admin interface, and the system generates both web pages (HTML) and mobile-ready content (JSON for Ionic Angular).

### Key Capabilities

| Feature | Description |
|---------|-------------|
| **Page Management** | Create and organise pages with unique URLs |
| **Component Library** | 50+ pre-built components for various needs |
| **Form Handling** | Collect and store user data without coding |
| **User Management** | Built-in authentication and user profiles |
| **Access Control** | Group-based permissions for pages and features |
| **Workflow Automation** | Trigger actions based on data changes |
| **Multi-Platform** | Automatic web and mobile rendering |

### Technology Stack

- **Backend:** PHP 8.2+ (vanilla PHP, no framework)
- **Database:** MySQL 8.0+
- **Frontend:** Bootstrap 4.6, jQuery, vanilla JavaScript
- **Mobile:** JSON API for Ionic Angular applications
- **Caching:** APCu for performance optimisation

---

## First Login

### Accessing the System

1. Open your web browser and navigate to your SelfHelp installation URL
2. You will see the login page (or a public page if one is configured)

*[Screenshot placeholder: Login page]*

### Logging In

1. Enter your **email address** in the email field
2. Enter your **password**
3. Click the **Login** button

> **Note:** If your account requires two-factor authentication (2FA), you'll receive a code via email that you'll need to enter on the next screen.

### First-Time Login

If you're logging in for the first time:
1. You may be prompted to change your password
2. Complete any required profile information
3. You'll be redirected to your default landing page

### Password Recovery

If you've forgotten your password:
1. Click the **Forgot Password** link on the login page
2. Enter your registered email address
3. Check your email for a reset link
4. Follow the link to create a new password

---

## Understanding the Interface

### User Interface Elements

When logged in as an administrator, you'll see several interface elements:

```
┌─────────────────────────────────────────────────────────────────┐
│  [Logo]  Navigation Menu                     [Profile] [Logout] │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│                      Page Content Area                          │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    Section 1                             │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    Section 2                             │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│  [CMS Edit Button]                              [Back to Top]   │
└─────────────────────────────────────────────────────────────────┘
```

### Navigation Elements

| Element | Description | Location |
|---------|-------------|----------|
| **Logo** | Returns to home page | Top left |
| **Main Navigation** | Links to main pages | Top bar |
| **Profile Menu** | User profile and settings | Top right |
| **CMS Edit Button** | Opens page editor (admin only) | Bottom corner |
| **Back to Top** | Scrolls to page top | Bottom corner |

### The CMS Editor

When you click the **CMS Edit** button (pencil icon), you enter the content management mode:

*[Screenshot placeholder: CMS edit mode]*

The CMS editor displays:
- **Page Properties** – Title, URL, access settings
- **Section List** – All components on the page
- **Section Editor** – Configure individual components
- **Action Buttons** – Add, edit, delete, reorder

---

## Basic Concepts

### Pages

A **page** is a container that holds content and has a unique URL. Each page has:

| Property | Description | Example |
|----------|-------------|---------|
| **Keyword** | Unique identifier (URL-friendly) | `home`, `contact`, `profile` |
| **Title** | Display name for navigation | "Home Page", "Contact Us" |
| **URL** | Route pattern | `/home`, `/contact` |
| **Type** | Access level (open, authenticated, admin) | Core, Internal, Experimenter |
| **Parent** | Hierarchical organisation | Navigation structure |

### Sections

A **section** is an instance of a component with specific configuration. Think of it as a "configured component placed on a page."

- Each section has a unique **name** for identification
- Sections can contain **child sections** (nested components)
- Configuration is stored in **fields** specific to the component type

### Components (Styles)

A **component** (internally called a "style") is a reusable UI element:

| Category | Examples |
|----------|----------|
| **Content** | markdown, heading, image, video |
| **Layout** | container, card, tabs, accordion |
| **Forms** | input, select, textarea, checkbox |
| **Navigation** | navigation, navigationBar, link |
| **Data Display** | table, showUserInput, dataContainer |
| **Interactive** | button, modal, alert |

### Fields

**Fields** are configuration options for components. Each component has specific fields:

```
Component: card
├── title (text) - The card header
├── css (text) - Custom CSS classes
├── children (sections) - Nested content
└── is_expanded (checkbox) - Default state
```

### Data Flow

```
User Request → Router → Page → Sections → Components → Response
                                   │
                                   ▼
                              Database
                         (dataTables system)
```

---

## Creating Your First Page

### Step 1: Access Page Management

1. Log in as an administrator
2. Navigate to **Admin** → **Pages** (or click the CMS button)
3. You'll see a list of existing pages

### Step 2: Create a New Page

1. Click **Add Page** or the **+** button
2. Fill in the required fields:

| Field | Value | Notes |
|-------|-------|-------|
| **Keyword** | `my-first-page` | URL-friendly, unique |
| **Title** | My First Page | Display name |
| **Protocol** | GET | HTTP method |
| **URL** | `/my-first-page` | Route pattern |
| **Page Type** | Internal | Requires login |

3. Click **Save**

### Step 3: Add a Section

1. Open your new page in the CMS editor
2. Click **Add Section** or the **+** button
3. Choose a component type (e.g., **markdown**)
4. Enter a unique section name (e.g., `welcome-message`)
5. Click **Create**

### Step 4: Configure the Section

1. Click on your new section to edit it
2. Fill in the fields:

For a **markdown** component:
```
text_md: 
# Welcome to My First Page

This is my first page created with SelfHelp CMS!

- Easy to use
- No coding required
- Professional results
```

3. Click **Save**

### Step 5: Preview Your Page

1. Navigate to `/my-first-page` in your browser
2. You should see your markdown content rendered as HTML

*[Screenshot placeholder: First page result]*

---

## Understanding Page Types

SelfHelp has several page types that determine access and behaviour:

| Type | Description | Use Case |
|------|-------------|----------|
| **Core** | System pages, not deletable | Login, Home |
| **Open** | Publicly accessible | Landing pages, Info |
| **Internal** | Requires authentication | User dashboards |
| **Experimenter** | Special access rules | Research features |
| **Backend** | Admin-only pages | Settings, Management |

### Page Access Types

Pages can also be configured for specific platforms:

| Access Type | Description |
|-------------|-------------|
| **Web Only** | Only visible in web browsers |
| **Mobile Only** | Only accessible via mobile API |
| **Both** | Available on all platforms |

---

## Next Steps

Now that you understand the basics, explore these topics:

### Immediate Next Steps

1. **[Pages and Navigation](pages-and-navigation.md)** – Learn how to organise pages and create menus
2. **[Sections and Components](sections-and-components.md)** – Explore the full component library
3. **[Styling Guide](styling-guide.md)** – Customise the appearance of your content

### Building Data-Driven Applications

4. **[Data Management](data-management.md)** – Create forms and manage data
5. **[User Management](user-management.md)** – Handle user accounts
6. **[Permissions and Security](permissions-and-security.md)** – Set up access control

### Advanced Topics

7. **[Actions and Workflows](actions-and-workflows.md)** – Automate processes
8. **[Advanced Features](advanced-features.md)** – Plugins and mobile development

---

## Tips for Success

### Best Practices

1. **Plan Your Structure** – Sketch out your page hierarchy before building
2. **Use Meaningful Names** – Choose descriptive keywords and section names
3. **Test Frequently** – Preview changes as you work
4. **Start Simple** – Begin with basic components, then add complexity
5. **Document Your Work** – Keep notes on your configuration choices

### Common Pitfalls to Avoid

| Pitfall | Solution |
|---------|----------|
| Duplicate keywords | Each page must have a unique keyword |
| Missing required fields | Check all required configuration |
| Circular references | Avoid sections referencing themselves |
| Permission issues | Verify group access settings |

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + S` | Save current changes |
| `Esc` | Cancel current operation |
| `Tab` | Navigate between fields |

---

*Next: [Pages and Navigation](pages-and-navigation.md)*


