<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the input item style component.
 */
class InputView extends FormFieldView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'type_input' ('value').
     * The type of the input field.
     */
    private $type;

    /**
     * DB field 'placeholder' (empty string).
     * The text to be displayed inside the input field.
     */
    private $placeholder;

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
        $this->type = $this->model->get_db_field("type_input", "text");
        $this->placeholder = $this->model->get_db_field("placeholder");
        if($this->type == "checkbox")
        {
            $this->css_group = "form-check";
            $this->css_label = "form-check-label";
            if($this->label == "") $this->label = "&zwnj;";
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * Render an input form field
     */
    protected function output_form_field()
    {
        $css_input = "form-control";
        if($this->label == "") $css_input .= " " . $this->css;
        $checked = "";
        if($this->type == "checkbox")
        {
            $css_input = "form-check-input position-static float-left";
            if($this->is_user_input())
            {
                if($this->default_value == "") return;
                if(($this->value !== null && $this->value !== "")
                    || ($this->value === null && $this->placeholder != ""))
                    $checked = "checked";
            }
            else if($this->default_value != "")
            {
                $checked = "checked";
            }
            $this->value = $this->default_value;
        }
        else if($this->value === null)
            $this->value = $this->default_value;
        require __DIR__ . "/tpl_input.php";
    }
}
?>
