<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";

/**
 * The controller class of the email component.
 */
class DataController extends BaseController
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);     
        if(isset($_POST['users'])){
            $model->set_selected_users($_POST['users']);
        }
        if(isset($_POST['forms'])){
            $model->set_selected_forms($_POST['forms']);
        }
    }

    /* Public Methods *********************************************************/
}
?>
