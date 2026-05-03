<?php
namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use App\Core\Logger;
use Models\Enrollment;
use Models\Curriculum;

use Illuminate\Support\Facades\DB;
use Models\AcademicPeriod;
use App\Repositories\StaffRepositories\AcademicPeriodRepository;

class EnrollmentRepository extends Repository{
  public function findById($id) {
        return Enrollment::with(['user', 'course', 'period', 'payments'])->find($id);
    }
  public function all() {
    // Replace Enrollment::all() with this:
    return Enrollment::with(['user', 'course', 'period', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
  }
    public function find($id) {
        return Enrollment::with('user')->find($id);
    }
  
    // Inside EnrollmentRepository.php

public function getLatestActiveEnrollment($userId) {
    return Enrollment::with(['course', 'period'])
        ->where('user_id', $userId)
        ->where('status', 'enrolled') // Or 'pending' if you want to show it early
        ->orderBy('created_at', 'desc')
        ->first();
}

public function getByUser($userId) {
    return Enrollment::with(['course', 'period', 'subjects'])
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();
}

    public function updateStatus($id, $status, $comments = null) {
        $enrollment = Enrollment::find($id);
        if (!$enrollment) return null;

        $enrollment->status = $status;

        // CRITICAL: Ensure this column name matches your database table exactly
        if ($comments !== null) {
            $enrollment->staff_comments = $comments; 
        }

        $enrollment->save();

        // Reload relationships so the email has the data
        return Enrollment::with(['user', 'period', 'course'])->find($id);
    }

  public function createSinglePayment($enrollmentId, $data) {
    $enrollment = Enrollment::findOrFail($enrollmentId);
    return $enrollment->payments()->create([
      'payment_type' => $data['payment_type'],
      'amount' => $data['amount'],
      'remarks' => $data['remarks'] ?? null,
      'status' => 'unpaid'
    ]);
  }
  
  public function createApplication(array $data, array $subjectIds) {
    return DB::transaction(function () use ($data, $subjectIds) {
      // Create the enrollment record
      $enrollment = Enrollment::create($data);

      // Sync subjects to the junction table (enrolled_subjects)
      if (!empty($subjectIds)) {
        $enrollment->subjects()->attach($subjectIds);
      }

      return $enrollment;
    });
  }

  public function getPendingWithDetails() {
    return Enrollment::with(['user', 'course', 'subjects'])
      ->where('status', 'pending')
      ->get();
  }
  public function getPayments(int $userId)
  {
    return \Models\User::findOrFail($userId)->payments;
  }
   public function getPaymentById(int $id)
    {
        return \Models\Payment::with(['enrollment.user', 'enrollment.period'])->findOrFail($id);
    }
  
  public function approveWithFees(int $id, array $fees) {
    return DB::transaction(function () use ($id, $fees) {
      $enrollment = Enrollment::findOrFail($id);

      // 1. Update Enrollment status
      $enrollment->update(['status' => 'enrolled']);

      // 2. Insert manual fees into payments table
      foreach ($fees as $fee) {
        $enrollment->payments()->create([
          'payment_type' => $fee['type'], // downpayment, prelim, etc.
          'amount' => $fee['amount'],
          'status' => 'unpaid'
        ]);
      }

      return $enrollment;
    });
  }

  public function updatePaymentStatus($paymentId, $data)
  {
      return DB::table('payments')->where('id', $paymentId)->update([
          'status' => $data['status'],
          'remarks' => $data['remarks'],
          'verified_by' => $data['verified_by'],
      ]);
  }
  public function findForStudent($userId, $enrollmentId)
  {
    // Changed 'users' to 'user' assuming a standard BelongsTo relationship
    return Enrollment::with(['course', 'subjects', 'payments', 'user']) 
      ->where('user_id', $userId)
      ->where('id', $enrollmentId)
      ->first();
  }
   // Inside EnrollmentRepository.php

  // Inside EnrollmentRepository.php

  public function sendBulkAnnouncement($enrollmentIds, $type, $startDate, $endDate)
  {

    // 1. Get unique users from the provided enrollment IDs
    $enrollments = Enrollment::with('user')
        ->whereIn('id', $enrollmentIds)
        ->get()
        ->unique('user_id');

    // 2. Fix the Period Access
    $acad = new AcademicPeriodRepository();
    $periods = $acad->getActivePeriods();
    $active = is_array($periods) ? (object)$periods[0] : $periods->first();

    if (!$active) {
        throw new \Exception("No active academic period found.");
    }

    // 3. Gmail Client Setup (Consider moving this to a Constructor/Service to stay DRY)
    $client = new \Google\Client();
    $client->setAuthConfig(BASE_PATH . '/credentials.json');
    $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));

    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents(BASE_PATH . '/token.json', json_encode($client->getAccessToken()));
    }
    $successCount=0;
    $service = new \Google\Service\Gmail($client);
    $formattedStart = date('M d, Y', strtotime($startDate));
    $formattedEnd = date('M d, Y', strtotime($endDate));
    $subject = "IMPORTANT: " . strtoupper($type) . " Payment Schedule";
    $baseUrl = 'http://enrollment.great-site.net';
    $logoPath = $baseUrl . '/static/images/UMLOGO.jpg';

    foreach ($enrollments as $enrollment) {
        $user = $enrollment->user;
        if (!$user?->email) continue;

        // HTML Design Template
        $messageBody = "
        <html>
        <body style='font-family: sans-serif; color: #333; line-height: 1.6;'>
            <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e1e1e1; border-radius: 8px; overflow: hidden;'>
                <div style='background-color: white; padding: 20px; text-align: center;'>
                  <img src='{$logoPath}' alt='University Logo' style='width: 80px; height: auto; margin-bottom: 10px;'>
                    <h4 style='color: #004d00; margin: 0; font-size: 24px; letter-spacing: 1px;'>The University of Manila</h1>
                    <h1 style='color: #004d00; margin: 0; font-size: 20px;'>Payment Announcement</h1>
                </div>

                <div style='padding: 30px;'>
                    <p>Dear <strong>{$user->full_name}</strong>,</p>
                    <p>This is a formal notice regarding the <strong>" . strtoupper($type) . "</strong> payment schedule for the <strong>{$active->acad_year} {$active->semester}</strong> period.</p>
                    
                    <div style='background-color: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>Type:</strong> " . strtoupper($type) . "</p>
                        <p style='margin: 0;'><strong>Start Date:</strong> {$formattedStart}</p>
                        <p style='margin: 0;'><strong>End Date:</strong> {$formattedEnd}</p>
                    </div>

                    <p>Please settle your balance through our authorized payment channels before the deadline to avoid any inconvenience.</p>
                    
                </div>
                <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                    This is an automated notification. Please do not reply to this email.<br>
                    &copy; " . date('Y') . " The University of Manila
                </div>
            </div>
        </body>
        </html>";

        // IMPORTANT: Change Content-Type to text/html
        $strRawMessage = "From: Your School Name <your-email@gmail.com>\r\n";
        $strRawMessage .= "To: {$user->email}\r\n";
        $strRawMessage .= "Subject: =?utf-8?B?" . base64_encode($subject) . "?=\r\n"; // Encoded subject for special chars
        $strRawMessage .= "MIME-Version: 1.0\r\n";
        $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $strRawMessage .= $messageBody;

        $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
        $msg = new \Google\Service\Gmail\Message();
        $msg->setRaw($mime);

        try {
            $service->users_messages->send("me", $msg);
            $successCount++;
        } catch (\Exception $e) {
            Logger::log("Gmail Error for {$user->email}: " . $e->getMessage());
        }
    }
    return $successCount;
  }
  public function sendApprovalEmail($enrollment, $amount) {
      $user = $enrollment->user;
      if (!$user?->email) return;
      $formattedAmount = number_format($amount, 2);

      // Find the downpayment amount from the generated fees
      $downpayment = collect($enrollment->payments)
          ->where('type', 'downpayment')
          ->first();
      
      $amount = $downpayment ? number_format($downpayment->amount, 2) : '0.00';

      $client = new \Google\Client();
      $client->setAuthConfig(BASE_PATH . '/credentials.json');
      $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));
      
      $service = new \Google\Service\Gmail($client);

      $subject = "Welcome! Your Enrollment is Approved";
      $baseUrl = 'http://enrollment.great-site.net';
      $logoPath = $baseUrl . '/static/images/UMLOGO.jpg';
      $messageBody = "
      <html>
      <body style='font-family: sans-serif; color: #333;'>
          <div style='max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>

              <div style='background-color: white; padding: 30px 20px; text-align: center;'>
                <img src='{$logoPath}' alt='University Logo' style='width: 80px; height: auto; margin-bottom: 10px;'>
                <h4 style='color: #004d00; margin: 0; font-size: 24px; letter-spacing: 1px;'>The University of Manila</h1>
                <h1 style='color: #004d00; margin: 0; font-size: 24px; letter-spacing: 1px;'>Admission Confirmed</h1>
              </div>

              <div style='padding: 30px;'>
                  <p>Hello <strong>{$user->full_name}</strong>,</p>
                  <p>Success! Your enrollment for <strong>{$enrollment->period->acad_year} {$enrollment->period->semester}</strong> has been approved.</p>
                  
                  <p>To finalize your registration, please settle your downpayment:</p>
                  
                  <div style='background-color: #f8f9fa; border-radius: 5px; padding: 20px; text-align: center; margin: 20px 0;'>
                      <span style='color: #6c757d; font-size: 14px;'>REQUIRED DOWNPAYMENT</span><br>
                      <span style='font-size: 32px; font-weight: bold; color: #28a745;'>₱{$formattedAmount}</span>
                  </div>
              </div>
              <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                  This is an automated notification. Please do not reply to this email.<br>
                  &copy; " . date('Y') . " The University of Manila
              </div>
          </div>
      </body>
      </html>";

      $strRawMessage = "From: The University of Manila <recon21342@gmail.com>\r\n";
      $strRawMessage = "To: {$user->email}\r\n";
      $strRawMessage .= "Subject: {$subject}\r\n";
      $strRawMessage .= "MIME-Version: 1.0\r\n";
      $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
      $strRawMessage .= $messageBody;

      $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
      $msg = new \Google\Service\Gmail\Message();
      $msg->setRaw($mime);

      try {
          $service->users_messages->send("me", $msg);
      } catch (\Exception $e) {
          Logger::log("Failed to send approval email to {$user->email}: " . $e->getMessage());
      }
    }
   public function sendRejectionEmail($enrollment) {
    // No need to call Enrollment::find($id) anymore!
    $user = $enrollment->user;
    if (!$user?->email) return;

    $reason = !empty($enrollment->staff_comments) 
                  ? $enrollment->staff_comments 
                  : 'No specific reason provided.';
                  
    $client = new \Google\Client();
    $client->setAuthConfig(BASE_PATH . '/credentials.json');
    $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));
    
    $service = new \Google\Service\Gmail($client);

    $subject = "Enrollment Application Update - The University of Manila";
    $baseUrl = 'http://enrollment.great-site.net';
    $logoPath = $baseUrl . '/static/images/UMLOGO.jpg';

    $messageBody = "
    <html>
    <body style='font-family: sans-serif; color: #333; line-height: 1.6;'>
        <div style='max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
            <div style='background-color: white; padding: 30px 20px; text-align: center; border-bottom: 3px solid #800000;'>
                <img src='{$logoPath}' alt='University Logo' style='width: 80px; height: auto; margin-bottom: 10px;'>
                <h2 style='color: #800000; margin: 0;'>The University of Manila</h2>
                <p style='color: #777; margin: 5px 0;'>Office of Admissions</p>
            </div>

            <div style='padding: 30px;'>
                <p>Hello <strong>{$user->full_name}</strong>,</p>
                <p>Thank you for your interest in enrolling for the <strong>{$enrollment->period->acad_year} {$enrollment->period->semester}</strong>.</p>
                
                <p>After careful review, we regret to inform you that your enrollment application has been <strong>REJECTED</strong> for the following reason:</p>
                
                <div style='background-color: #fff4f4; border-left: 4px solid #cc0000; padding: 15px; margin: 20px 0; color: #333;'>
                    <strong>Remarks:</strong> {$reason}
                </div>

                <p>If you have any questions regarding this decision, please visit the Admissions Office or reply to this inquiry through our official channels.</p>
            </div>

            <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                This is an automated notification. Please do not reply directly to this email.<br>
                &copy; " . date('Y') . " The University of Manila
            </div>
        </div>
    </body>
    </html>";
    $strRawMessage = "From: The University of Manila <recon21342@gmail.com>\r\n";
    $strRawMessage .= "To: {$user->email}\r\n";
    $strRawMessage .= "Subject: {$subject}\r\n";
    $strRawMessage .= "MIME-Version: 1.0\r\n";
    $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    $strRawMessage .= $messageBody;

    $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
    $msg = new \Google\Service\Gmail\Message();
    $msg->setRaw($mime);

    try {
        $service->users_messages->send("me", $msg);
        return true; // Explicitly return true on success
    } catch (\Google\Service\Exception $e) {
        $errorDetails = json_decode($e->getMessage(), true);
        return "Gmail API Error: " . ($errorDetails['error']['message'] ?? 'Unknown API Error');
    } catch (\Exception $e) {
        return "General Error: " . $e->getMessage();
    }
  }
 public function sendDroppedEmail($enrollment) {
    // Crucial: Check if relations are loaded. If not, the email will fail silently.
    $user = $enrollment->user;
    if (!$user?->email) {
        Logger::log("Dropped Email Error: User or Email not found for Enrollment ID {$enrollment->id}");
        return;
    }

    $client = new \Google\Client();
    $client->setAuthConfig(BASE_PATH . '/credentials.json');
    $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));
    
    $service = new \Google\Service\Gmail($client);

    $subject = "Enrollment Application Update - The University of Manila";
    $baseUrl = 'http://enrollment.great-site.net';
    $logoPath = $baseUrl . '/static/images/UMLOGO.jpg';

    // Fixed: Removed the stray ']' bracket below the </p> tag
    $messageBody = "
    <html>
    <body style='font-family: sans-serif; color: #333; line-height: 1.6;'>
        <div style='max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
            <div style='background-color: white; padding: 30px 20px; text-align: center; border-bottom: 3px solid #800000;'>
                <img src='{$logoPath}' alt='University Logo' style='width: 80px; height: auto; margin-bottom: 10px;'>
                <h2 style='color: #800000; margin: 0;'>The University of Manila</h2>
                <p style='color: #777; margin: 5px 0;'>Office of Admissions</p>
            </div>

            <div style='padding: 30px;'>
                <p>Hello <strong>{$user->full_name}</strong>,</p>
                <p>Thank you for your interest in enrolling for the <strong>{$enrollment->period->acad_year} {$enrollment->period->semester}</strong>.</p>
                
                <p>After careful review, we regret to inform you that your enrollment application has been <strong>DROPPED</strong>.</p>
                
                <p>If you have any questions regarding this decision, please visit the Admissions Office or reply to this inquiry through our official channels.</p>
            </div>

            <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                This is an automated notification. Please do not reply directly to this email.<br>
                &copy; " . date('Y') . " The University of Manila
            </div>
        </div>
    </body>
    </html>";
    $strRawMessage = "From: The Univertsity of Manila <recon21342@gmail.com>\r\n";
    $strRawMessage .= "To: {$user->email}\r\n";
    $strRawMessage .= "Subject: {$subject}\r\n";
    $strRawMessage .= "MIME-Version: 1.0\r\n";
    $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    $strRawMessage .= $messageBody;

    $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
    $msg = new \Google\Service\Gmail\Message();
    $msg->setRaw($mime);

    try {
        $service->users_messages->send("me", $msg);
    } catch (\Exception $e) {
       Logger::log("Gmail Error: " . $e->getMessage());
        return $e->getMessage();
    }
}
public function sendPaymentUpdateEmail($payment, $status, $remarks = '') {
    // 1. Ensure relations are loaded (Payment -> Enrollment -> User)
    $enrollment = $payment->enrollment;
    $user = $enrollment?->user;

    if (!$user?->email) {
        Logger::log("Payment Email Error: User or Email not found for Payment ID {$payment->id}");
        return;
    }

    // 2. Setup Gmail Client
    $client = new \Google\Client();
    $client->setAuthConfig(BASE_PATH . '/credentials.json');
    $client->setAccessToken(json_decode(file_get_contents(BASE_PATH . '/token.json'), true));
    $service = new \Google\Service\Gmail($client);

    // 3. Dynamic Content based on Status
    $isApproved = ($status === 'paid');
    $isDownpayment = (isset($payment->payment_type) && strtolower($payment->payment_type) === 'downpayment');
    
    // Set Subject
    if ($isApproved) {
        $subject = $isDownpayment ? "Congratulations! You are officially enrolled" : "Payment Verified";
    } else {
        $subject = "Action Required: Payment Proof Rejected";
    }

    $statusText = $isApproved ? "VERIFIED" : "REJECTED";
    $statusColor = $isApproved ? "#198754" : "#dc3545"; // Green for success, Red for danger
    
    $baseUrl = 'http://enrollment.great-site.net';
    $logoPath = $baseUrl . '/static/images/UMLOGO.jpg';
    $paymentType = ucfirst($payment->payment_type ?? 'Payment');

    // 4. Construct Message Body
    $messageBody = "
    <html>
    <body style='font-family: sans-serif; color: #333; line-height: 1.6;'>
        <div style='max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
            <div style='background-color: white; padding: 30px 20px; text-align: center; border-bottom: 3px solid #800000;'>
                <img src='{$logoPath}' alt='University Logo' style='width: 80px; height: auto; margin-bottom: 10px;'>
                <h2 style='color: #800000; margin: 0;'>The University of Manila</h2>
                <p style='color: #777; margin: 5px 0;'>Office of the Registrar</p>
            </div>

            <div style='padding: 30px;'>
                <p>Hello <strong>{$user->full_name}</strong>,</p>";

    // --- ADDED: Congratulations Message for Downpayment ---
    if ($isApproved && $isDownpayment) {
        $messageBody .= "
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h1 style='color: #198754; margin: 0;'>Congratulations!</h1>
                    <p style='font-size: 18px; color: #333;'>You are now <strong>OFFICIALLY ENROLLED</strong>.</p>
                </div>";
    }

    $messageBody .= "
                <p>This is to notify you regarding your payment for: <strong>{$paymentType}</strong>.</p>
                
                <div style='background-color: #f8f9fa; border-left: 4px solid {$statusColor}; padding: 15px; margin: 20px 0;'>
                    <p style='margin: 0; font-weight: bold;'>Status: <span style='color: {$statusColor};'>{$statusText}</span></p>
                    <p style='margin: 5px 0 0 0;'>Amount: ₱" . number_format($payment->amount, 2) . "</p>
                </div>";

    if (!$isApproved) {
        $messageBody .= "
                <p style='color: #dc3545;'><strong>Reason for Rejection:</strong><br>{$remarks}</p>
                <p>Please log in to the portal and re-upload a valid proof of payment to proceed with your enrollment.</p>";
    } else {
        if ($isDownpayment) {
            $messageBody .= "
                <p>Your downpayment has been verified. You are now cleared for the current academic period. You may view your Certificate of Registration (COR) in the student portal.</p>";
        } else {
            $messageBody .= "
                <p>Your payment has been successfully recorded. You may now check your updated account balance in the student portal.</p>";
        }
    }

    $messageBody .= "
                <p style='margin-top: 25px;'>Best regards,<br><strong>The University of Manila</strong></p>
            </div>

            <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                This is an automated notification regarding your enrollment at The University of Manila.<br>
                &copy; " . date('Y') . " The University of Manila
            </div>
        </div>
    </body>
    </html>";

    // 5. Prepare Gmail Raw Message
    $strRawMessage = "From: The University of Manila <recon21342@gmail.com>\r\n";
    $strRawMessage .= "To: {$user->email}\r\n";
    $strRawMessage .= "Subject: {$subject} - {$paymentType}\r\n";
    $strRawMessage .= "MIME-Version: 1.0\r\n";
    $strRawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    $strRawMessage .= $messageBody;

    $mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
    $msg = new \Google\Service\Gmail\Message();
    $msg->setRaw($mime);

    try {
        $service->users_messages->send("me", $msg);
        return true;
    } catch (\Exception $e) {
        Logger::log("Gmail Payment Email Error: " . $e->getMessage());
        return false;
    }
}
  public function getFilteredEnrollments(array $filters) {
    $query = Enrollment::with(['user', 'course', 'period', 'payments']);

    if (!empty($filters['search'])) {
        $query->whereHas('user', function($q) use ($filters) {
            $q->where('name', 'like', "%{$filters['search']}%")
              ->orWhere('username', 'like', "%{$filters['search']}%");
        })->orWhere('id_number', 'like', "%{$filters['search']}%");
    }

    if (!empty($filters['course'])) {
        $query->where('course_id', $filters['course']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    if (!empty($filters['year'])) {
        $query->where('grade_year', $filters['year']);
    }

    if (!empty($filters['period'])) {
        $query->where('period_id', $filters['period']);
    }

    if (!empty($filters['date'])) {
        $query->whereDate('created_at', $filters['date']);
    }

    if (!empty($filters['payment_status'])) {
        $query->whereHas('payments', function($q) use ($filters) {
            $q->where('status', $filters['payment_status']);
        });
    }

    return $query->orderBy('created_at', 'desc')->get();
}    
}
?>