<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/DataDeleteView.php";
require_once __DIR__ . "/DataDeleteModel.php";
require_once __DIR__ . "/DataDeleteModel.php";

/**
 * The class to define the asset select component.
 */
class DataDeleteComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param object $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param int $id
     *  The id of the section with the conditional container style.
     * 
     */
    public function __construct($services, $params, $id)
    {
        $id_dataTables = isset($params['id_dataTables']) ? $params['id_dataTables'] : null;
        $model = new DataDeleteModel($services, $id, $params, $id_dataTables);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new DataDeleteController($model);
        $view = new DataDeleteView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
