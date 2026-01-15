<?php
namespace Controllers;
use App\Core\Controller;
use App\Core\Logger;
use Models\User;
use App\Repositories\UserAccounts\UserRepository;
use Error;
use Exception;

class StaffController extends Controller {
  private $userRepo;
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
    $this->userRepo = new UserRepository();
  }

  public function dashboard() {
    $this->staffView('staff/dashboard', [
      'title' => 'Staff Home',
      'user_id' => $_SESSION['user_id']
    ]);
  }
  public function user_accounts() {
    $users = User::where('type', '!=', 'admin')->get();
    return $this->staffView('staff/user_accounts', [
      'users' => $users,
      'title' => 'Manage Accounts'
    ]);
  }

  public function addAccount() {
    $data = $_POST;

    // 1. Specific Validation
    if (empty($data['username']) || empty($data['email'])) {
      $_SESSION['error'] = "Username and Email are required fields.";
      return $this->redirect('/staff/user_accounts');
    }

    // 2. Check for Duplicates before trying to save
    if ($this->userRepo->exists('email', $data['email'])) {
      $_SESSION['error'] = "The email '{$data['email']}' is already registered.";
      return $this->redirect('/staff/user_accounts');
    }
    
    if ($this->userRepo->exists('username', $data['username'])) {
      $_SESSION['error'] = "The username '{$data['username']}' is already taken.";
      return $this->redirect('/staff/user_accounts');
    }

    try {
      // 3. Prepare verification data
      $token = bin2hex(random_bytes(32));
      $data['status'] = 'inactive'; 
      $data['verification_token'] = $token;

      // 4. Create and Email
      $user = $this->userRepo->createAccount($data);
      if ($user) {
        $this->userRepo->sendVerificationEmail($user->email, $token);
        $_SESSION['success'] = "Staff account created! A verification email has been sent to {$user->email}.";
      }
    } catch (Exception $e) {
      Logger::log("Critical Error in addAccount: " . $e->getMessage());
      $_SESSION['error'] = "System error: Could not complete registration.";
    }

    $this->redirect('/staff/user_accounts');
  }

  public function updateAccount($id) {
    $result = $this->userRepo->updateAccount($id, $_POST);

    if ($result === 'no_changes') {
        $_SESSION['info'] = "No changes were made.";
    } elseif ($result === true) {
        $_SESSION['success'] = "Account updated successfully.";
    } else {
        $_SESSION['error'] = "Update failed.";
    }

    $this->redirect('/staff/user_accounts');
  }

  public function deleteAccount($id) {
    // Prevent self-deletion
    if ($id == $_SESSION['user_id']) {
      $_SESSION['error'] = "You cannot delete your own account.";
      return $this->redirect('/staff/user_accounts');
    }

    $this->userRepo->deleteAccount($id);
    $_SESSION['success'] = "Account deleted successfully.";
    $this->redirect('/staff/user_accounts');
  }
}