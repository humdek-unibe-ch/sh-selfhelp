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
 * Reuses FormUserInputController::execute (writes already authorize from POST
 * ids only). Overrides after_successful_write to return to the list when
 * redirect_at_end is empty.
 */
class FormUserInputLogListController extends FormUserInputController
{
    public function __construct($model)
    {
        parent::__construct($model, false);
        $this->execute();
    }

    /**
     * After non-AJAX save/delete with no redirect_at_end, return to the list URL
     * (modal and page modes). AJAX success is handled in formUserInputLogList.js.
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
}
?>
