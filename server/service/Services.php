<?php
require_once __DIR__ . "/globals.php";
require_once __DIR__ . "/Acl.php";
require_once __DIR__ . "/PageDb.php";
require_once __DIR__ . "/Login.php";
require_once __DIR__ . "/Mailer.php";
require_once __DIR__ . "/Navigation.php";
require_once __DIR__ . "/ParsedownExtension.php";
require_once __DIR__ . "/Router.php";
require_once __DIR__ . "/UserInput.php";

/**
 * The service handler class. This class holds all service instances. The
 * services are instantiated in the constructor of the class.
 */
class Services
{
    /**
     * The instance of the access control layer (ACL) which allows to decide
     * which links to display.
     */
    private $acl = null;

    /**
     * The db instance which grants access to the DB.
     */
    private $db = null;

    /**
     * The login instance that allows to check user credentials.
     */
    private $login = null;

    /**
     * An instance of the PHPMailer service to handle outgoing emails.
     */
    private $mail = null;

    /**
     * The instance to the navigation service which allows to switch between
     * sections, associated to a specific page. Unlike the other attributes in
     * this class, Services::nav may be null if a page has only one view.
     */
    private $nav = null;

    /**
     * A markdown parser with custom extensions.
     */
    private $parsedown = null;

    /**
     * The router instance which is used to generate valid links.
     */
    private $router = null;

    /**
     * The User input service instnce to handle user input data.
     */
    private $user_input = null;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);

        $this->router = new Router($this->db, BASE_PATH);
        $this->router->addMatchTypes(array('v' => '[A-Za-z_]+[A-Za-z_0-9]*'));
        $this->init_router_routes();

        $this->acl = new Acl($this->db);

        $this->login = new Login($this->db,
            $this->router->route['name'] !== 'login');

        $this->mail = new Mailer($this->db);

        $this->user_input = new UserInput($this->db);

        $this->parsedown = new ParsedownExtension($this->user_input,
            $this->router);
        $this->parsedown->setSafeMode(false);
    }

    /**
     * A helper function to fetch all pages from the database and add the to
     * the router service.
     */
    private function init_router_routes()
    {
        $sql = "SELECT p.protocol, p.url, a.name AS action, p.keyword FROM pages AS p
            LEFT JOIN actions AS a ON a.id = p.id_actions
            WHERE protocol IS NOT NULL";
        $pages = $this->db->query_db($sql, array());
        foreach($pages as $page)
            $this->router->map($page['protocol'], $page['url'], $page['action'],
                $page['keyword']);
        $this->router->update_route();
    }

    /**
     * Get the service class Acl.
     *
     * @retval object
     *  The Acl service class.
     */
    public function get_acl()
    {
        return $this->acl;
    }

    /**
     * Get the service class PageDb.
     *
     * @retval object
     *  The PageDb service class.
     */
    public function get_db()
    {
        return $this->db;
    }

    /**
     * Get the service class Login.
     *
     * @retval object
     *  The Login service class.
     */
    public function get_login()
    {
        return $this->login;
    }

    /**
     * Get the service class Mailer.
     *
     * @retval object
     *  The Mailer service class.
     */
    public function get_mail()
    {
        return $this->mail;
    }

    /**
     * Get the service class Navigation.
     *
     * @retval object
     *  The Navigation service class.
     */
    public function get_nav()
    {
        return $this->nav;
    }

    /**
     * Get the service class ParsdownExtension.
     *
     * @retval object
     *  The ParsedownExtension service class.
     */
    public function get_parsedown()
    {
        return $this->parsedown;
    }

    /**
     * Get the service class Router.
     *
     * @retval object
     *  The Router service class.
     */
    public function get_router()
    {
        return $this->router;
    }

    /**
     * Get the service class UserInput.
     *
     * @retval object
     *  The UserInput service class.
     */
    public function get_user_input()
    {
        return $this->user_input;
    }

    /**
     * Set the service class Navigation.
     *
     * @param object $nav
     *  The Navigation service class.
     */
    public function set_nav($nav)
    {
        $this->nav = $nav;
    }
}
?>