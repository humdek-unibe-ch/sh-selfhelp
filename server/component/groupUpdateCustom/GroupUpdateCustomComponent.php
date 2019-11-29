<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../group/GroupComponent.php";
require_once __DIR__ . "/../group/GroupModel.php";
require_once __DIR__ . "/../group/GroupView.php";
require_once __DIR__ . "/GroupUpdateCustomController.php";

/**
 * The group update component.
 */
class GroupUpdateCustomComponent extends GroupComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the GroupModel class, the
     * GroupUpdateView class, and the GroupUpdateController class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'gid':     The id of the selected group.
     */
    public function __construct($services, $params)
    {
        $gid = isset($params['gid']) ? intval($params['gid']) : null;
        $model = new GroupModel($services, $gid);
        $controller = new GroupUpdateCustomController($model);
        $mode = $controller->has_succeeded() ? "select" : "updateCustom";
        $view = new GroupView($model, $controller, $mode);
        parent::__construct($model, $view, $controller);
    }
}
?>
