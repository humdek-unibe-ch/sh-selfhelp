<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the jumbotron style component.
 */
class JumbotronView extends BaseView
{
    /* Private Properties *****************************************************/

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
    }


    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fluid = ($this->fluid) ? "-fluid" : "";
        require __DIR__ . "/tpl_jumbotron.php";
    }
}
?>
