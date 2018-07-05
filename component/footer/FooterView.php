<?php
class FooterView implements IView
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

    public function output_content()
    {
        $impressum = $this->model->impressum;
        $disclaimer = $this->model->disclaimer;
        $agb = $this->model->agb;
        require __DIR__ . "/tpl_footer.php";
    }
}
?>

