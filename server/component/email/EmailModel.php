<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the email component such
 * that the data can easily be displayed in the view of the component.
 */
class EmailModel extends BaseModel
{
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
        $sql = "SELECT DISTINCT f.id, f.name FROM fields AS f
            LEFT JOIN fieldType AS ft ON ft.id = f.id_type
            LEFT JOIN pages_fields_translation AS pft ON pft.id_fields = f.id
            WHERE ft.name = 'email' AND pft.content IS NOT NULL ORDER BY `name`";
        $emails_db = $this->db->query_db($sql, array());
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

    /**
     * Fetch a specific email from the database in multiple languages.
     *
     * @param int $id
     *  The id of the email to be fetched.
     * @retval array
     *  An array of database entries with the following keys:
     *   - 'locale':    A short notation of the language.
     *   - 'l_id':      The id of the langauge.
     *   - 'p_id':      The page id associated to the email.
     *   - 'content':   The email message.
     */
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

    /**
     * Update the email content.
     *
     * @param int $pid
     *  The id of the page associated to the email.
     * @param int $fid
     *  The id of the field associted to the email.
     * @param int $lid
     *  The id of the language in which the email is written.
     * @param string $content
     *  The email message.
     * @retval bool
     *  True on success, false on failure
     */
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
