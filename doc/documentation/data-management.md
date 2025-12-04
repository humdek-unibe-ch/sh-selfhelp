# Data Management

This guide covers the unified dataTables system for storing, retrieving, and displaying data in SelfHelp CMS.

---

## Table of Contents

1. [Data Architecture Overview](#data-architecture-overview)
2. [Understanding dataTables](#understanding-datatables)
3. [Creating Forms](#creating-forms)
4. [Data Retrieval](#data-retrieval)
5. [Variable Interpolation](#variable-interpolation)
6. [Data Configuration](#data-configuration)
7. [File Uploads](#file-uploads)
8. [Best Practices](#best-practices)

---

## Data Architecture Overview

### Unified Data Storage

SelfHelp uses a unified **dataTables** system for all data storage:

```
┌─────────────────────────────────────────────────────────────────┐
│                    DATATABLE STRUCTURE                          │
│                                                                 │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │ dataTables  │    │  dataRows   │    │  dataCells  │         │
│  │             │───▶│             │───▶│             │         │
│  │ - id        │    │ - id        │    │ - id_dataRows│        │
│  │ - name      │    │ - id_table  │    │ - id_dataCols│        │
│  │ - displayName│   │ - id_users  │    │ - value     │         │
│  └─────────────┘    │ - timestamp │    └─────────────┘         │
│                     └─────────────┘                             │
│                            │                                    │
│                            ▼                                    │
│                     ┌─────────────┐                             │
│                     │  dataCols   │                             │
│                     │             │                             │
│                     │ - id        │                             │
│                     │ - name      │                             │
│                     │ - id_table  │                             │
│                     └─────────────┘                             │
└─────────────────────────────────────────────────────────────────┘
```

### Core Tables

| Table | Purpose |
|-------|---------|
| `dataTables` | Table definitions (name, displayName) |
| `dataCols` | Column definitions per table |
| `dataRows` | Individual records (linked to users, timestamps) |
| `dataCells` | Cell values (row + column + value) |

### Data Flow

```
User Input → Form Component → UserInput Service → dataTables
                                     │
                                     ▼
Data Display ← Component View ← UserInput Service ← Database Query
```

---

## Understanding dataTables

### What is a dataTable?

A **dataTable** is a virtual table that stores structured data:

- **Name** – Unique identifier (often section ID or custom name)
- **DisplayName** – Human-readable name
- **Columns** – Dynamic fields based on form inputs
- **Rows** – Individual records with user and timestamp

### Table Creation

Tables are created automatically when:

1. A `formUserInput` component is used
2. Data is imported via API
3. An action creates table entries

### Table Structure Example

```
Table: contact_form (displayName: "Contact Submissions")
├── Columns:
│   ├── name (text)
│   ├── email (email)
│   ├── message (textarea)
│   └── submitted_date (datetime)
├── Rows:
│   ├── Row 1: user_id=5, timestamp=2024-01-15...
│   ├── Row 2: user_id=8, timestamp=2024-01-16...
│   └── Row 3: user_id=5, timestamp=2024-01-17...
```

### Viewing dataTables

Access stored data through:

1. **Admin Interface** – Data tables management page
2. **showUserInput Component** – Display in pages
3. **API** – RESTful data access
4. **Database Views** – `view_datatables_data`

---

## Creating Forms

### Basic Form Structure

Forms require a container and input fields:

```
formUserInput (parent)
├── input (name: "full_name")
├── input (name: "email", type: "email")
├── textarea (name: "message")
└── checkbox (name: "subscribe")
```

### Form Components

#### `formUserInput`

The main form wrapper that handles data saving.

| Field | Description | Default |
|-------|-------------|---------|
| `name` | Table name for data storage | Section ID |
| `is_log` | 0=Persistent, 1=Journal | 0 |
| `label_submit` | Submit button text | "Submit" |
| `url_success` | Redirect URL after success | (none) |
| `ajax` | Submit without page reload | false |

#### Input Fields

| Component | Purpose | Key Fields |
|-----------|---------|------------|
| `input` | Text, email, number, date | name, type_input, label |
| `textarea` | Multi-line text | name, label, rows |
| `select` | Dropdown selection | name, items, is_multiple |
| `checkbox` | Boolean toggle | name, label |
| `radio` | Option buttons | name, items |
| `slider` | Range selection | name, min, max |

### Form Modes

#### Persistent Mode (`is_log: 0`)

- Updates existing record if user has one
- Creates new record on first submission
- User can edit their data

**Use for:** Profile information, preferences, settings

#### Journal Mode (`is_log: 1`)

- Creates new record every submission
- Records are timestamped
- Historical data preserved

**Use for:** Surveys, feedback, activity logs

### Form Example

Create a contact form:

1. **Add `formUserInput` section:**
   ```
   name: contact_submissions
   is_log: 1
   label_submit: Send Message
   url_success: /thank-you
   ```

2. **Add child input for name:**
   ```
   name: sender_name
   type_input: text
   label: Your Name
   is_required: true
   ```

3. **Add child input for email:**
   ```
   name: sender_email
   type_input: email
   label: Email Address
   is_required: true
   ```

4. **Add child textarea for message:**
   ```
   name: message
   label: Your Message
   is_required: true
   ```

### Form Validation

#### Required Fields

Set `is_required: true` on input components.

#### Input Types

Use appropriate `type_input` values for validation:

| Type | Validation |
|------|------------|
| `email` | Email format |
| `number` | Numeric only |
| `date` | Date picker |
| `tel` | Phone format |
| `url` | URL format |

#### Custom Validation

Use the `validate` component for complex rules:

```
validate
├── condition: [validation rules]
├── message: "Please correct the errors"
└── children: [form fields to validate]
```

---

## Data Retrieval

### showUserInput Component

Display data from dataTables:

| Field | Description |
|-------|-------------|
| `source` | Table name or ID |
| `columns` | Columns to display (JSON) |
| `filter` | SQL WHERE clause |
| `is_editable` | Allow editing |

### Column Configuration

```json
[
  {"field": "name", "label": "Name", "sortable": true},
  {"field": "email", "label": "Email Address"},
  {"field": "timestamp", "label": "Submitted", "format": "date"}
]
```

### Filtering Data

Use SQL-like filter expressions:

```
filter: "AND status = 'active'"
filter: "AND submitted_date > '2024-01-01'"
filter: "ORDER BY name ASC LIMIT 10"
```

### Data in Containers

Use `dataContainer` with `data_config` for complex scenarios:

```
dataContainer
├── data_config: [configuration]
└── children: [components with data binding]
```

### entryList and entryRecord

For displaying multiple records with rich formatting:

```
entryList
├── source: table_name
├── filter: [conditions]
└── children:
    └── entryRecord
        └── children: [display components with $variables]
```

---

## Variable Interpolation

### Interpolation Syntax

Insert dynamic values using double curly braces:

```
Hello, {{@user}}!
```

### Global Variables

| Variable | Description |
|----------|-------------|
| `{{@user}}` | Current user's name |
| `{{@user_email}}` | User's email address |
| `{{@user_code}}` | User's unique code |
| `{{@project}}` | Project name |

### System Variables

| Variable | Description |
|----------|-------------|
| `{{__keyword__}}` | Current page keyword |
| `{{__language__}}` | User's language ID |
| `{{__platform__}}` | web or mobile |

### URL Parameters

Access route parameters with hash:

```
User ID: {{#id}}
Category: {{#category}}
```

### Entry Record Variables

In list contexts, use dollar sign:

```
Record: $record_id
Value: $field_name
```

### Data Config Variables

From `data_config` results:

```
{{table_name.field_name}}
{{scope.field_name}}
```

### Interpolation Example

```markdown
# Profile for {{@user}}

**Email:** {{@user_email}}
**Member since:** {{profile.registration_date}}
**Points:** {{activity.total_points}}
```

---

## Data Configuration

### data_config Field

Complex data retrieval configuration:

```json
[
  {
    "table": "user_profile",
    "retrieve": "first",
    "current_user": true,
    "fields": [
      {"field_name": "bio", "field_holder": "user_bio", "not_found_text": "N/A"}
    ]
  }
]
```

### Configuration Options

| Option | Description | Values |
|--------|-------------|--------|
| `table` | Source table name | String |
| `retrieve` | Retrieval mode | first, last, all, JSON |
| `current_user` | Filter by current user | true/false |
| `filter` | SQL filter | String |
| `fields` | Field mapping | Array |
| `scope` | Variable prefix | String |

### Retrieval Modes

| Mode | Description |
|------|-------------|
| `first` | First matching record |
| `last` | Most recent record |
| `all` | All records (comma-separated) |
| `all_as_array` | All records (JSON array) |
| `JSON` | Full table as JSON |

### Field Mapping

```json
{
  "field_name": "source_column",
  "field_holder": "variable_name",
  "not_found_text": "Default if empty"
}
```

### Scope Usage

Add prefixes to avoid variable conflicts:

```json
{
  "table": "activity",
  "scope": "user_activity",
  "all_fields": true
}
```

Access as: `{{user_activity.total_points}}`

---

## File Uploads

### Upload Component

Use `input` with `type_input: file`:

```
name: document
type_input: file
label: Upload Document
accept: .pdf,.doc,.docx
```

### File Storage

Uploaded files are stored in:
- **Database:** File metadata in `uploadRows`
- **Filesystem:** Actual files in `/uploads/` directory

### Retrieving Files

Files are returned as URLs in data retrieval:

```
{{profile.avatar_url}}
```

### Image Display

Display uploaded images:

```
image
├── source: {{profile.photo}}
├── alt: Profile Photo
```

---

## Best Practices

### Form Design

1. **Clear Labels** – Use descriptive field labels
2. **Logical Order** – Arrange fields naturally
3. **Required Fields** – Mark mandatory fields clearly
4. **Validation** – Use appropriate input types
5. **Error Messages** – Provide helpful feedback

### Data Structure

1. **Consistent Naming** – Use snake_case for field names
2. **Meaningful Names** – `user_email` not `ue1`
3. **One Purpose** – Each table for one data type
4. **Documentation** – Document table purposes

### Performance

1. **Index Key Fields** – For frequently queried columns
2. **Limit Results** – Use filters to reduce data
3. **Cache Data** – Use data containers for repeated access
4. **Paginate Lists** – Don't load thousands of records

### Security

1. **Sanitise Input** – Never trust user data
2. **Access Control** – Restrict data by user
3. **Filter Carefully** – Avoid SQL injection in filters
4. **Audit Trail** – Use journal mode for sensitive data

### Data Integrity

| Practice | Implementation |
|----------|----------------|
| Unique values | Add UNIQUE constraint in schema |
| Required fields | Set `is_required: true` |
| Valid formats | Use appropriate `type_input` |
| Data validation | Use `validate` component |

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Data not saving | Check form structure, required fields |
| Wrong data displayed | Verify source table, filters |
| Variables not replacing | Check syntax, data availability |
| Duplicates appearing | Check `is_log` mode |

### Debugging Data

1. **Check dataTables** – Verify table exists
2. **View raw data** – Use database admin or API
3. **Test interpolation** – Use debug mode
4. **Check permissions** – User access to data

---

*Previous: [Styling Guide](styling-guide.md) | Next: [User Management](user-management.md)*

