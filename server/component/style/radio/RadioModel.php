<?php
require_once __DIR__ . "/../formField/FormFieldModel.php";
require_once __DIR__ . "/../json/JsonModel.php";

/**
 * This class is used to prepare all data related to the radio style
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class RadioModel extends FormFieldModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /* Public Methods *********************************************************/

    public function json_style_parse($items)
    {
        $json = new JsonModel($this->services, 0);
        return $json->json_style_parse($items);

    }
}
?>
