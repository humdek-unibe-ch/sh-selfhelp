<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";
require_once __DIR__ . "/../formUserInput/FormUserInputView.php";

/**
 * The view class of the mermaid inline component.
 */
class MermaidFormView extends FormUserInputView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'code' (empty string).
     * The text to be rendered as markdown content.
     */
    private $code_text;

    /**
     * the children fields for the modal view
     */
    private $form_children;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller, null);
        $this->code_text = $this->model->get_db_field('code');
        $this->form_children = $this->model->get_children();
    }

    /* Private Methods *********************************************************/

    /**
     * render modal form in a card view
     */
    private function output_modal()
    {
        $this->propagate_input_field_settings($this->form_children,
            !$this->is_log);
        $children = $this->form_children;
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "__form_name",
            "value" => htmlentities($this->name),
        ));
        $form = new BaseStyleComponent("form", array(
            "label" => $this->label,
            "type" => $this->type,
            "url" => $_SERVER['REQUEST_URI'] . '#section-' . $this->id_section,
            "children" => $children,
            "css" => "mermaidFormHiddenFields",
            "id" => $this->id_section,
        ));
        $modal = new BaseStyleComponent('modal', array(
            'id' => $this->model->convert_to_valid_html_id($this->name),
            'title' => "Please enter your input",
            'children' => array($form),
        ));

        $modal->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the mermaid view.
     */
    public function output_content()
    {
        if($this->name === "") return;
        $fields =  $this->model->get_user_field_names_with_values(
            $this->form_children);
        $code = $this->model->replace_user_field_values_in_code($fields, $this->code_text);
        $formName = $this->model->convert_to_valid_html_id($this->name);
        $fields = json_encode($fields);
        require __DIR__ . "/tpl_mermaidForm.php";
    }
	
}
?>
