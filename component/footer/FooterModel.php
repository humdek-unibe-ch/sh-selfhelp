<?php
class FooterModel extends LinkModel
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function get_impressum() { return $this->get_link("impressum"); }
    public function get_agb() { return $this->get_link("agb"); }
    public function get_disclaimer() { return $this->get_link("disclaimer"); }
}
?>
