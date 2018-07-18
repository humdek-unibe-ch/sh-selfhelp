<?php
require_once __DIR__ . '/BaseDb.php';

/**
 * Class to handle the communication with the DB
 *
 * @author moiri
 */
class PageDb extends BaseDb {

    /**
     * Open a connection to a mysql database
     *
     * @param string $server
     *  Address of the server.
     * @param string $database
     *  Name of the database.
     * @param string $login
     *  The username of the database user.
     * @param string $password
     *  The password of the database user.
     */
    function __construct($server, $dbname, $username, $password ) {
        parent::__construct( $server, $dbname, $username, $password );
    }

    /**
     * Get the title of a page by providing a link keyword.
     *
     * @param string $keyword
     *  A link keyword, used to identify router paths.
     *
     * @retval string
     *  Either the title of the page or the string "Unknown" if the title could
     *  not be found.
     */
    public function get_link_title($keyword)
    {
        $info = $this->fetch_page_info($keyword);
        return $info['title'];
    }

    /**
     * Fetch the main page information from the database.
     *
     * @param string $keyword
     *  The keyword identifying the page.
     */
    public function fetch_page_info($keyword)
    {
        $sql = "SELECT p.id, p.keyword, p.url, pt.title FROM pages AS p
            LEFT JOIN pages_translation AS pt ON pt.id_pages = p.id
            LEFT JOIN languages AS l ON pt.id_languages = l.id
            WHERE keyword=:keyword AND locale=:locale";
        $info = $this->query_db_first($sql,
            array(":keyword" => $keyword, ":locale" => $_SESSION['language']));
        if($info)
            return $info;
        else
            return array(
                "title" => "Unknown",
                "keyword" => "",
                "url" => "/",
                "id" => 0
            );
    }
}
?>
