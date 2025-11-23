<?php
// FILE: /app/core/Router.php

class Router {
    private $routes = [];
    private $apiRoutes = [];

    public function get($path, $controller, $action) {
        $this->addRoute('GET', $path, $controller, $action);
    }

    public function post($path, $controller, $action) {
        $this->addRoute('POST', $path, $controller, $action);
    }

    public function apiGet($path, $controller, $action) {
        $this->addApiRoute('GET', $path, $controller, $action);
    }

    public function apiPost($path, $controller, $action) {
        $this->addApiRoute('POST', $path, $controller, $action);
    }

    private function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    private function addApiRoute($method, $path, $controller, $action) {
        $this->apiRoutes[] = [
            'method' => $method,
            'path' => '/api' . $path,
            'controller' => $controller,
            'action' => $action,
            'isApi' => true
        ];
    }

    public function dispatch() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Remove base path if exists
        if (defined('BASE_PATH') && BASE_PATH !== '/') {
            $requestUri = str_replace(BASE_PATH, '', $requestUri);
        }

        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') {
            $requestUri = '/';
        }

        // Merge all routes
        $allRoutes = array_merge($this->routes, $this->apiRoutes);

        foreach ($allRoutes as $route) {
            $pattern = $this->convertToRegex($route['path']);

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match

                $controllerName = $route['controller'];
                $actionName = $route['action'];

                $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

                if (!file_exists($controllerFile)) {
                    http_response_code(500);
                    die("Controller not found: {$controllerName}");
                }

                require_once $controllerFile;

                if (!class_exists($controllerName)) {
                    http_response_code(500);
                    die("Controller class not found: {$controllerName}");
                }

                $controller = new $controllerName();

                if (!method_exists($controller, $actionName)) {
                    http_response_code(500);
                    die("Action not found: {$actionName}");
                }

                call_user_func_array([$controller, $actionName], $matches);
                return;
            }
        }

        // No route found
        http_response_code(404);
        if (strpos($requestUri, '/api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Endpoint not found', 'code' => 404]);
        } else {
            $view = new View();
            $view->render('errors/404', ['message' => 'Page not found']);
        }
    }

    private function convertToRegex($path) {
        // Convert /path/:id to regex
        $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
