<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the email component such
 * that the data can easily be displayed in the view of the component.
 */
class EmailModel extends BaseModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

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

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Fetch all emails from the database.
     *
     * @retval array
     *  An array with the keys
     *   - 'id': the id of the email field
     *   - 'name': the name of the email field
     */
    public function get_emails()
    {
        $sql = "SELECT id, name from fields WHERE id_type = :type";
        $emails_db = $this->db->query_db($sql, array(':type' => EMAIL_TYPE_ID));
        $emails = array();
        foreach($emails_db as $email)
        {
            $id = intval($email['id']);
            $emails[] = array(
                'id' => $id,
                'title' => $email['name'],
                'url' => $this->get_link_url('email', array('id' => $id)),
            );
        }
        return $emails;
    }

    public function get_email($id)
    {
        $sql = "SELECT l.locale AS locale, l.id AS l_id, p.id AS p_id, pft.content
            FROM languages AS l
            LEFT JOIN pages AS p ON p.keyword = 'email'
            LEFT JOIN pages_fields_translation AS pft
            ON l.id = pft.id_languages AND pft.id_pages = p.id
            AND pft.id_fields = :fid
            WHERE l.locale <> 'all'";
        return $this->db->query_db($sql, array(
            ":fid" => $id,
        ));
    }

    public function update_email($pid, $fid, $lid, $content)
    {
        $update = array(
            "content" => $content
        );
        $insert = array(
            "content" => $content,
            "id_fields" => $fid,
            "id_languages" => $lid,
            "id_pages" => $pid,
        );
        return $this->db->insert("pages_fields_translation", $insert, $update);
    }
}
?>
