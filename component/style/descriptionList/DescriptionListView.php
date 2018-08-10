<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the description list style component.
 */
class DescriptionListView extends BaseView
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

    /**
     * Render the list item pairs
     *
     * @param array $fields
     *  An array of fields where each field has the following keys
     *   'name':    the name of the field.
     *   'content': the content to be rendered next to the name.
     */
    private function output_field_items($fields, $mode = "")
    {
        foreach($fields as $field)
        {
            $name = $field['name'];
            $id = $field['id'];
            $db_type = $field['type'];
            $content = $field['content'];
            if($mode == "update")
            {
                require __DIR__ . "/tpl_field_label.php";
                if(in_array($field['type'],
                    array("text", "number", "checkbox")))
                {
                    $type = $field['type'];
                    require __DIR__ . "/tpl_field_input.php";
                }
                else if($field['type'] == "markdown-inline")
                {
                    $type = "text";
                    require __DIR__ . "/tpl_field_input.php";
                }
                else if(in_array($field['type'],
                    array("textarea","markdown")))
                {
                    require __DIR__ . "/tpl_field_textarea.php";
                }
            }
            else
                require __DIR__. "/tpl_field.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/descriptionList.css");
        return parent::get_css_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fields = $this->model->get_db_field("fields");
        $mode = $this->model->get_db_field("mode");
        if($mode == "update")
            require __DIR__ . "/tpl_list_cms.php";
        else
            require __DIR__ . "/tpl_list.php";
    }
}
?>
