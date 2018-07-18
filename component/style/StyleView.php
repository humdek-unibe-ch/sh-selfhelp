<?php
require_once __DIR__ . "/../IView.php";

/**
 * The view class of the style component.
 */
class StyleView implements IView
{
    /* Private Properties *****************************************************/

    private $model;
    private $style;
    private $fluid;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param string $style
     *  A string specifying the syle to be used to render the content.
     * @param bool $fluid
     *  If set to true the content will be rendered in a container-fluid
     *  bootstrap element, if set to false in a container.
     */
    public function __construct($model, $style, $fluid)
    {
        $this->model = $model;
        $this->style = $style;
        $this->fluid = $fluid;
    }

    /* Private Methods ********************************************************/

    /**
     * Render the section link if the section has a link.
     */
    private function output_link()
    {
        if(!$this->model->has_link()) return;
        $url = $this->model->get_url();
        $label = $this->model->get_link_label();
        echo "<hr/>";
        require_once __DIR__ . "/tpl_link.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fluid = ($this->fluid) ? "-fluid" : "";
        $title = $this->model->get_title();
        $content = $this->model->get_content();
        if($this->style == "jumbotron")
            require_once __DIR__ . "/tpl_jumbotron.php";
        else if($this->style == "well")
            require_once __DIR__ . "/tpl_well.php";
        else if($this->style == "error")
        {
            $level = "danger";
            require_once __DIR__ . "/tpl_alert.php";
        }
    }
}
?>
