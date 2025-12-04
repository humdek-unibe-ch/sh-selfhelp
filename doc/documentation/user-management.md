# User Management

This guide covers user account management, authentication, profiles, and user-related functionality in SelfHelp CMS.

---

## Table of Contents

1. [User System Overview](#user-system-overview)
2. [User Accounts](#user-accounts)
3. [Authentication](#authentication)
4. [User Profiles](#user-profiles)
5. [Two-Factor Authentication](#two-factor-authentication)
6. [Password Management](#password-management)
7. [User Administration](#user-administration)
8. [Session Management](#session-management)

---

## User System Overview

### User Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                       USER SYSTEM                               │
│                                                                 │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │                      USERS                                 │ │
│  │  - id, email, name, password                              │ │
│  │  - id_genders, id_languages, id_status                    │ │
│  │  - blocked, last_login                                    │ │
│  └───────────────────────────────────────────────────────────┘ │
│                             │                                   │
│           ┌─────────────────┼─────────────────┐                │
│           ▼                 ▼                 ▼                │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐          │
│  │   Groups    │   │   Profile   │   │   Activity  │          │
│  │ users_groups│   │   Data      │   │user_activity│          │
│  └─────────────┘   └─────────────┘   └─────────────┘          │
└─────────────────────────────────────────────────────────────────┘
```

### Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Core user information |
| `users_groups` | User-group relationships |
| `users_2fa_codes` | Two-factor authentication codes |
| `user_activity` | Login and activity tracking |
| `validation_codes` | Email verification, password reset |
| `userStatus` | User status definitions |

### User States

| Status | Description |
|--------|-------------|
| **Active** | Normal account, can log in |
| **Pending** | Awaiting email verification |
| **Blocked** | Access denied |
| **Disabled** | Account deactivated |

---

## User Accounts

### User Properties

| Property | Type | Description |
|----------|------|-------------|
| `email` | VARCHAR(100) | Unique email (login identifier) |
| `name` | VARCHAR(100) | Display name |
| `password` | VARCHAR(255) | Hashed password |
| `user_name` | VARCHAR(100) | Optional username |
| `id_genders` | INT | Gender reference |
| `id_languages` | INT | Preferred language |
| `id_status` | INT | Account status |
| `blocked` | BOOLEAN | Block flag |
| `last_login` | DATE | Last login timestamp |

### Special Users

| User ID | Purpose |
|---------|---------|
| 1 | Guest user (unauthenticated) |
| 2+ | Regular user accounts |

### Creating Users

#### Via Registration Form

1. User fills out registration form
2. Email verification sent (if configured)
3. User verifies email
4. Account activated

#### Via Admin Interface

1. Navigate to **Admin** → **Users**
2. Click **Add User**
3. Fill in required fields
4. Assign groups
5. Save

#### Via API

```http
POST /api/users
Content-Type: application/json

{
  "email": "user@example.com",
  "name": "John Doe",
  "password": "SecurePassword123!"
}
```

---

## Authentication

### Login Process

```
┌─────────────────────────────────────────────────────────────────┐
│                      LOGIN FLOW                                 │
│                                                                 │
│  1. User submits credentials                                    │
│                     │                                           │
│                     ▼                                           │
│  2. Validate email/password                                     │
│                     │                                           │
│         ┌─────────┼─────────┐                                  │
│         │         │         │                                   │
│         ▼         ▼         ▼                                   │
│     Success    Failure   2FA Required                          │
│         │         │         │                                   │
│         │         │         ▼                                   │
│         │         │   Send 2FA code                            │
│         │         │         │                                   │
│         │         │         ▼                                   │
│         │         │   Verify code                              │
│         │         │         │                                   │
│         ▼         ▼         ▼                                   │
│  Create Session  Error   Create Session                        │
└─────────────────────────────────────────────────────────────────┘
```

### Login Component

Use the `login` component:

```
login
├── url_login: /home (redirect after login)
├── label_login: "Login"
├── label_password: "Password"
├── label_email: "Email"
├── show_forgot_password: true
```

### Session Variables

After login, these session variables are available:

| Variable | Description |
|----------|-------------|
| `$_SESSION['id_user']` | User ID |
| `$_SESSION['logged_in']` | Boolean login state |
| `$_SESSION['user_language']` | Language preference |
| `$_SESSION['user_gender']` | Gender preference |

### Logout

The `/logout` page clears the session and redirects to the login page.

### Authentication Components

| Component | Purpose |
|-----------|---------|
| `login` | Login form |
| `logout` | Logout trigger |
| `register` | User registration |
| `resetPassword` | Password reset |
| `twoFactorAuth` | 2FA verification |

---

## User Profiles

### Profile Component

Display and edit user profiles:

```
profile
├── show_name: true
├── show_email: true
├── show_language: true
├── show_gender: true
├── allow_edit: true
```

### Profile Fields

| Field | Description |
|-------|-------------|
| Name | User's display name |
| Email | Email address (may be read-only) |
| Language | Preferred system language |
| Gender | For content personalisation |

### Custom Profile Data

Store additional profile data in dataTables:

1. Create a `formUserInput` for profile data
2. Set `is_log: 0` (persistent mode)
3. Add fields for additional information

### Profile Display

Use interpolation to display profile data:

```markdown
## Hello, {{@user}}!

**Email:** {{@user_email}}
**Language:** {{profile.preferred_language}}
```

---

## Two-Factor Authentication

### 2FA Overview

SelfHelp supports email-based two-factor authentication:

1. User enters email/password
2. If 2FA required, code sent via email
3. User enters code
4. Access granted

### 2FA Configuration

Groups can require 2FA:

| Field | Description |
|-------|-------------|
| `requires_2fa` | Boolean flag on groups |

### 2FA Component

```
twoFactorAuth
├── label_code: "Enter verification code"
├── label_submit: "Verify"
├── resend_label: "Resend code"
```

### 2FA Flow

```
1. Login attempt
2. Check user groups for 2FA requirement
3. Generate 6-digit code
4. Store in users_2fa_codes table
5. Send code via email
6. User enters code
7. Validate and create session
```

### Code Properties

| Property | Value |
|----------|-------|
| Length | 6 digits |
| Expiry | Configurable (default: 10 minutes) |
| Single use | Yes |

---

## Password Management

### Password Requirements

Configure password strength requirements:

| Requirement | Default |
|-------------|---------|
| Minimum length | 8 characters |
| Uppercase | Optional |
| Lowercase | Optional |
| Numbers | Optional |
| Special characters | Optional |

### Password Storage

Passwords are hashed using PHP's `password_hash()` with `PASSWORD_ARGON2ID`:

- **Memory cost:** 65536 (64MB)
- **Time cost:** 4 iterations
- **Threads:** 3 parallel

### Password Reset Flow

```
1. User requests password reset
2. Validation code generated
3. Reset email sent
4. User clicks link with code
5. User enters new password
6. Password updated, code invalidated
```

### Reset Password Component

```
resetPassword
├── label_email: "Enter your email"
├── label_submit: "Reset Password"
├── success_message: "Check your email"
```

### Security Considerations

1. **Rate limiting** – Prevent brute force attempts
2. **Code expiration** – Codes expire after set time
3. **Single use** – Codes invalidated after use
4. **Secure delivery** – Use HTTPS for all auth pages

---

## User Administration

### Admin Interface

Navigate to **Admin** → **Users** to manage users:

**User List:**
- View all users
- Filter by status, group
- Search by email, name

**User Details:**
- Edit profile information
- Change password
- Manage group membership
- View activity history
- Block/unblock account

### Bulk Operations

| Operation | Description |
|-----------|-------------|
| Add to group | Assign users to groups |
| Remove from group | Remove group membership |
| Block users | Disable account access |
| Delete users | Permanently remove accounts |

### User Activity Tracking

The `user_activity` table logs:

| Field | Description |
|-------|-------------|
| `id_users` | User ID |
| `url` | Visited URL |
| `timestamp` | Access time |
| `id_type` | Activity type |
| `keyword` | Page keyword |
| `mobile` | Mobile access flag |

### Viewing Activity

```sql
SELECT * FROM user_activity 
WHERE id_users = :user_id 
ORDER BY timestamp DESC
```

---

## Session Management

### Session Configuration

| Setting | Default | Description |
|---------|---------|-------------|
| `SESSION_TIMEOUT` | 36000 (10 hours) | Session lifetime |
| `SESSION_NAME` | PROJECT_NAME | Cookie name |
| `SameSite` | Lax | Cookie attribute |

### Session Variables

| Variable | Purpose |
|----------|---------|
| `id_user` | Current user ID |
| `logged_in` | Authentication state |
| `user_language` | Language preference |
| `user_gender` | Gender for content |
| `target_url` | Redirect after login |

### Session Security

1. **Regeneration** – ID regenerated on login
2. **HTTPS only** – Secure cookie flag
3. **SameSite** – CSRF protection
4. **Timeout** – Automatic expiration

### Mobile Sessions

Mobile apps handle sessions differently:

1. **API Token** – `X-API-Key` header
2. **Device Token** – Push notification token
3. **Persistent** – Longer session lifetime

---

## Best Practices

### User Registration

1. **Email verification** – Confirm valid email
2. **Strong passwords** – Enforce complexity
3. **Clear terms** – Present privacy policy
4. **Minimal data** – Collect only necessary info

### Authentication

1. **HTTPS everywhere** – Secure all auth pages
2. **Rate limiting** – Prevent brute force
3. **2FA for sensitive** – Require for admin groups
4. **Session security** – Regenerate IDs

### Profile Management

1. **Privacy controls** – Let users control visibility
2. **Data export** – GDPR compliance
3. **Account deletion** – Allow self-deletion
4. **Audit logging** – Track changes

### Administration

1. **Principle of least privilege** – Minimal permissions
2. **Activity monitoring** – Track admin actions
3. **Regular audits** – Review access rights
4. **Secure passwords** – Admin password policies

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Can't login | Check email, password, account status |
| Session expired | Increase timeout, check configuration |
| 2FA code not received | Check email settings, spam folder |
| Password reset fails | Verify email exists, check code expiry |

### Debug Steps

1. **Check user status** – Is account active?
2. **Verify credentials** – Correct email format?
3. **Session state** – Check `$_SESSION` contents
4. **Database** – Query users table directly

---

*Previous: [Data Management](data-management.md) | Next: [Permissions and Security](permissions-and-security.md)*

