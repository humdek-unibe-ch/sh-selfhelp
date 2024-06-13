<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the style component.
 */
class EntryRecordDeleteController extends BaseController
{
    /* Private Properties *****************************************************/

    
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
        if(!isset($_POST[DELETE_RECORD_ID])){
            return;
        }
        $this->model->delete_record($_POST[DELETE_RECORD_ID]);
        unset($_POST[DELETE_RECORD_ID]);
        $redirect_at_end = $this->model->get_db_field("redirect_at_end", "");
        if (!(isset($_POST['mobile']) && $_POST['mobile']) && $redirect_at_end != "") {
            $redirect_at_end = $this->model->get_services()->get_router()->get_url($redirect_at_end);
            header("Location: " . $redirect_at_end);
            die();
        }
    }

    /* Private Methods ********************************************************/

}
?>
