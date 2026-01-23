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

  // User Management
  $router->group('/users', function($router) {
    $router->get('', 'Controllers\StaffController@user_accounts'); // /staff/users
    $router->post('/create', 'Controllers\StaffController@addAccount');
    $router->post('/update/{id}', 'Controllers\StaffController@updateAccount');
    $router->get('/delete/{id}', 'Controllers\StaffController@deleteAccount');
  });

  // Curriculum Management
  $router->group('/courses', function($router) {
    $router->get('', 'Controllers\CourseController@courses');
    $router->post('/create', 'Controllers\CourseController@addCourse');
    $router->post('/update/{id}', 'Controllers\CourseController@updateCourse');
    $router->get('/delete/{id}', 'Controllers\CourseController@deleteCourse');
  });

  $router->group('/academic_periods', function($router) {
    $router->get('', 'Controllers\AcademicPeriodController@academic_periods');
    $router->post('/create', 'Controllers\AcademicPeriodController@store');
    $router->post('/update/{id}', 'Controllers\AcademicPeriodController@update');
    $router->get('/delete/{id}', 'Controllers\AcademicPeriodController@delete');
  });

  $router->group('/subjects', function($router) {
    $router->get('', 'Controllers\SubjectController@subjects');
    $router->post('/create', 'Controllers\SubjectController@store');
    $router->post('/update/{id}', 'Controllers\SubjectController@update');
    $router->get('/delete/{id}', 'Controllers\SubjectController@delete');
  });

  // Enrollment & Billing Management
 $router->group('/enrollments', function($router) {
    $router->get('', 'Controllers\StaffEnrollmentController@enrollments');
    
    // New: Route to view specific enrollment details
    $router->get('/details/{id}', 'Controllers\StaffEnrollmentController@details');
    
    // New: Route to handle rejection with comments
    $router->post('/reject/{id}', 'Controllers\StaffEnrollmentController@reject');

    $router->get('/approve/{id}', 'Controllers\StaffEnrollmentController@showApproveForm');
    $router->post('/approve/{id}', 'Controllers\StaffEnrollmentController@approve');
    $router->post('/drop/{id}', 'Controllers\StaffEnrollmentController@drop');
    $router->post('/add-payment/{id}', 'Controllers\StaffEnrollmentController@addPayment');

    $router->post('/payments/verify/{id}', 'Controllers\StaffEnrollmentController@verifyPayment');
    $router->get('/print/pdf/{id}', 'Controllers\StaffEnrollmentController@downloadPdf');
  });

  $router->group('/curriculum', function($router) {
    $router->get('/', 'CurriculumController@index'); 
    $router->get('/manage/{courseId}', 'CurriculumController@manage');
    $router->post('/setup', 'CurriculumController@setup'); // New: Initialize curriculum
    $router->post('/add', 'CurriculumController@store');
    $router->post('/update', 'CurriculumController@update');
    $router->post('/delete', 'CurriculumController@destroy');
    $router->post('/delete-all', 'CurriculumController@wipe'); // New: Wipe all subjects
  });
});

$router->group('/student', function($router) {
  // Main Dashboard
  $router->get('/dashboard', 'Controllers\StudentController@dashboard');

  // Online Enrollment Logic
  $router->get('/enroll', 'Controllers\StudentEnrollmentController@showForm');
  $router->post('/enroll/submit', 'Controllers\StudentEnrollmentController@submit');
  
  $router->get('/enrollments', 'Controllers\StudentEnrollmentController@index');
  $router->get('/enrollment/details/{id}', 'Controllers\StudentEnrollmentController@viewDetails');
  $router->get('/enrollment/pdf/{id}', 'Controllers\StudentEnrollmentController@downloadPdf');

  $router->post('/payment/upload/{id}', 'Controllers\StudentEnrollmentController@uploadProof');

  // Other Student Actions
  $router->get('/status', 'Controllers\StudentController@status');
  $router->get('/curriculum', 'Controllers\StudentController@curriculum');

  
});


// Get the path
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Get the base directory (e.g., / or /subfolder)
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

// Only remove the base path from the START of the URL
if ($basePath !== '/' && strpos($url, $basePath) === 0) {
    $url = substr($url, strlen($basePath));
}

// Ensure the result always starts with a single / and has no trailing slash
$url = '/' . trim($url, '/');

$router->route($url);


?>