<?php

namespace App\Repositories\UserAccounts;

use App\Core\Repository;
use App\Core\Logger;
use Models\User;
use Models\Subject;
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
  public function findByEmail($identifier)
  {
    return User::where('email', $identifier)->first();
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

  public function isDuplicate($username, $email)
{
    // Check if username exists
    if ($this->exists('username', $username)) {
        return 'Username is already taken.';
    }
    // Check if email exists
    if ($this->exists('email', $email)) {
        return 'Email is already registered.';
    }
    return false;
}
  public function sendPendingApprovalEmail($recipientEmail) {
  $client = new \Google\Client();
  $user = $this->findByEmail($recipientEmail); // Get user to check status
  
  $client->setAuthConfig(BASE_PATH . '/credentials.json');
  $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));

  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents(BASE_PATH . '/token.json', json_encode($client->getAccessToken()));
  }
  
  $service = new \Google\Service\Gmail($client);

  // Determine message based on status
  if ($user->status === 'active') {
    $statusTitle = "Account Activated!";
    $statusMessage = "Your account has been verified. You can now log in to the system.";
    $buttonText = "Login Now";
    $buttonUrl = "https://enrollment.great-site.net/auth/login";
    $color = "#28a745"; // Green
  } else {
    $statusTitle = "Pending Approval";
    $statusMessage = "Your account details have been updated. Please wait for a staff member to verify your account.";
    $buttonText = "View Site";
    $buttonUrl = "https://enrollment.great-site.net/";
    $color = "#ffc107"; // Yellow/Gold
  }

  $subject = "Account Status: " . $statusTitle;

  // HTML Body with CSS
  $body = "
  <html>
  <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;'>
      <div style='background-color: $color; padding: 20px; text-align: center;'>
        <h1 style='color: white; margin: 0;'>$statusTitle</h1>
      </div>
      <div style='padding: 30px; text-align: center;'>
        <p style='font-size: 18px;'>Hello, <b>" . htmlspecialchars($user->username ?? 'User') . "</b></p>
        <p>$statusMessage</p>
        <a href='$buttonUrl' style='display: inline-block; padding: 12px 25px; margin-top: 20px; background-color: $color; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>$buttonText</a>
      </div>
      <div style='background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
        &copy; 2026 Enrollment System. All rights reserved.
      </div>
    </div>
  </body>
  </html>";

  // Gmail API requires a specific MIME format for HTML
  $strRawMessage = "To: $recipientEmail\r\n";
  $strRawMessage .= "Subject: $subject\r\n";
  $strRawMessage .= "MIME-Version: 1.0\r\n";
  $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
  $strRawMessage .= $body;

  $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
  $msg = new \Google\Service\Gmail\Message();
  $msg->setRaw($mime);

  try {
    $service->users_messages->send("me", $msg);
  } catch (\Exception $e) {
    error_log("Gmail API Error: " . $e->getMessage());
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