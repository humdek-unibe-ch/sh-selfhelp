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
            $border = "";
            $name = $field['name'];
            $content = $field['content'];
            $edit = isset($field['edit']) ? $field['edit'] : true;
            $locale = isset($field['locale']) ? $field['locale'] : "";
            $type = $field['type'];
            if($mode == "update" && $edit)
            {
                $id = $field['id'];
                $id_language = $field['id_language'];
                $relation = "section_field";
                require __DIR__ . "/tpl_field_label.php";
                if(in_array($type,
                    array("text", "number", "checkbox")))
                {
                    require __DIR__ . "/tpl_field_hidden.php";
                    require __DIR__ . "/tpl_field_input.php";
                }
                else if($type == "markdown-inline")
                {
                    require __DIR__ . "/tpl_field_hidden.php";
                    $type = "text";
                    require __DIR__ . "/tpl_field_input.php";
                }
                else if(in_array($type,
                    array("textarea","markdown")))
                {
                    require __DIR__ . "/tpl_field_hidden.php";
                    require __DIR__ . "/tpl_field_textarea.php";
                }
                else if($type == "page-text")
                {
                    $type = "text";
                    $relation = "page_field";
                    require __DIR__ . "/tpl_field_hidden.php";
                    require __DIR__ . "/tpl_field_input.php";
                }
                else if($type == "style-list")
                {
                    $relation = "section_children_order";
                    $type = "text";
                    require __DIR__ . "/tpl_field_hidden.php";
                    require __DIR__ . "/tpl_field_hidden_order.php";
                    $this->output_style_list($content, true);
                }
            }
            else
            {
                $border = "border-top";
                require __DIR__ . "/tpl_field_label.php";
                if($content == null)
                {
                    $content = "<i>field is not set</i>";
                    require __DIR__. "/tpl_field.php";
                }
                else if($type == "style-list")
                    $this->output_style_list($content);
                else
                    require __DIR__. "/tpl_field.php";
            }
        }
    }
    private function output_style_item_index($index, $edit)
    {
        if(!$edit) return;
        require __DIR__. "/tpl_style_item_index.php";
    }

    private function output_style_list_items($items, $edit)
    {
        foreach($items as $index => $item)
        {
            $id = $item['id'];
            $name = $item['title'];
            require __DIR__ . "/tpl_field_child.php";
        }
    }

    private function output_style_list($items, $edit=false)
    {
        $sortable = ($edit) ? "sortable" : "";
        require __DIR__ . "/tpl_field_children.php";
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
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(
            __DIR__ . "/sortable.min.js",
            __DIR__ . "/sortable.jquery.binding.js",
            __DIR__ . "/descriptionList.js"
        );
        return parent::get_js_includes($local);
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
