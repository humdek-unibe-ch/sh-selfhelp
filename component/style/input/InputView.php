<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the input item style component. This component renders an
 * input form field.
 * The following fields are required:
 *  'text': The default value.
 *  'name': The name of the input field.
 *  'type': The type of the input field.
 */
class InputView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $text = $this->model->get_db_field("text");
        $type = $this->model->get_db_field("type");
        $name = $this->model->get_db_field("name");
        require __DIR__ . "/tpl_input.php";
    }
}
?>
