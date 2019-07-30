<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
require_once "./server/service/Services.php";
require_once "./server/service/Router.php";
require_once "./server/service/PageDb.php";
require_once "./server/service/globals_untracked.php";
require_once "./server/service/globals.php";
require_once "./server/page/ExportPage.php";
require_once "./server/page/SectionPage.php";
require_once "./server/page/ComponentPage.php";
require_once "./server/ajax/AjaxRequest.php";

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

$services = new Services();

// custom page creation functions
function create_request_page($services, $class_name, $method_name)
{
    $ajax = new AjaxRequest($services, $class_name, $method_name);
    $ajax->print_json();
}
function create_exportData_page($services, $select)
{
    $page = new ExportPage($services);
    $page->output($select);
}

$router = $services->get_router();
$db = $services->get_db();

// call closure or throw 404 status
if($router->route)
{
    if($router->route['target'] == "sections")
    {
        $page = new SectionPage($services, $router->route['name'],
            $router->route['params']);
        $page->output();
    }
    else if($router->route['target'] == "component")
    {
        $page = new ComponentPage($services, $router->route['name'],
            $router->route['params']);
        $page->output();
    }
    else if($router->route['target'] == "custom")
    {
        $function_name = "create_" . $router->route['name'] . "_page";
        if(is_callable($function_name))
            call_user_func_array($function_name,
                array_merge(array($services), $router->route['params'])
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
    $page = new SectionPage($services, 'missing', array());
    $page->output();
}
?>
