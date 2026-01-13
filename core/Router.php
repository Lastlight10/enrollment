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
        
        // Strip query strings and ensure there is a leading slash
        $url = parse_url($url, PHP_URL_PATH);
        $url = '/' . trim($url, '/'); 

        foreach ($this->routes as $route) {
            // Also clean the registered route path for a fair comparison
            $registeredPath = '/' . trim($route['path'], '/');

            if ($registeredPath === $url && $route['method'] === $requestMethod) {
            return $this->executeHandler($route['handler']);
            }
        }

        return $this->handle404($url);
    }

    private function executeHandler($handler) {
        list($controllerName, $method) = explode('@', $handler);
        
        // Remove "App\\Controllers\\" if you are already providing "Controllers\" in index.php
        $fullControllerPath = $controllerName; 

        if (class_exists($fullControllerPath)) {
            $controller = new $fullControllerPath();
            if (method_exists($controller, $method)) {
            return $controller->$method();
            }
        }
        return $this->handle404("Class or Method not found: $fullControllerPath@$method");
    }
    

    private function handle404($url) {
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>The page ($url) could not be found.</p>";
        Logger::log("ROUTING ERROR: 404 Not Found for $url");
    }
}