<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";

/**
 * The controller class of showUserInput style component.
 */
class ShowUserInputController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(count($_POST) === 0) return;
        if(!isset($_POST['user_input_remove_id']))
            return;

        $ids = explode(',', $_POST['user_input_remove_id']);        
        $this->model->mark_user_input_as_removed($ids, $_POST[ENTRY_RECORD_ID]);
        unset($_POST['user_input_remove_id']);
        unset($_POST[ENTRY_RECORD_ID]);
    }
}
?>
