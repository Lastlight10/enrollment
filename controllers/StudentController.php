<?php
namespace Controllers;

use App\Core\Controller;
use Models\User;

class StudentController extends Controller {
  public function __construct() {
    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
      $_SESSION['error'] = "Please log in to access the dashboard.";
      $this->redirect('/auth/login');
    }

    // 2. Check if user is a student
    if ($_SESSION['user_type'] !== 'student') {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/auth/login');
    }
  }

  public function dashboard() {
    // Fetch student data to make the dashboard dynamic
    $user = User::find($_SESSION['user_id']);

    $this->studentView('student/dashboard', [
      'title' => 'Student Dashboard',
      'user_name' => $user->username,
      'status' => $user->status,
    ]);
  }
}