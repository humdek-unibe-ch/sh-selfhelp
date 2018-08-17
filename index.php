<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
require_once "./service/router.php";
require_once "./service/globals_untracked.php";
require_once "./service/Login.php";
require_once "./page/HomePage.php";
require_once "./page/SectionPage.php";
require_once "./page/ComponentPage.php";
require_once "./page/CmsPage.php";
require_once "./ajax/AjaxRequest.php";

$router = new Router();
$router->setBasePath(BASE_PATH);

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
function create_cms_select_page($router, $db, $id_page, $id_root_section=null,
    $id_section=null)
{
    $page = new CmsPage($router, $db, "cms_select", intval($id_page),
        "select", intval($id_root_section), intval($id_section));
    $page->output();
}
function create_cms_insert_page($router, $db, $id_page, $id_root_section=null,
    $id_section=null)
{
    $page = new CmsPage($router, $db, "cms_insert", intval($id_page),
        "insert", intval($id_root_section), intval($id_section));
    $page->output();
}
function create_cms_update_page($router, $db, $id_page, $id_root_section=null,
    $id_section=null)
{
    $page = new CmsPage($router, $db, "cms_update", intval($id_page),
        "update", intval($id_root_section), intval($id_section));
    $page->output();
}
function create_cms_delete_page($router, $db, $id_page, $id_root_section=null,
    $id_section=null)
{
    $page = new CmsPage($router, $db, "cms_delete", intval($id_page),
        "delete", intval($id_root_section), intval($id_section));
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
        $id = null;
        if(array_key_exists('id', $router->route['params']))
            $id = $router->route['params']['id'];
        $page = new SectionPage($router, $db, $router->route['name'], $id);
        $page->output();
    }
    else if($router->route['target'] == "component")
    {
        $id = null;
        if(array_key_exists('id', $router->route['params']))
            $id = $router->route['params']['id'];
        $page = new ComponentPage($router, $db, $router->route['name'], $id);
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
