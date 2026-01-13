<?php
namespace Controllers;
use App\Core\Controller;

class StaffController extends Controller {
  public function __construct() {
    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
      $_SESSION['error'] = "Please log in to access the dashboard.";
      $this->redirect('/auth/login');
    }

    // 2. Check if user is actually staff
    if ($_SESSION['user_type'] !== 'staff' && $_SESSION['user_type'] !== 'admin') {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/auth/login');
    }
  }

  public function dashboard() {
    $this->staffView('staff/dashboard', [
      'title' => 'Staff Home',
      'user_id' => $_SESSION['user_id']
    ]);
  }
}