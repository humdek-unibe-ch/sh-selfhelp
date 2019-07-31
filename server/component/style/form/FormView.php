<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the form style component. This component renders a html
 * form.
 */
class FormView extends StyleView
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

    /**
     * DB field 'label_cancel' ('Cancel').
     * The label of the cancel button.
     */
    private $label_cancel;

    /**
     * DB field 'url_cancel' (empty string).
     * The target url when the cancel button is clicked.  If left empty, the
     * cancel button will not be rendered
     */
    private $cancel_url;

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
        $this->label_cancel = $this->model->get_db_field("label_cancel",
            "Cancel");
        $this->cancel_url = $this->model->get_db_field("url_cancel");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the canel button.
     */
    private function output_cancel()
    {
        $button = new BaseStyleComponent("button", array(
            "label" => $this->label_cancel,
            "type" => "secondary",
            "url" => $this->cancel_url,
            "css" => "float-right",
        ));
        $button->output_content();
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
