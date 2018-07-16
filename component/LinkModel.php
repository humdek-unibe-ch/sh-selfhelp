<?php
class LinkModel
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    protected function get_link($keyword)
    {
        $sql = "SELECT title FROM pages_translation AS pt
            LEFT JOIN pages AS p ON p.id = pt.id_pages
            LEFT JOIN languages AS l ON l.id = pt.id_languages
            WHERE p.keyword = :key AND l.locale = :locale";
        $res = $this->db->query_db_first($sql,
            array(':key' => $keyword, ':locale' => "de-CH"));
        if($res)
            return $res['title'];
        else
            return "Unknown";
    }
}
?>
