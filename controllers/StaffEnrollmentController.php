<?php
namespace Controllers;

use App\Core\Controller;
use App\Core\Request;
use Models\Enrollment;
use Exception;
use App\Repositories\StaffRepositories\EnrollmentRepository;

class StaffEnrollmentController extends Controller {
  protected $enrollmentRepo;

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
      'fees.*.type' => 'required|in:downpayment,prelim,midterm,finals,others',
      'fees.*.amount' => 'required|numeric|min:0'
    ]);

    $this->enrollmentRepo->approveWithFees($id, $validated['fees']);
    $_SESSION['success'] = "Enrollment Approved and fees generated.";
    return $this->redirect('/staff/enrollments');
  }

  /**
   * New: Reject with Comments
   */
  public function reject(Request $request, $id) {
    $validated = $request->validate([
      'staff_comments' => 'required|string|max:500'
    ]);

    // Update status to rejected and save the staff comment
    $this->enrollmentRepo->updateStatus($id, 'rejected', $validated['staff_comments']);
    
    $_SESSION['success'] = "Application has been rejected.";
    return $this->redirect('/staff/enrollments');
  }

  public function drop(Request $request, $id) {
    $this->enrollmentRepo->updateStatus($id, 'dropped');
    $_SESSION['success'] = "Enrollment has been dropped.";
    return $this->redirect('/staff/enrollments');
  }
  public function verifyPayment(Request $request, $id)
  {
    try {
      // Validate inputs
      $status = $request->input('status'); 
      $remarks = $request->input('remarks');
      
      if (!in_array($status, ['paid', 'unpaid'])) {
        throw new Exception("Invalid status selected.");
      }

      $this->enrollmentRepo->updatePaymentStatus($id, [
        'status'      => $status,
        'remarks'     => $remarks,
        'verified_by' => $_SESSION['user_id']
      ]);

      $_SESSION['success'] = ($status === 'paid') 
        ? "Payment approved successfully!" 
        : "Payment rejected with remarks.";
          
    } catch (Exception $e) {
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

        // 2. Setup Dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
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
}