# Section API Documentation

## Get Section

Retrieves a section by ID, including its style, associated fields, and translations across all languages.

### Endpoint

```
GET /cms-api/v1/admin/sections/{section_id}
```

### Authentication

Requires a valid JWT token with admin access.

### Path Parameters

| Parameter   | Type    | Description                   | Required |
|-------------|---------|-------------------------------|----------|
| section_id  | integer | The unique ID of the section  | Yes      |

### Response

#### Success Response (200 OK)

```json
{
  "status": 200,
  "message": "OK",
  "error": null,
  "logged_in": true,
  "meta": {
    "version": "v1",
    "timestamp": "2023-06-11T08:37:35+00:00"
  },
  "data": {
    "section": {
      "id": 123,
      "name": "Example Section",
      "style": {
        "id": 456,
        "name": "Container",
        "idType": 1,
        "idGroup": 2,
        "description": "A container style",
        "canHaveChildren": true
      }
    },
    "fields": [
      {
        "id": 1,
        "name": "title",
        "type": "text",
        "default_value": "Default Title",
        "help": "Enter a title",
        "disabled": false,
        "hidden": false,
        "display": "block",
        "translations": [
          {
            "language_id": 1,
            "language_code": "en",
            "content": "Example Title",
            "meta": null
          },
          {
            "language_id": 2,
            "language_code": "fr",
            "content": "Titre d'exemple",
            "meta": null
          }
        ]
      },
      {
        "id": 2,
        "name": "content",
        "type": "textarea",
        "default_value": "Default Content",
        "help": "Enter content",
        "disabled": false,
        "hidden": false,
        "display": "block",
        "translations": [
          {
            "language_id": 1,
            "language_code": "en",
            
            "content": "Example Content",
            "meta": {
              "key": "value"
            }
          }
        ]
      }
    ],
    "languages": [
      {
        "id": 1,
        "locale": "en"
      },
      {
        "id": 2,
        "locale": "fr"
      }
    ]
  }
}
```

#### Error Responses

##### Not Found (404)

```json
{
  "status": 404,
  "message": "Not Found",
  "error": "Section not found",
  "logged_in": true,
  "meta": {
    "version": "v1",
    "timestamp": "2023-06-11T08:37:35+00:00"
  },
  "data": null
}
```

##### Forbidden (403)

```json
{
  "status": 403,
  "message": "Forbidden",
  "error": "Access denied to section",
  "logged_in": true,
  "meta": {
    "version": "v1",
    "timestamp": "2023-06-11T08:37:35+00:00"
  },
  "data": null
}
```

### Response Schema

The response follows the standard API envelope pattern and includes the following data structure:

- `section`: The section entity with basic properties and its associated style.
- `fields`: An array of fields associated with the section's style, including:
  - Basic field properties (id, name, type, etc.)
  - Default values and display settings
  - Translations for each field, grouped by language
- `languages`: An array of languages found in the translations, with their IDs and locale codes.

### JSON Schema

The response is validated against the JSON schema located at:
`/config/schemas/api/v1/responses/admin/sections/section.json`

### Implementation Details

- The endpoint checks if the user has permission to view the section.
- It retrieves the section entity, its style, and all style fields.
- It fetches translations for the section's fields from the `sections_fields_translation` table.
- Translations are grouped by field ID, language ID
- The response includes all languages present in the translations.

### Related Entities

- `Section`: The main section entity.
- `Style`: The style associated with the section.
- `StylesField`: The fields associated with the style.
- `SectionsFieldsTranslation`: The translations for each field in the section.
- `Language`: The languages used in translations.
