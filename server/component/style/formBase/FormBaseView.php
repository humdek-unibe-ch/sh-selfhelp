<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The base view class of form style components.
 */
abstract class FormBaseView extends StyleView
{
    /* Private Properties *****************************************************/

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
        $this->label = $this->model->get_db_field("label", "Submit");
        $this->type = $this->model->get_db_field("type", "primary");
    }
}
?>
