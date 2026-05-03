<?php

namespace App\Repositories\UserAccounts;

use App\Core\Repository;
use App\Core\Logger;
use Models\User;
use Models\StudentCourses;
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

  public function isEnrolled($id)
  {
    return User::where('id',$id)->value('is_enrolled');
  }

  public function getEnrolledCourse($id)
  {
    return StudentCourses::where('user_id',$id)->value('course_id');
  }
  public function enrollStudent($id)
  {
    $user = User::find($id);

    if (!$user) {
      return false; 
    }
    $user->is_enrolled = true;

    return $user->save();
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
    if (!$user) return false;

    // Fill the model with new data
    $user->fill($data);

    // Check if any actual values changed compared to the database
    if (!$user->isDirty()) {
        return 'no_changes';
    }

    return $user->save();
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
  public function sendVerificationEmail($recipientEmail)
  {
    $client = new \Google\Client();
    $user = $this->findByEmail($recipientEmail);

    if (!$user) return;

    // Generate a unique token for verification
    $token = bin2hex(random_bytes(32));
    $user->update(['verification_token' => $token]);

    $client->setAuthConfig(BASE_PATH . '/credentials.json');
    $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));

    if ($client->isAccessTokenExpired()) {
      $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
      file_put_contents(BASE_PATH . '/token.json', json_encode($client->getAccessToken()));
    }

    $service = new \Google\Service\Gmail($client);
    $verifyUrl = "https://enrollment.great-site.net/auth/verify_email?token=" . $user->verification_token;
  
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
      <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px;'>
        <h2>Welcome, " . htmlspecialchars($user->username) . "!</h2>
        <p>Thank you for registering. To complete your enrollment and receive your <b>Student ID Number</b>, please verify your email.</p>
        <div style='text-align: center;'>
          <a href='$verifyUrl' style='background: #007bff; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>Verify Email Now</a>
        </div>
      </div>
    </body>
    </html>";

    $strRawMessage = "To: $recipientEmail\r\n";
    $strRawMessage .= "Subject: Action Required: Verify Your Account\r\n";
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
  public function sendAccountActivatedEmail($user)
  {
    $client = new \Google\Client();
    $client->setAuthConfig(BASE_PATH . '/credentials.json');
    $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));

    if ($client->isAccessTokenExpired()) {
      $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
      file_put_contents(BASE_PATH . '/token.json', json_encode($client->getAccessToken()));
    }

    $service = new \Google\Service\Gmail($client);
    $username = trim("{$user->username}");
    $fullName = trim("{$user->first_name} {$user->mid_name} {$user->last_name}");
    $statusLabel = ($user->status === 'active') ? 'Active' : 'Inactive';
    
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; color: #333;'>
      <div style='max-width: 600px; margin: 0 auto; border: 1px solid #28a745; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #28a745; color: white; padding: 20px; text-align: center;'>
          <h2 style='margin: 0;'>Account Activated Successfully!</h2>
        </div>
        <div style='padding: 25px;'>
          <p>Hello <b>" . htmlspecialchars($user->username) . "</b>,</p>
          <p>Your account has been successfully verified. Here are your official enrollment details:</p>
          
          <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
            <tr>
              <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Full Name:</td>
              <td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($fullName) . "</td>
            </tr>
            <tr>
              <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Username:</td>
              <td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($username) . "</td>
            </tr>
            <tr>
              <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Email Address:</td>
              <td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($user->email) . "</td>
            </tr>
            <tr>
              <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Student ID:</td>
              <td style='padding: 8px; border-bottom: 1px solid #eee; color: #28a745; font-weight: bold; font-size: 1.1em;'>" . $user->id_number . "</td>
            </tr>
            <tr>
              <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Account Status:</td>
              <td style='padding: 8px; border-bottom: 1px solid #eee;'><span style='color: #28a745;'>● $statusLabel</span></td>
            </tr>
          </table>

          <div style='text-align: center; margin-top: 30px;'>
            <a href='https://enrollment.great-site.net/auth/login' style='background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login to Portal</a>
          </div>
        </div>
        <div style='background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 11px; color: #888;'>
          Please keep this email for your records. If you did not register for this account, please ignore this email.
        </div>
      </div>
    </body>
    </html>";
    
    $strRawMessage = "From: The University of Manila <recon21342@gmail.com>\r\n";
    $strRawMessage = "To: {$user->email}\r\n";
    $strRawMessage .= "Subject: Welcome! Your Account is Now Active\r\n";
    $strRawMessage .= "MIME-Version: 1.0\r\n";
    $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    $strRawMessage .= $body;

    $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
    $msg = new \Google\Service\Gmail\Message();
    $msg->setRaw($mime);

    $service->users_messages->send("me", $msg);
  }

  public function findByVerificationToken($token)
  {
    return User::where('verification_token', $token)->first();
  }

  // Inside UserRepository class
  public function generateNextIdNumber() 
  {
    $yearPrefix = date('y'); // "26"
    
    $latest = User::where('id_number', 'LIKE', $yearPrefix . '%')
                  ->orderBy('id_number', 'DESC')
                  ->first();

    if ($latest && !empty($latest->id_number)) {
      // Just add 1 to the whole integer
      return (string)((int)$latest->id_number + 1);
    }

    // Start: 26 + 00001 = 2600001
    return $yearPrefix . "00001"; 
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
  public function getEnrolledCourseDetails($userId)
  {
    // Use Eloquent to get the relationship
    // Assuming a StudentCourse model exists with a 'course' relationship
    return \Models\StudentCourses::where('user_id', $userId)
        ->with('course')
        ->first();
  }
  public function updateStudentCourse($userId, $courseId)
  {
    // 1. Update or Create the course link
    $courseUpdate = StudentCourses::updateOrCreate(
        ['user_id' => $userId],
        ['course_id' => $courseId]
    );

    // 2. Force is_enrolled to true on the User model
    $user = User::find($userId);
    if ($user && $user->type === 'student') {
        $user->is_enrolled = true;
        $user->save();
    }

    return $courseUpdate;
  }
public function getFilteredUsersForReport(array $filters)
{
    $query = User::where('type', '!=', 'admin');

    if (!empty($filters['type']) && $filters['type'] !== 'all') {
        $query->where('type', $filters['type']);
    }

    if (!empty($filters['search'])) {
        $s = $filters['search'];
        $query->where(function($q) use ($s) {
            $q->where('first_name', 'like', "%$s%")
              ->orWhere('last_name', 'like', "%$s%")
              ->orWhere('username', 'like', "%$s%")
              ->orWhere('email', 'like', "%$s%")
              ->orWhere('id_number', 'like', "%$s%");
        });
    }

    return $query->orderBy('last_name', 'ASC')->get();
}
  public function deleteAccount($id) 
  {
    $user = User::findOrFail($id);
    return $user->delete();
  }
  public function sendPendingApprovalEmail($email)
  {
    $client = new \Google\Client();
    $client->setAuthConfig(BASE_PATH . '/credentials.json');
    $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));
    $baseUrl = 'http://enrollment.great-site.net';
    $logoPath = $baseUrl . '/static/images/UMLOGO.jpg';
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents(BASE_PATH . '/token.json', json_encode($client->getAccessToken()));
    }
    $user = User::where('email', $email)->first();

    if (!$user) {
      error_log("Email Error: No user found for $email");
      return; 
    }
    $service = new \Google\Service\Gmail($client);
    
    // Modern HTML Template
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;'>
        <div style='max-width: 600px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: white; padding: 20px; text-align: center;'>
                  <img src='{$logoPath}' alt='University Logo' style='width: 80px; height: auto; margin-bottom: 10px;'>
                    <h4 style='color: #004d00; margin: 0; font-size: 24px; letter-spacing: 1px;'>The University of Manila</h1>
                    <h1 style='color: #004d00; margin: 0; font-size: 20px;'>Payment Announcement</h1>
            </div>
            <div style='padding: 30px;'>
                <h3 style='color: #004d00;'>Account Update Notification</h3>
                <p>Hello,</p>
                <p>This is to inform you that your <strong>account information</strong> has been successfully updated by the system administrator.</p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #004d00; margin: 20px 0;'>
                    <p style='margin: 0;'><strong>Action Taken:</strong> Profile/Record Update</p>
                    <p style='margin: 0;'><strong>Date:</strong> " . date('F j, Y, g:i a') . "</p>
                    <p style='margin: 0;'><strong>Username:</strong> " . htmlspecialchars($user->username) . "</p>
                    <p style='margin: 0;'><strong>Email:</strong> " . htmlspecialchars($user->email) . "</p>
                </div>

                <p>If you did not authorize these changes or have questions, please contact the IT Support office immediately.</p>
                
            </div>
            <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                <p style='margin: 0;'>This is an automated message. Please do not reply to this email.</p>
                <p style='margin: 0;'>&copy; " . date('Y') . " The University of Manila</p>
            </div>
        </div>
    </body>
    </html>";

    $strRawMessage = "To: $email\r\n";
    $strRawMessage .= "Subject: Security Notice: Account Information Updated\r\n";
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
   public function sendRegisteredStaffEmail($email)
  {
    $client = new \Google\Client();
    $client->setAuthConfig(BASE_PATH . '/credentials.json');
    $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));

    if ($client->isAccessTokenExpired()) {
      $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
      file_put_contents(BASE_PATH . '/token.json', json_encode($client->getAccessToken()));
    }

    $service = new \Google\Service\Gmail($client);
    
    $body = "<html><body>
                <h3>Staff Account Registration Notice</h3>
                <p>Your account information has been registered in Enrollment System.</p>
            </body></html>";

    $strRawMessage = "To: $email\r\n";
    $strRawMessage .= "Subject: Account Information Updated\r\n";
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
  public function sendStudentCourse($userId, $courseId)
  {
      $client = new \Google\Client();
      $client->setAuthConfig(BASE_PATH . '/credentials.json');
      $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));

      if ($client->isAccessTokenExpired()) {
          $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
          file_put_contents(BASE_PATH . '/token.json', json_encode($client->getAccessToken()));
      }

      $user = \Models\User::find($userId);
      // Assuming you have a Course model or a courses table
      $courseName = \Models\Course::where('id', $courseId)->value('course_name') ?? 'Selected Course';
      
      $baseUrl = 'http://enrollment.great-site.net';
      $logoPath = $baseUrl . '/static/images/UMLOGO.jpg';
      $service = new \Google\Service\Gmail($client);

      $body = "
      <html>
      <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
          <div style='max-width: 600px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
              <div style='background-color: white; padding: 20px; text-align: center; border-bottom: 2px solid #004d00;'>
                  <img src='{$logoPath}' alt='University Logo' style='width: 80px; height: auto;'>
                  <h4 style='color: #004d00; margin: 10px 0 0 0;'>The University of Manila</h4>
              </div>
              <div style='padding: 30px;'>
                  <h3 style='color: #004d00;'>Course Assignment Update</h3>
                  <p>Hello <b>" . htmlspecialchars($user->first_name) . "</b>,</p>
                  <p>Your official course for the current academic period has been updated/assigned by the Registrar.</p>
                  
                  <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 4px solid #004d00; margin: 20px 0; text-align: center;'>
                      <span style='font-size: 14px; color: #666; display: block; margin-bottom: 5px;'>NEW ASSIGNED COURSE:</span>
                      <strong style='font-size: 18px; color: #004d00;'>" . htmlspecialchars($courseName) . "</strong>
                  </div>

                  <p>You may now proceed to the student portal to view your schedule and available subjects for this course.</p>
                  
                  <div style='text-align: center; margin-top: 25px;'>
                      <a href='{$baseUrl}/auth/login' style='background-color: #004d00; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Student Portal</a>
                  </div>
              </div>
              <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 11px; color: #777;'>
                  &copy; " . date('Y') . " The University of Manila - Registrar's Office
              </div>
          </div>
      </body>
      </html>";

      $strRawMessage = "To: {$user->email}\r\n";
      $strRawMessage .= "Subject: Notification: Course Assignment Updated\r\n";
      $strRawMessage .= "MIME-Version: 1.0\r\n";
      $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
      $strRawMessage .= $body;

      $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
      $msg = new \Google\Service\Gmail\Message();
      $msg->setRaw($mime);

      try {
          $service->users_messages->send("me", $msg);
      } catch (\Exception $e) {
          error_log("Gmail API Error (Course Update): " . $e->getMessage());
      }
    }

}