<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the select form style component. This component renders a
 * select form field.
 */
class SelectView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'name' (empty string).
     * The name of the form selection. If this is not set, the component will
     * not be rendered.
     */
    private $name;

    /**
     * DB field 'items' (empty array).
     * A list of options. where each element has the following keys
     *  'value':    The id of the option item.
     *  'text':     The content of the option item.
     */
    private $items;

    /**
     * DB field 'is_multiple' (false).
     * If set to true the selection form is a multiple select. If set to false
     * the selection form is a dropdown, single select.
     */
    private $is_multiple;

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
        $this->items = $this->model->get_db_field("items", array());
        $this->name = $this->model->get_db_field("name");
        $this->is_multiple = $this->model->get_db_field("is_multiple", false);
    }

    /* Private Methods ********************************************************/

    /**
     * Render a select option.
     */
    private function output_fields()
    {
        foreach($this->items as $field)
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
        if($this->name == "") return;
        $multiple = ($this->is_multiple) ? "multiple" : "";
        require __DIR__ . "/tpl_select.php";
    }
}
?>
