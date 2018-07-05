<?php
require_once __DIR__ . "/../IView.php";

class NavView implements IView
{
    private $router;
    private $model;

    public function __construct($router, $model)
    {
        $this->router = $router;
        $this->model = $model;
    }

    private function get_active_css($route_name)
    {
        if( $this->router->is_active( $route_name ) )
            return "active";
        return '';
    }

    private function output_nav_items()
    {
        $pages = $this->model->get_pages();
        foreach($pages as $key => $page_name)
        {
            $this->output_nav_item($key, $page_name);
        }
    }

    private function output_nav_item($key, $page_name)
    {
        require __DIR__ . "/tpl_nav_item.php";
    }

    public function output_content()
    {
        $home = $this->model->get_home();
        $login = $this->model->get_login();
        require __DIR__ . "/tpl_nav.php";
    }
}
?>
