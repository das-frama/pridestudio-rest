<?php declare(strict_types=1);

use app\App;
use app\controller\HomeController;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

require "../vendor/autoload.php";

//header("Access-Control-Allow-Origin: *");
//header("Content-Type: application/json; charset=UTF-8");
//header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
//header("Access-Control-Max-Age: 3600");
//header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$router = new Router();
$router->addRoute(
    (new Route("hello", "/", ["GET"]))->addMiddleware(new HomeController)
);

(new App($router))->run(ServerRequestFactory::fromGlobals());
