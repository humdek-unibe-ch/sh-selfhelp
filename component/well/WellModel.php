<?php
require_once __DIR__ . "/IWell.php";

class WellModel implements IWell
{
    private $title;
    private $content;

    public function __construct()
    {
        $this->title = "Title";
        $this->content = "Content";
    }

    public function get_title() { return $this->title; }
    public function get_content() {return $this->content; }
}
?>
