<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/LoopView.php";
require_once __DIR__ . "/LoopModel.php";

/**
 * A component calss for a nested list.
 */
class LoopComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor merely propagates the mode and view instance to the base
     * component.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $view
     *  The view instance of the component.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new LoopModel($services, $id, $params, $id_page, $entry_record);
        $view = new LoopView($model);
        parent::__construct($model, $view);
    }

}
?>
