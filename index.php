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
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$router = new Router();

$router->get('/', 'Controllers\HomeController@index');


$router->group('/auth', function($router) {
  $router->get('/register', 'Controllers\AuthController@showRegister');
  $router->post('/register', 'Controllers\AuthController@register');

  $router->get('/verify-email', 'Controllers\AuthController@verify_email');

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

  $router->get('/user_accounts', 'Controllers\StaffController@user_accounts');
  $router->post('/users/create', 'Controllers\StaffController@addAccount');
  $router->post('/users/update/{id}', 'Controllers\StaffController@updateAccount');
  $router->get('/users/delete/{id}', 'Controllers\StaffController@deleteAccount');

  $router->get('/courses', 'Controllers\CourseController@courses');
  $router->post('/courses/create', 'Controllers\CourseController@addCourse');
  $router->post('/courses/update/{id}', 'Controllers\CourseController@updateCourse');
  $router->get('/courses/delete/{id}', 'Controllers\CourseController@deleteCourse');

  $router->get('/academic_periods', 'Controllers\AcademicPeriodController@academic_periods');
  $router->post('/academic_periods/create', 'Controllers\AcademicPeriodController@store');
  $router->post('/academic_periods/update/{id}', 'Controllers\AcademicPeriodController@update');
  $router->get('/academic_periods/delete/{id}', 'Controllers\AcademicPeriodController@delete');

  $router->get('/subjects', 'Controllers\SubjectController@subjects');
  $router->post('/subjects/create', 'Controllers\SubjectController@store');
  $router->post('/subjects/update/{id}', 'Controllers\SubjectController@update');
  $router->get('/subjects/delete/{id}', 'Controllers\SubjectController@delete');

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