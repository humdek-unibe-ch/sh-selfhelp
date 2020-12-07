<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
    protected $default_value;

    /**
     * The current value of the form field.
     */
    protected $value = null;

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
    protected $name_base;

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
        $this->default_value = $this->model->get_db_field("value");
        $this->label = $this->model->get_db_field("label");
        $this->is_required = $this->model->get_db_field("is_required", false);
        $this->required = ($this->is_required) ? "required" : "";
    }

    /* Private Methods ********************************************************/

    /**
     * Render a hidden field holding the section id. This is only rendered if a
     * user input form is used.
     */
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

    /**
     * Render the base form field.
     */
    private function output_base_form_field()
    {
        require __DIR__ . "/tpl_form_field.php";
    }

    /* Protected Methods ******************************************************/

    /**
     * Chceks whether the field is a user input field.
     *
     * @retval bool
     *  True if the field is a user input, false otherwise.
     */
    protected function is_user_input()
    {
        return (!is_a($this->model, "BaseStyleModel")
            && $this->model->get_user_input());
    }

    /**
     * Chceks whether the default value ought to be overwritten by the db value.
     *
     * @retval bool
     *  True if the db value must be fetched, false otherwise.
     */
    protected function show_db_value()
    {
        return (!is_a($this->model, "BaseStyleModel")
            && $this->model->get_show_db_value());
    }

    /**
     * Render the form field.
     */
    abstract protected function output_form_field();

    /* Public Methods *********************************************************/

    /**
     * public getter for form field view to get the name of the field
     */
    public function get_name_base()
    {
        return $this->name_base;
    }

    /**
     * public getter for form field view to get the label of the field
     */
    public function get_label()
    {
        return $this->label;
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->name_base === "") return;

        if($this->show_db_value())
            $this->value = $this->model->get_form_field_value();

        if($this->is_user_input())
            $this->name = $this->name_base . "[value]";
        else
            $this->name = $this->name_base;

        if($this->label == "")
            $this->output_base_form_field();
        else
            require __DIR__ . "/tpl_label.php";
    }
	
	public function output_content_mobile()
    {
        $_SESSION['mobile'][] = 'field';
        $this->output_content();
    }

    /**
     * Public setter for the value.
     *
     * @param string $value
     *  The value to be set
     */
    public function set_value($value)
    {
        $this->value = $value;
    }
}
?>
