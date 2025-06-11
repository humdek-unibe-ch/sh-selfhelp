# SelfHelp Backend: Page Versioning Concept (Proposed Feature)

## 1. Database Table: `page_versions`
- **Columns:**
  - `id_pages` – reference to the page (integer or UUID)
  - `json` – stores the complete page structure/content as JSON
  - `published` – boolean (`true` if published, otherwise snapshot)
  - `created_at` – timestamp

---

## 2. Snapshot Creation
- Users can create a **snapshot** of the current page state.
- Each snapshot creates a new record in the `page_versions` table.
- Snapshots are used for version history and previews.

---

## 3. Publishing Workflow
- When a page is **published**:
  - Automatically create a new snapshot (if needed).
  - Mark this snapshot as `published = true`.
- Only one version per page is marked as published at a time.

---

## 4. Frontend Page Loading
- Regular users see the **latest published version** of the page.
- Frontend requests fetch the most recent published entry for a page.

---

## 5. Preview & CMS Development
- A `/preview` route/prefix loads the **latest working (unpublished) version** for editors/developers.
- Regular routes only show the published version.
- `/preview` enables safe testing of new edits and features before going live.

---

### Example Table Schema

| id (PK) | id_pages | json (text) | published | created_at  |
|---------|----------|-------------|-----------|-------------|
| 1       | 10       | {...}       | false     | 2024-06-01  |
| 2       | 10       | {...}       | true      | 2024-06-02  |

---

## Optional Enhancements
- Add `created_by` / `updated_by` for audit/history.
- Add `version_number` for easier reference.
- Add `comment` field for editors to describe changes.
