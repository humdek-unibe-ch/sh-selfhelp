<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the textarea style component. This component renders a
 * textarea form field.
 * The following fields are required:
 *  'text': The default content of the textarea.
 *  'name': The name of the textarea.
 */
class TextareaView extends BaseView
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
        $name = $this->model->get_db_field("name");
        require __DIR__ . "/tpl_textarea.php";
    }
}
?>
