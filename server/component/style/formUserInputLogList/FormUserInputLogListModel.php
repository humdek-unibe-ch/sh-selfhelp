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
    const QUICK_ACTION_RESERVED = array('edit' => true, 'delete' => true);

    /**
     * System keys from get_data rows that must not become writable dataCols.
     * id_users is kept on purpose so quick-action updates preserve row ownership.
     */
    const QUICK_ACTION_WRITE_EXCLUDE = array(
        'record_id' => true,
        'user_name' => true,
        'user_code' => true,
        'id_actionTriggerTypes' => true,
        'triggerType' => true,
        'trigger_type' => true,
        'entry_date' => true,
    );

    /**
     * Cached validated quick_actions for this instance.
     *
     * @var array|null
     */
    private $parsed_quick_actions = null;

    /**
     * Cached resolved actions_order keys.
     *
     * @var array|null
     */
    private $resolved_actions_order = null;

    /**
     * Config/validation issues for debug console.
     *
     * @var array
     */
    private $quick_action_issues = array();

    /**
     * Known form/dataCol field names for quick_actions when/set validation.
     *
     * @var array|null
     */
    private $known_data_fields = null;

    /**
     * record_id => raw list row (built once after get_list_rows).
     *
     * @var array|null
     */
    private $list_raw_rows_by_id = null;

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

    /**
     * Raw list rows from get_data (before fields_map / values_map).
     */
    private $list_raw_rows = null;

    /**
     * Column labels whose cells were built from values_map (trusted HTML).
     * Key = header label, value = true.
     */
    private $list_html_headers = array();

    /**
     * Header label => fields_map key (built lazily, first label wins).
     */
    private $list_header_fields = null;

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
                    // Keep internal history out of children / interpolation.
                    unset($record[USER_INPUT_HISTORY_LOG_COL]);
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
     * Create never writes history; strip client spoof of `_history_log`.
     *
     * @param array $user_input
     * @return int|false
     */
    public function save_user_input($user_input)
    {
        unset($user_input[USER_INPUT_HISTORY_LOG_COL]);
        return parent::save_user_input($user_input);
    }

    /**
     * On update, optionally append a pre-edit snapshot to `_history_log`.
     * Client-supplied `_history_log` is never trusted.
     *
     * @param array $user_input
     * @param int $record_id
     * @return int|false
     */
    public function update_user_input($user_input, $record_id)
    {
        unset($user_input[USER_INPUT_HISTORY_LOG_COL]);
        if (!$this->get_db_field('history_log', 0)) {
            return parent::update_user_input($user_input, $record_id);
        }
        $current = $this->get_form_entry_record(
            $record_id,
            $this->get_db_field('own_entries_only', 1)
        );
        // Do not update without a snapshot when history is required.
        if (!is_array($current) || count($current) === 0) {
            return false;
        }
        $history = $this->decode_history_log(
            isset($current[USER_INPUT_HISTORY_LOG_COL])
                ? $current[USER_INPUT_HISTORY_LOG_COL]
                : null
        );
        $history[] = $this->build_history_snapshot($current);
        $user_input[USER_INPUT_HISTORY_LOG_COL] = json_encode($history);
        return parent::update_user_input($user_input, $record_id);
    }

    /**
     * Build one history entry from the current row (pre-update values).
     *
     * @param array $row
     * @return array
     */
    private function build_history_snapshot($row)
    {
        $exclude = array(
            ENTRY_RECORD_ID => true,
            'user_name' => true,
            'user_code' => true,
            'id_actionTriggerTypes' => true,
            'triggerType' => true,
            'trigger_type' => true,
            USER_INPUT_HISTORY_LOG_COL => true,
        );
        $snapshot = array();
        foreach ($row as $key => $value) {
            if (isset($exclude[$key])) {
                continue;
            }
            $snapshot[$key] = $value;
        }
        // Actor of this edit (underscore key avoids colliding with a form field).
        $snapshot['_edited_by'] = isset($_SESSION['id_user'])
            ? $_SESSION['id_user']
            : GUEST_USER_ID;
        return $snapshot;
    }

    /**
     * @param mixed $raw
     * @return array
     */
    private function decode_history_log($raw)
    {
        if ($raw === null || $raw === '') {
            return array();
        }
        if (is_array($raw)) {
            return $raw;
        }
        $decoded = json_decode((string)$raw, true);
        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Remove `_history_log` from a list/debug row.
     *
     * @param array $row
     * @return array
     */
    private function strip_history_log_from_row($row)
    {
        if (is_array($row)) {
            unset($row[USER_INPUT_HISTORY_LOG_COL]);
        }
        return $row;
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
            $this->list_raw_rows = array();
            $this->list_raw_rows_by_id = array();
            $this->list_rows = array();
            return $this->list_rows;
        }
        $filter = trim((string)$this->get_db_field("filter", ""));
        $own = $this->get_db_field("own_entries_only", 1);
        $fields_map = $this->get_db_field('fields_map', array());
        if (!is_array($fields_map)) {
            $fields_map = array();
        }
        $values_map = $this->get_db_field('values_map', array());
        if (!is_array($values_map)) {
            $values_map = array();
        }
        $this->list_html_headers = array();
        // fields_map limits visible columns; still load any fields referenced by
        // values_map templates (e.g. {{module}} inside the title template).
        $selected_columns = $this->build_list_selected_columns($fields_map, $values_map);
        $res = $this->user_input->get_data($form_id, $filter, $own, null, false, true, $selected_columns);
        if (!$res) {
            $this->list_raw_rows = array();
            $this->list_raw_rows_by_id = array();
            $this->list_rows = array();
            return $this->list_rows;
        }
        $raw_rows = array();
        foreach ($res as $row) {
            $raw_rows[] = $this->strip_history_log_from_row($row);
        }
        $this->list_raw_rows = $raw_rows;
        $this->list_raw_rows_by_id = null;
        if (count($fields_map) > 0) {
            $mappedRows = array();
            foreach ($raw_rows as $row) {
                $mappedRow = array();
                $mappedRow[ENTRY_RECORD_ID] = $row[ENTRY_RECORD_ID];
                foreach ($fields_map as $field => $label) {
                    if ($field === USER_INPUT_HISTORY_LOG_COL) {
                        continue;
                    }
                    if (!array_key_exists($field, $row)) {
                        continue;
                    }
                    $mappedRow[$label] = $this->map_list_cell_value(
                        $field,
                        $label,
                        $row[$field],
                        $row,
                        $values_map
                    );
                }
                $mappedRows[] = $mappedRow;
            }
            $this->list_rows = $mappedRows;
        } else {
            // No fields_map: show all columns; still apply values_map by field name.
            $mappedRows = array();
            foreach ($raw_rows as $row) {
                $mappedRow = array();
                foreach ($row as $field => $raw) {
                    if ($field === ENTRY_RECORD_ID) {
                        $mappedRow[ENTRY_RECORD_ID] = $raw;
                        continue;
                    }
                    if ($field === USER_INPUT_HISTORY_LOG_COL) {
                        continue;
                    }
                    $mappedRow[$field] = $this->map_list_cell_value(
                        $field,
                        $field,
                        $raw,
                        $row,
                        $values_map
                    );
                }
                $mappedRows[] = $mappedRow;
            }
            $this->list_rows = $mappedRows;
        }
        return $this->list_rows;
    }

    /**
     * Columns to request from get_data.
     * With fields_map: visible keys plus any {{field}} refs in values_map
     * and any when/set fields from quick_actions.
     * Without fields_map: empty string = all columns.
     *
     * @param array $fields_map
     * @param array $values_map
     * @return string
     */
    private function build_list_selected_columns($fields_map, $values_map)
    {
        if (!is_array($fields_map) || count($fields_map) === 0) {
            return '';
        }
        $cols = array();
        foreach (array_keys($fields_map) as $field) {
            $field = trim((string)$field);
            if ($field !== '') {
                $cols[$field] = true;
            }
        }
        if (is_array($values_map)) {
            foreach ($values_map as $field => $template) {
                $field = trim((string)$field);
                if ($field !== '') {
                    $cols[$field] = true;
                }
                if (!is_string($template) || $template === '') {
                    continue;
                }
                if (preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $template, $matches)) {
                    foreach ($matches[1] as $name) {
                        if ($name === 'value' || $name === 'ASSET_PATH') {
                            continue;
                        }
                        $cols[$name] = true;
                    }
                }
            }
        }
        foreach ($this->get_quick_action_data_fields() as $field) {
            $cols[$field] = true;
        }
        return implode(',', array_keys($cols));
    }

    /**
     * Field names referenced by quick_actions when/set (for list column selection).
     *
     * @return array
     */
    private function get_quick_action_data_fields()
    {
        $fields = array();
        foreach ($this->get_parsed_quick_actions() as $action) {
            if (!empty($action['when']) && is_array($action['when'])) {
                foreach (array_keys($action['when']) as $key) {
                    $key = trim((string)$key);
                    if ($key !== '') {
                        $fields[$key] = true;
                    }
                }
            }
            if (!empty($action['set']) && is_array($action['set'])) {
                foreach (array_keys($action['set']) as $key) {
                    $key = trim((string)$key);
                    if ($key !== '') {
                        $fields[$key] = true;
                    }
                }
            }
        }
        return array_keys($fields);
    }

    /**
     * Validated quick actions (invalid entries omitted; issues in debug).
     * Requires non-empty scalar when/set maps; unknown fields skip the action.
     *
     * @return array
     */
    public function get_parsed_quick_actions()
    {
        if ($this->parsed_quick_actions !== null) {
            return $this->parsed_quick_actions;
        }
        $raw = $this->get_db_field('quick_actions', array());
        if (!is_array($raw)) {
            $raw = array();
        }
        $seen = array();
        $actions = array();
        foreach ($raw as $item) {
            if (!is_array($item)) {
                $this->add_quick_action_issue('invalid_action_entry', 'Non-object entry in quick_actions');
                continue;
            }
            $id = isset($item['id']) ? trim((string)$item['id']) : '';
            if ($id === '') {
                $this->add_quick_action_issue('missing_id', 'quick_actions entry missing id');
                continue;
            }
            if (isset(self::QUICK_ACTION_RESERVED[$id])) {
                $this->add_quick_action_issue('reserved_id', 'Reserved id: ' . $id);
                continue;
            }
            if (isset($seen[$id])) {
                $this->add_quick_action_issue('duplicate_id', 'Duplicate id: ' . $id);
                continue;
            }
            if (!isset($item['when']) || !is_array($item['when']) || count($item['when']) === 0) {
                $this->add_quick_action_issue('empty_when', 'Missing or empty when for id: ' . $id);
                continue;
            }
            $set_raw = isset($item['set']) && is_array($item['set']) ? $item['set'] : array();
            if (count($set_raw) === 0) {
                $this->add_quick_action_issue('empty_set', 'Empty set for id: ' . $id);
                continue;
            }
            $when = $this->sanitize_quick_action_value_map($item['when'], $id, 'when');
            if ($when === null) {
                continue;
            }
            $set = $this->sanitize_quick_action_value_map($set_raw, $id, 'set');
            if ($set === null) {
                continue;
            }
            $unknown = $this->find_unknown_quick_action_fields($when, $set);
            if (count($unknown) > 0) {
                $this->add_quick_action_issue(
                    'unknown_field',
                    'Unknown data fields for id ' . $id . ': ' . implode(', ', $unknown)
                );
                continue;
            }
            $seen[$id] = true;
            $actions[] = array(
                'id' => $id,
                'when' => $when,
                'set' => $set,
                'confirm' => !empty($item['confirm']),
                'css' => isset($item['css']) ? trim((string)$item['css']) : '',
            );
        }
        $this->parsed_quick_actions = $actions;
        return $this->parsed_quick_actions;
    }

    /**
     * Coerce when/set values to strings; reject arrays/objects.
     *
     * @param array $map
     * @param string $id
     * @param string $which when|set
     * @return array|null
     */
    private function sanitize_quick_action_value_map($map, $id, $which)
    {
        $clean = array();
        foreach ($map as $key => $value) {
            $key = trim((string)$key);
            if ($key === '') {
                $this->add_quick_action_issue(
                    'empty_' . $which . '_key',
                    'Empty ' . $which . ' key for id: ' . $id
                );
                return null;
            }
            if (is_array($value) || is_object($value)) {
                $this->add_quick_action_issue(
                    'non_scalar_' . $which,
                    'Non-scalar ' . $which . ' value for id ' . $id . ', field: ' . $key
                );
                return null;
            }
            if (is_bool($value)) {
                $clean[$key] = $value ? '1' : '0';
            } elseif ($value === null) {
                $clean[$key] = '';
            } else {
                $clean[$key] = (string)$value;
            }
        }
        return $clean;
    }

    /**
     * Form field names (children) plus existing dataCols for this section table.
     *
     * @return array name => true
     */
    private function get_known_data_field_names()
    {
        if ($this->known_data_fields !== null) {
            return $this->known_data_fields;
        }
        $known = array();
        $this->collect_form_field_names_from_children($this->get_children(), $known);

        $table_name = sprintf('%010d', $this->section_id);
        $form_id = $this->user_input->get_dataTable_id($table_name);
        if ($form_id) {
            $rows = $this->db->query_db(
                'SELECT `name` FROM dataCols WHERE id_dataTables = :tid',
                array(':tid' => $form_id)
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $name = isset($row['name']) ? trim((string)$row['name']) : '';
                    if ($name === '' || $name === USER_INPUT_HISTORY_LOG_COL) {
                        continue;
                    }
                    $known[$name] = true;
                }
            }
        }
        $this->known_data_fields = $known;
        return $this->known_data_fields;
    }

    /**
     * @param array $children
     * @param array $known
     */
    private function collect_form_field_names_from_children($children, &$known)
    {
        if (!is_array($children)) {
            return;
        }
        foreach ($children as $child) {
            if (!is_object($child) || !method_exists($child, 'get_style_instance')) {
                continue;
            }
            $style = $child->get_style_instance();
            if (is_a($style, 'FormFieldComponent') && method_exists($style, 'get_view')) {
                $view = $style->get_view();
                if ($view && method_exists($view, 'get_name_base')) {
                    $name = trim((string)$view->get_name_base());
                    if ($name !== '' && $name !== '__form_name') {
                        $known[$name] = true;
                    }
                }
            }
            if (method_exists($child, 'get_children')) {
                $this->collect_form_field_names_from_children($child->get_children(), $known);
            }
        }
    }

    /**
     * Keys in when/set that are not known form/dataCol fields.
     *
     * @param array $when
     * @param array $set
     * @return array
     */
    private function find_unknown_quick_action_fields($when, $set)
    {
        $known = $this->get_known_data_field_names();
        $unknown = array();
        foreach (array($when, $set) as $map) {
            if (!is_array($map)) {
                continue;
            }
            foreach (array_keys($map) as $key) {
                $key = trim((string)$key);
                if ($key === '' || !isset($known[$key])) {
                    $unknown[$key === '' ? '(empty)' : $key] = true;
                }
            }
        }
        return array_keys($unknown);
    }

    /**
     * @return array id => label string
     */
    public function get_quick_actions_labels()
    {
        $labels = $this->get_db_field('quick_actions_labels', array());
        return is_array($labels) ? $labels : array();
    }

    /**
     * Resolved action order keys (edit / delete / quick ids).
     * Empty order → edit, all quick actions, delete.
     * Non-empty order → listed keys only (de-duplicated); omitting edit/delete
     * hides them. Unlisted quick action ids are appended at the end.
     *
     * @return array
     */
    public function get_resolved_actions_order()
    {
        if ($this->resolved_actions_order !== null) {
            return $this->resolved_actions_order;
        }
        $order = $this->get_db_field('actions_order', array());
        if (!is_array($order)) {
            $order = array();
        }
        $quick_ids = array();
        foreach ($this->get_parsed_quick_actions() as $action) {
            $quick_ids[$action['id']] = true;
        }
        if (count($order) === 0) {
            $resolved = array('edit');
            foreach (array_keys($quick_ids) as $id) {
                $resolved[] = $id;
            }
            $resolved[] = 'delete';
            $this->resolved_actions_order = $resolved;
            return $this->resolved_actions_order;
        }
        $resolved = array();
        $seen = array();
        $used_quick = array();
        foreach ($order as $key) {
            $key = trim((string)$key);
            if ($key === '') {
                continue;
            }
            if (isset($seen[$key])) {
                $this->add_quick_action_issue('order_duplicate_key', 'Duplicate actions_order key: ' . $key);
                continue;
            }
            if ($key === 'edit' || $key === 'delete') {
                $resolved[] = $key;
                $seen[$key] = true;
                continue;
            }
            if (!isset($quick_ids[$key])) {
                $this->add_quick_action_issue('order_unknown_key', 'Unknown actions_order key: ' . $key);
                continue;
            }
            $resolved[] = $key;
            $seen[$key] = true;
            $used_quick[$key] = true;
        }
        // Append quick actions not listed in order (edit/delete stay omitted if unlisted).
        foreach (array_keys($quick_ids) as $id) {
            if (!isset($used_quick[$id])) {
                $resolved[] = $id;
            }
        }
        $this->resolved_actions_order = $resolved;
        return $this->resolved_actions_order;
    }

    /**
     * Whether a quick action's when clause matches a raw list/entry row.
     * Empty when → no match (fail closed; parser also rejects empty when).
     * Missing when-keys on the row → no match (action hidden).
     *
     * @param array $action
     * @param array $row
     * @return bool
     */
    public function quick_action_matches_row($action, $row)
    {
        if (!is_array($action) || !is_array($row)) {
            return false;
        }
        $when = isset($action['when']) && is_array($action['when']) ? $action['when'] : array();
        if (count($when) === 0) {
            return false;
        }
        foreach ($when as $field => $expected) {
            $field = trim((string)$field);
            if ($field === '' || !array_key_exists($field, $row)) {
                return false;
            }
            if ((string)$row[$field] !== (string)$expected) {
                return false;
            }
        }
        return true;
    }

    /**
     * Look up a validated action by id.
     *
     * @param string $id
     * @return array|null
     */
    public function get_quick_action_by_id($id)
    {
        $id = trim((string)$id);
        foreach ($this->get_parsed_quick_actions() as $action) {
            if ($action['id'] === $id) {
                return $action;
            }
        }
        return null;
    }

    /**
     * Build record_id => row index once after list fetch.
     */
    private function ensure_list_raw_index()
    {
        if ($this->list_raw_rows_by_id !== null) {
            return;
        }
        if ($this->list_raw_rows === null) {
            $this->get_list_rows();
        }
        $this->list_raw_rows_by_id = array();
        if (!is_array($this->list_raw_rows)) {
            return;
        }
        foreach ($this->list_raw_rows as $row) {
            if (isset($row[ENTRY_RECORD_ID])) {
                $this->list_raw_rows_by_id[intval($row[ENTRY_RECORD_ID])] = $row;
            }
        }
    }

    /**
     * Raw list row (field names) for a record id, after get_list_rows().
     *
     * @param int $record_id
     * @return array
     */
    public function get_list_raw_row($record_id)
    {
        $this->ensure_list_raw_index();
        $record_id = intval($record_id);
        return isset($this->list_raw_rows_by_id[$record_id])
            ? $this->list_raw_rows_by_id[$record_id]
            : array();
    }

    /**
     * Merge current row + CMS set for a quick-action write.
     * Full field map feeds formAction placeholders; id_users is preserved so
     * ownership does not flip to the clicking admin. System get_data keys are
     * stripped so they are not written as dataCols.
     *
     * @param array $current
     * @param array $set
     * @return array
     */
    public function build_quick_action_write_payload($current, $set)
    {
        $merged = array();
        if (is_array($current)) {
            foreach ($current as $key => $value) {
                if (isset(self::QUICK_ACTION_WRITE_EXCLUDE[$key])) {
                    continue;
                }
                if ($key === ENTRY_RECORD_ID || $key === USER_INPUT_HISTORY_LOG_COL) {
                    continue;
                }
                if (is_array($value) || is_object($value)) {
                    continue;
                }
                if ($value === null) {
                    $merged[$key] = '';
                } elseif (is_bool($value)) {
                    $merged[$key] = $value ? '1' : '0';
                } else {
                    $merged[$key] = (string)$value;
                }
            }
        }
        if (is_array($set)) {
            foreach ($set as $key => $value) {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    /**
     * Shared confirm UI strings from quick_actions_labels meta keys.
     *
     * @return array{ok:string,cancel:string,default_message:string}
     */
    public function get_quick_action_confirm_ui()
    {
        $labels = $this->get_quick_actions_labels();
        return array(
            'ok' => isset($labels['_ok']) && (string)$labels['_ok'] !== ''
                ? (string)$labels['_ok']
                : 'OK',
            'cancel' => isset($labels['_cancel']) && (string)$labels['_cancel'] !== ''
                ? (string)$labels['_cancel']
                : 'Cancel',
            'default_message' => isset($labels['_default_confirm'])
                && (string)$labels['_default_confirm'] !== ''
                ? (string)$labels['_default_confirm']
                : 'Do you want to continue?',
        );
    }

    /**
     * Button label / confirm strings for an action id.
     * Missing label falls back to a generic "Action" (never the raw id).
     *
     * @param string $id
     * @return array{label:string,confirm_title:string,confirm_message:string}
     */
    public function get_quick_action_label_bundle($id)
    {
        $labels = $this->get_quick_actions_labels();
        $ui = $this->get_quick_action_confirm_ui();
        $id = trim((string)$id);
        if (isset($labels[$id]) && (string)$labels[$id] !== '') {
            $label = (string)$labels[$id];
        } else {
            $label = 'Action';
            $this->add_quick_action_issue('missing_label', 'Missing label for id: ' . $id);
        }
        $confirm_title = isset($labels[$id . '_confirm_title'])
            ? (string)$labels[$id . '_confirm_title']
            : $label;
        $confirm_message = isset($labels[$id . '_confirm'])
            ? (string)$labels[$id . '_confirm']
            : $ui['default_message'];
        $action = $this->get_quick_action_by_id($id);
        if ($action && !empty($action['confirm']) && !isset($labels[$id . '_confirm'])) {
            $this->add_quick_action_issue('missing_confirm', 'Missing confirm message for id: ' . $id);
        }
        return array(
            'label' => $label,
            'confirm_title' => $confirm_title,
            'confirm_message' => $confirm_message,
        );
    }

    /**
     * @return bool
     */
    public function has_quick_actions()
    {
        return count($this->get_parsed_quick_actions()) > 0;
    }

    /**
     * @param string $code
     * @param string $message
     */
    private function add_quick_action_issue($code, $message)
    {
        foreach ($this->quick_action_issues as $issue) {
            if ($issue['code'] === $code && $issue['message'] === $message) {
                return;
            }
        }
        $this->quick_action_issues[] = array(
            'code' => $code,
            'message' => $message,
        );
    }

    /**
     * @return array
     */
    public function get_quick_action_issues()
    {
        // Force parse so issues from config are collected.
        $this->get_parsed_quick_actions();
        $this->get_resolved_actions_order();
        foreach ($this->get_parsed_quick_actions() as $action) {
            $this->get_quick_action_label_bundle($action['id']);
        }
        return $this->quick_action_issues;
    }

    /**
     * Resolve a list cell: apply values_map template when present, else raw value.
     * Marks $header_key in list_html_headers when a template is used.
     *
     * @param string $field Field name (values_map key)
     * @param string $header_key Column header key (label or field name)
     * @param mixed $raw
     * @param array $row
     * @param array $values_map
     * @return mixed
     */
    private function map_list_cell_value($field, $header_key, $raw, $row, $values_map)
    {
        if (
            isset($values_map[$field])
            && is_string($values_map[$field])
            && $values_map[$field] !== ''
        ) {
            $this->list_html_headers[$header_key] = true;
            return $this->apply_list_value_template($values_map[$field], $raw, $row);
        }
        return $raw;
    }

    /**
     * Whether a list column header was rendered via values_map (HTML-safe output).
     *
     * @param string $header
     * @return bool
     */
    public function is_list_header_html($header)
    {
        // Ensure headers flags are built (get_list_rows may not have run yet).
        if ($this->list_rows === null) {
            $this->get_list_rows();
        }
        return !empty($this->list_html_headers[$header]);
    }

    /**
     * Apply a values_map template. CMS template is trusted; row values are escaped.
     *
     * Placeholders: {{value}}, {{ASSET_PATH}}, {{field_name}} from the same row.
     *
     * @param string $template
     * @param mixed $value
     * @param array $row
     * @return string
     */
    private function apply_list_value_template($template, $value, $row)
    {
        $asset_path = defined('ASSET_PATH') ? ASSET_PATH : '';
        $replacements = array(
            '{{ASSET_PATH}}' => $asset_path,
            '{{value}}' => $this->escape_list_template_value($value, true),
        );
        if (is_array($row)) {
            foreach ($row as $field => $field_value) {
                if ($field === ENTRY_RECORD_ID || !is_scalar($field_value)) {
                    continue;
                }
                $replacements['{{' . $field . '}}'] = $this->escape_list_template_value($field_value, false);
            }
        }
        return strtr($template, $replacements);
    }

    /**
     * Escape a row value for use inside a values_map template.
     * When $reject_unsafe_path is true ({{value}}), reject .. / URL schemes.
     *
     * @param mixed $value
     * @param bool $reject_unsafe_path
     * @return string
     */
    private function escape_list_template_value($value, $reject_unsafe_path = false)
    {
        if ($value === null) {
            return '';
        }
        $str = (string)$value;
        if ($str === '') {
            return '';
        }
        if ($reject_unsafe_path) {
            if (
                strpos($str, '..') !== false
                || preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*:#', $str)
                || strpos($str, '//') === 0
            ) {
                return '';
            }
        }
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
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
     * Field name behind a list header (the fields_map key). Rendered as
     * data-field so custom CSS can target a column without depending on its
     * translated label. Without fields_map the header already is the field.
     *
     * @param string $header
     * @return string
     */
    public function get_list_header_field($header)
    {
        if ($this->list_header_fields === null) {
            $this->list_header_fields = array();
            $fields_map = $this->get_db_field('fields_map', array());
            if (is_array($fields_map)) {
                foreach ($fields_map as $field => $label) {
                    if (is_scalar($label) && !isset($this->list_header_fields[(string)$label])) {
                        $this->list_header_fields[(string)$label] = (string)$field;
                    }
                }
            }
        }
        return isset($this->list_header_fields[$header])
            ? $this->list_header_fields[$header]
            : (string)$header;
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
        return $path . ($qs !== '' ? '?' . $qs : '') . $this->get_section_hash();
    }

    /**
     * Deep-link for create/edit. Always includes sid=<this section id>
     * and #section-<id> so the browser lands on this style.
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
        return $path . '?' . http_build_query($query) . $this->get_section_hash();
    }

    /**
     * Fragment pointing at this style's root element (`id="section-{id}"`).
     *
     * @return string
     */
    private function get_section_hash()
    {
        $id = intval($this->section_id);
        return $id > 0 ? '#section-' . $id : '';
    }

    /**
     * Extend style debug payload with list raw/mapped rows and map config.
     * Consumed by StyleView::output_debug + show_debug.js (console.log).
     *
     * @return array
     */
    public function get_debug_data()
    {
        $data = parent::get_debug_data();
        if (!is_array($data)) {
            $data = array();
        }
        // Ensure list is built so raw/mapped rows are available.
        $mapped = $this->get_list_rows();
        $data['list_layout'] = $this->get_db_field('list_layout', formUserInputLogListLayouts_table);
        $data['actions_position'] = $this->get_db_field(
            'actions_position',
            formUserInputLogListActionsPositions_right
        );
        $data['fields_map'] = $this->get_db_field('fields_map', array());
        $data['values_map'] = $this->get_db_field('values_map', array());
        $data['quick_actions'] = $this->get_parsed_quick_actions();
        $data['quick_actions_labels'] = $this->get_quick_actions_labels();
        $data['actions_order'] = $this->get_resolved_actions_order();
        $data['quick_action_issues'] = $this->get_quick_action_issues();
        $data['list_headers'] = $this->get_list_headers();
        $data['list_raw_rows'] = $this->list_raw_rows !== null ? $this->list_raw_rows : array();
        $data['list_rows'] = $mapped;
        return $data;
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
