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

    /**
     * DB field 'cancel' (false).
     * If set to true this will render the cancel button. If set to false, the
     * cancel button will not be rendered.
     */
    private $cancel;

    /**
     * DB field 'cancel_url' (#back).
     * The target url when the cancel button is clicked. This has no effect if
     * the field "cancel" is set to false.
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
        $this->cancel = $this->model->get_db_field("cancel", false);
        $url = "";
        if(isset($_SERVER['HTTP_REFERER'])) $url = $_SERVER['HTTP_REFERER'];
        $this->cancel_url = $this->model->get_db_field("cancel_url", $url);
        $this->add_local_component("cancel", new BaseStyleComponent(
            "button", array(
                "label" => $this->model->get_db_field("label_cancel", "Cancel"),
                "type" => "secondary",
                "url" => $this->cancel_url,
                "css" => "float-right",
            )
        ));
    }

    /* Private Methods ********************************************************/

    private function output_cancel()
    {
        if($this->cancel)
            $this->output_local_component("cancel");
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
