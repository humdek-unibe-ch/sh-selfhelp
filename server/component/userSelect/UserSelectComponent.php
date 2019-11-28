<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../user/UserComponent.php";
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/UserSelectView.php";

/**
 * The select user component.
 */
class UserSelectComponent extends UserComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the UserModel class and the
     * UserSelectView class and passes instances to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'uid':     The id of the user to be deleted.
     */
    public function __construct($services, $params)
    {
        $uid = null;
        if(isset($params['uid'])) $uid = intval($params['uid']);
        $model = new UserModel($services, $uid);
        $view = new UserSelectView($model);
        parent::__construct($model, $view);
    }
}
?>
