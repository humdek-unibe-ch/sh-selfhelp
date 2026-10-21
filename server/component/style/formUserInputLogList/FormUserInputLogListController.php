<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formUserInput/FormUserInputController.php";

/**
 * Controller for formUserInputLogList.
 *
 * Reuses FormUserInputController::execute for all writes (authorize → update →
 * after_successful_write → redirect_at_end). Quick actions only prepare POST
 * and supply a merged CMS write payload via prepare_write_data (no client field
 * patches, no GUMP on form children, no entity-decode of stored values).
 * Quick-action writes use trigger `updated` so `finished` formActions do not
 * re-fire on toggles. Overrides after_successful_write to return to the list
 * when redirect_at_end is empty.
 */
class FormUserInputLogListController extends FormUserInputController
{
    /**
     * Validated quick-action write awaiting prepare_write_data (CMS set merged
     * onto the current row). Null for normal create/edit/delete posts.
     *
     * @var array|null
     */
    private $pending_quick_action_payload = null;

    public function __construct($model)
    {
        parent::__construct($model, false);
        $this->execute();
    }

    public function execute()
    {
        if (count($_POST) === 0) {
            parent::execute();
            return;
        }
        if (
            !isset($_POST['__form_name'])
            || $_POST['__form_name'] !== $this->model->get_db_field("name")
        ) {
            return;
        }
        if ($this->is_quick_action_post()) {
            if (!$this->prepare_quick_action_for_parent_execute()) {
                // Fail flags set (unknown action, stale when, etc.). Page/AJAX
                // response still renders the list so the UI can refresh.
                return;
            }
        }
        parent::execute();
    }

    /**
     * After non-AJAX save/delete/quick-action with no redirect_at_end, return
     * to the list URL (modal and page modes). AJAX success is handled in JS.
     * When redirect_at_end is set, parent::execute performs the Location header.
     */
    protected function after_successful_write()
    {
        if (isset($_POST['mobile']) && $_POST['mobile']) {
            return;
        }
        if (isset($_POST['ajax']) && (string)$_POST['ajax'] === '1') {
            return;
        }
        $redirect_at_end = $this->model->get_db_field("redirect_at_end", "");
        if ($redirect_at_end !== "") {
            return;
        }
        $list_url = $this->model->get_list_url();
        if ($list_url !== '') {
            header("Location: " . $list_url);
            die();
        }
    }

    /**
     * Quick actions: return the CMS-prepared payload once via the base write
     * hook (updated trigger, skip entity-decode of already-stored values).
     *
     * @param object $gump
     * @param int $selected_record_id
     * @param int $delete_record_id
     * @return array{fields:mixed,trigger_type:string,decode:bool}
     */
    protected function prepare_write_data($gump, $selected_record_id, $delete_record_id)
    {
        if ($this->pending_quick_action_payload !== null) {
            $fields = $this->pending_quick_action_payload;
            $this->pending_quick_action_payload = null;
            return array(
                'fields' => is_array($fields) ? $fields : array(),
                'trigger_type' => actionTriggerTypes_updated,
                'decode' => false,
            );
        }
        return parent::prepare_write_data($gump, $selected_record_id, $delete_record_id);
    }

    /**
     * @return bool
     */
    private function is_quick_action_post()
    {
        if (!isset($_POST[QUICK_ACTION_ID])) {
            return false;
        }
        $raw = $_POST[QUICK_ACTION_ID];
        if (is_array($raw) && isset($raw['value'])) {
            $raw = $raw['value'];
        }
        return trim((string)$raw) !== '';
    }

    /**
     * Validate quick action + when, then rewrite POST so parent::execute runs
     * the normal update path (authorize_write → prepare_write_data → update).
     * Client field values are discarded; write payload is CMS `set` merged onto
     * the current row (for formAction placeholders) with ownership preserved.
     *
     * @return bool true when parent::execute should run
     */
    private function prepare_quick_action_for_parent_execute()
    {
        // Early-fail alerts only; parent::execute sets alerts on the write path.
        $this->alert_fail = $this->model->get_db_field("alert_fail", "");

        $action_id = $_POST[QUICK_ACTION_ID];
        if (is_array($action_id) && isset($action_id['value'])) {
            $action_id = $action_id['value'];
        }
        $action_id = trim((string)$action_id);

        if (isset($_POST[SELECTED_RECORD_ID])) {
            $_POST[SELECTED_RECORD_ID] = $this->normalize_post_id($_POST[SELECTED_RECORD_ID]);
        }
        $record_id = isset($_POST[SELECTED_RECORD_ID]) ? intval($_POST[SELECTED_RECORD_ID]) : 0;

        if ($record_id <= 0 || $action_id === '') {
            $this->fail = true;
            $this->push_fail_alert("An unexpected problem occurred. Please Contact the Server Administrator");
            return false;
        }

        $action = $this->model->get_quick_action_by_id($action_id);
        if (!$action) {
            $this->fail = true;
            $this->push_fail_alert("An unexpected problem occurred. Please Contact the Server Administrator");
            return false;
        }

        $own = $this->model->get_db_field("own_entries_only", 1);
        $current = $this->model->get_form_entry_record($record_id, $own);
        if (!is_array($current) || count($current) === 0) {
            $this->fail = true;
            $this->push_fail_alert("An unexpected problem occurred. Please Contact the Server Administrator");
            return false;
        }
        // Stale UI: when no longer matches — fail so AJAX soft-refreshes the list.
        if (!$this->model->quick_action_matches_row($action, $current)) {
            $this->fail = true;
            $this->push_fail_alert(
                "This action is no longer available for this entry. The list was refreshed."
            );
            return false;
        }

        $this->pending_quick_action_payload = $this->model->build_quick_action_write_payload(
            $current,
            $action['set']
        );

        // Drop client payload; parent sees a normal selected_record update only.
        $form_name = $_POST['__form_name'];
        $ajax = isset($_POST['ajax']) ? $_POST['ajax'] : null;
        $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : null;
        $_POST = array(
            '__form_name' => $form_name,
            SELECTED_RECORD_ID => $record_id,
        );
        if ($ajax !== null) {
            $_POST['ajax'] = $ajax;
        }
        if ($mobile !== null) {
            $_POST['mobile'] = $mobile;
        }

        return true;
    }
}
?>
