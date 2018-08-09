<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the markdown component.
 */
class MarkdownView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        echo $this->model->get_markdown_text();
    }
}
?>
