<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/SearchView.php";
require_once __DIR__ . "/SearchController.php";
require_once __DIR__ . "/../StyleModel.php";

/**
 * The class to define the asset select component.
 */
class SearchComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section .
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new StyleModel($services, $id, $params, $id_page, $entry_record);
        $controller = new SearchController($model);
        $view = new SearchView($model, $controller);
        parent::__construct($model, $view);
    }
}
?>
