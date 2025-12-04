# Sections and Components

This guide explains how sections and components work in SelfHelp CMS, including the full component library and configuration options.

---

## Table of Contents

1. [Understanding Sections](#understanding-sections)
2. [Component Architecture](#component-architecture)
3. [Component Categories](#component-categories)
4. [Complete Component Reference](#complete-component-reference)
5. [Working with Sections](#working-with-sections)
6. [Conditional Display](#conditional-display)
7. [Data Binding](#data-binding)
8. [Mobile Output](#mobile-output)

---

## Understanding Sections

### What is a Section?

A **section** is a configured instance of a component placed on a page. While components are reusable templates, sections are specific implementations with their own:

- **Unique name** – Identifier within the system
- **Configuration** – Field values specific to this instance
- **Position** – Order on the page
- **Children** – Nested sections (for container components)

### Section Structure

```
Section: welcome-banner
├── Style: card
├── Parent: page (home) or section (wrapper)
├── Position: 1
├── Fields:
│   ├── title: "Welcome"
│   ├── css: "bg-primary text-white"
│   └── children: [nested-content]
└── Conditions: [display rules]
```

### Database Storage

Sections are stored across multiple tables:

| Table | Purpose |
|-------|---------|
| `sections` | Section identity (id, name, style) |
| `sections_fields_translation` | Field values with translations |
| `sections_hierarchy` | Parent-child relationships |
| `pages_sections` | Page-section assignments |

---

## Component Architecture

### MVC Pattern

Every component follows the Model-View-Controller pattern:

```
┌─────────────────────────────────────────────────────────────────┐
│                    Component (Style)                            │
│                                                                 │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │     Model       │  │      View       │  │   Controller    │ │
│  │                 │  │                 │  │   (optional)    │ │
│  │ - Data loading  │  │ - HTML output   │  │ - Form handling │ │
│  │ - Configuration │  │ - JSON output   │  │ - Validation    │ │
│  │ - Children      │  │ - CSS/JS files  │  │ - Actions       │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### Component Files

Each component consists of:

```
server/component/style/componentName/
├── ComponentNameComponent.php  # Main class
├── ComponentNameModel.php      # Data handling
├── ComponentNameView.php       # Rendering
├── ComponentNameController.php # User interaction (optional)
├── tpl_*.php                   # Template files
├── css/                        # Component CSS
└── js/                         # Component JavaScript
```

### Component Lifecycle

1. **Instantiation** – Component created with section ID and parameters
2. **Model Loading** – Configuration and data fetched from database
3. **Condition Check** – Visibility conditions evaluated
4. **Rendering** – View generates HTML (web) or JSON (mobile)
5. **Interaction** – Controller handles form submissions (if applicable)

---

## Component Categories

SelfHelp organises components into functional categories:

### Content Components
Display static or dynamic content

| Component | Purpose |
|-----------|---------|
| `markdown` | Rich text with Markdown support |
| `markdownInline` | Inline Markdown text |
| `heading` | HTML heading elements |
| `plaintext` | Unformatted text |
| `rawText` | Raw HTML output |

### Layout Components
Structure and organise content

| Component | Purpose |
|-----------|---------|
| `container` | Generic wrapper with CSS |
| `div` | Simple div wrapper |
| `card` | Bootstrap card layout |
| `jumbotron` | Hero unit |
| `tabs` | Tabbed content |
| `accordion` | Collapsible panels |

### Form Components
Collect user input

| Component | Purpose |
|-----------|---------|
| `form` | Form wrapper |
| `formUserInput` | Data-saving form |
| `input` | Text input fields |
| `textarea` | Multi-line text |
| `select` | Dropdown selection |
| `checkbox` | Boolean toggle |
| `radio` | Option buttons |
| `slider` | Range selection |

### Navigation Components
Site navigation and links

| Component | Purpose |
|-----------|---------|
| `navigation` | Standard menu |
| `navigationBar` | Navbar |
| `navigationNested` | Multi-level menu |
| `link` | Hyperlink |
| `button` | Action button |

### Data Display Components
Present data from the database

| Component | Purpose |
|-----------|---------|
| `table` | Data table |
| `showUserInput` | Display saved data |
| `dataContainer` | Data-bound container |
| `entryList` | List of data entries |
| `entryRecord` | Single entry display |

### Media Components
Images, video, and audio

| Component | Purpose |
|-----------|---------|
| `image` | Image display |
| `figure` | Image with caption |
| `video` | Video player |
| `audio` | Audio player |
| `carousel` | Image slideshow |

### Interactive Components
User interaction elements

| Component | Purpose |
|-----------|---------|
| `modal` | Popup dialog |
| `alert` | Notification message |
| `progressBar` | Progress indicator |
| `trigger` | Event trigger |
| `validate` | Form validation |

### Specialised Components
Specific functionality

| Component | Purpose |
|-----------|---------|
| `login` | Authentication form |
| `register` | User registration |
| `profile` | User profile display |
| `resetPassword` | Password reset |
| `twoFactorAuth` | 2FA verification |

---

## Complete Component Reference

### Content: `markdown`

Renders Markdown-formatted text as HTML.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `text_md` | markdown | Markdown content | (empty) |
| `css` | text | CSS classes | (none) |

**Example:**
```markdown
text_md:
# Welcome

This is **bold** and this is *italic*.

- List item 1
- List item 2
```

---

### Layout: `card`

Bootstrap 4 card component with header and body.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `title` | text | Card header text | (none) |
| `css` | text | CSS classes | (none) |
| `children` | children | Nested content | (none) |
| `is_expanded` | checkbox | Collapsible state | true |
| `is_collapsible` | checkbox | Enable collapse | false |

**Example Usage:**
Create a card with a title and nested markdown content.

---

### Layout: `container`

Generic wrapper for grouping content.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `css` | text | CSS classes | (none) |
| `children` | children | Nested content | (none) |

---

### Layout: `tabs`

Tabbed content interface.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `css` | text | CSS classes | (none) |
| `children` | children | Tab panes | (none) |

Each child should be a `tab` component with:
- `title` – Tab label
- `children` – Tab content

---

### Form: `formUserInput`

Data-collection form that saves to dataTables.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `name` | text | Form/table name | section name |
| `is_log` | select | Journal (log) vs persistent | 0 (persistent) |
| `label_submit` | text | Submit button text | "Submit" |
| `url_success` | text | Redirect after success | (none) |
| `ajax` | checkbox | AJAX submission | false |
| `children` | children | Form fields | (none) |

**Behaviour:**
- **Persistent (is_log: 0)** – Updates existing records
- **Journal (is_log: 1)** – Creates new record each time

---

### Form: `input`

Text input field.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `name` | text | Field name for data | (required) |
| `type_input` | select | Input type | "text" |
| `label` | text | Field label | (none) |
| `placeholder` | text | Placeholder text | (none) |
| `is_required` | checkbox | Required field | false |
| `css` | text | CSS classes | (none) |

**Input Types:**
- text, email, password, number, date, time, datetime-local, tel, url, color, file

---

### Form: `select`

Dropdown selection field.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `name` | text | Field name | (required) |
| `label` | text | Field label | (none) |
| `items` | json | Selection options | [] |
| `is_required` | checkbox | Required | false |
| `is_multiple` | checkbox | Multiple selection | false |

**Items Format:**
```json
[
  {"value": "1", "text": "Option One"},
  {"value": "2", "text": "Option Two"},
  {"value": "3", "text": "Option Three"}
]
```

---

### Navigation: `button`

Clickable button with various styles.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `label` | text | Button text | "Submit" |
| `url` | text | Link destination | (none) |
| `type` | select | Button style | "primary" |
| `css` | text | CSS classes | (none) |

**Button Types:**
primary, secondary, success, danger, warning, info, light, dark, link

---

### Data: `showUserInput`

Displays data from dataTables.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `source` | select | Data source | (required) |
| `columns` | json | Columns to display | [] |
| `filter` | text | SQL WHERE clause | (none) |
| `is_editable` | checkbox | Allow editing | false |

---

### Data: `dataContainer`

Container that binds to data for child components.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `data_config` | data-config | Data configuration | (none) |
| `children` | children | Content with data binding | (none) |

---

### Interactive: `modal`

Popup dialog window.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `title` | text | Modal header | (none) |
| `css` | text | CSS classes | (none) |
| `children` | children | Modal content | (none) |
| `trigger_id` | text | Button ID to open | (none) |

---

### Interactive: `alert`

Notification message box.

**Fields:**

| Field | Type | Description | Default |
|-------|------|-------------|---------|
| `type` | select | Alert style | "info" |
| `is_dismissable` | checkbox | Show close button | true |
| `children` | children | Alert content | (none) |

**Alert Types:**
primary, secondary, success, danger, warning, info, light, dark

---

## Working with Sections

### Adding a Section

1. Open the page in CMS editor
2. Click **Add Section** (+ button)
3. Select the component type (style)
4. Enter a unique section name
5. Configure the fields
6. Save

### Editing a Section

1. Click on the section in the CMS view
2. Modify field values
3. Save changes

### Reordering Sections

1. Use the drag handle or up/down arrows
2. Or modify the `position` field directly

### Nesting Sections

For container components with `children`:

1. Open the parent section
2. Click **Add Child Section**
3. Configure the child component
4. The child appears nested under the parent

### Deleting a Section

1. Select the section
2. Click **Delete** button
3. Confirm the deletion

> **Warning:** Deleting a section with children will also delete all nested sections.

---

## Conditional Display

### Condition System

Sections can be conditionally displayed based on:

- **User properties** – Groups, status, language
- **Data values** – From dataTables
- **URL parameters** – From the current request
- **Session data** – User session information

### Condition Configuration

The `condition` field accepts JSON:

```json
{
  "type": "and",
  "conditions": [
    {
      "type": "operator",
      "field": "{{user_group}}",
      "operator": "=",
      "value": "admin"
    }
  ]
}
```

### Condition Types

| Type | Description |
|------|-------------|
| `and` | All conditions must be true |
| `or` | Any condition must be true |
| `not` | Negates the condition |
| `operator` | Comparison operation |

### Operators

| Operator | Description |
|----------|-------------|
| `=` | Equal to |
| `!=` | Not equal to |
| `>` | Greater than |
| `<` | Less than |
| `>=` | Greater than or equal |
| `<=` | Less than or equal |
| `contains` | String contains |
| `in` | Value in list |
| `not_in` | Value not in list |

### Example: Show for Admin Users Only

```json
{
  "type": "operator",
  "field": "{{user_group}}",
  "operator": "in",
  "value": ["admin", "experimenter"]
}
```

---

## Data Binding

### Variable Interpolation

Use double curly braces to insert dynamic values:

```
Welcome, {{@user}}!
Your email is: {{@user_email}}
```

### Global Variables

| Variable | Description |
|----------|-------------|
| `{{@user}}` | Current user's name |
| `{{@user_email}}` | Current user's email |
| `{{@user_code}}` | User's unique code |
| `{{@project}}` | Project name |
| `{{__keyword__}}` | Current page keyword |
| `{{__language__}}` | User's language ID |

### Data Config Variables

With `data_config`, access database values:

```
{{table_name.field_name}}
```

### URL Parameters

Access route parameters:

```
User ID: {{#id}}
```

### Entry Record Variables

In list/entry contexts:

```
$record_id
$field_name
```

---

## Mobile Output

### Dual Rendering

Components automatically provide:

1. **Web Output** – HTML via `output_content()`
2. **Mobile Output** – JSON via `output_content_mobile()`

### JSON Structure

Mobile output follows this structure:

```json
{
  "style_name": "card",
  "css": "my-custom-class",
  "children": [
    {
      "style_name": "markdown",
      "text_md": {
        "content": "# Hello World"
      }
    }
  ],
  "title": {
    "content": "Card Title"
  }
}
```

### Mobile-Specific CSS

Use the `css_mobile` field for mobile-only styling:

| Field | Platform |
|-------|----------|
| `css` | Web only |
| `css_mobile` | Mobile only |

### Testing Mobile Output

1. Set page access type to include mobile
2. Use API endpoint with `mobile=1` parameter
3. Check JSON response structure

---

## Tips and Best Practices

### Section Naming

| Good | Bad |
|------|-----|
| `main-navigation` | `nav1` |
| `contact-form` | `cf` |
| `user-profile-header` | `header` |

### Component Selection

| Need | Component |
|------|-----------|
| Formatted text | `markdown` |
| Simple text | `plaintext` |
| Data collection | `formUserInput` + fields |
| Data display | `showUserInput` or `table` |
| Layout wrapper | `container` or `div` |
| Styled container | `card` |

### Performance

1. **Minimise nesting** – Deep hierarchies slow rendering
2. **Use conditions wisely** – Complex conditions add overhead
3. **Cache data** – Avoid repeated database queries
4. **Lazy loading** – Split large pages into tabs

---

*Previous: [Pages and Navigation](pages-and-navigation.md) | Next: [Styling Guide](styling-guide.md)*

