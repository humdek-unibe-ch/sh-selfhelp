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
     *  The model instance of the login component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->code_text = $this->model->get_db_field('code');
        $this->form_children = $this->model->get_children();
    }

    /* Private Methods *********************************************************/

    /**
     * Get fields that will be editable by the mermaid, we return a json array
     * with a structure
     * name:{
     *   value: field_value,
     *   label: label_used_for_description
     * }
     */
    private function getUserFieldNamesWithValues()
    {
        $arrFields = [];
        foreach($this->form_children as $child)
        {
            if(is_a($child, "StyleComponent"))
            {
                $name = $child->get_style_instance()->get_view()
                    ->get_name_base();
                $value = $child->get_style_instance()->get_model()
                    ->get_form_field_value();
                if($this->model->is_cms_page()){
                    $arrFields[$name] = array(
                        "value" => '',
                        "label" => ''
                    );
                }else{
                    $arrFields[$name] = array(
                        "value" => $value,
                        "label" => ''
                    );
                }
                $arrFields[$name]['label'] = $child->get_style_instance()
                    ->get_view()->get_label();
            }
        }
        return json_encode($arrFields);
    }

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
            "value" => $this->name,
        ));
        $form = new BaseStyleComponent("form", array(
            "label" => $this->label,
            "type" => $this->type,
            "url" => $_SERVER['REQUEST_URI'] . '#section-' . $this->id_section,
            "children" => $children,
            "css" => "mermaidFormHiddenFields",
            "id" => $this->id_section,
        ));
        $formModal = new BaseStyleComponent("card", array(
            "title" => "<span id='modalFormTitle'></span>",
            "children" => array($form),
            "type" => "warning",
            "css" => ""
        ));

        $formModal->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the mermaid view.
     */
    public function output_content()
    {
        if($this->name === "") return;
        $fields =  $this->getUserFieldNamesWithValues();
        $code = $this->code_text;
        $formName = $this->name;
        require __DIR__ . "/tpl_mermaidForm.php";
    }
}
?>
