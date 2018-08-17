<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the form style component. This component renders a html
 * form.
 */
class FormView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'url' (empty string).
     * The achtion url of the form. If this is not set, the form will not be
     * rendered.
     */
    private $url;

    /**
     * DB field 'type' ('primary').
     * The type of the submit button, e.g. 'primary', 'success', etc.
     */
    private $type;

    /**
     * DB field 'label' ('Submit').
     * The label of the submit button.
     */
    private $label;

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
        $this->url = $this->model->get_db_field("url");
        $this->type = $this->model->get_db_field("type", "primary");
        $this->label = $this->model->get_db_field("label", "Submit");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->url == "") return;
        require __DIR__ . "/tpl_form.php";
    }
}
?>
