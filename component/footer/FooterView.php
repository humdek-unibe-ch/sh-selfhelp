<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the footer component.
 */
class FooterView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Private Methods ********************************************************/

    /**
     * Render a footer link.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     */
    private function output_footer_link($key, $page_name)
    {
        $active = ($this->model->is_link_active($key)) ? "active" : "";
        $url = $this->model->get_link_url($key);
        require __DIR__ . "/tpl_footer_link.php";
    }

    /**
     * Render all footer links.
     */
    private function output_footer_links()
    {
        $pages = $this->model->get_pages();
        $first = true;
        foreach($pages as $key => $page_name)
        {
            if(!$first) echo "|";
            $this->output_footer_link($key, $page_name);
            $first = false;
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the 
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes()
    {
        return array(
            __DIR__ . "/footer.css"
        );
    }

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_footer.php";
    }
}
?>
