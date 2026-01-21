<?php

namespace Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Request;
use App\Repositories\UserAccounts\UserRepository;
use Models\User;

class AuthController extends Controller
{
  protected $userRepo;

  public function __construct()
  {
    $this->userRepo = new UserRepository();
  }

  public function logout()
  {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
      );
    }
    session_destroy();
    session_start();
    $_SESSION['success'] = "You have been successfully logged out.";
    $this->redirect('/auth/login');
  }

  public function showLogin()
  {
    $this->view('login/login', ['title' => 'Login'], 'default');
  }

  public function login(Request $request) 
  {
    $identifier = $this->input('username');
    $password = $this->input('password');
    $user = $this->userRepo->findByCredentials($identifier);

    if ($user && password_verify($password, $user->password)) {
      if ($user->status !== 'active') {
        $_SESSION['error'] = "Account is not verified. Please check your email.";
        $this->redirect('/auth/login');
        return;
      }

      $_SESSION['user_id'] = $user->id;
      $_SESSION['user_type'] = $user->type;

      Logger::log("LOGIN SUCCESS: User {$user->username} logged in.");
      $_SESSION['success'] = "Successfully logged in as " . $user->username;

      if ($user->type === 'admin' || $user->type === 'staff') {
        $this->redirect('/staff/dashboard');
      } else {
        $this->redirect('/student/dashboard');
      }
      return;
    }

    Logger::log("LOGIN FAILED: Attempt for {$identifier}");
    $this->view('login/login', ['error' => 'Invalid credentials'], 'default');
  }

  public function showRegister()
  {
    $this->view('login/register', ['title' => 'Create Account'], 'default');
  }

  public function register(Request $request)
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
      $this->userRepo->sendVerificationEmail($user->email, $token);
      $_SESSION['success'] = "Successfully Registered! Verification Email sent to " . $user->email . ".";
      $this->redirect('/auth/login');
    } catch (\Exception $e) {
      Logger::log("REGISTRATION ERROR: " . $e->getMessage());
      $this->view('login/register', ['error' => 'Username or Email already exists.'], 'default');
    }
  }

  public function verify_email()
  {
    $token = $_GET['token'] ?? '';
    if (empty($token)) {
      $_SESSION['error'] = "Invalid verification link.";
      $this->redirect('/auth/login');
      return;
    }

    $user = $this->userRepo->findByToken($token);
    if ($user) {
      $this->userRepo->update($user->id, ['status' => 'active', 'verification_token' => null]);
      Logger::log("USER ACTIVATED: ID {$user->id}");
      $_SESSION['success'] = "Account verified! You can now log in.";
      $this->redirect('/auth/login');
    } else {
      $this->view('login/login', ['error' => 'Invalid or expired verification link.'], 'default');
    }
  }

  public function showVerifyOtp()
  {
    $email = $_SESSION['verify_email'] ?? '';
    $this->view('login/verify_otp', ['email' => $email], 'default');
  }

  public function verifyOtp(Request $request)
  {
    $email = $_SESSION['verify_email'] ?? $this->input('email');
    $code = $this->input('otp_code');
    $flow = $_SESSION['verify_flow'] ?? 'register';

    if ($this->userRepo->verifyAndActivate($email, $code)) {
      unset($_SESSION['verify_email'], $_SESSION['verify_flow']);
      if ($flow === 'reset') {
        $_SESSION['reset_email'] = $email;
        $_SESSION['success'] = "OTP Code successfully verified.";
        $this->redirect('/auth/reset-password');
      } else {
        $_SESSION['success'] = "Account activated successfully!";
        $this->redirect('/auth/login');
      }
      return;
    }

    $this->view('login/verify_otp', [
      'email' => $email, 
      'error' => 'Invalid or expired OTP.',
      'flow' => $flow
    ], 'default');
  }

  public function forgotpass()
  {
    $this->view('login/forgotpass', ['title' => 'Reset Password'], 'default');
  }

  public function sendPasswordReset(Request $request)
  {
    $email = $this->input('email');
    if ($this->userRepo->exists('email', $email)) {
      $user = User::where('email', $email)->first();
      $this->userRepo->handleOtpFlow($user->id, $user->email);
      Logger::log("FORGOT PASSWORD: OTP sent to {$email}");
      $_SESSION['verify_email'] = $email;
      $_SESSION['verify_flow'] = 'reset';
      $this->redirect('/auth/verify-otp');
      return;
    }
    $this->view('login/forgotpass', ['error' => 'Email address not found.'], 'default');
  }

  public function showResetPassword()
  {
    $this->view('login/reset_password', ['title' => 'Create New Password'], 'default');
  }

  public function resetPassword(Request $request)
  {
    $email = $this->input('email');
    $newPassword = $this->input('password');
    $confirmPassword = $this->input('confirm_password');

    if ($newPassword !== $confirmPassword) {
      return $this->view('login/reset_password', [
        'error' => 'Passwords do not match.',
        'email' => $email
      ], 'default');
    }

    $user = User::where('email', $email)->first();
    if ($user) {
      $user->update(['password' => $newPassword]);
      Logger::log("PASSWORD RESET SUCCESS: User {$user->username}");
      $_SESSION['success'] = "Password successfully updated. Please login.";
      unset($_SESSION['reset_email'], $_SESSION['verify_email']);
      $this->redirect('/auth/login');
      return;
    }
    $this->redirect('/auth/login'); 
  }
}