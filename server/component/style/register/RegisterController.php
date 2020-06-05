<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the register component.
 */
class RegisterController extends BaseController
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
        if(isset($_POST['email']) && isset($_POST['code']))
        {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $code = filter_var($_POST['code'], FILTER_SANITIZE_STRING);
            if($email !== false && $code !== false
                && $model->register_user($email, $code))
                $this->success = true;
            else
                $this->fail = true;
        } else if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) !== false && $model->get_db_field("open_registration", false)) {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            if($model->register_user_without_code($email)){
                $this->success = true;
            }else{
                $this->fail = true;
            }
        }
    }

    /* Public Methods *********************************************************/
}
?>
