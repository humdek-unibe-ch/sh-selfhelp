<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class ModuleMailModel extends BaseModel
{

    /* Constructors ***********************************************************/

     /* Private Properties *****************************************************/
    /**
     * date from,
     */
    private $date_from;

    /**
     * date to,
     */
    private $date_to;

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    public function get_mail_queue(){
        $sql = "SELECT *
                FROM view_mailQueue 
                WHERE CAST(date_create AS DATE) BETWEEN STR_TO_DATE(:date_from,'%d-%m-%Y') AND STR_TO_DATE(:date_to,'%d-%m-%Y');";
        return $this->db->query_db($sql,array(
            ":date_from" => $this->date_from,
            ":date_to" => $this->date_to
        ));
    }

    public function set_date_from($date_from){
        $this->date_from = $date_from;
    }

    public function set_date_to($date_to){
        $this->date_to = $date_to;
    }

    public function get_date_from(){
        return $this->date_from;
    }

    public function get_date_to(){
        return $this->date_to;
    }

}