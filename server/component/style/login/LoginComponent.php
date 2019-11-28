<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/LoginView.php";
require_once __DIR__ . "/LoginModel.php";
require_once __DIR__ . "/LoginController.php";

/**
 * The login component.
 *
 * It has a very simple model where page fields are fetched from the database
 * (no sections). What makes this component special is the controller and,
 * consequently, the view that is depending on the controller.
 */
class LoginComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the LoginModel class and the
     * LoginView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section associated to the profile page.
     */
    public function __construct($services, $id)
    {
        $model = new LoginModel($services, $id);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new LoginController($model);
        $view = new LoginView($model, $controller);
        parent::__construct($model, $view);
    }
}
?>
