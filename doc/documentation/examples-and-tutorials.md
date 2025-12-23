# Examples and Tutorials

This guide provides step-by-step tutorials and practical examples for common SelfHelp CMS tasks.

---

## Table of Contents

1. [Building a Contact Form](#building-a-contact-form)
2. [Creating a User Dashboard](#creating-a-user-dashboard)
3. [Setting Up Navigation](#setting-up-navigation)
4. [Implementing Data Collection](#implementing-data-collection)
5. [Conditional Content Display](#conditional-content-display)
6. [Email Automation](#email-automation)
7. [User Registration Flow](#user-registration-flow)
8. [Data Display Examples](#data-display-examples)

---

## Building a Contact Form

### Overview

Create a contact form that:
- Collects name, email, and message
- Validates required fields
- Saves to database
- Sends email notification
- Redirects to thank you page

### Step 1: Create the Page

1. Go to **Admin** → **Pages**
2. Click **Add Page**
3. Configure:
   ```
   Keyword: contact
   URL: /contact
   Protocol: GET|POST
   Page Type: Open
   Nav Position: 4
   ```

### Step 2: Create Thank You Page

1. Add another page:
   ```
   Keyword: contact-thank-you
   URL: /contact/thank-you
   Protocol: GET
   Page Type: Open
   ```
2. Add a `markdown` section:
   ```markdown
   # Thank You!
   
   Your message has been sent. We'll respond within 24 hours.
   
   [Return to Home](/home)
   ```

### Step 3: Build the Form

1. Open the contact page in CMS
2. Add a `formUserInput` section:
   ```
   Name: contact-form
   is_log: 1 (Journal mode - new record each submission)
   label_submit: Send Message
   url_success: /contact/thank-you
   ```

3. Add child `input` for name:
   ```
   name: sender_name
   type_input: text
   label: Your Name
   is_required: true
   placeholder: Enter your full name
   ```

4. Add child `input` for email:
   ```
   name: sender_email
   type_input: email
   label: Email Address
   is_required: true
   placeholder: your@email.com
   ```

5. Add child `textarea` for message:
   ```
   name: message
   label: Your Message
   is_required: true
   rows: 5
   placeholder: How can we help?
   ```

### Step 4: Add Email Notification

1. Go to **Admin** → **Form Actions**
2. Create new action:
   ```
   Name: Contact Form Notification
   Trigger Type: On Insert
   Data Table: contact-form
   ```
3. Configure email:
   ```json
   {
     "type": "email",
     "recipients": ["admin@yoursite.com"],
     "subject": "New Contact Form Submission",
     "body": "New message from {{sender_name}} ({{sender_email}}):\n\n{{message}}"
   }
   ```

### Result

Users can now submit contact forms, data is saved, and you receive email notifications.

---

## Creating a User Dashboard

### Overview

Build a personalised dashboard showing:
- Welcome message with user's name
- User's recent activity
- Quick action buttons

### Step 1: Create Dashboard Page

```
Keyword: dashboard
URL: /dashboard
Protocol: GET
Page Type: Internal (requires login)
Nav Position: 2
```

### Step 2: Welcome Section

Add a `container` section with nested `markdown`:

```markdown
# Welcome back, {{@user}}!

Last login: {{profile.last_login}}

Here's your personalised dashboard.
```

### Step 3: Create Profile Data Form

1. Add a profile page with `formUserInput`:
   ```
   name: user_profile
   is_log: 0 (Persistent - updates same record)
   ```

2. Add profile fields:
   - `bio` (textarea)
   - `phone` (input, type: tel)
   - `preferences` (select)

### Step 4: Display Profile Data

Add `dataContainer` with data_config:

```json
[
  {
    "table": "user_profile",
    "retrieve": "first",
    "current_user": true,
    "all_fields": true,
    "scope": "profile"
  }
]
```

### Step 5: Add Quick Actions

Add `container` with button children:

```
button: Edit Profile
  url: /profile/edit
  type: primary
  css: mr-2

button: View History
  url: /history
  type: secondary
```

---

## Setting Up Navigation

### Overview

Create a multi-level navigation structure.

### Step 1: Plan Hierarchy

```
Home (nav: 1)
├── About (nav: 2)
│   ├── Our Team
│   └── History
├── Services (nav: 3)
│   ├── Consulting
│   └── Training
└── Contact (nav: 4)
```

### Step 2: Create Parent Pages

For each top-level page, set `nav_position`:

| Page | nav_position |
|------|--------------|
| Home | 1 |
| About | 2 |
| Services | 3 |
| Contact | 4 |

### Step 3: Create Child Pages

For child pages, set the `parent` field:

```
Page: our-team
Parent: about
Nav Position: 1 (within parent)
```

### Step 4: Add Navigation Component

On your main template/layout, add `navigation`:

```
navigation
  css: navbar-nav
  type: nested (for dropdowns)
```

Or use `navigationBar` for Bootstrap navbar:

```
navigationBar
  brand: Your Site
  brand_url: /home
  css: bg-light
```

### Result

Multi-level navigation with dropdowns for pages with children.

---

## Implementing Data Collection

### Overview

Create a survey/questionnaire that:
- Collects multiple answers
- Shows progress
- Displays results

### Step 1: Create Survey Pages

```
Page 1: survey-start (/survey)
Page 2: survey-questions (/survey/questions)
Page 3: survey-complete (/survey/complete)
```

### Step 2: Build Question Form

On `survey-questions`, add `formUserInput`:

```
name: survey_responses
is_log: 1
url_success: /survey/complete
```

Add questions using appropriate components:

**Rating Question (slider):**
```
slider
  name: satisfaction_rating
  label: How satisfied are you? (1-10)
  min: 1
  max: 10
  step: 1
```

**Multiple Choice (radio):**
```
radio
  name: frequency
  label: How often do you use our service?
  items: [
    {"value": "daily", "text": "Daily"},
    {"value": "weekly", "text": "Weekly"},
    {"value": "monthly", "text": "Monthly"},
    {"value": "rarely", "text": "Rarely"}
  ]
```

**Open Response (textarea):**
```
textarea
  name: feedback
  label: Additional comments
  rows: 4
```

### Step 3: Display Results

On admin page, add `showUserInput`:

```
showUserInput
  source: survey_responses
  columns: [
    {"field": "satisfaction_rating", "label": "Rating"},
    {"field": "frequency", "label": "Frequency"},
    {"field": "timestamp", "label": "Submitted"}
  ]
```

---

## Conditional Content Display

### Overview

Show different content based on:
- User group membership
- Data values
- URL parameters

### Example 1: Admin-Only Content

Add condition to section:

```json
{
  "type": "operator",
  "field": "{{user_group}}",
  "operator": "in",
  "value": ["admin"]
}
```

### Example 2: Show After Form Completion

```json
{
  "type": "operator",
  "field": "{{profile.completed}}",
  "operator": "=",
  "value": "1"
}
```

### Example 3: Conditional Container

Use `conditionalContainer` with two children:

1. `conditionMet` section - shown when true
2. `conditionFailed` section - shown when false

```
conditionalContainer
  condition: {...}
  children:
    - markdownWhenTrue (conditionMet)
    - markdownWhenFalse (conditionFailed)
```

### Example 4: Time-Based Content

```json
{
  "type": "operator",
  "field": "{{__current_hour__}}",
  "operator": ">=",
  "value": 9
}
```

---

## Email Automation

### Overview

Set up automated emails for:
- Welcome new users
- Form submission confirmations
- Scheduled reminders

### Example 1: Welcome Email

1. Create form action on registration form
2. Configure:

```json
{
  "type": "email",
  "recipients": ["{{sender_email}}"],
  "subject": "Welcome to {{@project}}!",
  "body": "Dear {{sender_name}},\n\nWelcome! Your account has been created.\n\nBest regards,\nThe Team"
}
```

### Example 2: Admin Notification

```json
{
  "type": "email",
  "recipients": ["admin@site.com", "manager@site.com"],
  "subject": "[New Registration] {{sender_name}}",
  "body": "A new user has registered:\n\nName: {{sender_name}}\nEmail: {{sender_email}}"
}
```

### Example 3: Scheduled Reminder

Create a reminder action:

```json
{
  "type": "email",
  "trigger": "scheduled",
  "delay": {"value": 7, "unit": "days"},
  "condition": {
    "field": "{{profile.completed}}",
    "operator": "=",
    "value": "0"
  },
  "subject": "Complete Your Profile",
  "body": "Hi {{@user}}, please complete your profile to access all features."
}
```

---

## User Registration Flow

### Overview

Custom registration with:
- Email verification
- Profile completion
- Group assignment

### Step 1: Registration Page

Use `register` component:

```
register
  url_login: /dashboard
  label_registration: Create Account
  show_name: true
  terms_text: I agree to the [terms](/terms)
```

### Step 2: Email Verification

Configure email verification in system settings:
- Enable email verification
- Set verification email template
- Configure redirect URLs

### Step 3: Profile Completion

Create profile form on a separate page:

```
formUserInput
  name: user_profile
  is_log: 0
  children:
    - input: full_name
    - select: department
    - textarea: bio
```

### Step 4: Assign to Group

Create form action:

```json
{
  "type": "add_group",
  "group_id": 5,
  "trigger": "on_insert"
}
```

---

## Data Display Examples

### Example 1: Simple Data Table

```
showUserInput
  source: contact-form
  columns: [
    {"field": "sender_name", "label": "Name", "sortable": true},
    {"field": "sender_email", "label": "Email"},
    {"field": "timestamp", "label": "Date", "format": "datetime"}
  ]
  filter: "ORDER BY timestamp DESC LIMIT 10"
```

### Example 2: Card-Based List

Use `entryList` with `card` template:

```
entryList
  source: products
  children:
    entryRecord
      children:
        card
          title: $product_name
          children:
            markdown: "**Price:** $price\n\n$description"
            button:
              label: View Details
              url: /product/$record_id
```

### Example 3: Data in Cards

```
dataContainer
  data_config: [...]
  children:
    card
      title: User Statistics
      children:
        markdown: |
          - **Total Points:** {{stats.total_points}}
          - **Rank:** {{stats.rank}}
          - **Level:** {{stats.level}}
```

### Example 4: Filtered Data Display

```
showUserInput
  source: orders
  filter: "AND status = 'pending' ORDER BY created_at DESC"
  columns: [
    {"field": "order_id", "label": "Order #"},
    {"field": "customer_name", "label": "Customer"},
    {"field": "total", "label": "Total", "format": "currency"}
  ]
```

---

## Tips for Success

### Planning

1. **Sketch first** – Plan your page structure before building
2. **Name consistently** – Use clear, descriptive names
3. **Start simple** – Add complexity gradually
4. **Test often** – Verify each step works

### Common Mistakes to Avoid

| Mistake | Prevention |
|---------|------------|
| Duplicate names | Use unique section names |
| Missing required fields | Check form validation |
| Wrong data source | Verify table names |
| Complex conditions | Start simple, build up |

### Helpful Patterns

**Form + Thank You:**
- Form page → Thank you page
- Use `url_success` for redirect

**List + Detail:**
- List page with `entryList`
- Detail page with URL parameter
- Use `#id` for detail lookup

**Dashboard + Settings:**
- Dashboard with data display
- Settings page with forms
- Use persistent forms (`is_log: 0`)

---

*Previous: [Troubleshooting](troubleshooting.md) | Next: [API Reference](api-reference.md)*


