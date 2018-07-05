<?php
require_once __DIR__ . "/../IView.php";

class WellView implements IView
{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function output_content()
    {
        $title = $this->model->get_title();
        $content = $this->model->get_content();
        require_once __DIR__ . "/tpl_well.php";
    }
}
?>
