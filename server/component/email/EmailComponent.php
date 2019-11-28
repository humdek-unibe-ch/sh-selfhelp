<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/EmailModel.php";
require_once __DIR__ . "/EmailController.php";
require_once __DIR__ . "/EmailView.php";

/**
 * The email component which allows to edit emails.
 */
class EmailComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the EmailMode class, the
     * EmailView class, and the EmailController class and passes them to the
     * constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $args
     *  An array of GET arguments
     *  - 'id': The currently active email id (null if no id is selected)
     */
    public function __construct($services, $args=null)
    {
        $model = new EmailModel($services);
        $controller = new EmailController($model);
        $id = $args['id'] ?? null;
        $view = new EmailView($model, $controller, $id);
        parent::__construct($model, $view, $controller);
    }
}

