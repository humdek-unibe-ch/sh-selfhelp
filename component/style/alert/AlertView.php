<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the alert style component.
 */
class AlertView extends BaseView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'is_dismissable' (false).
     * If set to true, the alert can be dismissed by clicking on a close button.
     */
    private $is_dismissable;

    /**
     * DB field 'type' ('primary').
     * The style of the alert. E.g. 'warning', 'danger', etc.
     */
    private $type;

    /**
     * If set to true the jumbotron gets the bootstrap class "container-fluid",
     * othetwise the class "container" is used.
     */
    private $fluid;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param bool $fluid
     *  If set to true the jumbotron gets the bootstrap class "container-fluid",
     *  othetwise the class "container" is used.
     */
    public function __construct($model, $fluid)
    {
        $this->fluid = $fluid;
        parent::__construct($model);
        $this->is_dismissable = $this->model->get_db_field("is_dismissable",
            false);
        $this->type = $this->model->get_db_field("type", "primary");
    }

    /* Private  Methods *******************************************************/

    private function output_close_button()
    {
        if(!$this->is_dismissable) return;
        require __DIR__ . "/tpl_close_alert.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/alert.css");
        return parent::get_css_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fluid = ($this->fluid) ? "-fluid" : "";
        $type = "alert-" . $this->type;
        require __DIR__ . "/tpl_alert.php";
    }
}
?>
