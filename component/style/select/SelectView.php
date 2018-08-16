<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the select form style component. This component renders a
 * select form field.
 * The following fields are required:
 *  'items': A list of options. where each element has the following keys
 *      'value': The id of the option item.
 *      'text': The content of the option item.
 *  'name': The name of the select field.
 */
class SelectView extends BaseView
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

    /* Private Methods ********************************************************/

    private function output_fields($fields)
    {
        foreach($fields as $field)
        {
            $value = $field['value'];
            $text = $field['text'];
            require __DIR__ . "/tpl_select_item.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fields = $this->model->get_db_field("items");
        $name = $this->model->get_db_field("name");
        require __DIR__ . "/tpl_select.php";
    }
}
?>
