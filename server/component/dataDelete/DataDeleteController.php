<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the style component.
 */
class DataDeleteController extends BaseController
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
        if (isset($_POST['DELETE_COLUMNS']) && count($_POST) > 1) {
            unset($_POST['DELETE_COLUMNS']);
            $res = $this->model->delete_columns($_POST);
            if ($res['result']) {
                $_SESSION[CONTROLLER_SUCCESS] = true;
                $_SESSION[CONTROLLER_SUCCESS_MSGS][] = $res['message'];
            } else {
                $_SESSION[CONTROLLER_FAIL] = false;
                $_SESSION[CONTROLLER_ERROR_MSGS][] = $res['message'];
            }
            // Unset the $_POST array
            $_POST = array();

            // Redirect to the same page to clear POST data
            header("Location: " . $_SERVER['REDIRECT_URL']);
            exit;
        }
    }

    /* Private Methods ********************************************************/
}
?>
