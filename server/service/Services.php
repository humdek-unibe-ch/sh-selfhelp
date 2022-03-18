<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/globals.php";
require_once __DIR__ . "/Acl.php";
require_once __DIR__ . "/PageDb.php";
require_once __DIR__ . "/Login.php";
require_once __DIR__ . "/jobs/Mailer.php";
require_once __DIR__ . "/Navigation.php";
require_once __DIR__ . "/ParsedownExtension.php";
require_once __DIR__ . "/Router.php";
require_once __DIR__ . "/UserInput.php";
require_once __DIR__ . "/Transaction.php";
require_once __DIR__ . "/JobScheduler.php";
require_once __DIR__ . "/conditions/Condition.php";

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
     * An instance of the transaction class used for loging.
     */
    private $transaction = null;

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
     * The JobSheduler service instnce to handle jobs scheduling and execution.
     */
    private $job_scheduler;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);

        $this->router = new Router($this->db, BASE_PATH);
        $this->router->addMatchTypes(array('v' => '[A-Za-z_]+[A-Za-z_0-9]*'));
        $this->init_router_routes();

        $this->login = new Login($this->db,
            $this->is_experimenter_page($this->router->route['name']),
            $this->does_redirect($this->router->route['name']));        

        $this->acl = new Acl($this->db);

        $this->transaction = new Transaction($this->db);

        $this->user_input = new UserInput($this->db);

        $this->condition = new Condition($this->db, $this->user_input, $this->router);

        $mail = new Mailer($this->db, $this->transaction, $this->user_input, $this->router, $this->condition);        

        $this->job_scheduler = new JobScheduler($this->db, $this->transaction, $mail, $this->condition);
        

        $this->parsedown = new ParsedownExtension($this->user_input,
            $this->router);
        $this->parsedown->setSafeMode(false);
    }

    /**
     * Checks wether the current page should enable a login redirect.
     *
     * @param string $keyword
     *  The keyword of the page to check.
     * @retval bool
     *  True if the redirec is enabled, false otherwise.
     */
    private function does_redirect($keyword)
    {
        if(defined('REDIRECT_ON_LOGIN') && !REDIRECT_ON_LOGIN)
            return false;
        return !$this->is_login_page($keyword)
            && !$this->is_script_page($keyword)
            && !$this->is_open_page($keyword);
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
     * Checks wether the current page is an open page.
     *
     * @param string $keyword
     *  The keyword of the page to check.
     * @retval bool
     *  True if the page is an open page, false otherwise.
     */
    private function is_open_page($keyword)
    {
        $sql = "SELECT * FROM pages WHERE keyword = :kw AND id_type = :type";
        $res = $this->db->query_db_first($sql, array(':kw' => $keyword,
            ':type' => OPEN_PAGE_ID));
        if($res)
            return true;
        return false;
    }

    /**
     * Checks wether the current page is a page executing a script.
     *
     * @param string $keyword
     *  The keyword of the page to check.
     * @retval bool
     *  True if the page is a script page, false otherwise.
     */
    private function is_script_page($keyword)
    {
        if($keyword === "request" || $keyword === "callback")
            return true;
        return false;
    }

    /**
     * Checks wether the current page is an experimenter page.
     *
     * @param string $keyword
     *  The keyword of the page to check.
     * @retval bool
     *  True if the page is an experimenter page, false otherwise.
     */
    private function is_experimenter_page($keyword)
    {
        $sql = "SELECT * FROM pages WHERE keyword = :kw AND id_type = :type";
        $res = $this->db->query_db_first($sql, array(':kw' => $keyword,
            ':type' => EXPERIMENT_PAGE_ID));
        if($res)
            return true;
        return false;
    }

    /**
     * Checks wether the current page is the login page.
     *
     * @param string $keyword
     *  The keyword of the page to check.
     * @retval bool
     *  True if the page is the login page, false otherwise.
     */
    private function is_login_page($keyword)
    {
        if($keyword === "login" || $keyword === "logout")
            return true;
        return false;
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
     * Get the service class Transaction.
     *
     * @retval object
     *  The Transaction service class.
     */
    public function get_transaction()
    {
        return $this->transaction;
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
     * Get the service class JobScheduler.
     *
     * @retval object
     *  The JobScheduler service class.
     */
    public function get_job_scheduler()
    {
        return $this->job_scheduler;
    }

    /**
     * Get the service class Condition.
     *
     * @retval object
     *  The Condition service class.
     */
    public function get_condition()
    {
        return $this->condition;
    }

    /**
     * Checks whether the current page is a page redirected base page.
     *
     * @param string $keyword
     *  The keyword of the page to check.
     * @retval bool
     *  True if the page is a script page, false otherwise.
     */
    public function is_redirected_page($keyword)
    {
        if($keyword === "missing"
                || $keyword === "no_access"
                || $keyword === "no_access_guest")
            return true;
        return false;
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
