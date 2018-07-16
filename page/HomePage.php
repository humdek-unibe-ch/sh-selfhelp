<?php
require_once __DIR__ . "/NavPage.php";
require_once __DIR__ . "/../component/well/WellComponent.php";

class HomePage extends NavPage
{
    public function __construct($router)
    {
        parent::__construct($router, "home");
        $this->add_component("home", new WellComponent());
    }

    protected function output_content()
    {
        $this->output_component("home");
    }

    protected function output_meta_tags() {}
}
?>
