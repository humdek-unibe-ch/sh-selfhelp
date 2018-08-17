<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the input item style component. This component renders an
 * input form field.
 */
class InputView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'text' (empty string).
     * The default value of the input field.
     */
    private $value;

    /**
     * DB field 'type' ('value').
     * The type of the input field.
     */
    private $type;

    /**
     * DB field 'name' (empty string).
     * The name of the input field. If this is not set, the component will not
     * be rendered.
     */
    private $name;

    /**
     * DB field 'label' (empty string).
     * The name to be placed next to the input field.
     */
    private $label;

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
        $this->value = $this->model->get_db_field("value");
        $this->type = $this->model->get_db_field("type", "text");
        $this->name = $this->model->get_db_field("name");
        $this->label = $this->model->get_db_field("label");
    }

    /* Private Methods ********************************************************/

    private function output_input()
    {
        $css_input = "form-control";
        $checked = "";
        if($this->type == "checkbox")
        {
            $css_input = "form-check-input position-static float-left";
            if($this->value != "") $checked = "checked";
        }
        require __DIR__ . "/tpl_input.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->name == "") return;
        $css_label = "";
        $css_group = "";
        $checked = "";
        if($this->type == "checkbox")
        {
            $css_group = "form-check";
            $css_label = "form-check-label";
            if($this->label == "") $this->label = "&zwnj;";
            if($this->value != "") $checked = "checked";
        }
        if($this->label == "")
            $this->output_input();
        else
            require __DIR__ . "/tpl_input_label.php";
    }
}
?>
