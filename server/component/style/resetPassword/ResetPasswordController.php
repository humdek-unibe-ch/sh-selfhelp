<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../emailFormBase/EmailFormBaseController.php";
/**
 * The controller class of the register component.
 */
class ResetPasswordController extends EmailFormBaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. Submitted credentials are checked.
     *
     * @param object $model
     *  The model instance of the register component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if (isset($_POST['reset_anonymous_user']) && $_POST['reset_anonymous_user'] && isset($_POST['user_name'])) {
            $model->set_reset_user_name($_POST['user_name']);
            if(isset($_POST['reset_anonymous_user_sec_q']) && $_POST['reset_anonymous_user_sec_q']){
                $url = $this->check_security_questions($this->model);
                if ($url) {
                    // redirect directly to reset
                    header("Location: " . $url);
                    die();
                }
            }
        } 
    }

    /* Private Methods *********************************************************/

    /**
     * Check the security questions and if the answers are correct generate a reset link and return it.
     * @param object
     * The model of the controller
     * @return mixed
     * If true, return the reset link else return false
     */
    function check_security_questions($model)
    {
        $user_security_questions = $model->get_user_security_questions();
        if ($user_security_questions) {
            $user_security_questions = json_decode($user_security_questions['security_questions']);
        } else {
            return false;
        }
        foreach ($user_security_questions as $key => $value) {
            if (isset($_POST[$key])) {
                if ($_POST[$key] !=  $value) {
                    // the answer is wrong
                    return false;
                }
            } else {
                return false;
            }
        }
        return $model->get_reset_url_for_anonymous_user();
    }

    /* Public Methods *********************************************************/
}
?>
