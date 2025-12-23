# SelfHelp CMS User Guide

**Version:** 7.10.0  
**Last Updated:** December 2024  
**Platform:** SelfHelp Content Management System

---

## Welcome to SelfHelp CMS

SelfHelp is a powerful, component-based Content Management System designed for building web applications without requiring programming knowledge. Whether you're creating simple informational pages or complex data-driven applications with user interactions, SelfHelp provides the tools you need.

### Key Features

- **🎨 Component-Based Design** – Build pages using pre-built, configurable components
- **📱 Dual Rendering** – Automatic support for web browsers and mobile applications (Ionic Angular)
- **🔐 Role-Based Access Control** – Fine-grained permissions for users and groups
- **📊 Data Management** – Unified data storage with powerful querying capabilities
- **⚡ Workflow Automation** – Trigger actions based on data events
- **🔌 Plugin Architecture** – Extend functionality with plugins
- **🌐 Multi-Language Support** – Built-in internationalisation capabilities

---

## Table of Contents

### Getting Started
- [Getting Started](getting-started.md) – System overview, first login, and basic concepts

### Core Concepts
- [Pages and Navigation](pages-and-navigation.md) – Creating and managing pages, navigation menus
- [Sections and Components](sections-and-components.md) – Understanding the component system
- [Styling Guide](styling-guide.md) – Available styles and customisation options

### Data & Users
- [Data Management](data-management.md) – dataTables, forms, and data retrieval
- [User Management](user-management.md) – User accounts and profiles
- [Permissions and Security](permissions-and-security.md) – Groups, ACL, and access control

### Automation & Integration
- [Actions and Workflows](actions-and-workflows.md) – Automated triggers and scheduled jobs
- [Advanced Features](advanced-features.md) – Plugins, mobile support, and performance

### Reference
- [API Reference](api-reference.md) – REST API endpoints and integration
- [Troubleshooting](troubleshooting.md) – Common issues and solutions
- [Examples and Tutorials](examples-and-tutorials.md) – Practical walkthroughs
- [Glossary](glossary.md) – Technical terms and definitions

---

## System Architecture Overview

SelfHelp follows a Model-View-Controller (MVC) architecture with these key layers:

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                          │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │   Web Browser   │  │  Mobile App     │  │  API Clients    │ │
│  │   (HTML/CSS)    │  │  (Ionic/JSON)   │  │  (REST/JSON)    │ │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘ │
└───────────┼────────────────────┼────────────────────┼──────────┘
            │                    │                    │
┌───────────▼────────────────────▼────────────────────▼──────────┐
│                      COMPONENT LAYER                           │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Pages → Sections → Components (Model + View + Controller)│   │
│  └─────────────────────────────────────────────────────────┘   │
└───────────────────────────────┬────────────────────────────────┘
                                │
┌───────────────────────────────▼────────────────────────────────┐
│                      SERVICE LAYER                             │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐  │
│  │   ACL   │ │ Router  │ │UserInput│ │JobSched │ │ Hooks   │  │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘  │
└───────────────────────────────┬────────────────────────────────┘
                                │
┌───────────────────────────────▼────────────────────────────────┐
│                      DATABASE LAYER                            │
│           MySQL with version-controlled migrations             │
└────────────────────────────────────────────────────────────────┘
```

### How Content is Organised

| Level | Description | Example |
|-------|-------------|---------|
| **Page** | A URL-addressable container | `/home`, `/profile`, `/contact` |
| **Section** | A configured component instance | "Welcome Banner", "Contact Form" |
| **Component** | A reusable UI element type | `card`, `form`, `button`, `input` |
| **Field** | Configuration option for a component | `title`, `css`, `url` |

---

## Quick Start

### 1. Access the System
Navigate to your SelfHelp installation URL and log in with your administrator credentials.

### 2. Navigate to the CMS
Click the **CMS** button (pencil icon) visible when logged in as an administrator.

### 3. Create Your First Page
1. Go to **Pages** in the admin menu
2. Click **Add Page**
3. Enter a keyword (URL-friendly name)
4. Set the page title and type
5. Save the page

### 4. Add Content
1. Select your new page
2. Click **Add Section**
3. Choose a component style (e.g., "markdown" for text)
4. Configure the component fields
5. Preview your page

For detailed instructions, see [Getting Started](getting-started.md).

---

## System Requirements

- **PHP:** 8.2 or higher (8.3 recommended)
- **MySQL:** 8.0 or higher
- **Web Server:** Apache with mod_rewrite or Nginx
- **Browser:** Modern browsers (Chrome, Firefox, Safari, Edge)

---

## Getting Help

### Documentation
This documentation covers all aspects of using SelfHelp CMS. Use the table of contents above to navigate to specific topics.

### Support
For technical support or to report issues, contact your system administrator or visit the project repository.

### Contributing
This documentation is maintained alongside the SelfHelp codebase. Contributions and suggestions for improvement are welcome.

---

## Version History

| Version | Date | Notable Changes |
|---------|------|-----------------|
| 7.10.0 | Dec 2024 | Current release with unified dataTables |
| 7.0.0 | 2024 | Major architectural updates |
| 6.x | 2023 | Enhanced mobile support |

For detailed changes, see the [CHANGELOG](../../CHANGELOG.md).

---

*© SelfHelp CMS - Licensed under Mozilla Public License 2.0*


