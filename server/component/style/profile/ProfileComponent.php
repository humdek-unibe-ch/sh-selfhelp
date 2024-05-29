<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ProfileView.php";
require_once __DIR__ . "/ProfileModel.php";
require_once __DIR__ . "/ProfileController.php";

/**
 * The user profile component.
 *
 * Similar to the login component, the profile component has a very basic model
 * but needs a custom controller to handle the input data.
 */
class ProfileComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the ProfileModel class, the
     * ProfileView class, and the ProfileController class and passes the view
     * instance to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the section associated to the profile page.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new ProfileModel($services, $id, $params, $id_page, $entry_record);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new ProfileController($model);
        $view = new ProfileView($model, $controller);
        parent::__construct($model, $view);
    }
}
?>
