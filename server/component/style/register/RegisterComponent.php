<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/RegisterView.php";
require_once __DIR__ . "/RegisterModel.php";
require_once __DIR__ . "/RegisterController.php";

/**
 * The register component.
 *
 * It has a very simple model where page fields are fetched from the database
 * (no sections). What makes this component special is the controller and,
 * consequently, the view that is depending on the controller.
 */
class RegisterComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model, Controller and View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of this registe component instance.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new RegisterModel($services, $id, $params, $id_page, $entry_record);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new RegisterController($model);
        $view = new RegisterView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
