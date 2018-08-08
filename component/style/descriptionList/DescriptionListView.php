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
    private function output_field_items($fields)
    {
        foreach($fields as $field)
        {
            $name = $field['name'];
            $content = $field['content'];
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
        require __DIR__ . "/tpl_list.php";
    }
}
?>
