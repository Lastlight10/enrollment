<?php

namespace Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Request; // Import Request
use Models\User;
use App\Repositories\UserAccounts\UserRepository;
use Exception;

class StaffController extends Controller
{
  private $userRepo;

  public function __construct()
  {
    if (!isset($_SESSION['user_id'])) {
      $_SESSION['error'] = "Please log in to access the dashboard.";
      $this->redirect('/auth/login');
    }

    if ($_SESSION['user_type'] !== 'staff' && $_SESSION['user_type'] !== 'admin') {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/auth/login');
    }
    $this->userRepo = new UserRepository();
  }

  public function dashboard()
  {
    $this->staffView('staff/dashboard', [
      'title' => 'Staff Home',
      'user_id' => $_SESSION['user_id']
    ]);
  }

  public function user_accounts()
  {
    $users = User::where('type', '!=', 'admin')->get();
    return $this->staffView('staff/user_accounts', [
      'users' => $users,
      'title' => 'Manage Accounts'
    ]);
  }

  // FIX: Added Request $request
  public function addAccount(Request $request)
  {
    $data = $request->all(); // Use request object instead of $_POST

    if (empty($data['username']) || empty($data['email'])) {
      $_SESSION['error'] = "Username and Email are required fields.";
      return $this->redirect('/staff/user_accounts');
    }

    if ($this->userRepo->exists('email', $data['email'])) {
      $_SESSION['error'] = "The email '{$data['email']}' is already registered.";
      return $this->redirect('/staff/user_accounts');
    }
    
    if ($this->userRepo->exists('username', $data['username'])) {
      $_SESSION['error'] = "The username '{$data['username']}' is already taken.";
      return $this->redirect('/staff/user_accounts');
    }

    try {
      $token = bin2hex(random_bytes(32));
      $data['status'] = 'inactive'; 
      $data['verification_token'] = $token;

      $user = $this->userRepo->createAccount($data);
      if ($user) {
        $this->userRepo->sendVerificationEmail($user->email, $token);
        $_SESSION['success'] = "Staff account created! Verification email sent to {$user->email}.";
      }
    } catch (Exception $e) {
      Logger::log("Critical Error in addAccount: " . $e->getMessage());
      $_SESSION['error'] = "System error: Could not complete registration.";
    }

    $this->redirect('/staff/user_accounts');
  }

  // FIX: Added Request $request, $id
  public function updateAccount(Request $request, $id)
  {
    $result = $this->userRepo->updateAccount($id, $request->all());

    if ($result === 'no_changes') {
      $_SESSION['info'] = "No changes were made.";
    } elseif ($result === true || $result === 1) {
      $_SESSION['success'] = "Account updated successfully.";
    } else {
      $_SESSION['error'] = "Update failed.";
    }

    $this->redirect('/staff/user_accounts');
  }

  // FIX: Added Request $request, $id
  public function deleteAccount(Request $request, $id)
  {
    if ($id == $_SESSION['user_id']) {
      $_SESSION['error'] = "You cannot delete your own account.";
      return $this->redirect('/staff/user_accounts');
    }

    try {
      $this->userRepo->deleteAccount($id);
      $_SESSION['success'] = "Account deleted successfully.";
    } catch (Exception $e) {
      Logger::log("Delete error: " . $e->getMessage());
      $_SESSION['error'] = "Could not delete account. It may be linked to other records.";
    }
    
    $this->redirect('/staff/user_accounts');
  }
}