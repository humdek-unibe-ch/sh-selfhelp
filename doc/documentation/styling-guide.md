# Styling Guide

This guide covers all aspects of visual customisation in SelfHelp CMS, including Bootstrap integration, custom CSS, and responsive design.

---

## Table of Contents

1. [Styling Overview](#styling-overview)
2. [Bootstrap 4.6 Integration](#bootstrap-46-integration)
3. [CSS Field Usage](#css-field-usage)
4. [Custom CSS Pages](#custom-css-pages)
5. [Component-Specific Styling](#component-specific-styling)
6. [Responsive Design](#responsive-design)
7. [Style Field Types](#style-field-types)
8. [Best Practices](#best-practices)

---

## Styling Overview

### How Styling Works

SelfHelp uses a layered approach to styling:

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. Bootstrap 4.6 (Base Framework)                               │
├─────────────────────────────────────────────────────────────────┤
│ 2. Component Default Styles (server/component/style/*/css/)     │
├─────────────────────────────────────────────────────────────────┤
│ 3. Global Custom CSS (CSS page/Custom stylesheet)               │
├─────────────────────────────────────────────────────────────────┤
│ 4. Page-Specific CSS (custom_css field)                         │
├─────────────────────────────────────────────────────────────────┤
│ 5. Section-Specific CSS (css field per component)               │
└─────────────────────────────────────────────────────────────────┘
```

### CSS Cascade

Later styles override earlier ones:

1. **Bootstrap base** – Default framework styles
2. **Component CSS** – Built-in component styles
3. **Global CSS** – Site-wide customisations
4. **Page CSS** – Page-specific overrides
5. **Section CSS** – Component instance classes

---

## Bootstrap 4.6 Integration

### Framework Features

SelfHelp includes Bootstrap 4.6 with full support for:

- **Grid System** – 12-column responsive layout
- **Components** – Cards, modals, navs, forms
- **Utilities** – Spacing, colours, flexbox, display
- **JavaScript** – Collapse, dropdown, modal, tooltip

### Grid System

Use Bootstrap's grid for responsive layouts:

```html
<div class="container">
  <div class="row">
    <div class="col-md-6">Half width on medium+</div>
    <div class="col-md-6">Half width on medium+</div>
  </div>
</div>
```

**Grid Classes:**

| Class | Screen Size | Breakpoint |
|-------|-------------|------------|
| `col-` | Extra small | <576px |
| `col-sm-` | Small | ≥576px |
| `col-md-` | Medium | ≥768px |
| `col-lg-` | Large | ≥992px |
| `col-xl-` | Extra large | ≥1200px |

### Common Utility Classes

#### Spacing

| Class | Property | Size |
|-------|----------|------|
| `m-1` to `m-5` | Margin | 0.25rem to 3rem |
| `p-1` to `p-5` | Padding | 0.25rem to 3rem |
| `mt-`, `mb-`, `ml-`, `mr-` | Single side | margin-top, etc. |
| `mx-`, `my-` | Axis | X or Y axis |

#### Colours

**Background:**
- `bg-primary`, `bg-secondary`, `bg-success`, `bg-danger`
- `bg-warning`, `bg-info`, `bg-light`, `bg-dark`, `bg-white`

**Text:**
- `text-primary`, `text-secondary`, `text-success`, `text-danger`
- `text-warning`, `text-info`, `text-light`, `text-dark`, `text-muted`

#### Display

- `d-none` – Hidden
- `d-block` – Block display
- `d-flex` – Flexbox container
- `d-inline` – Inline display
- `d-md-none` – Hidden on medium and up

#### Flexbox

- `justify-content-start`, `justify-content-center`, `justify-content-end`
- `align-items-start`, `align-items-center`, `align-items-end`
- `flex-wrap`, `flex-nowrap`
- `flex-row`, `flex-column`

#### Text

- `text-left`, `text-center`, `text-right`
- `text-uppercase`, `text-lowercase`, `text-capitalize`
- `font-weight-bold`, `font-weight-normal`, `font-italic`

---

## CSS Field Usage

### The CSS Field

Most components have a `css` field for adding CSS classes:

```
Component: card
└── css: "shadow-lg rounded-lg border-0"
```

### Combining Classes

Add multiple classes separated by spaces:

```
css: "bg-light p-4 mb-3 rounded shadow"
```

### Section-Specific Styling

Each section automatically gets a unique class:

```css
.style-section-123 {
  /* Styles for section with ID 123 */
}
```

Use this in custom CSS to target specific sections.

### Locale-Based Styling

Sections include the user's locale:

```css
.selfHelp-locale-en-GB {
  /* English (UK) specific styles */
}

.selfHelp-locale-de-CH {
  /* German (Switzerland) specific styles */
}
```

---

## Custom CSS Pages

### Creating Custom Styles

Add site-wide custom CSS:

1. Create a page with keyword `css` (if not exists)
2. Add a `rawText` section
3. Write your CSS in the content field

```css
/* Global custom styles */
:root {
  --brand-primary: #007bff;
  --brand-secondary: #6c757d;
}

.site-header {
  background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
}

.custom-card {
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
```

### Page-Specific CSS

Add CSS to individual pages using the `custom_css` page field:

```css
/* Styles only for this page */
.page-specific-banner {
  height: 400px;
  background-size: cover;
}
```

### CSS Variables

Define and use CSS custom properties:

```css
/* Define variables */
:root {
  --primary-colour: #3498db;
  --secondary-colour: #2ecc71;
  --font-main: 'Roboto', sans-serif;
  --spacing-unit: 1rem;
}

/* Use variables */
.custom-element {
  color: var(--primary-colour);
  font-family: var(--font-main);
  margin: var(--spacing-unit);
}
```

---

## Component-Specific Styling

### Card Styling

```
css: "shadow border-0 rounded-lg"
```

Common card classes:
- `shadow`, `shadow-sm`, `shadow-lg` – Shadow depth
- `border-0` – Remove border
- `rounded-lg` – Rounded corners
- `card-hover` – Hover effects (custom)

### Button Styling

```
css: "btn-lg rounded-pill px-5"
```

Button modifiers:
- `btn-lg`, `btn-sm` – Size
- `btn-block` – Full width
- `btn-outline-*` – Outline style
- `rounded-pill` – Pill shape

### Form Styling

```
css: "form-control-lg bg-light border-0"
```

Form classes:
- `form-control-lg`, `form-control-sm` – Size
- `form-inline` – Inline form
- `was-validated` – Validation state

### Table Styling

```
css: "table-striped table-hover table-bordered"
```

Table classes:
- `table-striped` – Alternating rows
- `table-hover` – Hover effect
- `table-bordered` – All borders
- `table-sm` – Compact
- `table-responsive` – Horizontal scroll

### Alert Styling

```
css: "alert-dismissible fade show"
```

Alert modifiers:
- `alert-dismissible` – With close button
- `fade show` – Animation
- `mb-0` – Remove margin

---

## Responsive Design

### Mobile-First Approach

Bootstrap uses mobile-first design. Start with mobile styles, then add breakpoints:

```css
/* Mobile (default) */
.my-element {
  padding: 1rem;
}

/* Tablet and up */
@media (min-width: 768px) {
  .my-element {
    padding: 2rem;
  }
}

/* Desktop and up */
@media (min-width: 992px) {
  .my-element {
    padding: 3rem;
  }
}
```

### Responsive Utilities

Show/hide elements at different breakpoints:

| Class | Visible On |
|-------|------------|
| `d-none d-md-block` | Medium and up |
| `d-block d-md-none` | Small only |
| `d-none d-lg-flex` | Large and up (flex) |

### Mobile-Specific CSS Field

Use the `css_mobile` field for mobile app styling:

```
css: "desktop-styling"
css_mobile: "mobile-specific-styling"
```

### Responsive Grid Examples

**Two columns on desktop, stacked on mobile:**
```html
<div class="row">
  <div class="col-12 col-md-6">Column 1</div>
  <div class="col-12 col-md-6">Column 2</div>
</div>
```

**Three columns on large, two on medium, one on small:**
```html
<div class="row">
  <div class="col-12 col-md-6 col-lg-4">Item</div>
  <div class="col-12 col-md-6 col-lg-4">Item</div>
  <div class="col-12 col-md-6 col-lg-4">Item</div>
</div>
```

---

## Style Field Types

### Field Types Overview

| Type | Description | Example |
|------|-------------|---------|
| `text` | Single-line text | CSS classes |
| `textarea` | Multi-line text | Long content |
| `markdown` | Markdown format | Rich text |
| `select` | Dropdown | Type selection |
| `checkbox` | Boolean toggle | Enable/disable |
| `json` | JSON structure | Complex config |
| `color` | Colour picker | Colour values |
| `number` | Numeric value | Size, count |

### Using Select Fields

Pre-defined options for consistency:

```json
[
  {"value": "primary", "text": "Primary"},
  {"value": "secondary", "text": "Secondary"},
  {"value": "success", "text": "Success"}
]
```

### JSON Field Styling

Configure complex styles via JSON:

```json
{
  "background": "linear-gradient(45deg, #ff6b6b, #feca57)",
  "borderRadius": "20px",
  "boxShadow": "0 10px 30px rgba(0,0,0,0.2)"
}
```

---

## Best Practices

### CSS Organisation

1. **Use meaningful class names:**
   ```
   Good: featured-product-card, user-avatar-large
   Bad: card1, big-thing
   ```

2. **Follow BEM naming (optional):**
   ```css
   .card { }
   .card__header { }
   .card__body { }
   .card--highlighted { }
   ```

3. **Group related styles:**
   ```css
   /* Typography */
   .heading-primary { }
   .heading-secondary { }
   
   /* Cards */
   .custom-card { }
   .custom-card-featured { }
   ```

### Performance

1. **Minimise custom CSS** – Use Bootstrap utilities when possible
2. **Avoid inline styles** – Use CSS classes instead
3. **Limit specificity** – Avoid deeply nested selectors
4. **Use CSS variables** – For consistent, maintainable values

### Accessibility

1. **Colour contrast** – Ensure readable text (WCAG 2.1 AA)
2. **Focus states** – Don't remove focus outlines
3. **Responsive text** – Use relative units (rem, em)
4. **Touch targets** – Minimum 44x44px on mobile

### Common Patterns

**Centred content:**
```
css: "d-flex justify-content-center align-items-center"
```

**Sticky header:**
```
css: "sticky-top bg-white shadow-sm"
```

**Full-width on mobile, contained on desktop:**
```
css: "container-fluid px-0 px-md-3"
```

**Card grid:**
```
css: "row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"
```

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Styles not applying | Check class spelling, specificity |
| Layout broken | Verify Bootstrap grid structure |
| Mobile display wrong | Check responsive breakpoints |
| Custom CSS ignored | Verify CSS page is loading |

### Debugging Tips

1. **Browser DevTools** – Inspect element to see applied styles
2. **Check specificity** – More specific selectors override general ones
3. **Clear cache** – Browser may cache old CSS
4. **Validate CSS** – Check for syntax errors

### Override Strategies

**When Bootstrap styles conflict:**

```css
/* Increase specificity */
.my-section .btn-primary {
  background-color: #custom;
}

/* Use !important (sparingly) */
.force-style {
  color: red !important;
}

/* Use CSS variables */
.btn-primary {
  --bs-btn-bg: #custom;
}
```

---

*Previous: [Sections and Components](sections-and-components.md) | Next: [Data Management](data-management.md)*


