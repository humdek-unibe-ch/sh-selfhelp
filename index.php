<?php  
// header('Access-Control-Allow-Origin: http://localhost:8100');
// header("Access-Control-Allow-Credentials: true");

cors();

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
require_once "./server/callback/CallbackRequest.php";

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
function create_exportData_page($services, $select, $option=null, $id=null)
{
    $page = new ExportPage($services);
    $page->output($select, $option, $id);
}

// create callback request
function create_callback_page($services, $class_name, $method_name)
{
    $callback = new CallbackRequest($services, $class_name, $method_name);
    $callback->print_json();
}

$router = $services->get_router();
$db = $services->get_db();

if (isset($_POST['mobile']) && $_POST['mobile']) {
    mobile_call($services, $router, $db);    
} else {
    web_call($services, $router, $db);
}

function mobile_call($services, $router, $db){
    $_SESSION['mobile'] = [];
    $res = [];
    if($router->route)
    {
        if ($router->route['target'] == "sections") {
            $page = new SectionPage(
                $services,
                $router->route['name'],
                $router->route['params'],
                true
            );
            $start_time = microtime(true);
            $start_date = date("Y-m-d H:i:s");
            $res = $page->output_base_content_mobile();
            $res['navigation'] = array_values($res['navigation']);
            if (isset($res['content'])) {
                $res['content'] = array_values($res['content']);
            }
            $end_time = microtime(true);
            $res['time'] = [];
            $res['time']['exec_time'] = $end_time - $start_time;
            $res['time']['start_date'] = $start_date;
            $adminIndex = array_search('admin-link', array_column($res['navigation'], 'keyword'));
            if($adminIndex){
                unset($res['navigation'][$adminIndex]); //remove the admin tab if it is returned in the navigation
            }
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
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
            //if transaction logs work as expected this should be removed
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
}

function web_call($services, $router, $db){
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
            //if transaction logs work as expected this should be removed
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
}

function cors() {
    
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
        exit(0);
    }
    
}

?>
