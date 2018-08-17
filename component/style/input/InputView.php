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
     * DB field 'type' ('text').
     * The type of the input field.
     */
    private $type;

    /**
     * DB field 'name' (empty string).
     * The name of the input field.
     */
    private $name;

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
        $this->value = $this->model->get_db_field("text");
        $this->type = $this->model->get_db_field("type", "text");
        $this->name = $this->model->get_db_field("name");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $css = "form-control";
        if($this->type == "checkbox") $css = "";
        $checked = "";
        if($this->type == "checkbox" && $this->value == "1")
            $checked = "checked";
        require __DIR__ . "/tpl_input.php";
    }
}
?>
