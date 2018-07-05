<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/WellView.php";
require_once __DIR__ . "/WellModel.php";

class WellComponent extends BaseComponent
{
    public function __construct()
    {
        $model = new WellModel();
        $view = new WellView($model);
        parent::__construct($view);
    }
}
?>
