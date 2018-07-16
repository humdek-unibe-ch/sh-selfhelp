<?php
require_once __DIR__ . "/INav.php";
require_once __DIR__ . "/../LinkModel.php";

class NavModel extends LinkModel implements INav
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function get_home() { return $this->get_link("home"); }
    public function get_login() { return $this->get_link("login"); }
    public function get_pages()
    {
        $sql = "SELECT p.keyword, pt.title FROM pages_translation AS pt
            LEFT JOIN pages AS p ON p.id = pt.id_pages
            LEFT JOIN languages AS l ON l.id = pt.id_languages
            WHERE p.nav_position > 0 AND l.locale = :locale
            ORDER BY p.nav_position";
        $pages_db = $this->db->query_db($sql, array(':locale' => "de-CH"));
        $pages = array();
        foreach($pages_db as $item)
            $pages[$item['keyword']] = $item['title'];
        return $pages;
    }
}
?>
