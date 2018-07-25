<?php
$_SERVER['DOCUMENT_ROOT']  = __DIR__;
require_once "./service/router.php";
require_once "./service/globals_untracked.php";
require_once "./service/Login.php";
require_once "./page/HomePage.php";
require_once "./page/LoginPage.php";
require_once "./page/SectionPage.php";
require_once "./page/SessionPage.php";
require_once "./page/ComponentPage.php";

$router = new Router();
$router->setBasePath(BASE_PATH);

$db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);

// custom page creation functions
function create_session_page($router, $db, $id)
{
    $page = new SessionPage($router, $db, intval($id));
    $page->output();
}
function create_login_page($router, $db)
{
    $page = new LoginPage($router, $db);
    $page->output();
}
function create_home_page($router, $db)
{
    $page = new HomePage($router, $db);
    $page->output();
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
        $page = new SectionPage($router, $db, $router->route['name']);
        $page->output();
    }
    else if($router->route['target'] == "component")
    {
        $page = new ComponentPage($router, $db, $router->route['name']);
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
}
else {
    // no route was matched
    $page = new SectionPage($router, $db, 'missing');
    $page->output();
}
?>
