<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the group insert component.
 */
class ModuleMailController extends BaseController
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
        if(isset($_POST['dateFrom']) && isset($_POST['dateTo']) && isset($_POST['dateType']))
        {
            $this->model->set_date_from($_POST['dateFrom']);
            $this->model->set_date_to($_POST['dateTo']);
            $this->model->set_date_type($_POST['dateType']);
        }else{
            $this->model->set_date_from(date('d-m-Y'));
            $this->model->set_date_to(date('d-m-Y'));
            $this->model->set_date_type('date_create');
        }
    }

    /* Public Methods *********************************************************/

}
?>
