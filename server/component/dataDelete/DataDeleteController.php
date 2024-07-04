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
                $_SESSION[CONTROLLER_FAIL] = true;
                $_SESSION[CONTROLLER_ERROR_MSGS][] = $res['message'];
            }
            $this->redirect_after_post_execute();
        } else if (isset($_POST['DELETE_DATATABLE'])) {
            if ($_POST['display_name'] == $_POST['display_name_confirmation']) {
                $res = $this->model->delete_dataTable();
                if ($res['result']) {
                    $_SESSION[CONTROLLER_SUCCESS] = true;
                    $_SESSION[CONTROLLER_SUCCESS_MSGS][] = $res['message'];
                    $_POST = array();
                    // Redirect to the same page to clear POST data
                    header("Location: " . $this->model->get_link_url("data"));
                    exit;
                } else {
                    $_SESSION[CONTROLLER_FAIL] = true;
                    $_SESSION[CONTROLLER_ERROR_MSGS][] = $res['message'];
                    $this->redirect_after_post_execute();
                }
            } else {
                $_SESSION[CONTROLLER_FAIL] = true;
                $_SESSION[CONTROLLER_ERROR_MSGS][] = 'The entered display dataTable name does not match!';
                $this->redirect_after_post_execute();
            }
        }
    }

    /* Private Methods ********************************************************/

    private function redirect_after_post_execute()
    {
        // Unset the $_POST array
        $_POST = array();

        // Redirect to the same page to clear POST data
        header("Location: " . $_SERVER['REDIRECT_URL']);
        exit;
    }
}
?>
