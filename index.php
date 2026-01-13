<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Repository;
use App\Core\Connection;

// Initialize DB Connection
Connection::init();

$router = new Router();

$router->get('/', 'Controllers\HomeController@index');


$router->group('/auth', function($router) {
  $router->get('/register', 'Controllers\AuthController@showRegister');
  $router->post('/register', 'Controllers\AuthController@register');
  
  $router->get('/login', 'Controllers\AuthController@showLogin');
  $router->post('/login', 'Controllers\AuthController@login');

   $router->get('/logout', 'Controllers\AuthController@logout');

  $router->get('/verify-otp', 'Controllers\AuthController@showVerifyOtp');
  $router->post('/verify-otp', 'Controllers\AuthController@verifyOtp');

  $router->get('/forgotpass', 'Controllers\AuthController@forgotpass');
  $router->post('/forgotpass', 'Controllers\AuthController@sendPasswordReset'); // New POST route

  $router->get('/reset-password', 'Controllers\AuthController@showResetPassword');
  $router->post('/reset-password', 'Controllers\AuthController@resetPassword');
});

$router->group('/staff', function($router) {
  $router->get('/dashboard', 'Controllers\StaffController@dashboard');

});

$router->group('/student', function($router) {
  $router->get('/dashboard', 'Controllers\StudentController@dashboard');

});

// Get the path and strip out any query strings (?key=value)
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If you are working in a subfolder (e.g., localhost/enrollment/), 
// you need to remove that folder name from the URL so the router only sees '/'
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$url = str_replace($scriptName, '', $url);

// Ensure it starts with / and isn't empty
$url = '/' . ltrim($url, '/');

$router->route($url);


?>