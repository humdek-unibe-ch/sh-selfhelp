<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/EntryRecordDeleteView.php";
require_once __DIR__ . "/EntryRecordDeleteModel.php";
require_once __DIR__ . "/EntryRecordDeleteModel.php";

/**
 * The class to define the asset select component.
 */
class EntryRecordDeleteComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param object $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section with the conditional container style.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new EntryRecordDeleteModel($services, $id, $params, $id_page, $entry_record);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new EntryRecordDeleteController($model);
        $view = new EntryRecordDeleteView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
