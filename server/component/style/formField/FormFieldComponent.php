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
}
?>
