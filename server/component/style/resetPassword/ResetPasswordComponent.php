<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ResetPasswordView.php";
require_once __DIR__ . "/ResetPasswordModel.php";
require_once __DIR__ . "/ResetPasswordController.php";

/**
 * This is a style component that renders the password reste form, handles the
 * form input and sends a new activation link to reset the password.
 */
class ResetPasswordComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the ResetPasswordModel class, the
     * ResetPasswordView class, and the ResetPasswordController class amd
     * passes the instances to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section associated to the profile page.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new ResetPasswordModel($services, $id, $params, $id_page, $entry_record);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new ResetPasswordController($model);
        $view = new ResetPasswordView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
