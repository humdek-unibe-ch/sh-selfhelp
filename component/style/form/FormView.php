<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the form style component. This component renders a html
 * form.
 * The following fields are required:
 *  'url': The action url.
 *  'label': The label of the submit button.
 *  'type': The type of the submit button, e.g. 'primary', 'success', etc.
 *  'children': The form children to be rendered inside the body.
 */
class FormView extends BaseView
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
        $url = $this->model->get_db_field("url");
        $type = $this->model->get_db_field("type");
        $label = $this->model->get_db_field("label");
        require __DIR__ . "/tpl_form.php";
    }
}
?>
