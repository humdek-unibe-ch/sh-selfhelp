<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the textarea style component. This component renders a
 * textarea form field.
 */
class TextareaView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'text' (empty string).
     * The default content of the textarea.
     */
    private $text;

    /**
     * DB field 'name' (empty string).
     * The name of the textarea. If this is not set, the component will not
     * be rendered.
     */
    private $name;

    /**
     * DB field 'label' (empty string).
     * The name to be placed next to the textarea.
     */
    private $label;

    /**
     * DB field 'required' (false).
     * If set to true the field is required to be filled in. If set to false the
     * empty string is also accepted as value.
     */
    private $is_required;

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
        $this->text = $this->model->get_db_field("text");
        $this->name = $this->model->get_db_field("name");
        $this->label = $this->model->get_db_field("label");
        $this->is_required = $this->model->get_db_field("is_required", false);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the textarea.
     */
    public function output_textarea()
    {
        $css = ($this->label == "") ? $this->css : "";
        $required = ($this->is_required) ? "required" : "";
        require __DIR__ . "/tpl_textarea.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(
            __DIR__ . "/jquery.ns-autogrow.min.js",
            __DIR__ . "/textarea.js",
        );
        return parent::get_js_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->name == "") return;
        if($this->label == "")
            $this->output_textarea();
        else
            require __DIR__ . "/tpl_textarea_label.php";
    }
}
?>
