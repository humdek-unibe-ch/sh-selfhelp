<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../cms/CmsModel.php";
require_once __DIR__ . "/../../service/ext/swaggest_json_schema_0.12.39.0_require/vendor/autoload.php";

/**
 * This class imports sections and pages from a json file which was created by
 * the export (see CmsExportModel). An import has three steps:
 *  1. The whole file is checked and turned into a plan. The names, styles and
 *     fields are looked up, the values are validated and sanitized with the
 *     same rules as the editor (see CmsModel::check_field_content and
 *     CmsModel::secure_field_content). Nothing is written. If one value is
 *     not valid the import is refused, nothing is imported.
 *  2. The plan is written to the database in one transaction with a few
 *     multi row inserts, no matter how many sections the file contains.
 *  3. The imported section is linked to its parent.
 * A section which is used in several places of the file (same id, style and
 * name) is created once and linked everywhere, like it was exported.
 */
class CmsImportModel extends CmsModel
{
    /* Constants **************************************************************/

    /**
     * The maximum number of rows and the maximum size of the values of one
     * multi row insert.
     */
    const CHUNK_ROWS = 200;
    const CHUNK_BYTES = 1000000;

    /* Public Properties *****************************************************/

    /**
     * Page or section
     */
    public $type;

    /**
     * The imported json
     */
    public $json;

    /**
     * The id of the root section that was created by the last section import
     */
    public $imported_section_id = null;

    /**
     * The id of the page that was created by the last page import
     */
    public $imported_page_id = null;

    /* Private Properties *****************************************************/

    /**
     * The ids of the styles, fields (with their type), languages and genders
     * by their names. See CmsImportModel::load_lookups.
     */
    private $lookups = array();

    /**
     * The sections which are created. Each entry has the keys 'id_styles',
     * 'label' (the name in the file, used in messages), 'base' (the name
     * without prefix and suffix), 'name' (the unique name in the database),
     * 'fields' (see CmsImportModel::plan_fields), 'existing' (true for the
     * navigation section of a page, it is created with the page) and 'id'
     * (null until the section is saved).
     */
    private $sections = array();

    /**
     * The links between the sections which are created. The key is
     * "table|parent|child", each entry has the keys 'table', 'parent' and
     * 'child' (indexes of $sections) and 'position'.
     */
    private $links = array();

    /**
     * The sections which are used in several places of the file: the key is
     * "id|style|name" (see CmsImportModel::plan_section), the value the index
     * of the section in $sections.
     */
    private $shared = array();

    /**
     * The indexes of the section which is planned at the moment and of all its
     * parents. A section which is found in this list again contains itself.
     */
    private $plan_path = array();

    /**
     * If true the navigation items (nav_children) of the sections are
     * imported. This is the case for page imports.
     */
    private $plan_nav = false;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $type)
    {
        parent::__construct($services, null, null, null);
        $this->type = $type;
    }

    /* Private Methods ********************************************************/

    /**
     * Load the ids of the styles, fields, languages and genders. A single
     * import can contain hundreds of sections which all use the same few
     * lookups. This is much cheaper than a query for each section and field.
     * If a query fails an exception is thrown.
     */
    private function load_lookups()
    {
        $this->lookups = array("styles" => array(), "fields" => array(), "languages" => array(), "genders" => array());
        $queries = array(
            "styles" => "SELECT id, name AS value, '' AS type FROM styles",
            "fields" => "SELECT f.id, f.name AS value, ft.name AS type FROM fields AS f LEFT JOIN fieldType AS ft ON ft.id = f.id_type",
            "languages" => "SELECT id, locale AS value, '' AS type FROM languages",
            "genders" => "SELECT id, name AS value, '' AS type FROM genders",
        );
        foreach ($queries as $key => $sql) {
            $rows = $this->db->query_db($sql);
            if ($rows === false) {
                throw new Exception("The import could not read the " . $key . " from the database.");
            }
            foreach ($rows as $row) {
                $this->lookups[$key][$row['value']] = array("id" => intval($row['id']), "type" => (string)$row['type']);
            }
        }
    }

    /**
     * Get an entry of a lookup. If it does not exist an exception is thrown
     * which refuses the import.
     * @param string $lookup the lookup: styles, fields, languages or genders
     * @param string $value the name
     * @param string $label description used in the error message
     * @retval array the entry with the keys 'id' and 'type'
     */
    private function get_lookup($lookup, $value, $label)
    {
        if (!isset($this->lookups[$lookup][$value])) {
            throw new Exception($label . " '" . $value . "' does not exist in this system");
        }
        return $this->lookups[$lookup][$value];
    }

    /**
     * Validate the fields of a section or a page and prepare them for the
     * database. A value which is not valid refuses the whole import.
     * @param string $label the name of the section (or page) for the messages
     * @param array $fields the exported fields
     * @param bool $is_page true for the fields of a page, they have no gender
     * @retval array
     * The rows (id_fields, id_languages, id_genders, content, meta) which are
     * written to sections_fields_translation. A page field has the id of the
     * gender null. The content is sanitized.
     */
    private function plan_fields($label, $fields, $is_page = false)
    {
        $rows = array();
        foreach ($fields as $field) {
            if ($field['locale'] === '' || (!$is_page && $field['gender'] === '')) {
                // the export lists all the fields of the style, also the ones without content
                continue;
            }
            $name = $field['field_name'];
            $where = ($is_page ? "Page '" : "Section '") . $label . "', field '" . $name . "' ["
                . $field['locale'] . ($is_page ? "" : ", " . $field['gender']) . "]: ";
            $info = $this->get_lookup("fields", $name, $where . "The field");
            $id_language = $this->get_lookup("languages", $field['locale'], $where . "The language")['id'];
            $id_gender = $is_page ? null : $this->get_lookup("genders", $field['gender'], $where . "The gender")['id'];
            $key = $info['id'] . "|" . $id_language . "|" . $id_gender;
            if (isset($rows[$key])) {
                throw new Exception($where . "The field is defined twice.");
            }
            $content = $field['content'];
            if (!$this->check_field_content($info['type'], $content)) {
                throw new Exception($where . "The value is not valid for a field of the type '" . $info['type'] . "'.");
            }
            // The content of an export is sanitized already: existing html entities must not be encoded again
            $content = (string)$this->secure_field_content($info['type'], $content, false);
            $meta = isset($field['meta']) ? $field['meta'] : null;
            if ($meta !== null && $meta !== "") {
                // the meta data is json and it must fit into the column
                if (!is_string($meta) || strlen($meta) > 10000) {
                    throw new Exception($where . "The meta data is not valid.");
                }
                json_decode($meta);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception($where . "The meta data is not valid.");
                }
            }
            $rows[$key] = array($info['id'], $id_language, $id_gender, $content, $meta);
        }
        return array_values($rows);
    }

    /**
     * Add a link between two sections to the plan. A link is added only once.
     * @param string $table the link table: sections_hierarchy or sections_navigation
     * @param int $parent the index of the parent section
     * @param int $child the index of the child section
     * @param int $position the position of the child
     */
    private function plan_link($table, $parent, $child, $position)
    {
        $key = $table . "|" . $parent . "|" . $child;
        if (!isset($this->links[$key])) {
            $this->links[$key] = array("table" => $table, "parent" => $parent, "child" => $child, "position" => intval($position));
        }
    }

    /**
     * Add a section with all its children (and navigation items) to the plan.
     * Nothing is written to the database.
     * A section which is used several times in the file (same id, style and
     * name, e.g. a section which is referenced from several places) is planned
     * once, the other occurrences are only linked. A section which is found
     * inside itself is a circular reference and refuses the import.
     * @param array $section the exported section
     * @param bool $existing true for the navigation section of a page
     * @retval int
     * The index of the section in the plan.
     */
    private function plan_section($section, $existing = false)
    {
        $label = $section['section_name'];
        if (!isset($section['style_name'])) {
            throw new Exception("The section '" . $label . "' has no style");
        }
        $key = null;
        if (!$existing && isset($section['id_sections']) && intval($section['id_sections']) > 0) {
            $key = intval($section['id_sections']) . "|" . $section['style_name'] . "|" . $label;
        }
        if ($key !== null && isset($this->shared[$key])) {
            if (isset($this->plan_path[$this->shared[$key]])) {
                throw new Exception("The section '" . $label . "' contains itself (circular reference).");
            }
            return $this->shared[$key];
        }
        $id_styles = $existing ? null : $this->get_lookup("styles", $section['style_name'], "The style")['id'];
        // the timestamp of a previous import is removed, otherwise the names grow with every export/import cycle;
        // the name of a section can only contain letters, numbers, - and _ (see NAME_PATTERN)
        $base = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', preg_replace('/_\d{10}$/', '', $label));
        $idx = count($this->sections);
        $this->sections[$idx] = array(
            "id_styles" => $id_styles,
            "label" => $label,
            "base" => $base !== "" ? $base : preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $section['style_name']),
            "name" => null,
            "fields" => $this->plan_fields($label, $section['fields']),
            "existing" => $existing,
            "id" => null,
        );
        if ($key !== null) {
            $this->shared[$key] = $idx;
        }
        $this->plan_path[$idx] = true;
        if (isset($section['children'])) {
            foreach ($section['children'] as $child) {
                $this->plan_link("sections_hierarchy", $idx, $this->plan_section($child), $child['position']);
            }
        }
        if ($this->plan_nav && isset($section['nav_children'])) {
            foreach ($section['nav_children'] as $nav_child) {
                $this->plan_link("sections_navigation", $idx, $this->plan_section($nav_child), $nav_child['position']);
            }
        }
        unset($this->plan_path[$idx]);
        return $idx;
    }

    /**
     * Start a new plan.
     * @param bool $plan_nav true if the navigation items are imported
     */
    private function reset_plan($plan_nav)
    {
        $this->sections = array();
        $this->links = array();
        $this->shared = array();
        $this->plan_path = array();
        $this->plan_nav = $plan_nav;
        $this->imported_section_id = null;
        $this->imported_page_id = null;
        $this->load_lookups();
    }

    /**
     * Get the names of the sections which already exist (lower case, the
     * names are not case sensitive in the database).
     * @param array $names the names to look for
     * @retval array the used names as keys
     */
    private function fetch_used_names($names)
    {
        $used = array();
        foreach (array_chunk($names, self::CHUNK_ROWS) as $chunk) {
            $rows = $this->db->query_db(
                "SELECT name FROM sections WHERE name IN (" . implode(",", array_fill(0, count($chunk), "?")) . ")",
                $chunk
            );
            if ($rows === false) {
                throw new Exception("The names of the sections could not be checked.");
            }
            foreach ($rows as $row) {
                $used[strtolower($row['name'])] = true;
            }
        }
        return $used;
    }

    /**
     * Give each new section a unique name. The names of the sections must be
     * unique in the database, therefore the sections of an import get the
     * timestamp of the import as suffix (and a counter if the name is used).
     */
    private function assign_names()
    {
        $suffix = "_" . time();
        $pending = array();
        foreach ($this->sections as $idx => $section) {
            if (!$section['existing']) {
                $pending[$idx] = 0;
            }
        }
        $taken = array();
        while (count($pending) > 0) {
            $candidates = array();
            foreach ($pending as $idx => $attempt) {
                $end = $attempt == 0 ? $suffix : $suffix . "_" . $attempt;
                $candidates[$idx] = substr($this->sections[$idx]['base'], 0, 100 - strlen($end)) . $end;
            }
            $used = $this->fetch_used_names(array_values(array_unique($candidates)));
            foreach ($candidates as $idx => $name) {
                $lower = strtolower($name);
                if (isset($used[$lower]) || isset($taken[$lower])) {
                    $pending[$idx]++;
                    continue;
                }
                $taken[$lower] = true;
                $this->sections[$idx]['name'] = $name;
                unset($pending[$idx]);
            }
        }
    }

    /**
     * Insert rows with multi row inserts. The rows are split in chunks which
     * are not too big. If the database refuses a chunk an exception is thrown.
     * @param string $table the table (never user input)
     * @param array $cols the columns (never user input)
     * @param array $rows the values, one array for each row
     */
    private function insert_rows($table, $cols, $rows)
    {
        $chunk = array();
        $bytes = 0;
        foreach ($rows as $row) {
            $chunk[] = $row;
            foreach ($row as $value) {
                $bytes += strlen((string)$value);
            }
            if (count($chunk) >= self::CHUNK_ROWS || $bytes >= self::CHUNK_BYTES) {
                $this->insert_chunk($table, $cols, $chunk);
                $chunk = array();
                $bytes = 0;
            }
        }
        if (count($chunk) > 0) {
            $this->insert_chunk($table, $cols, $chunk);
        }
    }

    /**
     * Insert a chunk of rows.
     * @param string $table the table
     * @param array $cols the columns
     * @param array $rows the rows, not empty
     */
    private function insert_chunk($table, $cols, $rows)
    {
        if ($this->db->insert_mult($table, $cols, $rows) === false) {
            throw new Exception("The data could not be saved (table '" . $table . "').");
        }
    }

    /**
     * Write the plan to the database: the new sections, their fields and the
     * links between the sections. This must run inside a transaction.
     * @param int $id_page the id of the page the navigation items belong to
     */
    private function write_plan($id_page = null)
    {
        $new = array();
        foreach ($this->sections as $idx => $section) {
            if (!$section['existing']) {
                $new[$idx] = array($section['id_styles'], $section['name']);
            }
        }
        foreach (array_chunk($new, self::CHUNK_ROWS, true) as $chunk) {
            $this->insert_chunk("sections", array("id_styles", "name"), array_values($chunk));
            // read the ids by the unique names, the ids of a multi row insert are not guaranteed to follow each other
            $names = array_column($chunk, 1);
            $rows = $this->db->query_db(
                "SELECT id, name FROM sections WHERE name IN (" . implode(",", array_fill(0, count($names), "?")) . ")",
                $names
            );
            if ($rows === false) {
                throw new Exception("The new sections could not be read from the database.");
            }
            $ids = array();
            foreach ($rows as $row) {
                $ids[strtolower($row['name'])] = intval($row['id']);
            }
            foreach ($chunk as $idx => $row) {
                if (!isset($ids[strtolower($row[1])])) {
                    throw new Exception("The section '" . $this->sections[$idx]['label'] . "' could not be saved.");
                }
                $this->sections[$idx]['id'] = $ids[strtolower($row[1])];
            }
        }
        $fields = array();
        foreach ($this->sections as $section) {
            foreach ($section['fields'] as $field) {
                $fields[] = array_merge(array($section['id']), $field);
            }
        }
        $this->insert_rows("sections_fields_translation",
            array("id_sections", "id_fields", "id_languages", "id_genders", "content", "meta"), $fields);
        $children = array();
        $navigation = array();
        foreach ($this->links as $link) {
            $parent = $this->sections[$link['parent']]['id'];
            $child = $this->sections[$link['child']]['id'];
            if ($link['table'] == "sections_hierarchy") {
                $children[] = array($parent, $child, $link['position']);
            } else {
                $navigation[] = array($parent, $child, $id_page, $link['position']);
            }
        }
        $this->insert_rows("sections_hierarchy", array("parent", "child", "position"), $children);
        $this->insert_rows("sections_navigation", array("parent", "child", "id_pages", "position"), $navigation);
    }

    /* Public Methods *********************************************************/

    /**
     * Turn a schema validation exception into a short message for the UI.
     * Swaggest appends ", data: {...}" with the whole file; that must not be
     * shown in an alert.
     *
     * @param Exception $e
     *  The exception of the schema validation.
     * @retval string
     *  A short error message.
     */
    private function format_json_schema_error($e)
    {
        $message = $e->getMessage();
        $cut = strpos($message, ', data:');
        if ($cut !== false) {
            $message = substr($message, 0, $cut);
        }
        $message = trim(preg_replace('/\s+/', ' ', $message));
        if (strlen($message) > 200) {
            $message = substr($message, 0, 197) . '...';
        }
        $what = $this->type == 'page' ? 'page' : 'section';
        if ($message === '') {
            return 'The file does not match a ' . $what . ' export.';
        }
        return 'The file does not match a ' . $what . ' export: ' . $message;
    }

    /**
     * Validate the json file against the schema and if it is valid we save it
     * @param string $json_string json data as string
     * @retval boolean true or false if the file is invalid
     */
    public function validate_and_set_json($json_string)
    {
        $data = json_decode($json_string, true);
        if (!is_array($data)) {
            $this->json = 'The file is not a valid JSON file.';
            return false;
        }
        // A section export used with Import page (or the other way round) is the
        // usual mistake; say so before the schema dumps the whole file.
        if ($this->type == 'page' && isset($data['section']) && !isset($data['page'])) {
            $this->json = 'This file is a section export. Use Import section instead of Import page.';
            return false;
        }
        if ($this->type == 'section' && isset($data['page']) && !isset($data['section'])) {
            $this->json = 'This file is a page export. Use Import page instead of Import section.';
            return false;
        }
        if ($this->type == 'section') {
            $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../../schemas/section.json')));
        } else {
            $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../../schemas/page.json')));
        }
        try {
            $schema->in(json_decode($json_string, false));
            $this->json = $data;
            return true;
        } catch (Exception $e) {
            $this->json = $this->format_json_schema_error($e);
            return false;
        }
    }

    /**
     * Import section based on the json input file
     * @param string $relation = null
     * Where the section is attached:
     *  - page_children: the section is added to the page `$id_page`
     *  - section_children: the section is added to the section `$parent_id`,
     *    which must belong to the page `$id_page`
     *  - null: the section is only created and stays unassigned
     * @param integer $id_page = null
     * The page in which the section is imported; used to check the access rights and for the page children
     * @param integer $parent_id = null
     * The parent section
     * @param integer $position = null
     * the position where the section should be inserted. If null the section is added at the end
     * @retval mixed
     * true on success otherwise the error message
     */
    public function import_section($relation = null, $id_page = null, $parent_id = null, $position = null)
    {
        if (!$relation && $parent_id > 0) {
            $relation = RELATION_SECTION_CHILDREN;
        }
        if ($relation && $relation != RELATION_PAGE_CHILDREN && $relation != RELATION_SECTION_CHILDREN) {
            return "Invalid relation: " . $relation;
        }
        if ($relation && !($id_page > 0)) {
            return "The page in which the section is imported is missing.";
        }
        if ($relation && !$this->acl->has_access_update($_SESSION['id_user'], $id_page)) {
            return "You do not have the rights to change the page.";
        }
        if ($relation == RELATION_PAGE_CHILDREN && !$this->db->query_db_first("SELECT id FROM pages WHERE id = :id", array(":id" => $id_page))) {
            return "The page does not exist.";
        }
        if ($relation == RELATION_SECTION_CHILDREN) {
            if (!($parent_id > 0 && $this->db->query_db_first("SELECT id FROM sections WHERE id = :id", array(":id" => $parent_id)))) {
                return "The parent section does not exist.";
            }
            // the user may only change the sections of the page he has the rights for
            if (!$this->is_section_on_page($parent_id, $id_page)) {
                return "The parent section does not belong to the page.";
            }
        }
        $started = false;
        try {
            $this->reset_plan(false); // a section import never has navigation items
            $root = $this->plan_section($this->json['section']);
            $this->assign_names();
            $this->db->begin_transaction();
            $started = true;
            $this->write_plan();
            $section_id = $this->sections[$root]['id'];
            if ($relation) {
                $target = $this->get_link_target($relation, $id_page, $parent_id);
                $link = $this->db->insert($target['table'], array(
                    $target['parent_col'] => $target['id_parent'],
                    $target['child_col'] => $section_id,
                    "position" => $position !== null ? $position : $this->get_next_position($target)
                ));
                if (!$link) {
                    throw new Exception('Cannot add the section to the ' . ($relation == RELATION_PAGE_CHILDREN ? 'page' : 'parent section'));
                }
                $this->adjust_hierarchy($target['id_parent'], $relation);
            }
            $this->db->commit();
            $this->imported_section_id = $section_id;
            return true;
        } catch (Exception $e) {
            if ($started) {
                $this->db->rollback();
            }
            return $e->getMessage();
        }
    }

    /**
     * Import a complete page based on the json input file. The page gets its
     * own copy of all sections.
     * @param string $keyword = null
     * The keyword of the new page. If it is empty the keyword from the json is used
     * @retval mixed
     * true on success otherwise the error message
     */
    public function import_page($keyword = null)
    {
        if (!$this->can_create_new_page()) {
            return "You do not have the rights to create pages.";
        }
        $page = $this->json['page'];
        if ($page['action'] != 'sections') {
            return "Only pages with sections can be imported. The action of this page is '" . $page['action'] . "'.";
        }
        $keyword = $keyword ? $keyword : $page['keyword'];
        $keyword_error = $this->get_keyword_error($keyword);
        if ($keyword_error) {
            return $keyword_error;
        }
        $is_navigation = isset($page['navigation_section']);
        $protocols = array_intersect(explode('|', isset($page['protocol']) ? $page['protocol'] : ''), CmsModel::PAGE_PROTOCOLS);
        $access_type = $this->db->get_lookup_id_by_code(pageAccessTypes, isset($page['page_access_type']) && $page['page_access_type'] ? $page['page_access_type'] : pageAccessTypes_mobile_and_web);
        if (!$access_type) {
            $access_type = $this->db->get_lookup_id_by_code(pageAccessTypes, pageAccessTypes_mobile_and_web);
        }
        $started = false;
        try {
            $this->reset_plan(true);
            $page_fields = array();
            foreach ($this->plan_fields($page['keyword'], $page['fields'], true) as $field) {
                $page_fields[] = array($field[0], $field[1], $field[3]);
            }
            $roots = array(); // a section which is listed twice is linked once
            foreach ($page['sections'] as $section) {
                $roots[$this->plan_section($section)] = intval($section['position']);
            }
            $navigation = $is_navigation ? $this->plan_section($page['navigation_section'], true) : null;
            $this->assign_names();
            $this->db->begin_transaction();
            $started = true;
            $pid = $this->create_new_page(
                $keyword,
                '/' . $keyword . ($is_navigation ? '/[i:nav]' : ''),
                count($protocols) > 0 ? implode('|', $protocols) : 'GET|POST',
                $is_navigation ? 4 : 3, // 4 creates a navigation page, 3 a page with sections
                null,
                isset($page['is_headless']) && $page['is_headless'],
                isset($page['page_type']) && $page['page_type'] == 'open',
                null,
                $access_type
            );
            if (!$pid) {
                throw new Exception('The page could not be created');
            }
            if ($is_navigation) {
                // create_new_page already created an empty navigation section; fill it with the imported content
                $nav = $this->db->query_db_first("SELECT id_navigation_section FROM pages WHERE id = :id", array(":id" => $pid));
                $this->sections[$navigation]['id'] = intval($nav['id_navigation_section']);
            }
            $this->write_plan($pid);
            $rows = array();
            foreach ($page_fields as $field) {
                $rows[] = array_merge(array($pid), $field);
            }
            $this->insert_rows("pages_fields_translation", array("id_pages", "id_fields", "id_languages", "content"), $rows);
            $rows = array();
            foreach ($roots as $idx => $position) {
                $rows[] = array($pid, $this->sections[$idx]['id'], $position);
            }
            $this->insert_rows("pages_sections", array("id_pages", "id_sections", "position"), $rows);
            $this->db->commit();
            $this->imported_page_id = $pid;
            return true;
        } catch (Exception $e) {
            if ($started) {
                $this->db->rollback();
            }
            return $e->getMessage();
        }
    }
}
