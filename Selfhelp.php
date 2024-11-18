<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
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
require_once "./server/cms-api/CmsApiRequest.php";

function create_exportData_page($services, $selector, $option = null, $id = null)
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

/**
 * A class that initialize SelfHelp
 */
class Selfhelp
{

    /**
     * Creating a SelfHelp Instance.
     */
    public function __construct()
    {
        $this->init();
    }

    /* Private Methods *********************************************************/

    private function init()
    {
        // load plugin globals
        $this->loadPluginGlobals();
        // only activate in debug mode
        if (DEBUG == 1) {
            set_error_handler("exception_error_handler");
        }
        if (defined('CORS') && CORS) {
            $this->cors();
        }
        $services = new Services();
        if (isset($_POST['mobile']) && $_POST['mobile']) {
            $this->mobile_call($services);
        } else {
            $this->web_call($services);
        }
    }

    /**
     * Load plugins globals
     */
    private function loadPluginGlobals()
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
     * Enable calls with cors for mobile and mobile preview
     */
    private function cors()
    {

        // Allow from any origin
        if (
            (isset($_SERVER['HTTP_ORIGIN']) &&
                (
                    strpos($_SERVER['HTTP_ORIGIN'], 'http://localhost:4200') !== false || // used for testing
                    strpos($_SERVER['HTTP_ORIGIN'], 'https://localhost:8100') !== false || // used for testing
                    strpos($_SERVER['HTTP_ORIGIN'], 'http://localhost:8100') !== false || // used for testing
                    strpos($_SERVER['HTTP_ORIGIN'], 'http://192.168.0.58') !== false || // used for testing
                    strpos($_SERVER['HTTP_ORIGIN'], 'https://192.168.0.58') !== false || // used for testing
                    strpos($_SERVER['HTTP_ORIGIN'], 'http://192.168.0.58:8100') !== false || // used for testing
                    strpos($_SERVER['HTTP_ORIGIN'], 'https://192.168.0.58:8100') !== false || // used for testing
                    strpos($_SERVER['HTTP_ORIGIN'], 'https://tpf-test.humdek.unibe.ch') !== false ||
                    strpos($_SERVER['HTTP_ORIGIN'], 'https://selfhelp.philhum.unibe.ch') !== false)
            ) ||
            isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'http://localhost:4200') !== false
        ) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            if (isset($_SERVER['HTTP_ORIGIN'])) {
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            }
            if (isset($_SERVER['HTTP_REFERER'])) {
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_REFERER']}");
            }
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

    // custom page creation functions
    private function create_request_page($services, $class_name, $method_name, $keyword = null)
    {
        $ajax = new AjaxRequest($services, $class_name, $method_name, $keyword);
        $ajax->print_json();
    }

    // custom page creation functions
    private function create_cms_api_request_page($services, $class_name, $method_name, $keyword = null)
    {
        $class_name = ucfirst($class_name) . 'CmsApi';
        $method_name = $_SERVER['REQUEST_METHOD'] . '_' . $method_name;
        $cmsApi = new CmsApiRequest($services, $class_name, $method_name, $keyword);
        $cmsApi->return_response();
    }

    /**
     * Catch mobile calls and output json
     * @param object $services
     * All the services
     */
    function mobile_call($services)
    {
        $router = $services->get_router();
        $db = $services->get_db();
        $debug_start_time = microtime(true);
        if (isset($_POST['id_languages']) && $_POST['id_languages'] != '') {
            $_SESSION['user_language'] = $_POST['id_languages'];
            $_SESSION['language'] = $_POST['id_languages'];
            if (!$_SESSION['language']) {
                $_SESSION['language'] = $_SESSION['default_language_id'];
            }
            if (isset($_SESSION['id_user']) && isset($_SESSION['user_language']) && $_SESSION['user_language'] != '') {
                $db->update_by_ids('users', array("id_languages" => $_SESSION['user_language']), array('id' => $_SESSION['id_user'])); // set the language in the user table
            }
        }
        $res = [];
        if ($router->route) {
            if ($router->route['target'] == "sections") {
                $start_time = microtime(true);
                $page = new SectionPage(
                    $services,
                    $router->route['name'],
                    $router->route['params']
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
            } else if ($router->route['target'] == "component") {
                $page = new ComponentPage(
                    $services,
                    $router->route['name'],
                    $router->route['params']
                );
                $page->output();
            } else if ($router->route['target'] == PAGE_ACTION_BACKEND) {
                $function_name = "create_" . $router->route['name'] . "_page";
                if (is_callable($function_name)) {
                    call_user_func_array($function_name, array_merge(array("services" => $services), $router->route['params']));
                } else {
                    throw new Exception("Cannot call custom function '$function_name'");
                }
            } else if ($router->route['target'] == PAGE_ACTION_AJAX) {
                $this->create_request_page($services, $router->route['params']['class'], $router->route['params']['method'], $router->route['name']);
            } else if ($router->route['target'] == PAGE_ACTION_CMS_API) {
                $this->create_cms_api_request_page($services, $router->route['params']['class'], $router->route['params']['method'], $router->route['name']);
            }
            // log user activity 
            $router->log_user_activity($debug_start_time, true);
        } else {
            // no route was matched
            $page = new SectionPage($services, 'missing', array());
            $page->output();
        }
    }

    /**
     * Catch web calls and output the page
     * @param object $services
     * All the services
     */
    private function web_call($services)
    {
        // call closure or throw 404 status
        $router = $services->get_router();
        $debug_start_time = microtime(true);
        if ($router->route) {
            if ($router->route['target'] == "sections") {
                $page = new SectionPage(
                    $services,
                    $router->route['name'],
                    $router->route['params']
                );
                $page->output();
            } else if ($router->route['target'] == "component") {
                $page = new ComponentPage(
                    $services,
                    $router->route['name'],
                    $router->route['params']
                );
                $page->output();
            } else if ($router->route['target'] == PAGE_ACTION_BACKEND) {
                $function_name = "create_" . $router->route['name'] . "_page";
                if (is_callable($function_name)) {
                    call_user_func_array($function_name, array_merge(array("services" => $services), $router->route['params']));
                } else {
                    $page = new SectionPage($services, 'missing', array());
                    $page->output();
                    throw new Exception("Cannot call custom function '$function_name'");
                }
            } else if ($router->route['target'] == PAGE_ACTION_AJAX) {
                $this->create_request_page($services, $router->route['params']['class'], $router->route['params']['method'], $router->route['name']);
            } else if ($router->route['target'] == PAGE_ACTION_CMS_API) {
                $this->create_cms_api_request_page($services, $router->route['params']['class'], $router->route['params']['method'], $router->route['name']);
            }
            // log user activity
            $router->log_user_activity($debug_start_time);
            $router->get_other_users_editing_this_page();
        } else {
            // no route was matched
            $page = new SectionPage($services, 'missing', array());
            $page->output();
        }
    }


    /* Public Methods *********************************************************/
}
?>
