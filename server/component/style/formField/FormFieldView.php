<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The base view class of form field style components.
 * This class provides common functionality that is used for all for field style
 * components.
 */
abstract class FormFieldView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'value' (empty string).
     * The default value of the form field.
     */
    protected $value;

    /**
     * DB field 'is_required' (false).
     * If set to true the slection must be filled out before submitting,
     * otherwise not.
     */
    protected $is_required;

    /**
     * DB field 'name' (empty string).
     * The name of the form field. If this is not set, the component will
     * not be rendered.
     */
    protected $name;

    /**
     * DB field 'label' (empty string).
     * The name to be placed next to the form field.
     */
    protected $label;

    /**
     * A unique string identifying the form field. It is composed out of the
     * prefix "form_field-" and the section id of the form field.
     */
    protected $id_field;

    /**
     * The form label css classes.
     */
    protected $css_label;

    /**
     * The form group css classes.
     */
    protected $css_group;

    /**
     * The appropriate string for whether the field is required or not.
     */
    protected $required;

    /**
     * The name provided by the user. The actual form name will be modyfied
     * depending on whether the form field is a user input or not. If this is
     * not set, the component will not be rendered.
     */
    private $name_base;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model)
    {
        $this->css_label = "";
        $this->css_group = "";
        parent::__construct($model);

        $this->name_base = $this->model->get_db_field("name");
        $this->value = $this->model->get_db_field("value");
        $this->label = $this->model->get_db_field("label");
        $this->is_required = $this->model->get_db_field("is_required", false);
        $this->required = ($this->is_required) ? "required" : "";
    }

    /* Private Methods ********************************************************/

    private function output_id_field()
    {
        if(!$this->is_user_input()) return;
        $hidden = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => $this->name_base . "[id]",
            "value" => $this->id_section,
        ));
        $hidden->output_content();
    }

    private function is_user_input()
    {
        return (!is_a($this->model, "BaseStyleModel")
            && $this->model->get_user_input());
    }

    private function output_base_form_field()
    {
        require __DIR__ . "/tpl_form_field.php";
    }

    /* Protected Methods ******************************************************/

    /**
     * Render the form field.
     */
    abstract protected function output_form_field();

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if(!is_a($this->model, "BaseStyleModel"))
            $this->value = $this->model->get_form_field_value();
        $this->name = $this->is_user_input() ?
             $this->name_base . "[value]" : $this->name_base;
        if($this->name == "") return;
        if($this->label == "")
            $this->output_base_form_field();
        else
            require __DIR__ . "/tpl_label.php";
    }
}
?>
