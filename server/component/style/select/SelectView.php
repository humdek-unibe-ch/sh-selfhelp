<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the select form style component. This component renders a
 * select form field.
 */
class SelectView extends FormFieldView
{
    /* Private Properties *****************************************************/

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
        $this->is_multiple = $this->model->get_db_field("is_multiple", false);
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

    /* Protected Methods ********************************************************/

    /**
     * Render the select form.
     */
    protected function output_form_field()
    {
        $css = ($this->label == "") ? $this->css : "";
        $multiple = ($this->is_multiple) ? "multiple" : "";
        $required = ($this->is_required) ? "required" : "";
        require __DIR__ . "/tpl_select.php";
    }
}
?>
