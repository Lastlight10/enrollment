<?php
namespace App\Core;

class Router {
    private $routes = [];
    private $groupPrefix = ''; // Tracks the current group prefix

    public function group($prefix, $callback) {
        // Store the old prefix to allow for nested groups
        $previousPrefix = $this->groupPrefix;
        $this->groupPrefix .= $prefix;

        // Execute the callback (where $router->get/post will be called)
        $callback($this);

        // Reset the prefix back to what it was
        $this->groupPrefix = $previousPrefix;
    }

    public function get($path, $handler) {
        $this->add('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->add('POST', $path, $handler);
}
    public function add($method, $path, $handler) {
        // Combine prefix and path, then clean up double slashes
        $fullPath = $this->groupPrefix . $path;
        $fullPath = '/' . ltrim($fullPath, '/'); 

        $this->routes[] = [
            'method' => strtoupper($method),
            'path'   => $fullPath,
            'handler' => $handler
        ];
        }

    public function route($url) {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $url = '/' . trim(parse_url($url, PHP_URL_PATH), '/'); 

        foreach ($this->routes as $route) {
            $registeredPath = '/' . trim($route['path'], '/');

            // 1. Convert {id} or {any} placeholders into Regex patterns
            // This converts "/staff/users/update/{id}" into "#^/staff/users/update/([^/]+)$#"
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $registeredPath);
            $pattern = "#^" . $pattern . "$#";

            if ($route['method'] === $requestMethod && preg_match($pattern, $url, $matches)) {
                // Remove the first match (the full URL) to keep only the captured parameters
                array_shift($matches); 
                return $this->executeHandler($route['handler'], $matches);
            }
        }

        return $this->handle404($url);
    }

    private function executeHandler($handler, $params = []) {
        list($controllerName, $method) = explode('@', $handler);
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $method)) {
                // call_user_func_array allows us to pass the $id from the URL 
                // directly into the controller's method arguments
                return call_user_func_array([$controller, $method], $params);
            }
        }
        return $this->handle404("Class or Method not found: $controllerName@$method");
    }
    

    private function handle404($url) {
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>The page ($url) could not be found.</p>";
        Logger::log("ROUTING ERROR: 404 Not Found for $url");
    }
}