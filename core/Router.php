<?php
namespace App\Core;

class Router {
  private $routes = [];
  private $groupPrefix = '';

  public function group($prefix, $callback) {
    $previousPrefix = $this->groupPrefix;
    $this->groupPrefix .= $prefix;
    $callback($this); // This passes the current instance to the callback
    $this->groupPrefix = $previousPrefix;
  }

  public function get($path, $handler) {
    $this->add('GET', $path, $handler);
  }

  public function post($path, $handler) {
    $this->add('POST', $path, $handler);
  }

  public function add($method, $path, $handler) {
    $fullPath = '/' . trim($this->groupPrefix, '/') . '/' . trim($path, '/');
    
    $fullPath = preg_replace('#/+#', '/', $fullPath);

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
      $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $registeredPath);
      $pattern = "#^" . $pattern . "$#";

      if ($route['method'] === $requestMethod && preg_match($pattern, $url, $matches)) {
        array_shift($matches); 
        return $this->executeHandler($route['handler'], $matches);
      }
    }
    return $this->handle404($url);
  }

  private function executeHandler($handler, $params = []) {
    list($controllerName, $method) = explode('@', $handler);
    
    // Ensure we use the full namespace for controllers if not provided
    if (!str_contains($controllerName, 'Controllers\\')) {
        $controllerName = 'Controllers\\' . $controllerName;
    }

    if (class_exists($controllerName)) {
      $controller = new $controllerName();
      if (method_exists($controller, $method)) {
        $request = new \App\Core\Request();
        // The Request object goes first, then the URL parameters like {id}
        array_unshift($params, $request);
        return call_user_func_array([$controller, $method], $params);
      }
    }
    return $this->handle404("Class or Method not found: $controllerName@$method");
  }

  private function handle404($url) {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>The page ($url) could not be found.</p>";
  }
}