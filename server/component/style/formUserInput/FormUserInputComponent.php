<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/FormUserInputView.php";
require_once __DIR__ . "/FormUserInputModel.php";
require_once __DIR__ . "/FormUserInputController.php";

/**
 * A component class for a formUserInput style component. This style is intended
 * to handle user input.
 *
 * - Persisten user input is stored to the database and made availabile to the
 * user for continuous modification.
 * - Journal user input is stored to the database together with a timestamp and
 * cannot be changed afterwards.
 */
class FormUserInputComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this navigation component.
     */
    public function __construct($services, $id)
    {
        $model = new FormUserInputModel($services, $id);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new FormUserInputController($model);
        $view = new FormUserInputView($model, $controller);

        parent::__construct($model, $view, $controller);
    }
}
?>
