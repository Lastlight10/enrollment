<?php

namespace App\Repositories\UserAccounts;

use App\Core\Repository;
use App\Core\Logger;
use Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserRepository extends Repository
{
  /**
   * Find a user by their Primary Key
   */
  public function find($id)
  {
    return User::find($id);
  }

  /**
   * Authentication: Find user by Email or Username
   */
  public function findByCredentials($identifier)
  {
    return User::where('username', $identifier)
      ->first();
  }

  /**
   * Create a new User record
   */
  public function create(array $data)
  {
    return User::create($data);
  }

  /**
   * Check if a specific field value already exists
   */
  public function exists($column, $value)
  {
    return User::where($column, $value)->exists();
  }

  /**
   * Generate, Save, and Send a new OTP to a user
   */
  public function handleOtpFlow($userId, $email)
  {
    // 1. Generate 6-digit code
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // 2. Update Database
    User::where('id', $userId)->update([
      'otp_code' => $code,
      'otp_expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
      
    ]);
    Logger::log("OTP SENT.");

    // 3. Send Email
    return $this->sendOtpEmail($email, $code);
  }

  /**
   * Verify OTP and Activate the account
   */
  public function verifyAndActivate($email, $code)
  {
    $user = User::where('email', $email)
      ->where('otp_code', $code)
      ->where('otp_expires_at', '>', date('Y-m-d H:i:s'))
      ->first();

    if ($user) {
      return $user->update([
        'status' => 'active',
        'otp_code' => null,
        'otp_expires_at' => null
      ]);
    }

    return false;
  }

  /**
   * Internal PHPMailer logic
   */
  private function sendOtpEmail($email, $code)
  {
    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com'; // Gmail SMTP server
      $mail->SMTPAuth   = true;
      $mail->Username   = 'recon21342@gmail.com'; // !!! REPLACE WITH YOUR GMAIL EMAIL !!!
      $mail->Password   = 'xtdsnpxtjgekndlu';   // !!! REPLACE WITH YOUR GMAIL APP PASSWORD !!!
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SMTPS (465)
      $mail->Port       = 465;

      $mail->setFrom('recon21342@gmail.com', 'UM ENROLLMENT SYSTEM');
      $mail->addAddress($email);

      $mail->isHTML(true);
      $mail->Subject = 'Verification Code - Enrollment System';
      $mail->Body    = "<h3>Verification Required</h3>
                        <p>Your OTP code is: <b>$code</b></p>
                        <p>This code will expire in 5 minutes.</p>";

      $mail->send();
      return true;
    } catch (Exception $e) {
      Logger::log("MAILER ERROR: {$mail->ErrorInfo}");
      return false;
    }
  }
}