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
     * DB field 'value' (empty string).
     * The default value of the select form.
     */
    private $value;

    /**
     * DB field 'name' (empty string).
     * The name of the form selection. If this is not set, the component will
     * not be rendered.
     */
    private $name;

    /**
     * DB field 'label' (empty string).
     * The name to be placed next to the selection.
     */
    private $label;

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

    /**
     * DB field 'is_required' (false).
     * If set to true the slection must be filled out before submitting,
     * otherwise not.
     */
    private $is_required;

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
        $this->value = $this->model->get_db_field("value");
        $this->label = $this->model->get_db_field("label");
        $this->is_multiple = $this->model->get_db_field("is_multiple", false);
        $this->is_required = $this->model->get_db_field("is_required", false);
    }

    /* Private Methods ********************************************************/

    /**
     * Render a select option.
     */
    private function output_fields()
    {
        if($this->value == "")
        {
            $empty = $this->model->get_db_field("alt");
            require __DIR__ . "/tpl_select_empty.php";
        }
        if(!is_array($this->items)) return;
        foreach($this->items as $field)
        {
            if(!isset($field['value']) || !isset($field['text'])) continue;
            $value = htmlspecialchars($field['value']);
            $text = htmlspecialchars($field['text']);
            $selected = ($value == $this->value) ? 'selected="selected"' : "";
            require __DIR__ . "/tpl_select_item.php";
        }
    }

    /**
     * Render the select form.
     */
    private function output_select()
    {
        $css = ($this->label == "") ? $this->css : "";
        $multiple = ($this->is_multiple) ? "multiple" : "";
        $required = ($this->is_required) ? "required" : "";
        require __DIR__ . "/tpl_select.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->name == "") return;
        if($this->label == "")
            $this->output_select();
        else
            require __DIR__ . "/tpl_select_label.php";
    }
}
?>
