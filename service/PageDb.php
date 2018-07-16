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
        $sql = "SELECT title FROM pages_translation AS pt
            LEFT JOIN pages AS p ON p.id = pt.id_pages
            LEFT JOIN languages AS l ON l.id = pt.id_languages
            WHERE p.keyword = :key AND l.locale = :locale";
        $res = $this->query_db_first($sql,
            array(':key' => $keyword, ':locale' => "de-CH"));
        if($res)
            return $res['title'];
        else
            return "Unknown";
    }
}
