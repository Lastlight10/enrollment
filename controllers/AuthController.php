<?php

namespace Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Repositories\UserAccounts\UserRepository;
use Models\User;

class AuthController extends Controller
{
  protected $userRepo;

  public function __construct()
  {
    $this->userRepo = new UserRepository();
  }

  /**
 * Handle Logout Logic
 */
public function logout()
{
    // 1. Clear all session variables
    $_SESSION = [];

    // 2. Destroy the session cookie if it exists
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // 3. Destroy the session on the server
    session_destroy();

    // 4. Start a fresh session to show a success message
    session_start();
    $_SESSION['success'] = "You have been successfully logged out.";

    // 5. Redirect to login page
    $this->redirect('/auth/login');
}

  /**
   * Display the Login View
   */
  public function showLogin()
  {
    $this->view('login/login', ['title' => 'Login'], 'default');
  }

  /**
   * Handle Login Logic (POST)
   */
  public function login()
  {
    $identifier = $this->input('username');
    $password = $this->input('password');

    $user = $this->userRepo->findByCredentials($identifier);
  
    // password_verify handles the BCrypt comparison
    if ($user && password_verify($password, $user->password)) {
      if ($user->status !== 'active') {
        return $this->view('login/login', ['error' => 'Account not verified.'], 'default');
      }

      $_SESSION['user_id'] = $user->id;
      $_SESSION['user_type'] = $user->type;

      Logger::log("LOGIN SUCCESS: User {$user->username} logged in.");
      
      // Use . to join strings in PHP
      $_SESSION['success'] = "Successfully logged in as " . $user->username;

      // Use === for comparison
      if ($user->type === 'admin' || $user->type === 'staff') {
        $this->redirect('/staff/dashboard'); // Match the router path exactly
      } else {

        if($user-> status ==='inactive'){
          $this->view('login/login', ['error' => 'Account is not verified.'], 'default');
        }
        $this->redirect('/student/dashboard');
      }
    }

    Logger::log("LOGIN FAILED: Attempt for {$identifier}");
    $this->view('login/login', ['error' => 'Invalid credentials'], 'default');
  }

  /**
   * Display Registration View
   */
  public function showRegister()
  {
    $this->view('login/register', ['title' => 'Create Account'], 'default');
  }

  public function verify_email()
  {
    $token = $_GET['token'] ?? '';

    if (empty($token)) {
      die("Invalid Request: No token provided.");
    }

    $user = $this->userRepo->findByToken($token);

    if ($user) {
      $updateData = [
        'status'             => 'active',
        'verification_token' => null 
      ];

      $this->userRepo->update($user->id, $updateData);
      Logger::log("USER ACTIVATED: ID {$user->id}");

      $_SESSION['success'] = "Account verified! You can now log in.";
      $this->redirect('/auth/login');
    } else {
      // Updated path to show login with error if token fails
      $this->view('login/login', ['error' => 'Invalid or expired verification link.'], 'default');
    }
  }
  public function register()
  {
    $token = bin2hex(random_bytes(32));
    $data = [
      'username'   => $this->input('username'),
      'password'   => $this->input('password'), 
      'email'      => $this->input('email'),
      'first_name' => $this->input('first_name'),
      'mid_name'   => $this->input('mid_name'),
      'last_name'  => $this->input('last_name'),
      'birth_date' => $this->input('birth_date'),
      'type'       => 'student',
      'status'     => 'inactive',
      'verification_token' => $token
    ];

    try {
      $user = $this->userRepo->create($data);
      
      // Use PHPMailer to send the link
      $this->userRepo->sendVerificationEmail($user->email, $token);


      $_SESSION['success']= "Successfully Registerd! Verification Email sent to " . $user->email . ".";
      $this->redirect('/auth/login');
    }
    catch (\Exception $e) {
      Logger::log("REGISTRATION ERROR: " . $e->getMessage());
      $this->view('login/register', ['error' => 'Username or Email already exists.'], 'default');
    }
  }

  /**
   * Display and Handle OTP Verification
   */
  public function showVerifyOtp()
  {
    // Get from session, fallback to empty
    $email = $_SESSION['verify_email'] ?? '';
    $this->view('login/verify_otp', ['email' => $email], 'default');
  }

  public function verifyOtp()
  {
    $email = $_SESSION['verify_email'] ?? $this->input('email');
    $code = $this->input('otp_code');
    $flow = $_SESSION['verify_flow'] ?? 'register';

    if ($this->userRepo->verifyAndActivate($email, $code)) {
      // Clear the session data after successful verification
      unset($_SESSION['verify_email'], $_SESSION['verify_flow']);
      
      if ($flow === 'reset') {
        $_SESSION['reset_email'] = $email;
        $_SESSION['success'] = "OTP Code successfully verified.";
        $this->redirect('/auth/reset-password');
      } else {
        $this->redirect('/auth/login');
      }
    }

    $this->view('login/verify_otp', [
      'email' => $email, 
      'error' => 'Invalid or expired OTP.',
      'flow' => $flow
    ], 'default');
  }

  /**
 * Display the Forgot Password View
 */
public function forgotpass()
{
    $this->view('login/forgotpass', ['title' => 'Reset Password'], 'default');
}

/**
 * Handle the Forgot Password request (POST)
 */
public function sendPasswordReset()
{
    $email = $this->input('email');
    
    // Check if the email exists in our records
    if ($this->userRepo->exists('email', $email)) {
        // Trigger the same OTP flow used for registration
        $user = User::where('email', $email)->first();
        $this->userRepo->handleOtpFlow($user->id, $user->email);
        
        Logger::log("FORGOT PASSWORD: OTP sent to {$email}");
        $_SESSION['verify_email'] = $email;
        $_SESSION['verify_flow'] = 'reset';
        
        // Redirect to OTP verification page
        header('Location: /auth/verify-otp');
        exit();
    }

    $this->view('login/forgotpass', [
        'error' => 'Email address not found.'
    ], 'default');
}

/**
 * Display the New Password Form
 */
public function showResetPassword()
{
    // Safety check: only show if the user actually went through the OTP process
    // We can use a temporary session flag set in verifyOtp
    $this->view('login/reset_password', ['title' => 'Create New Password'], 'default');
}

/**
 * Handle the New Password Submission (POST)
 */
public function resetPassword()
{
  $email = $this->input('email'); // Passed via hidden input in the view
  $newPassword = $this->input('password');
  $confirmPassword = $this->input('confirm_password');

  if ($newPassword !== $confirmPassword) {
    // Change 'login/resetpass' to 'login/reset_password' to match the file
    return $this->view('login/reset_password', [
      'error' => 'Passwords do not match.',
      'email' => $email
    ], 'default');
  }

  // Update the user password
  $user = User::where('email', $email)->first();
  if ($user) {
    $user->update([
      'password' => $newPassword
    ]);
    
    Logger::log("PASSWORD RESET SUCCESS: User {$user->username}");
    
    $_SESSION['success'] = "Password successfully updated. Please login.";

    // 2. CLEAN UP (Since you saw reset_email was still there)
    unset($_SESSION['reset_email']); 
    unset($_SESSION['verify_email']);

    // 3. REDIRECT & KILL SCRIPT
    header('Location: /auth/login');
    exit(); // CRITICAL: Prevents further code from overwriting the session
  }

  header('Location: /auth/login');  
  }
}
