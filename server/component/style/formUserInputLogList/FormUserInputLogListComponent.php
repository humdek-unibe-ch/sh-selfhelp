<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/FormUserInputLogListView.php";
require_once __DIR__ . "/FormUserInputLogListModel.php";
require_once __DIR__ . "/FormUserInputLogListController.php";

/**
 * formUserInputLogList: log form with built-in list, create/edit/delete.
 */
class FormUserInputLogListComponent extends BaseComponent
{
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new FormUserInputLogListModel($services, $id, $params, $id_page, $entry_record);
        $controller = null;
        if (!$model->is_cms_page()) {
            $controller = new FormUserInputLogListController($model);
        }
        $view = new FormUserInputLogListView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
