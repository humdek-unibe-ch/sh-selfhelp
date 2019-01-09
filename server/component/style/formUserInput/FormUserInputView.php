<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the formUserInput style component.
 */
class FormUserInputView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'name' (empty string).
     * The name of the form. This will help to group all input data to
     * a specific set. If this field is not set, the style will not be rendered.
     */
    private $name;

    /**
     * DB field 'label' ('Submit').
     * The label of the submit button.
     */
    private $label;

    /**
     * DB field 'type' ('primary').
     * The type of the submit button, e.g. 'primary', 'success', etc.
     */
    private $type;

    /**
     * DB field 'is_log' (false).
     * If set to true the form will save journal data, i.e. each data set is
     * stored individually with a timestamp. If set to false the form will save
     * persistent data which can be edited continuously by the user.
     */
    private $is_log;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->name = $this->model->get_db_field("name");
        $this->label = $this->model->get_db_field("label", "Submit");
        $this->type = $this->model->get_db_field("type", "primary");
        $this->is_log = $this->model->get_db_field("is_log", false);
    }

    /**
     * For each child of style formField enable the setting that the last db
     * entry is displayed in the form field. This is a recursive method.
     *
     * @param array $children
     *  The child component array of the current component.
     * @param bool $show_data
     *  If set to true, existing data is updated.
     *  If set to false, each data set is saved as a timestamped new entry.
     */
    private function propagate_input_field_settings($children, $show_data)
    {
        foreach($children as $child)
        {
            $style = $child->get_style_instance();
            if(is_a($style, "FormFieldComponent"))
            {
                if($show_data) $style->enable_show_db_value();
                $style->enable_user_input();
            }
            $this->propagate_input_field_settings($child->get_children(),
                $show_data);
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->name === "") return;
        $children = $this->model->get_children();
        $this->propagate_input_field_settings($children, !$this->is_log);
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "__form_name",
            "value" => $this->name,
        ));
        $form = new BaseStyleComponent("form", array(
            "label" => $this->label,
            "type" => $this->type,
            "url" => $_SERVER['REQUEST_URI'],
            "children" => $children,
            "css" => $this->css,
        ));
        require __DIR__ . "/tpl_form.php";
    }
}
?>
