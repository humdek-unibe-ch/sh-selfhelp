# Section Export/Import API Documentation

This document describes the enhanced section export/import functionality that allows you to export sections from pages and import them into other pages or sections.

## Overview

The section export/import system provides 4 main API endpoints:

1. **Export all sections from a page** - Exports all sections (including nested children) from a specific page
2. **Export a specific section** - Exports a single section and all its nested children
3. **Import sections to a page** - Imports sections as root-level sections on a page
4. **Import sections to a section** - Imports sections as children of an existing section

## API Endpoints

### 1. Export Page Sections

**Endpoint:** `GET /cms-api/v1/admin/pages/{page_keyword}/sections/export`

**Description:** Exports all sections from a page, maintaining hierarchical structure and position information.

**Response Format:**
```json
{
  "data": {
    "sectionsData": [
      {
        "name": "Header Section",
        "style_name": "header",
        "position": 1,
        "fields": [
          {
            "name": "title",
            "type": "text",
            "display": true,
            "default_value": "",
            "help": "Enter the title",
            "disabled": false,
            "hidden": false,
            "translations": [
              {
                "locale": "en",
                "gender": "default",
                "content": "Welcome Title",
                "meta": null
              }
            ]
          }
        ],
        "children": [
          {
            "name": "Sub Section",
            "style_name": "content",
            "position": 1,
            "fields": [...],
            "children": []
          }
        ]
      }
    ]
  }
}
```

### 2. Export Specific Section

**Endpoint:** `GET /cms-api/v1/admin/pages/{page_keyword}/sections/{section_id}/export`

**Description:** Exports a specific section and all its nested children.

**Response:** Same format as above, but only contains the specified section and its children.

### 3. Import Sections to Page

**Endpoint:** `POST /cms-api/v1/admin/pages/{page_keyword}/sections/import`

**Request Body:**
```json
{
  "sections": [
    {
      "name": "Imported Section",
      "style_name": "header",
      "position": 1,
      "fields": [
        {
          "name": "title",
          "type": "text",
          "display": true,
          "default_value": "",
          "help": "Enter the title",
          "disabled": false,
          "hidden": false,
          "translations": [
            {
              "locale": "en",
              "gender": "default",
              "content": "Imported Title",
              "meta": null
            }
          ]
        }
      ],
      "children": []
    }
  ]
}
```

**Response:**
```json
{
  "data": {
    "importedSections": [
      {
        "id": 123,
        "name": "Imported Section",
        "style_name": "header",
        "position": 1
      }
    ]
  }
}
```

### 4. Import Sections to Section

**Endpoint:** `POST /cms-api/v1/admin/pages/{page_keyword}/sections/{parent_section_id}/import`

**Request/Response:** Same format as importing to page, but sections are added as children of the specified parent section.

## Key Features

### 1. Style and Language Resolution

- **Export**: Uses `style_name` and `locale` instead of database IDs for portability
- **Import**: Automatically resolves style names and language locales to their corresponding database IDs
- **Error Handling**: Logs warnings for missing styles/languages but continues import process

### 2. Complete Field Information

The export includes comprehensive field configuration:
- Field name and type
- Display flag (content vs property field)
- Default value, help text
- Disabled and hidden flags
- All translations with locale, gender, content, and meta data

### 3. Hierarchical Structure

- Maintains parent-child relationships between sections
- Preserves position information within each level
- Supports recursive nesting of unlimited depth

### 4. Position Management

- **Export**: Includes position information for proper ordering
- **Import**: Respects provided positions or auto-assigns if not specified
- **Auto-increment**: Automatically calculates next available position when needed

### 5. Translation Support

- Exports all translations for each field
- Groups by locale and gender
- Includes meta data for advanced field configurations
- Handles both content fields (display=true) and property fields (display=false)

## Error Handling

### Style Resolution
If a style name doesn't exist during import:
- Logs a warning transaction
- Continues with import (section created without style)
- Does not fail the entire import process

### Language Resolution
If a locale doesn't exist during import:
- Skips translations for that language
- Continues with other translations
- Does not fail the import process

### Field Validation
- Creates new fields if they don't exist
- Establishes proper StylesField relationships
- Updates existing translations or creates new ones

## Permissions

All export/import endpoints require the `admin.page.export` permission, which is automatically granted to users with the `admin` role.

## Usage Examples

### Complete Export/Import Workflow

1. **Export from source page:**
```bash
curl -X GET "https://api.example.com/cms-api/v1/admin/pages/source-page/sections/export" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

2. **Import to target page:**
```bash
curl -X POST "https://api.example.com/cms-api/v1/admin/pages/target-page/sections/import" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sections": [EXPORTED_SECTIONS_DATA]}'
```

### Partial Section Export/Import

1. **Export specific section:**
```bash
curl -X GET "https://api.example.com/cms-api/v1/admin/pages/source-page/sections/123/export" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

2. **Import as child section:**
```bash
curl -X POST "https://api.example.com/cms-api/v1/admin/pages/target-page/sections/456/import" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sections": [EXPORTED_SECTION_DATA]}'
```

## Technical Implementation

### Database Changes
- Enhanced API routes with proper parameter validation
- Updated permissions system for export/import operations
- Improved transaction handling for atomic operations

### Service Layer Improvements
- Complete field configuration export/import
- Hierarchical structure building with position management
- Style and language resolution with fallback handling
- Comprehensive error handling and logging

### Testing
Added comprehensive test coverage for:
- Page section export functionality
- Individual section export functionality  
- Section import to pages
- Section import to sections
- Error handling scenarios 