<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/NavView.php";
require_once __DIR__ . "/NavModel.php";

class NavComponent extends BaseComponent
{
    public function __construct($router)
    {
        $model = new NavModel();
        $view = new NavView($router, $model);
        parent::__construct($view);
    }
}
?>
