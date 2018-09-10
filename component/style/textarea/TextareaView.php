<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the textarea style component. This component renders a
 * textarea form field.
 */
class TextareaView extends FormFieldView
{
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
    }

    /* Protected Methods ********************************************************/

    /**
     * Render the textarea.
     */
    protected function output_form_field()
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
}
?>
