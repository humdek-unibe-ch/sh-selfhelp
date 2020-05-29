<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/ModuleMailComposeEmailView.php";
require_once __DIR__ . "/../moduleMail/ModuleMailModel.php";
require_once __DIR__ . "/../moduleMail/ModuleMailController.php";

/**
 * The class to define the asset select component.
 */
class ModuleMailComposeEmailComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new ModuleMailModel($services, null);
        $controller = new ModuleMailController($model);
        $view = new ModuleMailComposeEmailView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
