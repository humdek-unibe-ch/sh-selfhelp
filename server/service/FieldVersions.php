<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * Version history of CMS field contents (table `fields_versions`).
 *
 * Only fields with `fields.versioned = 1` are versioned. The content in
 * pages_fields_translation / sections_fields_translation is always the active
 * version; a snapshot is stored when that content changes. A field instance is
 * identified by the relation (page or section field), the owner id (page or
 * section id), the field and the language. Only the default gender (1) of a
 * section field is versioned.
 */
class FieldVersions
{
    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * Cache of the versioned flag per field id for this request.
     */
    private $versioned = array();

    /**
     * @param object $db
     *  An instance of the service class PageDb.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Check whether a field keeps a version history.
     *
     * @param int $id_fields
     *  The id of the field.
     * @retval bool
     *  True if the field is versioned.
     */
    public function is_versioned($id_fields)
    {
        $id_fields = intval($id_fields);
        if (!isset($this->versioned[$id_fields])) {
            $row = $this->db->query_db_first("SELECT versioned FROM fields WHERE id = :id",
                array(':id' => $id_fields));
            $this->versioned[$id_fields] = $row && intval($row['versioned']) === 1;
        }
        return $this->versioned[$id_fields];
    }

    /**
     * Store the content as a new version unless the latest version has the
     * same content.
     *
     * @param string $relation
     *  RELATION_PAGE_FIELD or RELATION_SECTION_FIELD.
     * @param int $owner_id
     *  The id of the page or section.
     * @param int $id_fields
     *  The id of the field.
     * @param int $id_languages
     *  The id of the language.
     * @param string $content
     *  The content.
     * @param string $meta
     *  The meta data of a section field, if any.
     * @param int $restored_from
     *  The id of the version this content was restored from, if any.
     * @param int|false $id_users
     *  The user who made the change; null for system/import snapshots, false
     *  for the session user.
     * @retval int|false
     *  The id of the version that holds this content, false on failure.
     */
    public function snapshot($relation, $owner_id, $id_fields, $id_languages, $content,
        $meta = null, $restored_from = null, $id_users = false)
    {
        $owner = $this->owner_columns($relation, $owner_id);
        if (!$owner) {
            return false;
        }
        $content = (string)$content;
        $hash = hash('sha256', $content);
        $latest = $this->get_latest($relation, $owner_id, $id_fields, $id_languages);
        if ($latest && $latest['content_hash'] === $hash && !$restored_from) {
            return intval($latest['id']);
        }
        if ($id_users === false) {
            $id_users = isset($_SESSION['id_user']) && intval($_SESSION['id_user']) > 0
                ? intval($_SESSION['id_user']) : null;
        }
        // The version number is computed in the same statement as the insert.
        $sql = "INSERT INTO fields_versions (id_fields, id_pages, id_sections, id_languages,
                version_no, content, meta, content_hash, restored_from, id_users)
            SELECT :new_id_fields, :new_id_pages, :new_id_sections, :new_id_languages,
                COALESCE(MAX(version_no), 0) + 1, :content, :meta, :hash, :restored_from, :id_users
            FROM fields_versions
            WHERE id_fields = :id_fields AND id_languages = :id_languages AND " . $owner['where'];
        $args = array_merge($owner['args'], array(
            ':new_id_fields' => $id_fields,
            ':new_id_pages' => $owner['id_pages'],
            ':new_id_sections' => $owner['id_sections'],
            ':new_id_languages' => $id_languages,
            ':id_fields' => $id_fields,
            ':id_languages' => $id_languages,
            ':content' => $content,
            ':meta' => $meta ? $meta : null,
            ':hash' => $hash,
            ':restored_from' => $restored_from ? $restored_from : null,
            ':id_users' => $id_users
        ));
        if ($this->db->execute_update_db($sql, $args) === false) {
            return false;
        }
        $latest = $this->get_latest($relation, $owner_id, $id_fields, $id_languages);
        return $latest ? intval($latest['id']) : false;
    }

    /**
     * Make sure the current content of a versioned field is stored as a
     * version. Content written outside of the CMS (SQL imports) is picked up
     * this way as a system snapshot.
     *
     * @param string $relation
     *  RELATION_PAGE_FIELD or RELATION_SECTION_FIELD.
     * @param int $owner_id
     *  The id of the page or section.
     * @param int $id_fields
     *  The id of the field.
     * @param int $id_languages
     *  The id of the language.
     * @retval int|null
     *  The id of the version that holds the current content, null if the field
     *  has no content or is not versioned.
     */
    public function ensure_current($relation, $owner_id, $id_fields, $id_languages)
    {
        if (!$this->is_versioned($id_fields)) {
            return null;
        }
        $current = $this->get_current($relation, $owner_id, $id_fields, $id_languages);
        if ($current === null) {
            return null;
        }
        $id = $this->snapshot($relation, $owner_id, $id_fields, $id_languages,
            $current['content'], $current['meta'], null, null);
        return $id ? $id : null;
    }

    /**
     * The number of the version that holds the current content. Content
     * written outside the CMS is stored as a version first (see
     * ensure_current()), so the number always matches what is live.
     *
     * @param string $relation
     *  RELATION_PAGE_FIELD or RELATION_SECTION_FIELD.
     * @param int $owner_id
     *  The id of the page or section.
     * @param int $id_fields
     *  The id of the field.
     * @param int $id_languages
     *  The id of the language.
     * @retval int|null
     *  The version number, null if the field has no content or is not
     *  versioned.
     */
    public function get_current_version_no($relation, $owner_id, $id_fields, $id_languages)
    {
        $id = $this->ensure_current($relation, $owner_id, $id_fields, $id_languages);
        if (!$id) {
            return null;
        }
        $row = $this->db->query_db_first("SELECT version_no FROM fields_versions WHERE id = :id",
            array(':id' => $id));
        return $row ? intval($row['version_no']) : null;
    }

    /**
     * List the versions of a field instance, newest first, without content.
     *
     * @param string $relation
     *  RELATION_PAGE_FIELD or RELATION_SECTION_FIELD.
     * @param int $owner_id
     *  The id of the page or section.
     * @param int $id_fields
     *  The id of the field.
     * @param int $id_languages
     *  The id of the language.
     * @retval array
     *  Rows with id, version_no, created_at, user_email, size, content_hash,
     *  restored_from_no and active (the newest version with the current content).
     */
    public function list_versions($relation, $owner_id, $id_fields, $id_languages)
    {
        $owner = $this->owner_columns($relation, $owner_id, 'fv.');
        if (!$owner) {
            return array();
        }
        $sql = "SELECT fv.id, fv.version_no, fv.created_at, fv.content_hash,
                CHAR_LENGTH(fv.content) AS size, u.email AS user_email,
                r.version_no AS restored_from_no
            FROM fields_versions fv
            LEFT JOIN users u ON u.id = fv.id_users
            LEFT JOIN fields_versions r ON r.id = fv.restored_from
            WHERE fv.id_fields = :id_fields AND fv.id_languages = :id_languages AND " . $owner['where'] . "
            ORDER BY fv.version_no DESC";
        $rows = $this->db->query_db($sql, array_merge($owner['args'], array(
            ':id_fields' => $id_fields,
            ':id_languages' => $id_languages
        )));
        if (!$rows) {
            return array();
        }
        $current = $this->get_current($relation, $owner_id, $id_fields, $id_languages);
        $current_hash = $current !== null ? hash('sha256', $current['content']) : null;
        $active_found = false;
        foreach ($rows as &$row) {
            $row['active'] = !$active_found && $row['content_hash'] === $current_hash;
            if ($row['active']) {
                $active_found = true;
            }
        }
        return $rows;
    }

    /**
     * Get one version with its content.
     *
     * @param int $id
     *  The id of the version.
     * @retval array|false
     *  The version row, false if it does not exist.
     */
    public function get_version($id)
    {
        return $this->db->query_db_first("SELECT fv.*, f.name AS field_name
            FROM fields_versions fv
            INNER JOIN fields f ON f.id = fv.id_fields
            WHERE fv.id = :id", array(':id' => $id));
    }

    /**
     * Make an old version the current content of its field again. The restore
     * is recorded as a new version and the whole cache is cleared, because the
     * field may be read through any cached page or section.
     *
     * @param int $id
     *  The id of the version to restore.
     * @retval int|false
     *  The id of the new version, false on failure or if the field is no
     *  longer versioned.
     */
    public function restore($id)
    {
        $version = $this->get_version($id);
        if (!$version || !$this->is_versioned($version['id_fields'])) {
            return false;
        }
        if ($version['id_pages']) {
            $relation = RELATION_PAGE_FIELD;
            $owner_id = intval($version['id_pages']);
            $res = $this->db->insert('pages_fields_translation', array(
                'id_pages' => $owner_id,
                'id_fields' => $version['id_fields'],
                'id_languages' => $version['id_languages'],
                'content' => $version['content']
            ), array('content' => $version['content']));
        } else {
            $relation = RELATION_SECTION_FIELD;
            $owner_id = intval($version['id_sections']);
            $res = $this->db->insert('sections_fields_translation', array(
                'id_sections' => $owner_id,
                'id_fields' => $version['id_fields'],
                'id_languages' => $version['id_languages'],
                'id_genders' => 1,
                'content' => $version['content'],
                'meta' => $version['meta']
            ), array('content' => $version['content'], 'meta' => $version['meta']));
        }
        if ($res === false) {
            return false;
        }
        $new_id = $this->snapshot($relation, $owner_id, $version['id_fields'],
            $version['id_languages'], $version['content'], $version['meta'], intval($version['id']));
        $this->db->clear_cache();
        return $new_id;
    }

    /**
     * Get the current content of a field instance (gender 1 for a section
     * field).
     *
     * @retval array|null
     *  Array with content and meta, null if the field has no content row.
     */
    public function get_current($relation, $owner_id, $id_fields, $id_languages)
    {
        if ($relation === RELATION_PAGE_FIELD) {
            $row = $this->db->query_db_first("SELECT content, NULL AS meta FROM pages_fields_translation
                WHERE id_pages = :owner AND id_fields = :id_fields AND id_languages = :id_languages",
                array(':owner' => $owner_id, ':id_fields' => $id_fields, ':id_languages' => $id_languages));
        } else if ($relation === RELATION_SECTION_FIELD) {
            $row = $this->db->query_db_first("SELECT content, meta FROM sections_fields_translation
                WHERE id_sections = :owner AND id_fields = :id_fields AND id_languages = :id_languages
                    AND id_genders = 1",
                array(':owner' => $owner_id, ':id_fields' => $id_fields, ':id_languages' => $id_languages));
        } else {
            return null;
        }
        return $row ? $row : null;
    }

    /**
     * Get the newest version of a field instance (without content).
     */
    private function get_latest($relation, $owner_id, $id_fields, $id_languages)
    {
        $owner = $this->owner_columns($relation, $owner_id);
        if (!$owner) {
            return false;
        }
        return $this->db->query_db_first("SELECT id, version_no, content_hash FROM fields_versions
            WHERE id_fields = :id_fields AND id_languages = :id_languages AND " . $owner['where'] . "
            ORDER BY version_no DESC LIMIT 1", array_merge($owner['args'], array(
                ':id_fields' => $id_fields,
                ':id_languages' => $id_languages
            )));
    }

    /**
     * The owner of a field instance: the owner part of a WHERE clause with its
     * arguments, plus the column values for an insert.
     */
    private function owner_columns($relation, $owner_id, $prefix = '')
    {
        $owner_id = intval($owner_id);
        if ($owner_id <= 0) {
            return false;
        }
        if ($relation === RELATION_PAGE_FIELD) {
            return array(
                'where' => $prefix . "id_pages = :owner_id AND " . $prefix . "id_sections IS NULL",
                'args' => array(':owner_id' => $owner_id),
                'id_pages' => $owner_id,
                'id_sections' => null
            );
        }
        if ($relation === RELATION_SECTION_FIELD) {
            return array(
                'where' => $prefix . "id_sections = :owner_id AND " . $prefix . "id_pages IS NULL",
                'args' => array(':owner_id' => $owner_id),
                'id_pages' => null,
                'id_sections' => $owner_id
            );
        }
        return false;
    }
}
?>
