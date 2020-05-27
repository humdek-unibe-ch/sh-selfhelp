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
     * mail queue id,
     */
    private $mqid;

    /**
     * date from,
     */
    private $date_from;

    /**
     * date to,
     */
    private $date_to;

    /**
     * date type,
     */
    private $date_type;

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $mqid)
    {
        parent::__construct($services);
        $this->mqid = $mqid;
    }

    public function get_mail_queue()
    {
        $sql = "SELECT *
                FROM view_mailQueue 
                WHERE CAST(" . $this->date_type . " AS DATE) BETWEEN STR_TO_DATE(:date_from,'%d-%m-%Y') AND STR_TO_DATE(:date_to,'%d-%m-%Y');";
        return $this->db->query_db($sql, array(
            ":date_from" => $this->date_from,
            ":date_to" => $this->date_to
        ));
    }

    public function set_date_from($date_from)
    {
        $this->date_from = $date_from;
    }

    public function set_date_to($date_to)
    {
        $this->date_to = $date_to;
    }

    public function set_date_type($date_type)
    {
        $this->date_type = $date_type;
    }

    public function get_date_from()
    {
        return $this->date_from;
    }

    public function get_date_to()
    {
        return $this->date_to;
    }

    public function get_date_type()
    {
        return $this->date_type;
    }

    public function get_mqid()
    {
        return $this->mqid;
    }

    public function delete_selected_queue_entry()
    {
        return $this->db->update_by_ids(
            'mailQueue',
            array(
                "id_mailQueueStatus" => $this->db->get_lookup_id_by_value(Mailer::STATUS_LOOKUP_TYPE, Mailer::STATUS_DELETED)
            ),
            array(
                "id" => $this->mqid
            )
        );
    }

    public function send_selected_queue_entry(){
        return $this->mail->send_mail_from_queue($this->mqid, Mailer::SENT_BY_USER) !== false;
    }
}
