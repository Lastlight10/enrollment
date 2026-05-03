<?php
namespace Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Request;
use App\Repositories\UserAccounts\UserRepository;
use Models\Enrollment;
use Exception;
use App\Repositories\StaffRepositories\EnrollmentRepository;

class StaffEnrollmentController extends Controller {
  protected $enrollmentRepo;
  protected $userRepo;

  public function __construct() {
    if (!isset($_SESSION['user_id'])) {
      $_SESSION['error'] = "Please log in to access the dashboard.";
      $this->redirect('/auth/login');
    }
    // Corrected check: if not staff AND not admin, redirect
    if ($_SESSION['user_type'] !== 'staff' && $_SESSION['user_type'] !== 'admin') {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/dashboard'); // Adjusted to general dashboard
    }
    $this->enrollmentRepo = new EnrollmentRepository();
    $this->userRepo = new UserRepository();
  }

  public function enrollments() {
    return $this->staffView('staff/enrollments', [
      // Ensure the Repository's all() method uses ->with(['user', 'course'])
      'enrollments' => $this->enrollmentRepo->all(),
      'title' => 'Manage Enrollments'
    ]);
  }

  /**
   * New: View Enrollment Details
   */
  public function details(Request $request, $id) {
    // Now $id will correctly be the number '1' instead of the Request object
    $enrollment = $this->enrollmentRepo->findById($id);
    
    if (!$enrollment) {
        $_SESSION['error'] = "Enrollment record not found.";
        return $this->redirect('/staff/enrollments');
    }

    return $this->staffView('staff/enrollment_details', [
        'e' => $enrollment,
        'title' => 'Enrollment Details'
    ]);
}

public function approve(Request $request, $id) {
    $validated = $request->validate([
      'fees' => 'required|array|min:1',
      // ADDED 'full_payment' to the allowed types here
      'fees.*.type' => 'required|in:downpayment,full_payment,prelim,midterm,finals,others',
      'fees.*.amount' => 'required|numeric|min:0'
    ]);

    $enrollment = $this->enrollmentRepo->approveWithFees($id, $validated['fees']);

    if ($enrollment) {
      $this->userRepo->enrollStudent($enrollment->user_id);
      
      $paymentAmount = 0;
      foreach ($validated['fees'] as $fee) {
        // Updated to check for BOTH downpayment and full_payment
        if ($fee['type'] === 'downpayment' || $fee['type'] === 'full_payment') {
          $paymentAmount = $fee['amount'];
          break;
        }
      }

      // Pass the found amount (either downpayment or full payment) to the email
      $this->enrollmentRepo->sendApprovalEmail($enrollment, $paymentAmount);
    }

    $_SESSION['success'] = "Enrollment Approved and fees generated.";
    return $this->redirect('/staff/enrollments');
}

  /**
   * New: Reject with Comments
   */
  /**
 * Reject with Comments
 */
public function reject(Request $request, $id) {
    $validated = $request->validate([
        'staff_comments' => 'required|string|max:100'
    ]);

    $enrollment = $this->enrollmentRepo->updateStatus($id, 'rejected', $validated['staff_comments']);

    if ($enrollment) {
        // We wrap this in a try-catch in the Repo, but let's check here too
        $result = $this->enrollmentRepo->sendRejectionEmail($enrollment);
        
        if ($result === true) {
            $_SESSION['success'] = "Application rejected and email sent!";
        } else {
            // This will pass the Gmail error to your frontend alert
            $_SESSION['error'] = "Status updated, but EMAIL FAILED: " . $result;
        }
    }

    return $this->redirect('/staff/enrollments');
}
    public function drop(Request $request, $id) {
      // Perform update and get the FRESH object back
      $enrollment = $this->enrollmentRepo->updateStatus($id, 'dropped');

      if ($enrollment && $enrollment->user) {
        $user_email = $enrollment->user->email;
        
        // This will now have the 'user' and 'period' relationships loaded
        $this->enrollmentRepo->sendDroppedEmail($enrollment);

        $_SESSION['success'] = "Enrollment has been dropped for: {$user_email}";
      } else {
        $_SESSION['error'] = "Failed to drop enrollment or user not found.";
      }

      return $this->redirect('/staff/enrollments');
    }
  public function announceEmail(Request $request) {
    try {
      $ids = $request->input('enrollment_ids');
      $type = $request->input('payment_type');
      $start = $request->input('startDate');
      $end = $request->input('endDate');

      if (empty($ids)) {
        $_SESSION['error'] = "No enrolled students.";
        throw new Exception("No students selected for announcement.");
          
      }

      $count = $this->enrollmentRepo->sendBulkAnnouncement($ids, $type, $start, $end);

      $_SESSION['success'] = "Successfully sent announcement to $count students.";
    } catch (Exception $e) {
      $_SESSION['error'] = $e->getMessage();
    }
    return $this->redirect('/staff/enrollments');
  }
  public function printReport(Request $request) {
    try {
      // 1. Collect filters from GET request
      $filters = [
          'search'         => $request->input('search'),
          'course'         => $request->input('course'),
          'status'         => $request->input('status'),
          'year'           => $request->input('year'),
          'period'         => $request->input('period'),
          'date'           => $request->input('date'),
          'payment_status' => $request->input('payment_status'),
      ];

      // 2. Fetch filtered data from Repository
      $enrollments = $this->enrollmentRepo->getFilteredEnrollments($filters);

      // 3. Setup Dompdf
      $projectRoot = realpath(__DIR__ . '/../');
      // 2. Setup Dompdf
      $options = new \Dompdf\Options();
      $options->set('isRemoteEnabled', true);
      $options->set('defaultFont', 'DejaVu Sans');
      $options->set('chroot', $projectRoot);
      
      $dompdf = new \Dompdf\Dompdf($options);

      // 4. Render View to HTML
      ob_start();
      include __DIR__ . '/../views/staff/enrollment_list_pdf.php';
      $html = ob_get_clean();

      // 5. Generate PDF
      $dompdf->loadHtml($html);
      $dompdf->setPaper('letter', 'portrait'); // Landscape is better for tables
      $dompdf->render();
      
      $filename = "Enrollment_Report_" . date('Y-m-d') . ".pdf";
      $dompdf->stream($filename, ["Attachment" => false]);
      exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Report Error: " . $e->getMessage();
        return $this->redirect('/staff/enrollments');
    }
  }

  public function verifyPayment(Request $request, $id) {
    try {
      $status = $request->input('status');
      $remarks = $request->input('remarks');

      if (!in_array($status, ['paid', 'unpaid'])) {
        throw new Exception("Invalid status selected.");
      }

      // This now returns the Payment model with User/Enrollment data attached
      $payment = $this->enrollmentRepo->getPaymentById($id);

      $this->enrollmentRepo->updatePaymentStatus($id, [
        'status'      => $status,
        'remarks'     => $remarks,
        'verified_by' => $_SESSION['user_id']
      ]);

      // Now $payment->enrollment->user->email will be available for this function
      $this->enrollmentRepo->sendPaymentUpdateEmail($payment, $status, $remarks);

      $_SESSION['success'] = ($status === 'paid')
        ? "Payment approved and student notified!"
        : "Payment rejected and student notified.";

    } catch (Exception $e) {
      // This will catch both your custom Exception and Eloquent's ModelNotFoundException
      $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    return $this->redirect($_SERVER['HTTP_REFERER']);
  }
  
  public function downloadPdf(Request $request, $id)
{
    try {
        // 1. Fetch data (Staff can see any enrollment, no user_id check)
        $enrollment = Enrollment::with(['course', 'subjects', 'payments', 'user', 'period'])->find($id);

        if (!$enrollment) {
            $_SESSION['error'] = "Enrollment record not found.";
            return $this->redirect('/staff/enrollments');
        }
        $projectRoot = realpath(__DIR__ . '/../');
        // 2. Setup Dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', $projectRoot);
        
        $dompdf = new \Dompdf\Dompdf($options);

        // 3. Prepare data for the template
        $e = $enrollment; 
        
        ob_start();
        // You can reuse the SAME template you made for the student!
        include __DIR__ . '/../views/student/pdf_template.php'; 
        $html = ob_get_clean();

        // 4. Processing
        $dompdf->loadHtml($html);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        // 5. Stream
        $dompdf->stream("OFFICIAL_Enrollment_{$e->id_number}_{$id}.pdf", ["Attachment" => false]);
        exit;

    } catch (Exception $ex) {
        $_SESSION['error'] = "Staff PDF Error: " . $ex->getMessage();
        return $this->redirect("/staff/enrollments/details/$id");
    }
}
// Ensure the order matches what your Router sends
public function addFees(Request $request, $id) { 
    // 1. Get the data from the Request object correctly
    $fees = $request->input('fees') ?? [];

    if (empty($fees)) {
        $_SESSION['error'] = "No fees provided";
        return $this->redirect("/staff/enrollments/details/$id");
    }

    try {
        // 2. PASS THE $id (the string/number), NOT the $request object
        $this->enrollmentRepo->addAdditionalPayments($id, $fees);

        $_SESSION['success'] = "Fees added successfully";
    } catch (\Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    return $this->redirect("/staff/enrollments/details/$id");
}
}