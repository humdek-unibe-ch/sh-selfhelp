<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ShowUserInputView.php";
require_once __DIR__ . "/ShowUserInputModel.php";
require_once __DIR__ . "/ShowUserInputController.php";

/**
 * A component class for a showUserInput style component. This style is
 * intended to display user data that was stored in the database via one of the
 * form style components.
 */
class ShowUserInputComponent extends BaseComponent
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
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new ShowUserInputModel($services, $id, $params, $id_page, $entry_record);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new ShowUserInputController($model);
        $view = new ShowUserInputView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
