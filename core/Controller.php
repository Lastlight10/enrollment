<?php

namespace App\Core;

abstract class Controller
{
  protected function view($view, $data = [], $layout = 'default')
  {
    extract($data);

    // This correctly goes up from /core to the root /enrollment
    $root = dirname(__DIR__, 1); 
    
    // Check if we are in App/Core or just Core
    // If your file is in C:\project\core\Controller.php, use dirname(__DIR__, 1)
    // If your file is in C:\project\App\Core\Controller.php, use dirname(__DIR__, 2)
    $projectRoot = realpath(dirname(__FILE__) . '/../');

    $viewPath = $projectRoot . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
    $layoutPath = $projectRoot . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';

    if (!file_exists($viewPath)) {
      die("View file not found at: " . $viewPath);
    }

    ob_start();
    require_once $viewPath;
    $content = ob_get_clean();

    if (!file_exists($layoutPath)) {
      die("Layout file not found at: " . $layoutPath);
    }

    require_once $layoutPath;
  }
  protected function staffView($view, $data = []) {
    $this->view($view, $data, 'staff');
  }
  protected function studentView($view, $data = []) {
    $this->view($view, $data, 'student');
  }

  protected function input($key, $default = null)
  {
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return $value !== null ? htmlspecialchars(trim($value)) : null;
  }

  /**
   * Simple redirect helper
   */
  protected function redirect($url)
  {
    header("Location: {$url}");
    exit();
  }
  
}