# Glossary

A comprehensive reference of terms used in SelfHelp CMS.

---

## A

### ACL (Access Control List)
A permission system that defines which users or groups can access specific pages or perform specific actions (select, insert, update, delete).

### Action
An automated task triggered by form submissions or scheduled events. Actions can send emails, add/remove users from groups, or execute custom tasks.

### APCu
Alternative PHP Cache User – a caching system used by SelfHelp to improve performance by storing frequently accessed data in memory.

---

## B

### Base Component
The foundation class (`BaseComponent.php`) from which all SelfHelp components inherit. Defines the basic MVC structure.

### Bootstrap
The CSS framework (version 4.6) integrated into SelfHelp for responsive design and UI components.

---

## C

### Cache
Temporary storage for frequently accessed data to improve performance. SelfHelp uses APCu for caching.

### Child Section
A section nested inside another section. Container components can have child sections.

### Clockwork
A debugging tool integrated into SelfHelp for performance monitoring and profiling.

### CMS (Content Management System)
The administrative interface for creating and managing content in SelfHelp.

### Component
A reusable UI element that can be configured and placed on pages. Also called a "style" in the codebase.

### Condition
A rule that determines whether a section is displayed. Conditions can check user properties, data values, or other criteria.

### Controller
The C in MVC – handles user interaction and form submissions for a component.

---

## D

### Data Configuration (data_config)
A JSON configuration that specifies how to retrieve data from dataTables for display or interpolation.

### dataCells
Database table storing individual cell values (row + column + value).

### dataCols
Database table defining columns for dataTables.

### dataRows
Database table storing individual records with user associations and timestamps.

### dataTables
The unified data storage system in SelfHelp. A virtual table system for storing structured data.

### Debug Mode
A configuration option that enables detailed error messages and logging for development.

### Dual Rendering
SelfHelp's ability to output content as HTML (for web) and JSON (for mobile apps) from the same configuration.

---

## E

### Entry Record
A data record accessed in list/detail views. Variables like `$record_id` and `$field_name` reference entry data.

### entryList
A component that displays multiple data records with customisable templates.

### entryRecord
A component that wraps the display of a single data record within an entryList.

---

## F

### Field
A configuration option for a component (e.g., `title`, `css`, `url`). Also refers to form input fields.

### Field Type
The data type of a field (text, markdown, json, select, checkbox, etc.).

### Form Action
An automated task triggered when a form is submitted.

### formUserInput
A form component that saves submitted data to dataTables.

---

## G

### Global Variables
System-wide variables accessible in interpolation (e.g., `{{@user}}`, `{{@project}}`).

### Group
A collection of users with shared permissions. Users can belong to multiple groups.

### Guest User
The default user (ID: 1) representing unauthenticated visitors.

---

## H

### Hook
A mechanism allowing plugins to modify core behaviour by intercepting method calls.

### Hook Type
The type of hook: `hook_on_function_execute` (run alongside) or `hook_overwrite_return` (replace result).

---

## I

### Interpolation
The process of replacing variable placeholders (e.g., `{{@user}}`) with actual values.

### Internal Page
A page type that requires user authentication to access.

---

## J

### Job
A scheduled task queued for execution. Jobs can be emails, notifications, or custom tasks.

### Job Scheduler
The service that manages scheduled jobs and their execution.

### Journal Mode
Form mode (`is_log: 1`) that creates a new record for each submission, preserving historical data.

---

## K

### Keyword
The unique URL-friendly identifier for a page (e.g., `home`, `contact`, `profile`).

---

## L

### Lookup
A predefined set of values stored in the `lookups` table. Used for statuses, types, and other enumerations.

---

## M

### Markdown
A text formatting syntax supported by SelfHelp for rich content. The `markdown` component renders Markdown as HTML.

### Migration
A database update script that modifies the schema when upgrading SelfHelp versions.

### Mobile Output
The JSON representation of page content for mobile applications (Ionic Angular).

### Model
The M in MVC – handles data loading and business logic for a component.

### MVC (Model-View-Controller)
The architectural pattern used by SelfHelp components for separation of concerns.

---

## N

### Navigation
The system of menus and links that allow users to move between pages.

### Navigation Position (nav_position)
The order in which a page appears in the navigation menu.

---

## O

### Open Page
A page type accessible to everyone without authentication.

---

## P

### Page
A URL-addressable container that holds sections and content.

### Page Access Type
Configuration determining which platforms can access a page (web, mobile, or both).

### Page Type
The access level of a page (Core, Open, Internal, Backend, etc.).

### Parsedown
The Markdown parsing library used by SelfHelp.

### Persistent Mode
Form mode (`is_log: 0`) that updates the same record for each user submission.

### Plugin
An extension that adds functionality to SelfHelp without modifying core code.

---

## R

### Router
The service that maps URLs to pages and handles routing.

### Route
A URL pattern that maps to a specific page.

---

## S

### Scheduled Job
A task queued for future execution, such as sending emails or running scripts.

### Section
A configured instance of a component placed on a page.

### Section Hierarchy
The parent-child relationship between sections (stored in `sections_hierarchy`).

### Services
The dependency injection container that provides access to system services (database, router, ACL, etc.).

### Session
The server-side storage of user state between requests.

### Style
The internal name for a component type (e.g., `card`, `markdown`, `input`).

### Style Group
A category grouping related components (e.g., Layout, Forms, Data).

---

## T

### Template
A PHP file that defines the HTML structure for a component's output.

### Transaction
A logged database operation for audit purposes.

### Trigger
The condition or event that initiates an action (e.g., form submission, schedule).

### Two-Factor Authentication (2FA)
An additional security layer requiring a verification code sent via email.

---

## U

### User
An authenticated account in the system.

### User Input
Data submitted by users through forms.

### UserInput Service
The service that handles form data storage and retrieval.

---

## V

### Validation Code
A temporary code used for email verification or password reset.

### Variable
A placeholder that gets replaced with actual values during rendering (e.g., `{{@user}}`).

### View
The V in MVC – handles HTML/JSON output generation for a component.

---

## W

### Workflow
An automated sequence of actions triggered by events.

---

## Symbols and Syntax

### `{{variable}}`
Double curly braces – variable interpolation syntax.

### `{{@variable}}`
Global variable prefix.

### `{{#param}}`
URL parameter prefix.

### `$variable`
Entry record variable prefix.

### `__variable__`
System variable format (e.g., `__keyword__`, `__language__`).

---

## Database Tables Quick Reference

| Table | Purpose |
|-------|---------|
| `users` | User accounts |
| `groups` | User groups |
| `users_groups` | User-group memberships |
| `pages` | Page definitions |
| `sections` | Section instances |
| `sections_fields_translation` | Section field values |
| `sections_hierarchy` | Section parent-child relationships |
| `dataTables` | Data table definitions |
| `dataRows` | Data records |
| `dataCols` | Data columns |
| `dataCells` | Data values |
| `acl_groups` | Group permissions |
| `acl_users` | User-specific permissions |
| `formActions` | Form action definitions |
| `scheduledJobs` | Job queue |
| `mailQueue` | Email queue |
| `transactions` | Audit log |
| `lookups` | Lookup values |
| `hooks` | Plugin hooks |

---

## Component Categories Quick Reference

| Category | Components |
|----------|------------|
| **Content** | markdown, heading, plaintext, rawText |
| **Layout** | container, div, card, tabs, accordion |
| **Forms** | formUserInput, input, select, textarea, checkbox, radio |
| **Navigation** | navigation, navigationBar, link, button |
| **Data Display** | showUserInput, table, dataContainer, entryList |
| **Media** | image, video, audio, carousel |
| **Interactive** | modal, alert, progressBar |
| **Authentication** | login, register, resetPassword, twoFactorAuth |

---

*Previous: [API Reference](api-reference.md) | [Return to Home](README.md)*


