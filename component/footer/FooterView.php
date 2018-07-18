<?php
/**
 * The view class of the footer component.
 */
class FooterView implements IView
{
    /* Private Properties *****************************************************/

    private $router;
    private $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($router, $model)
    {
        $this->router = $router;
        $this->model = $model;
    }

    /* Private Methods ********************************************************/

    /**
     * Determines wheter a link is active or not and returns a css class
     * accordingly.
     *
     * @param string $route_name
     *  The identification string of a route.
     * @retval string
     *  Returns "active" if the route is the active route, an empty string
     *  otherwise.
     */
    private function get_active_css($route_name)
    {
        if($this->router->is_active($route_name))
            return "active";
        return '';
    }

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
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_footer.php";
    }
}
?>

