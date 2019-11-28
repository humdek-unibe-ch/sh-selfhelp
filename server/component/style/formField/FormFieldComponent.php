<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/FormFieldView.php";
require_once __DIR__ . "/FormFieldModel.php";

/**
 * A component calss for a nested list.
 */
abstract class FormFieldComponent extends BaseComponent
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
    public function __construct($model, $view)
    {
        parent::__construct($model, $view);
    }

    /**
     * Set FormFieldModel::show_db_value to false.
     */
    public function disable_show_db_value()
    {
        $this->model->set_show_db_value(false);
    }

    /**
     * Set FormFieldModel::show_db_value to true.
     */
    public function enable_show_db_value()
    {
        $this->model->set_show_db_value(true);
    }

    /**
     * Set FormFieldModel::is_user_input to true.
     */
    public function enable_user_input()
    {
        $this->model->set_user_input(true);
    }

    /**
     * Update the value of the form field view.
     *
     * @param string $value
     *  The new value.
     */
    public function update_value_view($value)
    {
        $this->view->set_value($value);
    }
}
?>
