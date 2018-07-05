<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/FooterView.php";
require_once __DIR__ . "/FooterModel.php";

class FooterComponent extends BaseComponent
{
    public function __construct($router)
    {
        $model = new FooterModel();
        $view = new FooterView($router, $model);
        parent::__construct($view);
    }

    public function get_css_includes()
    {
        return array(
            __DIR__ . "/footer.css"
        );
    }
}
?>
