<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * DB field 'anchor' (empty string).
     * The id of a anchor section to jump to on submit instead of the form.
     */
    protected $anchor;

    /**
     * DB field 'name' (empty string).
     * The name of the form. This will help to group all input data to
     * a specific set. If this field is not set, the style will not be rendered.
     */
    protected $name;

    /**
     * DB field 'label' ('Submit').
     * The label of the submit button.
     */
    protected $label;

    /**
     * DB field 'type' ('primary').
     * The type of the submit button, e.g. 'primary', 'success', etc.
     */
    protected $type;

    /**
     * DB field 'is_log' (false).
     * If set to true the form will save journal data, i.e. each data set is
     * stored individually with a timestamp. If set to false the form will save
     * persistent data which can be edited continuously by the user.
     */
    protected $is_log;

    /**
     * DB field 'submit_and_send_email' (false).
     * If set to true the form will have one more submit button which will send an email with the form data to the user
     */
    protected $submit_and_send_email;

    /**
     * DB field 'submit_and_send_lable' ('').
     * The label on the submit and send button
     */
    protected $submit_and_send_lable;

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
        $this->anchor = $this->model->get_db_field("anchor");
        $this->submit_and_send_email = $this->model->get_db_field("submit_and_send_email", false);
        $this->submit_and_send_label = $this->model->get_db_field("submit_and_send_label", '');
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
    protected function propagate_input_field_settings($children, $show_data)
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
            "value" => htmlentities($this->name),
        ));
        $url = $_SERVER['REQUEST_URI'] . '#section-'
                . ($this->anchor ? $this->anchor : $this->id_section);
        $form = new BaseStyleComponent("form", array(
            "label" => $this->label,
            "type" => $this->type,
            "url" => $url,
            "children" => $children,
            "css" => $this->css,
            "id" => $this->id_section,
            "submit_and_send_email" => $this->submit_and_send_email,
            "submit_and_send_label" => $this->submit_and_send_label
        ));
        require __DIR__ . "/tpl_form.php";
    }
}
?>
