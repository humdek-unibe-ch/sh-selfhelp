<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
require_once "./service/router.php";
require_once "./service/globals_untracked.php";
require_once "./service/globals.php";
require_once "./service/Login.php";
require_once "./page/HomePage.php";
require_once "./page/SectionPage.php";
require_once "./page/ComponentPage.php";
require_once "./ajax/AjaxRequest.php";

/**
 * Helper function to show stacktrace also of wranings.
 */
function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
// only activate in debug mode
if(DEBUG == 1) set_error_handler("exception_error_handler");

$router = new Router();
$router->setBasePath(BASE_PATH);
$router->addMatchTypes(array('v' => '[A-Za-z_]+[A-Za-z_0-9]*'));

$db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);

// custom page creation functions
function create_login_page($router, $db)
{
    $page = new SectionPage($router, $db, "login");
    $page->disable_navigation();
    $page->output();
}
function create_home_page($router, $db)
{
    $page = new HomePage($router, $db);
    $page->output();
}
function create_request_page($router, $db, $request)
{
    $ajax = new AjaxRequest($db, $request);
    $ajax->print_json();
}

// define routing paths
$sql = "SELECT p.protocol, p.url, a.name AS action, p.keyword FROM pages AS p
    LEFT JOIN actions AS a ON a.id = p.id_actions
    WHERE protocol IS NOT NULL";
$pages = $db->query_db($sql, array());
foreach($pages as $page)
    $router->map( $page['protocol'], $page['url'], $page['action'],
        $page['keyword']);

// match current request url
$router->update_route();

// call closure or throw 404 status
if($router->route)
{
    if($router->route['target'] == "sections")
    {
        $page = new SectionPage($router, $db, $router->route['name'],
            $router->route['params']);
        $page->output();
    }
    else if($router->route['target'] == "component")
    {
        $page = new ComponentPage($router, $db, $router->route['name'], 
            $router->route['params']);
        $page->output();
    }
    else if($router->route['target'] == "custom")
    {
        $function_name = "create_" . $router->route['name'] . "_page";
        if(is_callable($function_name))
            call_user_func_array($function_name,
                array_merge(array($router, $db), $router->route['params'])
            );
        else
            throw new Exception("Cannot call custom function '$function_name'");
    }
    // log user activity on experiment pages
    $sql = "SELECT * FROM pages WHERE id_type = :id AND keyword = :key";
    if($db->query_db_first($sql,
        array(":id" => EXPERIMENT_PAGE_ID, ":key" => $router->route['name'])))
    {
        $db->insert("user_activity", array(
            "id_users" => $_SESSION['id_user'],
            "url" => $_SERVER['REQUEST_URI'],
        ));
    }
}
else {
    // no route was matched
    $page = new SectionPage($router, $db, 'missing', array());
    $page->output();
}
?>
