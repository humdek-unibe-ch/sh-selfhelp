# Actions and Workflows

This guide covers automated actions, scheduled jobs, and workflow automation in SelfHelp CMS.

---

## Table of Contents

1. [Workflow System Overview](#workflow-system-overview)
2. [Form Actions](#form-actions)
3. [Scheduled Jobs](#scheduled-jobs)
4. [Email Notifications](#email-notifications)
5. [Task Automation](#task-automation)
6. [Action Triggers](#action-triggers)
7. [Conditional Logic](#conditional-logic)
8. [Best Practices](#best-practices)

---

## Workflow System Overview

### Automation Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    WORKFLOW SYSTEM                              │
│                                                                 │
│  ┌───────────────┐                                             │
│  │    TRIGGER    │  Form submission, schedule, condition       │
│  └───────┬───────┘                                             │
│          │                                                      │
│          ▼                                                      │
│  ┌───────────────┐                                             │
│  │   EVALUATE    │  Check conditions, process data             │
│  └───────┬───────┘                                             │
│          │                                                      │
│          ▼                                                      │
│  ┌───────────────┐                                             │
│  │    ACTION     │  Email, notification, task, group change    │
│  └───────┬───────┘                                             │
│          │                                                      │
│          ▼                                                      │
│  ┌───────────────┐                                             │
│  │  SCHEDULED    │  Queue for execution                        │
│  │     JOB       │                                             │
│  └───────────────┘                                             │
└─────────────────────────────────────────────────────────────────┘
```

### Core Components

| Component | Purpose |
|-----------|---------|
| **Form Actions** | Actions triggered by form submissions |
| **Scheduled Jobs** | Queued tasks for execution |
| **Email Queue** | Pending email notifications |
| **Notifications** | Push notifications |
| **Tasks** | Custom automation tasks |

### Database Tables

| Table | Purpose |
|-------|---------|
| `formActions` | Action definitions |
| `scheduledJobs` | Job queue |
| `scheduledJobs_mailQueue` | Email job links |
| `scheduledJobs_notifications` | Notification links |
| `scheduledJobs_tasks` | Task job links |
| `mailQueue` | Email details |
| `notifications` | Notification details |
| `tasks` | Task configurations |

---

## Form Actions

### What are Form Actions?

Form Actions automatically execute when forms are submitted. They can:

- Send email notifications
- Add/remove users from groups
- Send push notifications
- Execute custom tasks
- Schedule future actions

### Form Action Configuration

Form actions are stored in the `formActions` table:

| Field | Description |
|-------|-------------|
| `id` | Unique identifier |
| `name` | Action name |
| `id_formProjectActionTriggerTypes` | Trigger type |
| `config` | JSON configuration |
| `id_dataTables` | Associated data table |

### Action Types

| Type | Code | Description |
|------|------|-------------|
| **Email** | `email` | Send email notification |
| **Add Group** | `add_group` | Add user to group |
| **Remove Group** | `remove_group` | Remove from group |
| **Notification** | `notification` | Push notification |
| **Task** | `task` | Custom task |

### Creating Form Actions

1. Navigate to **Admin** → **Form Actions**
2. Click **Add Action**
3. Configure:
   - **Name:** Descriptive action name
   - **Trigger Type:** When to execute
   - **Data Table:** Associated form
   - **Configuration:** Action-specific settings

### Example: Email on Form Submission

```json
{
  "type": "email",
  "recipients": ["admin@example.com"],
  "subject": "New Form Submission",
  "body": "A new form has been submitted by {{@user}}",
  "trigger": "on_insert"
}
```

---

## Scheduled Jobs

### Job Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    SCHEDULED JOBS                               │
│                                                                 │
│  ┌─────────────────┐    ┌─────────────────┐                    │
│  │  scheduledJobs  │───▶│   Job Types     │                    │
│  │                 │    │   - email       │                    │
│  │  - id_jobTypes  │    │   - notification│                    │
│  │  - id_jobStatus │    │   - task        │                    │
│  │  - date_execute │    └─────────────────┘                    │
│  │  - config       │                                           │
│  └────────┬────────┘                                           │
│           │                                                     │
│           ├────────────────┐                                   │
│           ▼                ▼                                   │
│  ┌─────────────────┐  ┌─────────────────┐                     │
│  │   mailQueue     │  │  notifications  │                     │
│  │   (emails)      │  │  (push)         │                     │
│  └─────────────────┘  └─────────────────┘                     │
└─────────────────────────────────────────────────────────────────┘
```

### Job Statuses

| Status | Code | Description |
|--------|------|-------------|
| **Queued** | `queued` | Waiting for execution |
| **Processing** | `processing` | Currently executing |
| **Completed** | `done` | Successfully finished |
| **Failed** | `failed` | Execution error |
| **Deleted** | `deleted` | Soft deleted |

### Job Types

| Type | Description |
|------|-------------|
| `email` | Send email |
| `notification` | Push notification |
| `task` | Custom task |
| `reminder` | Scheduled reminder |

### Job Processing

Jobs are processed by cron:

```bash
# Run every minute
* * * * * php /path/to/selfhelp/server/cronjobs/job_scheduler.php
```

### Viewing Jobs

Navigate to **Admin** → **Scheduled Jobs**:

- Filter by status, type, date
- View job details
- Retry failed jobs
- Cancel pending jobs

---

## Email Notifications

### Email System

```
┌─────────────────────────────────────────────────────────────────┐
│                    EMAIL WORKFLOW                               │
│                                                                 │
│  Action Triggered → mailQueue Entry → Scheduled Job → Mailer   │
│                                                                 │
│  ┌─────────────────┐                                           │
│  │   mailQueue     │                                           │
│  │                 │                                           │
│  │  - from_email   │                                           │
│  │  - recipient_emails                                         │
│  │  - subject      │                                           │
│  │  - body         │                                           │
│  │  - is_html      │                                           │
│  └─────────────────┘                                           │
└─────────────────────────────────────────────────────────────────┘
```

### Email Configuration

| Field | Description |
|-------|-------------|
| `from_email` | Sender email address |
| `from_name` | Sender name |
| `reply_to` | Reply-to address |
| `recipient_emails` | Recipients (comma-separated) |
| `cc_emails` | CC recipients |
| `bcc_emails` | BCC recipients |
| `subject` | Email subject |
| `body` | Email content |
| `is_html` | HTML format flag |

### Email Templates

Use interpolation in email content:

```
Subject: Welcome, {{@user}}!

Body:
Dear {{@user}},

Thank you for registering. Your account has been created.

Email: {{@user_email}}
Registration Date: {{current_date}}

Best regards,
The {{@project}} Team
```

### Email Attachments

Attachments via `mailAttachments` table:

| Field | Description |
|-------|-------------|
| `attachment_name` | Display name |
| `attachment_path` | File system path |
| `attachment_url` | Web URL |

---

## Task Automation

### Task Types

| Task | Description |
|------|-------------|
| **Add to Group** | Add user to specified group |
| **Remove from Group** | Remove user from group |
| **Custom Script** | Execute custom code |
| **R Script** | Run R statistical analysis |
| **External API** | Call external services |

### Add to Group Task

Configuration:
```json
{
  "type": "add_group",
  "group_id": 5,
  "user_source": "form_field",
  "field_name": "user_id"
}
```

### Remove from Group Task

Configuration:
```json
{
  "type": "remove_group",
  "group_id": 3,
  "user_source": "current_user"
}
```

### Custom Tasks

Tasks are defined in the `tasks` table:

```json
{
  "task_type": "custom",
  "script": "my_custom_task",
  "parameters": {
    "param1": "value1",
    "param2": "{{form_field}}"
  }
}
```

---

## Action Triggers

### Trigger Types

| Trigger | Code | When Executed |
|---------|------|---------------|
| **On Insert** | `on_insert` | New record created |
| **On Update** | `on_update` | Record modified |
| **On Delete** | `on_delete` | Record deleted |
| **Scheduled** | `scheduled` | At specific time |
| **Manual** | `manual` | User-triggered |

### Trigger Configuration

```json
{
  "trigger_type": "on_insert",
  "conditions": [
    {
      "field": "status",
      "operator": "=",
      "value": "approved"
    }
  ]
}
```

### Delayed Execution

Schedule actions for future execution:

```json
{
  "trigger_type": "scheduled",
  "delay": {
    "value": 24,
    "unit": "hours"
  }
}
```

### Recurring Actions

Use reminders for recurring actions:

```json
{
  "trigger_type": "reminder",
  "schedule": {
    "frequency": "daily",
    "time": "09:00"
  }
}
```

---

## Conditional Logic

### Condition System

Actions can have conditions that must be met:

```json
{
  "conditions": {
    "type": "and",
    "rules": [
      {
        "field": "{{status}}",
        "operator": "=",
        "value": "active"
      },
      {
        "field": "{{score}}",
        "operator": ">=",
        "value": 80
      }
    ]
  }
}
```

### Operators

| Operator | Description |
|----------|-------------|
| `=` | Equal |
| `!=` | Not equal |
| `>` | Greater than |
| `<` | Less than |
| `>=` | Greater or equal |
| `<=` | Less or equal |
| `contains` | String contains |
| `in` | Value in list |
| `not_in` | Value not in list |
| `is_empty` | Field is empty |
| `is_not_empty` | Field has value |

### Condition Groups

Combine conditions with logic:

```json
{
  "type": "or",
  "rules": [
    {
      "type": "and",
      "rules": [
        {"field": "role", "operator": "=", "value": "admin"},
        {"field": "active", "operator": "=", "value": true}
      ]
    },
    {
      "field": "override", "operator": "=", "value": true
    }
  ]
}
```

---

## Best Practices

### Action Design

1. **Clear naming** – Use descriptive action names
2. **Single purpose** – One action, one task
3. **Idempotent** – Safe to run multiple times
4. **Error handling** – Plan for failures

### Email Best Practices

| Practice | Reason |
|----------|--------|
| Test templates | Verify interpolation works |
| Use HTML sparingly | Plain text more reliable |
| Include unsubscribe | Compliance |
| Rate limit | Prevent spam flags |

### Job Management

1. **Monitor queue** – Check for stuck jobs
2. **Retry strategy** – Configure retry limits
3. **Logging** – Enable detailed logs
4. **Cleanup** – Archive old completed jobs

### Performance

1. **Batch operations** – Group similar actions
2. **Off-peak scheduling** – Schedule heavy tasks at low-traffic times
3. **Timeout configuration** – Set appropriate limits
4. **Resource limits** – Prevent runaway processes

### Debugging Workflows

1. **Check job status** – View in admin interface
2. **Review logs** – Check transaction logs
3. **Test conditions** – Verify condition evaluation
4. **Manual execution** – Test individual actions

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Action not triggering | Check trigger type, conditions |
| Email not sent | Check mail configuration, queue |
| Job stuck processing | Check for errors, restart |
| Conditions not matching | Debug condition evaluation |

### Debug Steps

1. **Verify trigger** – Is the action linked to the form?
2. **Check conditions** – Are conditions evaluating correctly?
3. **View job queue** – Is a job created?
4. **Review job status** – Any errors?
5. **Check logs** – Transaction logs for details

### Log Analysis

```sql
-- Find failed jobs
SELECT * FROM scheduledJobs 
WHERE id_jobStatus = (SELECT id FROM lookups WHERE lookup_code = 'failed')
ORDER BY date_create DESC;

-- View job transactions
SELECT * FROM transactions 
WHERE table_name = 'scheduledJobs'
ORDER BY transaction_time DESC;
```

---

*Previous: [Permissions and Security](permissions-and-security.md) | Next: [Advanced Features](advanced-features.md)*

