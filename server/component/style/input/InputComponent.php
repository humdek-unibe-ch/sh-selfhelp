<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formField/FormFieldComponent.php";
require_once __DIR__ . "/../formField/FormFieldModel.php";
require_once __DIR__ . "/InputView.php";

/**
 * A component class for the input style component.
 * This component renders an input form field.
 */
class InputComponent extends FormFieldComponent
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
        $model = new FormFieldModel($services, $id);
        $view = new InputView($model, $id);
        parent::__construct($model, $view);
    }
}
?>
