<?php
$_SERVER['DOCUMENT_ROOT']  = __DIR__;
require_once "./service/router.php";
require_once "./service/globals_untracked.php";
require_once "./page/HomePage.php";
require_once "./page/SectionPage.php";
require_once "./page/ComponentPage.php";
require_once "./service/Login.php";

$router = new Router();

// map homepage
$router->setBasePath(BASE_PATH);

// define routing paths
$router->map( 'GET', '/', function($router) {
    $page = new HomePage($router);
    $page->output();
}, 'home');
$router->map( 'GET', '/sitzungen', 'component', 'sessions');
$router->map( 'GET', '/protokolle', 'sections', 'protocols');
$router->map( 'GET', '/kontakt', 'sections', 'contact');
$router->map( 'GET|POST', '/login', 'component', 'login');
$router->map( 'GET', '/profile', 'sections', 'profile');
$router->map( 'GET', '/impressum', 'sections', 'impressum');
$router->map( 'GET', '/disclaimer', 'sections', 'disclaimer');
$router->map( 'GET', '/agb', 'sections', 'agb');

// match current request url
$router->update_route();

// call closure or throw 404 status
if($router->route)
{
    if($router->route['target'] == "sections")
    {
        $page = new SectionPage($router, $router->route['name']);
        $page->output();
    }
    else if($router->route['target'] == "component")
    {
        $page = new ComponentPage($router, $router->route['name']);
        $page->output();
    }
    else if(is_callable($router->route['target']))
    {
        call_user_func_array($router->route['target'],
            array_merge(array($router), $router->route['params'])
        );
    }
}
else {
    // no route was matched
    $page = new SectionPage($router, 'missing');
    $page->output();
}
?>
