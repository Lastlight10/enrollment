<?php

namespace Controllers;

use App\Core\Controller;
use App\Core\Request;
use Models\Enrollment;
use App\Core\Router;
use App\Repositories\StudentRepositories\EnrollmentRepository as StudentEnrollmentRepo;
use App\Repositories\StaffRepositories\AcademicPeriodRepository;
use App\Repositories\StaffRepositories\SubjectRepository;
use App\Repositories\StaffRepositories\CourseRepository;
use Exception;

class StudentEnrollmentController extends Controller
{
  private $enrollRepo;

  public function __construct()
  {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
      $_SESSION['error'] = "Only students can access the enrollment page.";
      $this->redirect('/auth/login');
    }
    $this->enrollRepo = new StudentEnrollmentRepo();
  }

  public function index()
  {
    $userId = $_SESSION['user_id'];
    // Fetch enrollments with related course info
    $enrollments = $this->enrollRepo->getByStudent($userId);

    return $this->studentView('student/enrollments_list', [
      'title' => 'My Enrollments',
      'enrollments' => $enrollments
    ]);
  }
  public function showForm()
    {
      $periodRepo = new AcademicPeriodRepository();
      $subjectRepo = new SubjectRepository();
      $courseRepo = new CourseRepository();

      $periods = $periodRepo->getActivePeriods(); 
      
      return $this->studentView('student/enroll', [
        'title'    => 'Online Enrollment',
        'periods'  => $periods,
        'subjects' => $subjectRepo->all(),
        'courses'  => $courseRepo->allNoGe()
      ]);
    }


  public function submit(Request $request)
  {
  try {
    $userId = $_SESSION['user_id'];
    $data = $request->all();
    $periodId = $data['period_id'] ?? null;
    $subjectIds = $data['subjects'] ?? [];

    // 1. Basic Validation
    if (empty($periodId)) throw new Exception("Please select an academic period.");
    if (empty($subjectIds)) throw new Exception("Please select at least one subject.");

    // 2. Check for existing application
    $existing = $this->enrollRepo->findExistingEnrollment($userId, $periodId);

    if ($existing) {
      $status = $existing->status; 

      // CASE: DROPPED - Permanent block for this period
      if ($status === 'dropped') {
        $_SESSION['error'] = "This enrollment has been dropped and cannot be re-submitted for this period.";
        return $this->redirect('/student/enroll');
      }

      // CASE: PENDING or ENROLLED - Active block
      if ($status === 'pending' || $status === 'enrolled') {
        $_SESSION['error'] = ($status === 'enrolled') 
          ? "You are already officially enrolled for this period." 
          : "You already have a pending application. Please wait for approval.";
        return $this->redirect('/student/enroll');
      }

      // CASE: REJECTED - Clear old data and allow fresh re-submission
      if ($status === 'rejected') {
        $this->enrollRepo->clearPreviousAttempt($existing->id); 
      }
    }

    // 3. Proceed with Enrollment (For brand new or previously rejected)
    $this->enrollRepo->enroll($userId, $data, $subjectIds);

    $_SESSION['success'] = "Enrollment submitted successfully!";
    return $this->redirect('/student/dashboard');

  } catch (Exception $e) {
    if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
      $_SESSION['error'] = "An active application already exists for this period.";
    } else {
      $_SESSION['error'] = $e->getMessage() ?: "An unexpected error occurred.";
    }
    return $this->redirect('/student/enroll');
  }
  }
  
  public function viewDetails(Request $request, $id)
  {
    // Now $id will correctly be the number '1' from the URL
    $enrollment = $this->enrollRepo->findForStudent($_SESSION['user_id'], $id);

    if (!$enrollment) {
      $_SESSION['error'] = "Record not found or access denied.";
      return $this->redirect('/student/dashboard');
    }

    return $this->studentView('student/enrollment_details', [
      'title' => 'Enrollment Details',
      'e' => $enrollment
    ]);
  }
  // In App\Repositories\StudentRepositories\EnrollmentRepository.php
 public function getSuggestedSubjects(Request $request) 
{
    try {
        $courseId  = $request->input('course_id');
        $yearLevel = $request->input('year_level');
        $periodId  = $request->input('period_id');

        // 1. Validate Input
        if (!$courseId || !$yearLevel || !$periodId) {
            throw new Exception("Missing required enrollment details.");
        }

        // 2. Ensure Repository is initialized (if not already in __construct)
        if (!$this->enrollRepo) {
            $this->enrollRepo = new StudentEnrollmentRepo();
        }

        // 3. Find the Period
        $periodRepo = new AcademicPeriodRepository();
        $period = $periodRepo->find($periodId);

        if (!$period) {
            throw new Exception("Academic period (ID: $periodId) not found in database.");
        }

        // 4. Fetch the subjects
        // IMPORTANT: Ensure $period->semester matches the values in your curriculum table
        $subjects = $this->enrollRepo->getCurriculumSubjects(
            $courseId, 
            $yearLevel, 
            $period->semester
        );

        // 5. Success Response
        header('Content-Type: application/json');
        echo json_encode($subjects ?: []); // Return empty array instead of null
        exit;

    } catch (Exception $e) {
        // This is what is sending the 400 error you see in the console
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $e->getMessage(),
            'debug_info' => [
                'period_id' => $periodId ?? 'none'
            ]
        ]);
        exit;
    }
}

  public function uploadProof(Request $request, $paymentId)
  {
    try {
      $file = $_FILES['proof_image'] ?? null;

      if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Invalid file upload.");
      }

      // Security: Global namespace backslash added here
      $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
      $fileType = \mime_content_type($file['tmp_name']); 
      
      if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Only JPG and PNG images are allowed.");
      }

      // Use absolute pathing or ensure base directory is correct for your framework
      $uploadDir = 'static/images/uploads/payments/';
      
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      // Clean extension handling
      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $fileName = 'pay_' . $paymentId . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
      $targetPath = $uploadDir . $fileName;

      if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Update DB via Repository
        $this->enrollRepo->updatePaymentProof($paymentId, $fileName);
        $_SESSION['success'] = "Receipt uploaded successfully. Please wait for staff verification.";
      } else {
        throw new Exception("Failed to save the image to the server.");
      }

    } catch (Exception $e) {
      $_SESSION['error'] = $e->getMessage();
    }

    $enrollmentId = $request->input('enrollment_id'); // Ensure your Request class supports this
    $redirectPath = $enrollmentId ? "/student/enrollment/details/$enrollmentId" : "/student/dashboard";
    
    return $this->redirect($redirectPath);
  }
  // In your EnrollmentController.php
  public function downloadPdf(Request $request, $id)
  {
      try {
          // 1. Fetch data using your existing repository logic
          $enrollment = $this->enrollRepo->findForStudent($_SESSION['user_id'], $id);

          if (!$enrollment) {
              $_SESSION['error'] = "Record not found or access denied.";
              return $this->redirect('/student/dashboard');
          }
          $projectRoot = realpath(__DIR__ . '/../');
          // 2. Setup Dompdf (ensure 'vendor/autoload.php' is loaded in your index.php)
          $options = new \Dompdf\Options();
          $options->set('isRemoteEnabled', true); // Critical for loading CSS/Images
          $options->set('defaultFont', 'DejaVu Sans');
          $options->set('chroot', $projectRoot);
          
          $dompdf = new \Dompdf\Dompdf($options);

          // 3. Render the HTML content 
          // We pass the $enrollment data (aliased as $e) to a separate view file
          $e = $enrollment; 
          
          // Use output buffering to capture the HTML from a view file
          ob_start();
          // Adjust this path to wherever you store your PDF templates
          include __DIR__ . '/../views/student/pdf_template.php';
          $html = ob_get_clean();

          // 4. Dompdf processing
          $dompdf->loadHtml($html);
          $dompdf->setPaper('Letter', 'portrait');
          $dompdf->render();

          // 5. Output PDF to Browser
          // "Attachment" => false opens it in the browser; true forces download
          $dompdf->stream("Enrollment_Ref_{$id}.pdf", ["Attachment" => false]);
          exit;

      } catch (Exception $e) {
          $_SESSION['error'] = "PDF Generation Error: " . $e->getMessage();
          return $this->redirect("/student/enrollment/details/$id");
      }
  }
}