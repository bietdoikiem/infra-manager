<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use App\Utils\ServerLogger;
// use ArgumentsResolver\InDepthArgumentsResolver;
use ArgumentsResolver\NamedArgumentsResolver;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

/**
 * Define STDIN, STDOUT and STDERR stream output for PHP built-in web server
 * And
 * Some configurations for PHP built-in web server
 */
if (!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'rb'));
if (!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'wb'));
if (!defined('STDERR')) define('STDERR', fopen('php://stderr', 'wb'));

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
  /* Project page */
  $r->addRoute('GET', '/', 'App\Controllers\ProjectController/index');
  $r->addRoute('GET', '/projects', 'App\Controllers\ProjectController/index');
  $r->addRoute('POST', '/projects/add', 'App\Controllers\ProjectController/add');
  $r->addRoute('POST', '/projects/edit', 'App\Controllers\ProjectController/edit');
  $r->addRoute('POST', '/projects/delete', 'App\Controllers\ProjectController/delete');
  /* BigQuery page */
  $r->addRoute('GET', '/bigquery', 'App\Controllers\ProjectController/bigquery_index');
  $r->addRoute('GET', '/bigquery/search', 'App\Controllers\ProjectController/search');
  /* Statistics page */
  $r->addRoute('GET', '/stats', 'App\Controllers\StatsController/index');
});

$container = new DI\Container();


// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
  $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
  case FastRoute\Dispatcher::NOT_FOUND:
    // ... 404 Not found
    call_user_func(array($container->get("App\Controllers\ErrorController"), 'error'));
    break;
  case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
    $allowedMethods = $routeInfo[1];
    // ... 405 Method Not Allowed
    call_user_func(array($container->get("App\Controllers\ErrorController"), 'error'));
    break;
  case FastRoute\Dispatcher::FOUND:
    $handler = $routeInfo[1];
    $vars = ($httpMethod == 'POST') ? array_map([App\Utils\FilterUtils::class, 'mask_empty'], $_POST) : $routeInfo[2];
    list($class, $method) = explode("/", $handler, 2);
    if (isset($_GET['page']) && isset($_GET['size'])) {
      $vars = $_GET;
    } else if (isset($_GET['keyword']) && isset($_GET['country'])) {
      $vars = $_GET;
      ServerLogger::log("=> Route Vars:", $vars);
      ServerLogger::log("Hello SEARCH");
    }
    ServerLogger::log("=> Route Vars:", $vars);
    call_user_func_array(array($container->get($class), $method), (new NamedArgumentsResolver([$container->get($class), $method]))->resolve($vars));
    break;
}
