<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";

/**
 * A component class for a filter style component. This class serves as a base
 * component class for all filters
 */
class FilterComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $view
     *  The view instance of the component.
     * @param int $id_page
     *  The id of the parent page
     */
    public function __construct($model, $view, $id_page)
    {
        parent::__construct($model, $view);
        $this->set_request_access($id_page, "AjaxDataSource", "set_data_filter");
    }
}
?>

