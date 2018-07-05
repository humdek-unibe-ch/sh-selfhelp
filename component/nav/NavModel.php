<?php
require_once __DIR__ . "/INav.php";

class NavModel implements INav
{
    private $home;
    private $pages;
    private $login;

    public function __construct()
    {
        $this->home = "Schlaf Coach";
        $this->pages = array(
            "sessions" => "Sitzungen",
            "protocols" => "Protokolle",
            "contact" => "Kontakt"
        );
        $this->login = "Log In";
    }

    public function get_home() { return $this->home; }
    public function get_login() { return $this->login; }
    public function get_pages() { return $this->pages; }
}
?>
