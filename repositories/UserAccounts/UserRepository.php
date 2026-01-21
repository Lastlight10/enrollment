<?php

namespace App\Repositories\UserAccounts;

use App\Core\Repository;
use App\Core\Logger;
use Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserRepository extends Repository
{
  public function find($id)
  {
    return User::find($id);
  }

  public function findByCredentials($identifier)
  {
    return User::where('username', $identifier)->first();
  }

  public function findByToken($id)
  {
    return Subject::find($id);
  }

  public function create(array $data)
  {
    return User::create($data);
  }

  public function update($id, array $data)
  {
    $user = User::find($id);
    if ($user) {
      return $user->update($data);
    }
    return false;
  }

  public function exists($column, $value)
  {
    return User::where($column, $value)->exists();
  }

  public function handleOtpFlow($userId, $email)
  {
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    User::where('id', $userId)->update([
      'otp_code' => $code,
      'otp_expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
    ]);
    
    Logger::log("OTP GENERATED for User ID: {$userId}");
    return $this->sendOtpEmail($email, $code);
  }

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
   * Private helper to configure PHPMailer to avoid code repetition
   */
  private function getMailer()
  {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER'];
    $mail->Password   = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = $_ENV['SMTP_PORT'];
    $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
    return $mail;
  }

  private function sendOtpEmail($email, $code)
  {
    try {
      $mail = $this->getMailer();
      $mail->addAddress($email);
      $mail->isHTML(true);
      $mail->Subject = 'Verification Code - Enrollment System';
      $mail->Body    = "<h3>Verification Required</h3>
                        <p>Your OTP code is: <b>$code</b></p>
                        <p>This code will expire in 5 minutes.</p>";
      $mail->send();
      return true;
    } catch (Exception $e) {
      Logger::log("MAILER ERROR (OTP): {$e->getMessage()}");
      return false;
    }
  }

  public function sendVerificationEmail($email, $token)
  {
    try {
      $mail = $this->getMailer();
      $mail->addAddress($email);

      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
      $host = $_SERVER['HTTP_HOST'];
      $verifyLink = $protocol . $host . "/auth/verify-email?token=" . $token;

      $mail->isHTML(true);
      $mail->Subject = 'Verify Your Account';
      $mail->Body = "
        <div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px;'>
          <h2 style='color: #333;'>Confirm Your Email Address</h2>
          <p>Hi there,</p>
          <p>Thank you for signing up for the Enrollment System. To complete your registration, please verify your email by clicking the button below:</p>
          <div style='text-align: center; margin: 30px 0;'>
            <a href='{$verifyLink}' style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify My Account</a>
          </div>
          <p style='font-size: 12px; color: #777;'>If you did not create an account, you can safely ignore this email.</p>
        </div>";

      $mail->send();
      return true;
    } catch (Exception $e) {
      Logger::log("MAILER ERROR (Verification): {$e->getMessage()}");
      return false;
    }
  }

  public function createAccount(array $data)
  {
    return User::create($data);
  }

  public function updateAccount($id, array $data)
  {
    $user = User::findOrFail($id);
    $user->fill($data);

    if (!$user->isDirty()) {
      return 'no_changes';
    }

    return $user->save();
  }

  public function deleteAccount($id) 
  {
    $user = User::findOrFail($id);
    return $user->delete();
  }
}