<?php
namespace Controllers;

use App\Core\Controller;
use Models\User;

class StudentController extends Controller {
  public function __construct() {
    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
      $_SESSION['error'] = "Please log in to access the dashboard.";
      $this->redirect('/auth/login');
    }

    // 2. Check if user is a student
    if ($_SESSION['user_type'] !== 'student') {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/auth/login');
    }
  }

  public function dashboard() {
  $userId = $_SESSION['user_id'];
  $user = User::find($userId);

  // 1. Get Enrollment History (for the table)
  $enrollmentRepo = new \App\Repositories\StudentRepositories\EnrollmentRepository();
  $history = $enrollmentRepo->getStudentHistory($userId);

  // 2. Check current enrollment status for the progress bar
  // We'll assume the latest enrollment represents the current status
  $currentEnrollment = $history->first(); 
  $is_paid = false;
  if ($currentEnrollment) {
    // Check if there are any verified payments for this enrollment
    $is_paid = $currentEnrollment->payments()->where('status', 'verified')->exists();
  }

  // 3. Get data for the Modal dropdowns
  $periodRepo = new \App\Repositories\StaffRepositories\AcademicPeriodRepository();
  $courseRepo = new \App\Repositories\StaffRepositories\CourseRepository();
  $subjectRepo = new \App\Repositories\StaffRepositories\SubjectRepository();

  $this->studentView('student/dashboard', [
    'title'     => 'Student Dashboard',
    'user_name' => $user->username,
    'status'    => $user->status,
    'history'   => $history,
    'is_paid'   => $is_paid,
    'periods'   => $periodRepo->all(),
    'courses'   => $courseRepo->all(),
    'subjects'  => $subjectRepo->all()
  ]);
}
}