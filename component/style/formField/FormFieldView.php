<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The base view class of form field style components,
 */
abstract class FormFieldView extends BaseView
{
    /* Private Properties *****************************************************/

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
     * DB field 'is_user_input' (true).
     * If set to true, the form name is reffixed with the section id. If set to
     * false, the name remains unchanged.
     */
    protected $is_user_input;

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
        $this->id_field = "form_field-" . $this->id_section;
        $this->name = $this->model->get_db_field("name");
        $this->is_user_input = $this->model->get_db_field("is_user_input", true);
        if($this->is_user_input)
            $this->name = $this->id_section . "-" . $this->name;
        $this->label = $this->model->get_db_field("label");
        $this->is_required = $this->model->get_db_field("is_required", false);
        $this->required = ($this->is_required) ? "required" : "";
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
        if($this->name == "") return;
        if($this->label == "")
            $this->output_form_field();
        else
            require __DIR__ . "/tpl_label.php";
    }
}
?>
