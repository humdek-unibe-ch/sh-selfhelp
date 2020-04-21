<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../chatAdmin/ChatAdminComponent.php";
require_once __DIR__ . "/../chatAdmin/ChatAdminModel.php";
require_once __DIR__ . "/ChatAdminInsertView.php";
require_once __DIR__ . "/ChatAdminInsertController.php";

/**
 * The chat admin insert component.
 */
class ChatAdminInsertComponent extends ChatAdminComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new ChatAdminModel($services);
        $controller = new ChatAdminInsertController($model);
        $view = new ChatAdminInsertView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
