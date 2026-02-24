<?php
namespace Controllers;

use App\Core\Controller;
use App\Repositories\StaffRepositories\CurriculumRepository;

class StudentCurriculumController extends Controller {
  protected $curriculumRepo;

  public function __construct() {
    if (!isset($_SESSION['user_id'])) {
      $_SESSION['error'] = "Please log in to access the dashboard.";
      $this->redirect('/auth/login');
    }

    // Allow students, staff, and admins to view the curriculum
    $allowedRoles = ['student', 'staff', 'admin'];
    if (!in_array($_SESSION['user_type'], $allowedRoles)) {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/dashboard');
    }

    $this->curriculumRepo = new CurriculumRepository();
  }

  public function index() {
    return $this->studentView('student/curriculum', [
      'curriculums' => $this->curriculumRepo->getActiveCurriculums(),
      'title' => 'Available Curriculums'
    ]);
  }

  public function viewCurriculum($request, $id) {
    $course = $this->curriculumRepo->getByCourseId($id);

    if (!$course) {
      $_SESSION['error'] = "Curriculum not found.";
      $this->redirect('/student/curriculum');
    }

    return $this->studentView('student/curriculum_details', [
      'course' => $course,
      'title' => $course->name . " Curriculum"
    ]);
  }
}