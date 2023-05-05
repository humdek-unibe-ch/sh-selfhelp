<?php 
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
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

if(defined('SHOW_PHP_INFO') && SHOW_PHP_INFO){
    echo phpinfo();
    return;
}

// load plugin globals
loadPluginGlobals();

if(defined('CORS') && CORS){
    cors();
}

/**
 * Load plugins globals
 */
function loadPluginGlobals()
{
    if ($handle = opendir(PLUGIN_SERVER_PATH)) {
        while (false !== ($dir = readdir($handle))) {
            if (filetype(PLUGIN_SERVER_PATH . '/' . $dir) == "dir") {
                $plugin_path = __DIR__ . '/server/plugins/' . $dir . '/server/service/';
                if (file_exists($plugin_path .  "globals.php")) {
                    require_once $plugin_path . "globals.php";
                }
            }
        }
    }
}

/**
 * Helper function to show stacktrace also of warnings.
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
function create_request_page($services, $class_name, $method_name, $keyword = null)
{
    $ajax = new AjaxRequest($services, $class_name, $method_name, $keyword);
    $ajax->print_json();
}
function create_exportData_page($services, $selector, $option=null, $id=null)
{
    $page = new ExportPage($services);
    $page->output($selector, $option, $id);
}

// create callback request
function create_callback_page($services, $class, $method)
{
    $callback = new CallbackRequest($services, $class, $method);
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
    $debug_start_time = microtime(true);
    $_SESSION['user_language'] = isset($_POST['id_languages']) && $_POST['id_languages'] > 1 ? $_POST['id_languages'] : LANGUAGE;
    $_SESSION['language'] = isset($_POST['id_languages']) && $_POST['id_languages'] > 1 ? $_POST['id_languages'] : LANGUAGE;
    if (isset($_SESSION['id_user'])) {
        $db->update_by_ids('users', array("id_languages" => $_SESSION['user_language']), array('id' => $_SESSION['id_user'])); // set the language in the user table
    }
    $res = [];
    if($router->route)
    {
        if ($router->route['target'] == "sections") {
            $start_time = microtime(true);
            $page = new SectionPage(
                $services,
                $router->route['name'],
                $router->route['params'],
                true
            );            
            $start_date = date("Y-m-d H:i:s");
            $res = $page->output_base_content_mobile();
            if (isset($res['navigation'])) {
                $res['navigation'] = array_values($res['navigation']);
                $adminIndex = array_search('admin-link', array_column($res['navigation'], 'keyword'));
                if ($adminIndex) {
                    unset($res['navigation'][$adminIndex]); //remove the admin tab if it is returned in the navigation
                }
            }
            if (isset($res['content'])) {
                $res['content'] = array_values($res['content']);
            }
            $end_time = microtime(true);
            $res['time'] = [];
            $res['time']['exec_time'] = $end_time - $start_time;
            $res['time']['start_date'] = $start_date;                        
            $res['logged_in'] = $_SESSION['logged_in'];
            $res['base_path'] = BASE_PATH;
            $res['default_language_id'] = LANGUAGE;
            $res['user_language'] = $_SESSION['user_language'];
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        }
        else if($router->route['target'] == "component")
        {
            $page = new ComponentPage($services, $router->route['name'],
                $router->route['params']);
            $page->output();
        } else if ($router->route['target'] == PAGE_ACTION_BACKEND) {
            $function_name = "create_" . $router->route['name'] . "_page";
            if (is_callable($function_name)) {
                call_user_func_array($function_name, array_merge(array("services"=>$services), $router->route['params']));                
            } else {
                throw new Exception("Cannot call custom function '$function_name'");
            }
        } else if ($router->route['target'] == "ajax") {
            create_request_page($services, $router->route['params']['class'], $router->route['params']['method'], $router->route['name']);
        }
        // log user activity 
        $router->log_user_activity($debug_start_time, true);
    }
    else {
        // no route was matched
        $page = new SectionPage($services, 'missing', array());
        $page->output();
    }
}

function web_call($services, $router, $db){
    // call closure or throw 404 status
    $debug_start_time = microtime(true);    
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
        } else if ($router->route['target'] == PAGE_ACTION_BACKEND) {
            $function_name = "create_" . $router->route['name'] . "_page";
            if (is_callable($function_name)) {
                call_user_func_array($function_name, array_merge(array("services"=>$services), $router->route['params']));                
            } else {                
                $page = new SectionPage($services, 'missing', array());
                $page->output();                
                throw new Exception("Cannot call custom function '$function_name'");
            }
        } else if ($router->route['target'] == "ajax") {
            create_request_page($services, $router->route['params']['class'], $router->route['params']['method'], $router->route['name']);
        }
        // log user activity
        $router->log_user_activity($debug_start_time);
        
    }
    else {
        // no route was matched
        $page = new SectionPage($services, 'missing', array());
        $page->output();
    }
}

function cors() {

    // Allow from any origin
    if (
        isset($_SERVER['HTTP_ORIGIN']) &&
        (strpos($_SERVER['HTTP_ORIGIN'],'https://46.126.153.11') !== false ||
        strpos($_SERVER['HTTP_ORIGIN'], 'https://tpf-test.humdek.unibe.ch') !== false ||
        strpos($_SERVER['HTTP_ORIGIN'], 'https://selfhelp.philhum.unibe.ch') !== false)
    ) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Methods: GET, POST');
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
