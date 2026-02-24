<?php
namespace Controllers;
use App\Core\Controller;

class HomeController extends Controller {
  public function index() {
    // Pass the data required by the login view
    $data = [
      'title' => 'Login - Enrollment System'
    ];
    
    // Render the login view using the default layout
    $this->view('login/login', $data);
  }
}