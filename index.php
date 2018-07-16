<?php
$_SERVER['DOCUMENT_ROOT']  = __DIR__;
session_start();
require_once "./service/router.php";
require_once "./service/globals_untracked.php";
require_once "./page/home/HomePage.php";

$router = new Router();

// map homepage
$router->setBasePath(BASE_PATH);
$router->map( 'GET', '/', function( $router ) {
    $page = new HomePage($router);
    $page->output();
}, 'home');
$router->map( 'GET', '/sitzungen', function( $router ) {
    $page = new HomePage($router);
    $page->output();
}, 'sessions');
$router->map( 'GET', '/protokolle', function( $router ) {
    $page = new HomePage($router);
    $page->output();
}, 'protocols');
$router->map( 'GET', '/kontakt', function( $router ) {
    $page = new HomePage($router);
    $page->output();
}, 'contact');
$router->map( 'GET', '/login', function( $router ) {
    $page = new HomePage($router);
    $page->output();
}, 'login');
$router->map( 'GET', '/imressum', function( $router ) {
    $page = new HomePage($router);
    $page->output();
}, 'impressum');
$router->map( 'GET', '/diaclaimer', function( $router ) {
    $page = new HomePage($router);
    $page->output();
}, 'disclaimer');
$router->map( 'GET', '/agb', function( $router ) {
    $page = new HomePage($router);
    $page->output();
}, 'agb');

// match current request url
$router->update_route();

// call closure or throw 404 status
if( $router->route && is_callable( $router->route['target'] ) ) {
    call_user_func_array( $router->route['target'], array_merge( array( $router ), $router->route['params'] ) );
} else {
    // no route was matched
    echo "Missing";
}

?>
