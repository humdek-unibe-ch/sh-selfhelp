<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";

/**
 * The model of the version history page of a versioned CMS field. The field
 * instance comes from the route: relation, owner (page or section id), field
 * and language. See the service FieldVersions.
 */
class CmsFieldVersionsModel extends BaseModel
{
    /* Constants **************************************************************/

    /**
     * Lines of unchanged text shown around a change.
     */
    const DIFF_CONTEXT = 3;

    /**
     * Above this number of compared line pairs the changed block is shown as
     * removed and added as a whole instead of computing the line diff.
     */
    const DIFF_MAX_CELLS = 4000000;

    /* Public Properties ******************************************************/

    /**
     * RELATION_PAGE_FIELD or RELATION_SECTION_FIELD.
     */
    public $relation;

    /**
     * The id of the page or section that owns the field.
     */
    public $owner_id;

    /**
     * The id of the field.
     */
    public $id_fields;

    /**
     * The id of the language.
     */
    public $id_languages;

    /**
     * The number of the version to show, null for none.
     */
    public $version_no;

    /* Private Properties *****************************************************/

    /**
     * The field version service.
     */
    private $field_versions;

    /**
     * The result of has_access(), computed once.
     */
    private $access = null;

    /**
     * The versions of the field instance, loaded once.
     */
    private $versions = null;

    /* Constructors ***********************************************************/

    /**
     * @param array $services
     *  An associative array holding the different available services.
     * @param array $params
     *  The route parameters: relation, owner_id, id_fields, id_languages and
     *  the optional version.
     */
    public function __construct($services, $params)
    {
        parent::__construct($services);
        $this->field_versions = $services->get_field_versions();
        $relation = isset($params['relation']) ? $params['relation'] : '';
        $this->relation = in_array($relation, array(RELATION_PAGE_FIELD, RELATION_SECTION_FIELD), true)
            ? $relation : null;
        $this->owner_id = isset($params['owner_id']) ? intval($params['owner_id']) : 0;
        $this->id_fields = isset($params['id_fields']) ? intval($params['id_fields']) : 0;
        $this->id_languages = isset($params['id_languages']) ? intval($params['id_languages']) : 0;
        $this->version_no = isset($params['version']) ? intval($params['version']) : null;
    }

    /* Private Methods ********************************************************/

    /**
     * Check whether the current user may change the owner of the field: update
     * access to the page, for a section field to one page the section is used
     * on (an unassigned section needs update access to the CMS).
     *
     * @retval bool
     *  True if the user has update access.
     */
    private function can_update()
    {
        $id_user = $_SESSION['id_user'];
        if ($this->relation === RELATION_PAGE_FIELD) {
            return $this->acl->has_access_update($id_user, $this->owner_id);
        }
        $lineage = $this->db->fetch_section_lineage($this->owner_id);
        if ($lineage === false) {
            return false;
        }
        if (count($lineage['pages']) === 0) {
            return $this->acl->has_access_update($id_user,
                $this->db->fetch_page_id_by_keyword('cmsUpdate'));
        }
        foreach ($lineage['pages'] as $id_page) {
            if ($this->acl->has_access_update($id_user, $id_page)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Pretty-print JSON so that a change shows up on its own line (Global
     * Values are stored as one line). Other text is returned as it is.
     */
    private function readable($content)
    {
        $trimmed = ltrim((string)$content);
        if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
        return (string)$content;
    }

    /* Public Methods *********************************************************/

    /**
     * The field must be versioned and the user needs update access to its
     * owner.
     *
     * @retval bool
     *  True if the history may be shown.
     */
    public function has_access()
    {
        if ($this->access === null) {
            $this->access = isset($_SESSION['id_user']) && $this->relation !== null
                && $this->owner_id > 0 && $this->id_languages > 0
                && $this->field_versions->is_versioned($this->id_fields)
                && $this->can_update();
        }
        return $this->access;
    }

    /**
     * The versions of the field, newest first. The current content is stored
     * as a version first, so content written outside the CMS shows up too.
     *
     * @retval array
     *  See FieldVersions::list_versions().
     */
    public function get_versions()
    {
        if ($this->versions === null) {
            $this->field_versions->ensure_current($this->relation, $this->owner_id,
                $this->id_fields, $this->id_languages);
            $this->versions = $this->field_versions->list_versions($this->relation,
                $this->owner_id, $this->id_fields, $this->id_languages);
        }
        return $this->versions;
    }

    /**
     * Get a version of this field instance by its id or its number.
     *
     * @param int $id
     *  The id of the version, or null to use $version_no.
     * @param int $version_no
     *  The number of the version.
     * @retval array|false
     *  The version with its content, false if it does not belong to the field.
     */
    public function get_version($id, $version_no = null)
    {
        foreach ($this->get_versions() as $version) {
            if (($id !== null && intval($version['id']) === intval($id))
                || ($id === null && intval($version['version_no']) === intval($version_no))) {
                return array_merge($version, $this->field_versions->get_version($version['id']));
            }
        }
        return false;
    }

    /**
     * The version selected in the url. Without a selection it is the newest
     * version that is not the current content (what the last change replaced),
     * or the current one if there is no other.
     *
     * @retval array|false
     *  The version with its content, false if the field has no versions.
     */
    public function get_selected_version()
    {
        if ($this->version_no) {
            return $this->get_version(null, $this->version_no);
        }
        $versions = $this->get_versions();
        foreach ($versions as $version) {
            if (!$version['active']) {
                return $this->get_version($version['id']);
            }
        }
        return count($versions) > 0 ? $this->get_version($versions[0]['id']) : false;
    }

    /**
     * The current content of the field.
     *
     * @retval string
     *  The content, an empty string if the field has none.
     */
    public function get_current_content()
    {
        $current = $this->field_versions->get_current($this->relation, $this->owner_id,
            $this->id_fields, $this->id_languages);
        return $current ? $current['content'] : '';
    }

    /**
     * Make a version of this field the current content again.
     *
     * @param int $id
     *  The id of the version.
     * @retval array|false
     *  The restored version, false if it does not belong to the field or the
     *  restore failed.
     */
    public function restore($id)
    {
        $version = $this->get_version($id);
        if (!$version || !$this->field_versions->restore($version['id'])) {
            return false;
        }
        return $version;
    }

    /**
     * The line diff from a version to the current content.
     *
     * @param string $old
     *  The content of the version.
     * @param string $new
     *  The current content.
     * @retval array
     *  Rows with type ('same', 'del', 'add' or 'skip'), text and the line
     *  numbers old and new (null where the line does not exist); a skip row
     *  stands for unchanged lines that are hidden (text = their number).
     */
    public function get_diff($old, $new)
    {
        $a = explode("\n", str_replace("\r\n", "\n", $this->readable($old)));
        $b = explode("\n", str_replace("\r\n", "\n", $this->readable($new)));
        // Common start and end are compared first, so the line diff only runs
        // on the changed middle part.
        $start = 0;
        while ($start < count($a) && $start < count($b) && $a[$start] === $b[$start]) {
            $start++;
        }
        $end = 0;
        while ($end < count($a) - $start && $end < count($b) - $start
            && $a[count($a) - 1 - $end] === $b[count($b) - 1 - $end]) {
            $end++;
        }
        $mid_a = array_slice($a, $start, count($a) - $start - $end);
        $mid_b = array_slice($b, $start, count($b) - $start - $end);
        $ops = array();
        foreach (array_slice($a, 0, $start) as $line) {
            $ops[] = array('type' => 'same', 'text' => $line);
        }
        $n = count($mid_a);
        $m = count($mid_b);
        if ($n * $m > self::DIFF_MAX_CELLS) {
            foreach ($mid_a as $line) {
                $ops[] = array('type' => 'del', 'text' => $line);
            }
            foreach ($mid_b as $line) {
                $ops[] = array('type' => 'add', 'text' => $line);
            }
        } else {
            // Longest common subsequence, from the end.
            $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
            for ($i = $n - 1; $i >= 0; $i--) {
                for ($j = $m - 1; $j >= 0; $j--) {
                    $lcs[$i][$j] = $mid_a[$i] === $mid_b[$j]
                        ? $lcs[$i + 1][$j + 1] + 1 : max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
                }
            }
            $i = 0;
            $j = 0;
            while ($i < $n || $j < $m) {
                if ($i < $n && $j < $m && $mid_a[$i] === $mid_b[$j]) {
                    $ops[] = array('type' => 'same', 'text' => $mid_a[$i]);
                    $i++;
                    $j++;
                } else if ($j < $m && ($i >= $n || $lcs[$i][$j + 1] >= $lcs[$i + 1][$j])) {
                    $ops[] = array('type' => 'add', 'text' => $mid_b[$j]);
                    $j++;
                } else {
                    $ops[] = array('type' => 'del', 'text' => $mid_a[$i]);
                    $i++;
                }
            }
        }
        foreach (array_slice($a, count($a) - $end) as $line) {
            $ops[] = array('type' => 'same', 'text' => $line);
        }
        $old = 0;
        $new = 0;
        foreach ($ops as &$op) {
            $op['old'] = $op['type'] !== 'add' ? ++$old : null;
            $op['new'] = $op['type'] !== 'del' ? ++$new : null;
        }
        unset($op);
        // Keep only the unchanged lines near a change.
        $keep = array();
        foreach ($ops as $k => $op) {
            if ($op['type'] !== 'same') {
                for ($c = max(0, $k - self::DIFF_CONTEXT); $c <= min(count($ops) - 1, $k + self::DIFF_CONTEXT); $c++) {
                    $keep[$c] = true;
                }
            }
        }
        $rows = array();
        $hidden = 0;
        foreach ($ops as $k => $op) {
            if (isset($keep[$k])) {
                if ($hidden > 0) {
                    $rows[] = array('type' => 'skip', 'text' => $hidden, 'old' => null, 'new' => null);
                    $hidden = 0;
                }
                $rows[] = $op;
            } else {
                $hidden++;
            }
        }
        if ($hidden > 0 && count($rows) > 0) {
            $rows[] = array('type' => 'skip', 'text' => $hidden, 'old' => null, 'new' => null);
        }
        return $rows;
    }

    /**
     * The content of a version in readable form (JSON pretty-printed).
     */
    public function get_readable($content)
    {
        return $this->readable($content);
    }

    /**
     * The name of the field and a label of its owner and language.
     *
     * @retval array
     *  field, owner and language.
     */
    public function get_labels()
    {
        $field = $this->db->query_db_first("SELECT name FROM fields WHERE id = :id",
            array(':id' => $this->id_fields));
        if ($this->relation === RELATION_PAGE_FIELD) {
            $owner = $this->db->query_db_first("SELECT keyword AS name FROM pages WHERE id = :id",
                array(':id' => $this->owner_id));
            $owner_label = 'Page ' . ($owner ? $owner['name'] : $this->owner_id);
        } else {
            $owner = $this->db->query_db_first("SELECT name FROM sections WHERE id = :id",
                array(':id' => $this->owner_id));
            $owner_label = 'Section ' . ($owner ? $owner['name'] : $this->owner_id);
        }
        $language = $this->db->query_db_first("SELECT locale FROM languages WHERE id = :id",
            array(':id' => $this->id_languages));
        return array(
            'field' => $field ? $field['name'] : '',
            'owner' => $owner_label,
            'language' => $language ? $language['locale'] : ''
        );
    }

    /**
     * The url of this page, optionally with a version selected.
     *
     * @param int $version_no
     *  The number of the version, null for none.
     * @retval string
     *  The url.
     */
    public function get_version_url($version_no = null)
    {
        return $this->get_link_url("cmsFieldVersions", array(
            "relation" => $this->relation,
            "owner_id" => $this->owner_id,
            "id_fields" => $this->id_fields,
            "id_languages" => $this->id_languages,
            "version" => $version_no
        ));
    }

    /**
     * The CMS page of the field owner (for a section: on the first page it is
     * used on).
     *
     * @retval string
     *  The url.
     */
    public function get_back_url()
    {
        if ($this->relation === RELATION_PAGE_FIELD) {
            return $this->get_link_url("cmsSelect", array("pid" => $this->owner_id));
        }
        $lineage = $this->db->fetch_section_lineage($this->owner_id);
        if ($lineage && count($lineage['pages']) > 0) {
            return $this->get_link_url("cmsSelect", array(
                "pid" => $lineage['pages'][0],
                "sid" => $this->owner_id
            ));
        }
        return $this->get_link_url("cmsSelect");
    }
}
?>
