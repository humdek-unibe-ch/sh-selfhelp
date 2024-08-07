<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ConditionalContainerView.php";
require_once __DIR__ . "/ConditionalContainerModel.php";

/**
 * The conditional container style component.
 */
class ConditionalContainerComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the ConditionalContainerModel
     * class and the ConditionalContainerView class and passes the view and
     * model instance to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new ConditionalContainerModel($services, $id, $params, $id_page, $entry_record);
        $view = new ConditionalContainerView($model);
        parent::__construct($model, $view);
    }
}
?>
