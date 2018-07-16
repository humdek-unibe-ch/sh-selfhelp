<?php
class FooterModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function get_impressum() { return $this->db->get_link_title("impressum"); }
    public function get_agb() { return $this->db->get_link_title("agb"); }
    public function get_disclaimer() { return $this->db->get_link_title("disclaimer"); }
}
?>
