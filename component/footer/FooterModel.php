<?php
class FooterModel
{
    public $impressum;
    public $disclaimer;
    public $agb;

    public function __construct()
    {
        $this->impressum = "Impressum";
        $this->disclaimer = "Disclaimer";
        $this->agb = "AGB";
    }
}
?>
