<?php
/**
 */
class SearchSection
{
    /* Private Properties *****************************************************/

    private $db;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /* Private Methods ********************************************************/

    private function fetch_all_accessible_sections()
    {
        if(!isset($_POST["pattern"])) return null;
        $sql = "SELECT id, name, id_styles FROM sections
            WHERE name LIKE :pattern";
        return $this->db->query_db($sql,
            array(":pattern" => "%" . $_POST["pattern"] . "%"));
    }

    /* Public Methods *********************************************************/

    public function get_data()
    {
        return $this->fetch_all_accessible_sections();
    }
}
?>
