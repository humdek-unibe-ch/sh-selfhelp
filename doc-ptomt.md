# SelfHelp CMS User Guide Generation Prompt

## Overview
You are tasked with creating comprehensive, user-friendly documentation for the SelfHelp CMS system. This documentation should be targeted at non-technical end users who need to understand how to use the system to build and manage web applications. The documentation must be derived entirely from scanning and analyzing the codebase to understand the system's capabilities, features, and workflows.

**Key Resources to Analyze:**
- `@server/db/db_strucutre.sql` - Complete database schema, tables, relationships, and views
- Component system with dual rendering (HTML for web, JSON for Ionic Angular mobile frontend)
- Page configuration with access types (web, mobile, or both)

## Documentation Structure
Create the documentation in the `doc/documentation` folder with the following structure:

```
docs/documentation
├── README.md (main entry point with table of contents)
├── getting-started.md
├── pages-and-navigation.md
├── sections-and-components.md
├── styling-guide.md
├── data-management.md
├── user-management.md
├── permissions-and-security.md
├── actions-and-workflows.md
├── advanced-features.md
├── troubleshooting.md
├── examples-and-tutorials.md
├── api-reference.md (if applicable)
└── glossary.md
```

## Required Analysis Areas

### 1. System Architecture Understanding
Scan the entire codebase to understand:
- How the MVC architecture works (Model-View-Controller pattern)
- Component-based system and how components are loaded
- Database-driven configuration system
- Plugin architecture and how it extends functionality
- Dual rendering system: HTML for web, JSON for mobile/Ionic Angular frontend
- Page access types and mobile-specific rendering
- Caching mechanisms and performance optimizations

### 2. Page Management
Analyze how pages are created and managed:
- Page creation process in the admin interface
- Page properties and configuration options
- Navigation system and menu structures
- Page routing and URL structures
- Page templates and layouts
- Page access types (web, mobile, or both)
- Mobile-specific rendering and JSON output for Ionic Angular frontend

### 3. Section and Component System
Deep dive into the section system:
- How sections are added to pages
- Section ordering and positioning
- Section visibility conditions
- Component types and their purposes
- Component configuration options
- Data binding and interpolation in components
- Dual rendering: HTML for web, JSON for mobile/Ionic Angular frontend
- Mobile-specific component output methods

### 4. Styling System
Comprehensive analysis of styling:
- Available style types (from `server/component/style/`)
- CSS customization capabilities
- Bootstrap 4.6 integration and class usage
- Custom CSS page functionality
- Responsive design features
- Style field configurations

### 5. Data Management
Understand data handling:
- dataTables system and structure
- Data retrieval and storage
- Data interpolation syntax ({{variable}} patterns)
- Data sources (INTERNAL, EXTERNAL, SESSION, GLOBAL)
- Form data handling and validation
- File uploads and media management

### 6. User and Group Management
Admin functionality analysis:
- User creation and management
- Group creation and membership
- Role-based access control (RBAC)
- Permission assignment
- User authentication and sessions
- Two-factor authentication (if implemented)

### 7. Actions and Workflows
Automated system analysis:
- Action triggers based on data saves
- Email notification system
- Task execution (add/remove groups, etc.)
- Scheduled jobs and automation
- Workflow configuration
- Conditional logic in actions

## Documentation Requirements

### Target Audience
- Non-technical users (content managers, business users)
- Administrators who need to configure the system
- Developers who need to understand the system for customization

### Writing Style
- Clear, simple language avoiding technical jargon
- Step-by-step instructions with screenshots (describe where screenshots should be taken)
- Practical examples and use cases
- Cross-references between related topics
- Consistent terminology throughout

### Content Depth
For each feature, include:
- What it is and why it's useful
- How to access/configure it
- Step-by-step usage instructions
- Common use cases and examples
- Best practices and tips
- Troubleshooting common issues

## Specific Documentation Sections

### Getting Started
- System overview and capabilities
- First login and basic navigation
- Understanding the admin interface
- Basic concepts (pages, sections, components)

### Pages and Navigation
- Creating new pages
- Page properties configuration
- Setting page titles, descriptions, keywords
- Navigation menu management
- Page ordering and hierarchy
- Page templates and layouts

### Sections and Components
- Adding sections to pages
- Section ordering and layout
- Understanding component types:
  - Text and content components
  - Form components (input, select, textarea)
  - Display components (images, tables)
  - Interactive components (buttons, links)
  - Layout components (containers, cards, tabs)
- Component configuration options
- Section visibility conditions

### Styling Guide
- Overview of the styling system
- Available component styles (list all from codebase)
- For each style, document:
  - Purpose and use cases
  - Configuration options
  - CSS customization methods
  - Bootstrap 4.6 class integration
  - Responsive behavior
- Custom CSS page usage
- Style field definitions and usage

### Data Management
- Understanding dataTables
- Creating and configuring data tables
- Data collection forms
- Data retrieval and display
- Variable interpolation syntax
- Data sources and contexts
- File upload handling

### User Management
- User account creation
- User profiles and settings
- Password management
- User roles and permissions

### Permissions and Security
- Group creation and management
- ACL (Access Control List) configuration
- Permission assignment to pages and sections
- User-group relationships
- Security best practices

### Actions and Workflows
- Action creation and configuration
- Trigger conditions (data saves, form submissions)
- Action types:
  - Email notifications
  - User group modifications (add/remove)
  - Task execution
  - Data manipulation
- Workflow setup and management
- Testing and debugging actions

### Advanced Features
- Plugin system usage
- API integration (if available)
- Mobile responsiveness and Ionic Angular frontend
- Page rendering modes (web, mobile, or both)
- JSON API output for mobile applications
- Understanding dual rendering: HTML for web, JSON for Ionic Angular
- Mobile component output methods and data structures
- Performance optimization
- Backup and maintenance

## Code Analysis Instructions

### Scanning Methodology
1. **System Architecture**: Start with `Selfhelp.php`, `Services.php`, and core classes
2. **Components**: Scan `server/component/style/` for all component types, including mobile output methods
3. **Database**: Analyze `@server/db/db_strucutre.sql` for complete database schema and relationships
4. **Admin Interface**: Study admin controllers and views
5. **API**: Review API endpoints and response formats for mobile integration
6. **Mobile/Ionic**: Understand how components output JSON for Ionic Angular frontend
7. **Configuration**: Examine configuration files and setup processes

### Key Files to Analyze
- `server/component/BaseComponent.php`, `BaseModel.php`, `BaseView.php`
- All component directories in `server/component/style/`
- `server/service/` classes
- `server/page/` and `server/ajax/` directories
- `@server/db/db_strucutre.sql` - Complete database schema and structure
- Database schema and migration files
- Admin interface files
- Plugin examples and documentation

### Extract Information From
- Class docblocks and comments
- Configuration arrays and field definitions
- `@server/db/db_strucutre.sql` - Complete database schema, tables, relationships, and views
- Database table structures and relationships
- UI labels and help text
- Error messages and validation rules
- Example configurations and templates
- Mobile-specific output methods in components

## Output Format Requirements

### Markdown Structure
- Use proper heading hierarchy (# ## ### ####)
- Code blocks with syntax highlighting where appropriate
- Tables for configuration options and field descriptions
- Bullet points and numbered lists for steps
- Cross-references with relative links (`[link text](relative-path.md)`)

### Visual Elements
- Describe where screenshots should be included
- Use ASCII diagrams for workflows and relationships
- Include code examples with proper formatting
- Use emphasis and strong text for important concepts

### Navigation and Linking
- Main README.md with complete table of contents
- Cross-links between related sections
- Consistent anchor naming for internal references
- Glossary for technical terms

## Quality Assurance

### Accuracy Checks
- All described features must exist in the codebase
- Configuration options must match actual field names
- Step-by-step instructions must be verifiable
- Examples must be realistic and functional

### Completeness
- Cover all major features discovered in code analysis
- Include both basic and advanced usage scenarios
- Document limitations and known issues
- Provide troubleshooting guidance

### Usability
- Instructions should be clear enough for non-technical users
- Examples should be copy-paste ready where applicable
- Include warnings for potentially destructive actions
- Provide alternative approaches where available

## Final Deliverables

1. **Complete documentation set** in `docs/documentation` folder
2. **README.md** as main entry point with comprehensive table of contents
3. **Cross-linked structure** allowing users to navigate related topics
4. **Search-friendly** content with consistent terminology
5. **Update-ready** structure for future maintenance

## Additional Requirements

- Use British English spelling conventions
- Include version information and last updated dates
- Add contributor guidelines for future updates
- Include contact information for support
- Provide feedback mechanism description

Begin by thoroughly scanning the codebase, then create the documentation structure, and finally populate each section with detailed, accurate information derived from the code analysis.
