<?php
abstract class BaseComponent
{
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function output_content()
    {
        $this->view->output_content();
    }

    public function get_css_includes()
    {
        return array();
    }

    public function get_js_includes()
    {
        return array();
    }
}
?>
