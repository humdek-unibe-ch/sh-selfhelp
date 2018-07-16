<?php
class LoginPage extends BasePage
{
    public function __construct($router)
    {
        parent::__construct($router, "login");

        $this->add_component("login", new WellComponent());
    }

    protected function output_content()
    {
        $this->output_component("login");
    }

    protected function output_meta_tags() {}
}
?>
