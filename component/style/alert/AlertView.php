<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the alert style component.
 */
class AlertView extends BaseView
{
    /* Private Properties******************************************************/

    private $fluid;
    private $type;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param string $type
     *  The alert type. This can be anything offered by bootstrap (e.g. success,
     *  warning, primary, etc.)
     * @param bool $fluid
     *  If set to true the jumbotron gets the bootstrap class "container-fluid",
     *  othetwise the class "container" is used.
     */
    public function __construct($model, $type, $fluid)
    {
        $this->fluid = $fluid;
        $this->type = $type;
        parent::__construct($model);
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
