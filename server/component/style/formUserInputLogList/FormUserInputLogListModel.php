<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formUserInput/FormUserInputModel.php";

/**
 * Model for formUserInputLogList: log form + list.
 * is_log is always true in code (not a CMS/DB field on this style).
 * Deep-links: fixed `record` + `sid` (section id) so multiple instances on one
 * page do not collide. Writes authorize from POST ids only (FormUserInputController).
 */
class FormUserInputLogListModel extends FormUserInputModel
{
    const URL_RECORD_PARAM = 'record';
    const URL_SECTION_PARAM = 'sid';

    /**
     * Resolved record id from URL (-1 = none, 0 = new, >0 = edit).
     */
    private $url_record_id = -1;

    /**
     * Raw URL param value (e.g. "new" or "11").
     */
    private $url_record_raw = null;

    /**
     * True when ?record=<id> was requested but missing or not allowed.
     */
    private $record_access_denied = false;

    /**
     * Cached list rows (mapped).
     */
    private $list_rows = null;

    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);
        if (!$this->is_cms_page()) {
            $this->resolve_url_record();
            if ($this->url_record_id > 0) {
                $record = $this->get_form_entry_record(
                    $this->url_record_id,
                    $this->get_db_field("own_entries_only", 1)
                );
                if ($record) {
                    $this->set_entry_record(array_merge($this->get_entry_record() ?: array(), $record));
                    $this->loadChildren($this->get_entry_record());
                } else {
                    $this->record_access_denied = true;
                    $this->url_record_id = -1;
                }
            }
        }
    }

    /**
     * Always log mode for this style.
     *
     * @return bool
     */
    public function is_log()
    {
        return true;
    }

    /**
     * Selected record for form display (URL / entry_record only).
     * Writes must not use this for authorization — see controller.
     *
     * @return int
     */
    public function get_selected_record_id()
    {
        if ($this->url_record_id > 0) {
            return $this->url_record_id;
        }
        $from_entry = parent::get_selected_record_id();
        if ($from_entry > 0) {
            return $from_entry;
        }
        return -1;
    }

    /**
     * Fixed deep-link key for the record id / "new" (not CMS-configurable).
     *
     * @return string
     */
    public function get_url_param_name()
    {
        return self::URL_RECORD_PARAM;
    }

    /**
     * Fixed query key for this style's section id (disambiguates multi-instance pages).
     *
     * @return string
     */
    public function get_url_section_param_name()
    {
        return self::URL_SECTION_PARAM;
    }

    /**
     * @return int -1 none, 0 new, >0 edit id
     */
    public function get_url_record_id()
    {
        return $this->url_record_id;
    }

    /**
     * @return string|null
     */
    public function get_url_record_raw()
    {
        return $this->url_record_raw;
    }

    /**
     * @return bool
     */
    public function is_record_access_denied()
    {
        return $this->record_access_denied;
    }

    /**
     * @return bool
     */
    public function should_show_list()
    {
        if ($this->record_access_denied) {
            // Denied deep-link: missing page only (modal and page modes).
            return false;
        }
        if (!$this->get_db_field("show_list", 1)) {
            return false;
        }
        if ($this->get_db_field("show_form_in_modal", 1)) {
            return true;
        }
        return $this->url_record_raw === null;
    }

    /**
     * @return bool
     */
    public function should_show_page_form()
    {
        if ($this->record_access_denied) {
            return false;
        }
        if ($this->get_db_field("show_form_in_modal", 1)) {
            return false;
        }
        if (!$this->get_db_field("show_list", 1)) {
            return true;
        }
        return $this->url_record_raw !== null;
    }

    /**
     * @return bool
     */
    public function should_show_modal_form()
    {
        if ($this->record_access_denied) {
            return false;
        }
        return (bool)$this->get_db_field("show_form_in_modal", 1);
    }

    /**
     * List rows with fields_map applied. Fetches all allowed records.
     *
     * @return array
     */
    public function get_list_rows()
    {
        if ($this->list_rows !== null) {
            return $this->list_rows;
        }
        // CMS preview: render shell only — no live participant data.
        if ($this->is_cms_page()) {
            $this->list_rows = array();
            return $this->list_rows;
        }
        $table_name = sprintf('%010d', $this->section_id);
        $form_id = $this->user_input->get_dataTable_id($table_name);
        if (!$form_id) {
            $this->list_rows = array();
            return $this->list_rows;
        }
        $filter = trim((string)$this->get_db_field("filter", ""));
        $own = $this->get_db_field("own_entries_only", 1);
        $fields_map = $this->get_db_field('fields_map', array());
        if (!is_array($fields_map)) {
            $fields_map = array();
        }
        $selected_columns = '';
        if (count($fields_map) > 0) {
            $cols = array_keys($fields_map);
            $selected_columns = implode(',', array_filter(array_map('trim', $cols), function ($v) {
                return $v !== '';
            }));
        }
        $res = $this->user_input->get_data($form_id, $filter, $own, null, false, true, $selected_columns);
        if (!$res) {
            $this->list_rows = array();
            return $this->list_rows;
        }
        if (count($fields_map) > 0) {
            $mappedRows = array();
            foreach ($res as $row) {
                $mappedRow = array();
                $mappedRow[ENTRY_RECORD_ID] = $row[ENTRY_RECORD_ID];
                foreach ($fields_map as $field => $label) {
                    if (array_key_exists($field, $row)) {
                        $mappedRow[$label] = $row[$field];
                    }
                }
                $mappedRows[] = $mappedRow;
            }
            $this->list_rows = $mappedRows;
        } else {
            $this->list_rows = $res;
        }
        return $this->list_rows;
    }

    /**
     * @return array
     */
    public function get_list_headers()
    {
        $fields_map = $this->get_db_field('fields_map', array());
        if (is_array($fields_map) && count($fields_map) > 0) {
            return array_values($fields_map);
        }
        $rows = $this->get_list_rows();
        if (!$rows || count($rows) === 0) {
            return array();
        }
        $headers = array();
        foreach (array_keys($rows[0]) as $key) {
            if ($key === ENTRY_RECORD_ID) {
                continue;
            }
            $headers[] = $key;
        }
        return $headers;
    }

    /**
     * @return string
     */
    public function get_list_url()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $parts = parse_url($uri);
        $path = $parts['path'] ?? '';
        $query = array();
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        unset($query[self::URL_RECORD_PARAM], $query[self::URL_SECTION_PARAM]);
        $qs = http_build_query($query);
        return $path . ($qs !== '' ? '?' . $qs : '');
    }

    /**
     * Deep-link for create/edit. Always includes sid=<this section id>.
     *
     * @param string|int $record_value
     * @return string
     */
    public function get_record_url($record_value)
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $parts = parse_url($uri);
        $path = $parts['path'] ?? '';
        $query = array();
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        $query[self::URL_RECORD_PARAM] = $record_value;
        $query[self::URL_SECTION_PARAM] = intval($this->section_id);
        return $path . '?' . http_build_query($query);
    }

    /**
     * Resolve ?record=&sid= only when sid matches this section.
     * Both params are required; record alone is ignored.
     * Record load still goes through get_form_entry_record + own_entries_only.
     */
    private function resolve_url_record()
    {
        $param = self::URL_RECORD_PARAM;
        $sid_param = self::URL_SECTION_PARAM;
        $raw = null;
        if (isset($this->params[$param])) {
            $raw = $this->params[$param];
        } elseif (isset($_GET[$param])) {
            $raw = $_GET[$param];
        }
        if ($raw === null || $raw === false) {
            $this->url_record_id = -1;
            $this->url_record_raw = null;
            return;
        }

        $sid = 0;
        if (isset($this->params[$sid_param])) {
            $sid = intval($this->params[$sid_param]);
        } elseif (isset($_GET[$sid_param])) {
            $sid = intval($_GET[$sid_param]);
        }
        // sid is required and must match this section (multi-instance safe).
        if ($sid <= 0 || $sid !== intval($this->section_id)) {
            $this->url_record_id = -1;
            $this->url_record_raw = null;
            return;
        }

        $this->url_record_raw = $raw;
        if ($raw === 'new' || $raw === '') {
            $this->url_record_id = 0;
            return;
        }
        $this->url_record_id = intval($raw);
        if ($this->url_record_id <= 0) {
            $this->url_record_id = -1;
            $this->url_record_raw = null;
        }
    }
}
?>
