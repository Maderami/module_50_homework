<?php
require_once __DIR__ . '/core/lib/Autoloader.php';


// Для пространства имен core
Autoloader::addNamespace('Core', __DIR__ . '/core');
Autoloader::addNamespace('Core\Lib', __DIR__ . '/core/lib');
// Для пространства имен core\config
Autoloader::addNamespace('Core\Configs', __DIR__ . '/core/configs');

// Для пространства имен core\controllers
Autoloader::addNamespace('Core\Controllers', __DIR__ . '/core/controllers');

// Для пространства имен core\models
Autoloader::addNamespace('Core\Models', __DIR__ . '/core/models');

Autoloader::register();

use Core\Configs\Config;
use Core\Configs\DataBase;
use Core\Lib\Router;


$twig = (new Config())->getConfig();

// Подключение к БД
$db = (new DataBase)->connection();
// Маршрутизация
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$GLOBALS['request'] = $request;
$method = $_SERVER['REQUEST_METHOD'];
if(preg_match('#^/image/img_id=(\d+)$#', $request, $matches)){
    $_GET['id'] = $matches[1];
}elseif (preg_match('#^/image/img_id=(\d+)/delete$#', $request, $matches) && $method === 'POST'){
    $_GET['id'] = $matches[1];
}elseif (preg_match('#^/image/img_id=(\d+)/comment$#', $request, $matches) && $method === 'POST'){
    $_GET['id'] = $matches[1];
}elseif (preg_match('#^/comment/cmm_id=(\d+)/delete$#', $request, $matches) && $method === 'POST'){
    $_GET['id'] = $matches[1];
}


switch ($request) {
    case '/':
    case '/home':
        $routeMain = new Router('GalleryController', 'index', 'Image', $db, '');
        $routeMain->run($twig);
        break;
    case '/login':
        $routeMain = new Router('AuthController', 'login', 'User', $db, '');
        $routeMain->run($twig);
        break;
    case '/register':
        $routeMain = new Router('AuthController', 'register', 'User', $db, '');
        $routeMain->run($twig);
        break;
    case '/logout':
        $routeMain = new Router('AuthController', 'logout', 'User', $db, '');
        $routeMain->run($twig);
        session_destroy();
        break;
    case '/upload':
        $routeMain = new Router('GalleryController', 'upload', 'Image', $db, '');
        $routeMain->run($twig);
        break;
    case '/image/img_id=' . $_GET['id']:
        $routeMain = new Router('GalleryController', 'show', 'Image', $db, '');
        $routeMain->run($twig);
        break;
    case '/image/img_id=' . $_GET['id'] . '/delete':
        $routeMain = new Router('GalleryController', 'deleteImage', 'Image', $db, '');
        $routeMain->run($twig);
        break;
    case '/image/img_id=' . $_GET['id'] . '/comment':
        $routeMain = new Router('GalleryController', 'commentImage', 'Image', $db, '');
        $routeMain->run($twig);
        break;
    case '/comment/cmm_id=' . $_GET['id'] . '/delete':
        $routeMain = new Router('GalleryController', 'deleteComment', 'Image', $db, '');
        $routeMain->run($twig);
        break;
    default:

        header('HTTP/1.0 404 Not Found');
        echo $twig->render('404.twig', ['content' => $_GET['id']]);
        break;
}