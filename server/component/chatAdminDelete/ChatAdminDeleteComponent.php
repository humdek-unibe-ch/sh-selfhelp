<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../chatAdmin/ChatAdminModel.php";
require_once __DIR__ . "/../chatAdmin/ChatAdminComponent.php";
require_once __DIR__ . "/ChatAdminDeleteView.php";
require_once __DIR__ . "/ChatAdminDeleteController.php";

/**
 * The chat admin delete component.
 */
class ChatAdminDeleteComponent extends ChatAdminComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model, the View, and the
     * Controller and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'rid':     The id of the chat room to be deleted.
     */
    public function __construct($services, $params)
    {
        $model = new ChatAdminModel($services, intval($params['rid']));
        $controller = new ChatAdminDeleteController($model);
        $view = new ChatAdminDeleteView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
