# SH-Selfhelp CMS Migration Plan

## Overview

This document outlines the plan to migrate the current vanilla PHP/template-based CMS to a modern architecture with a React frontend and PHP API backend. The current CMS consists of multiple components (cmsSelect, cmsInsert, cmsUpdate, cmsDelete, cmsExport, cmsImport) that handle various aspects of content management through a unified model (CmsModel.php).

## Current System Analysis

### Core Structure

- **Base Component**: `server/component/cms` - Contains the common model, view, and component classes
- **CRUD Components**:
  - `cmsSelect` - Displays CMS content
  - `cmsInsert` - Creates new pages and sections
  - `cmsUpdate` - Modifies existing content
  - `cmsDelete` - Removes content
  - `cmsExport` - Exports content
  - `cmsImport` - Imports content

### Main Entities

1. **Pages** - Main content containers with properties (keyword, URL, protocol)
2. **Sections** - Content subdivisions that can be nested
3. **Fields** - Individual content elements with different types
4. **Navigation** - Menu structure organization

## Migration Architecture

### Backend API Structure

```
server/cms-api/v1/
├── admin/
│   ├── pages/
│   │   ├── PagesAdminApi.php       # Page CRUD operations
│   │   └── PageMetaAdminApi.php    # Page metadata operations
│   ├── sections/
│   │   ├── SectionsAdminApi.php    # Section CRUD operations
│   │   └── FieldsAdminApi.php      # Field operations
│   └── AdminCmsApi.php             # Entry point for admin API
└── content/
    └── ContentCmsApi.php           # Public content access API
```

### Frontend Structure

```
client/
├── src/
│   ├── components/             # Reusable React components
│   │   ├── cms/                # CMS-specific components
│   │   │   ├── PageEditor.jsx
│   │   │   ├── SectionManager.jsx
│   │   │   ├── FieldEditor.jsx
│   │   │   └── NavigationBuilder.jsx
│   ├── pages/                  # Full page components
│   │   ├── cms/
│   │   │   ├── Dashboard.jsx   # Main CMS entry point
│   │   │   ├── PageList.jsx    # Page management
│   │   │   ├── PageEdit.jsx    # Page editing
│   │   │   └── Settings.jsx    # CMS settings
│   ├── services/               # API service wrappers
│   │   ├── cms-api.js          # API client for CMS operations
│   │   └── auth-api.js         # API client for authentication
│   └── state/                  # Global state management
│       └── cmsSlice.js         # Redux slice for CMS state
```

## Backend API Endpoints

### 1. Page Management

```
# List pages
GET /api/v1/admin/pages

# Get page details
GET /api/v1/admin/pages/{id}

# Create page
POST /api/v1/admin/pages

# Update page
PUT /api/v1/admin/pages/{id}

# Delete page
DELETE /api/v1/admin/pages/{id}
```

### 2. Section Management

```
# List sections for a page
GET /api/v1/admin/sections?page_id={id}

# Get unassigned sections
GET /api/v1/admin/sections/unassigned

# Get section details
GET /api/v1/admin/sections/{id}

# Create section
POST /api/v1/admin/sections

# Update section
PUT /api/v1/admin/sections/{id}

# Delete section
DELETE /api/v1/admin/sections/{id}

# Link section to page/section
POST /api/v1/admin/sections/{id}/link

# Remove section link
DELETE /api/v1/admin/sections/{id}/link
```

### 3. Field Management

```
# List fields for a section
GET /api/v1/admin/sections/{id}/fields

# Get field details
GET /api/v1/admin/fields/{id}

# Update field
PUT /api/v1/admin/fields/{id}

# Batch update fields
PUT /api/v1/admin/fields/batch
```

### 4. Navigation Management

```
# Get navigation hierarchy
GET /api/v1/admin/navigation

# Update navigation item
PUT /api/v1/admin/navigation/{id}

# Reorder navigation items
POST /api/v1/admin/navigation/reorder
```

### 5. Import/Export

```
# Export content
GET /api/v1/admin/export

# Import content
POST /api/v1/admin/import
```

## Implementation Plan

### Phase 1: Backend API Development

1. **Set up API structure**
   - Create base API request handling classes
   - Set up authentication with JWT (reuse existing auth system)
   - Implement error handling and response formatting

2. **Implement core API endpoints**
   - Pages API
   - Sections API
   - Fields API
   - Navigation API

3. **Testing**
   - Unit test each API endpoint
   - Verify compatibility with existing database schema

### Phase 2: Frontend Development

1. **Set up React project**
   - Configure Next.js with Tailwind CSS and Mantine v7
   - Set up authentication flow
   - Create API service wrappers

2. **Develop core components**
   - Page List & Editor
   - Section Manager
   - Field Editor with different field types
   - Navigation Builder

3. **Implement main CMS pages**
   - Dashboard
   - Content Management Interface
   - Settings

### Phase 3: Advanced Features and Integration

1. **Implement import/export functionality**
   - Export API
   - Import API with validation
   - Frontend handling

2. **Add rich media management**
   - Image upload and management
   - Document attachment handling

3. **User roles and permissions**
   - Integrate with existing ACL system
   - Role-based UI adaptations

## API Implementation Details

### PagesAdminApi.php

Key methods to implement:

```php
// Get list of pages with filters
public function GET_pages($filter = null, $sort = null, $page = 1, $limit = 20)

// Get single page details
public function GET_page($id)

// Create new page
public function POST_page($keyword, $url, $protocol, $type, $position = null, $is_headless = false, $parent_id = null, $access_type = 1)

// Update page
public function PUT_page($id, $fields)

// Delete page
public function DELETE_page($id)
```

### SectionsAdminApi.php

Key methods to implement:

```php
// Get sections for a page
public function GET_sections($page_id = null, $parent_section_id = null)

// Get unassigned sections
public function GET_unassigned_sections()

// Get section hierarchy
public function GET_section_hierarchy($root_id = null)

// Create new section
public function POST_section($name, $id_style, $relation, $position = null)

// Link section
public function POST_section_link($id, $relation, $position = null)

// Remove section link
public function DELETE_section_link($id_section, $relation)
```

### FieldsAdminApi.php

Key methods to implement:

```php
// Update fields in batch
public function PUT_fields($fields)

// Validate field content based on type
private function validate_field($type, $value)

// Sanitize field input
private function secure_field($type, $content)
```

## Frontend Component Details

### 1. PageEditor.jsx

Component for editing page properties:

```jsx
const PageEditor = ({ pageId }) => {
  const [page, setPage] = useState(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    // Fetch page data
    cmsApi.getPage(pageId).then(data => {
      setPage(data);
      setLoading(false);
    });
  }, [pageId]);
  
  const handleSubmit = (values) => {
    return cmsApi.updatePage(pageId, values);
  };
  
  if (loading) return <LoadingSpinner />;
  
  return (
    <Form initialValues={page} onSubmit={handleSubmit}>
      {/* Form fields for page properties */}
    </Form>
  );
};
```

### 2. SectionManager.jsx

Component for managing sections:

```jsx
const SectionManager = ({ pageId }) => {
  const [sections, setSections] = useState([]);
  const [unassignedSections, setUnassignedSections] = useState([]);
  
  useEffect(() => {
    // Fetch sections for this page
    Promise.all([
      cmsApi.getSections(pageId),
      cmsApi.getUnassignedSections()
    ]).then(([pageSections, unassigned]) => {
      setSections(pageSections);
      setUnassignedSections(unassigned);
    });
  }, [pageId]);
  
  const handleAddSection = (sectionId, position) => {
    return cmsApi.linkSection(sectionId, {
      relation: 'page',
      id: pageId,
      position
    });
  };
  
  // Additional handler methods
  
  return (
    <div>
      <SectionTree sections={sections} onReorder={handleReorder} />
      <UnassignedSectionList 
        sections={unassignedSections} 
        onAdd={handleAddSection}
      />
    </div>
  );
};
```

## Best Practices

1. **API Design**
   - Use consistent naming conventions
   - Implement proper error handling
   - Include validation for all inputs
   - Document all endpoints

2. **Frontend Development**
   - Use React hooks for state management
   - Implement proper loading states
   - Create reusable components
   - Add proper error handling and validation

3. **Authentication**
   - Use the existing JWT system
   - Implement token refresh
   - Handle session expiration gracefully

4. **Testing**
   - Write unit tests for API endpoints
   - Create component tests for UI
   - Implement end-to-end tests for critical flows

## Data Migration Considerations

- No data migration is needed as the database structure remains unchanged
- Ensure gradual transition by supporting both old and new CMS temporarily
- Add feature flags to control access to new CMS features

## Conclusion

This migration plan provides a structured approach to transitioning the SH-Selfhelp CMS from a vanilla PHP/template implementation to a modern React frontend with API backend. By following this plan, the existing functionality will be preserved while introducing the benefits of a more maintainable, scalable, and user-friendly interface.