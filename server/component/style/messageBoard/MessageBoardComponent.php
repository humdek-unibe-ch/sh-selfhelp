<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/MessageBoardView.php";
require_once __DIR__ . "/MessageBoardModel.php";
require_once __DIR__ . "/MessageBoardController.php";

/**
 * The class to define the component of the style messageBoard.
 */
class MessageBoardComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class, the View
     * class and the Controller class and passes them to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of this component.
     */
    public function __construct($services, $id)
    {
        $model = new MessageBoardModel($services, $id);
        $controller = new MessageBoardController($model);
        $view = new MessageBoardView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
