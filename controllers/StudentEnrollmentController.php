<?php

namespace Controllers;

use App\Core\Controller;
use App\Core\Request;
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
      'courses'  => $courseRepo->all()
    ]);
  }


public function submit(Request $request)
{
  try {
    $userId = $_SESSION['user_id'];
    $data = $request->all();
    
    // Ensure we are getting the subjects array from our hidden inputs
    $subjectIds = $data['subjects'] ?? [];

    // Basic Validation
    if (empty($data['period_id'])) {
      throw new Exception("Please select an academic period.");
    }
    
    if (empty($data['course_id'])) {
      throw new Exception("Please select your course.");
    }

    if (empty($subjectIds)) {
      throw new Exception("Please select at least one subject to proceed.");
    }

    // Pass the sanitized data to the repository
    // The repository should handle the DB transaction for both 'enrollments' and 'enrolled_subjects'
    $this->enrollRepo->enroll($userId, $data, $subjectIds);

    $_SESSION['success'] = "Enrollment submitted successfully! Please settle your payment to finalize admission.";
    return $this->redirect('/student/dashboard');

  } catch (Exception $e) {
    if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
        $_SESSION['error'] = "You have already submitted an application for this academic period.";
    } else {
        $_SESSION['error'] = "An unexpected error occurred. Please try again.";
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
}