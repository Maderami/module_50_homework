<?php

namespace Core\Lib;


class Router
{
    private $controller;
    private $action;
    private $model;
    private $routes = [];
    private $db;
    private $request;

    public function __construct($controller, $action, $model, $db, $request)
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->model = $model;
        $this->db = $db;
        $this->request = $request;
    }

    public function run($twig)
    {
        // контроллер и действие по умолчанию
        $controllerName = $this->getController();
        $actionName = $this->getAction() . 'Action';
        // подцепляем файл с классом контроллера
        $controllerFile = $controllerName . '.php';
        $controllerPath = __DIR__ . '/../controllers/' . $controllerFile;

        if (file_exists($controllerPath)) {
            include $controllerPath;

        } else {
            Router::ErrorPage404();
        }
        $stringClass = 'Core\\Controllers\\' . $controllerName;
        // создаем контроллер
        $controller = new $stringClass($this->db, $twig);
        $action = $actionName;

        if (method_exists($controller, $action)) {
            // вызываем действие контроллера
                $controller->$action($_GET['id']);
        } else {
            Router::ErrorPage404();
        }
    }

    private function getController()
    {
        return $this->controller;
    }

    private function getAction()
    {
        return $this->action;
    }

    static function ErrorPage404()
    {
        $host = 'http://' . $_SERVER['HTTP_HOST'] . '/';
        header('HTTP/1.1 404 Not Found');
        header("Status: 404 Not Found");
        header('Location:' . $host . '404');
    }

    public function addRoutePattern($pattern, $handler)
    {
        $this->routes[$pattern] = $handler;
    }

    public function dispatch($uri)
    {
        foreach ($this->routes as $pattern => $handler) {
            if (preg_match("#^$pattern$#", $uri, $matches)) {
                array_shift($matches);
                return call_user_func_array($handler, $matches);
            }
        }

        header("HTTP/1.0 404 Not Found");
        return "Страница не найдена";
    }

    private function getModel()
    {
        return $this->model;
    }
}