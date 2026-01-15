<?php
namespace Controllers;

use App\Core\Controller;
use App\Repositories\StaffRepositories\SubjectRepository;
use App\Repositories\StaffRepositories\CourseRepository;
use Exception;

class SubjectController extends Controller {
  private $subjectRepo;
  private $courseRepo;

  public function __construct() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'staff' && $_SESSION['user_type'] !== 'admin')) {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/auth/login');
    }
    $this->subjectRepo = new SubjectRepository();
    $this->courseRepo = new CourseRepository();
  }

  public function subjects() {
    return $this->staffView('staff/subjects', [
      'subjects' => $this->subjectRepo->all(),
      'courses' => $this->courseRepo->all(), // Needed for the dropdowns
      'title' => 'Manage Subjects'
    ]);
  }

  public function store() {
    try {
      $this->subjectRepo->create($_POST);
      $_SESSION['success'] = "Subject added successfully.";
    } catch (Exception $e) {
      $_SESSION['error'] = "Failed to add subject. Code might already exist.";
    }
    $this->redirect('/staff/subjects');
  }

  public function update($id) {
    try {
      $result = $this->subjectRepo->update($id, $_POST);
      if ($result === 'no_changes') {
        $_SESSION['info'] = "No changes made.";
      } else {
        $_SESSION['success'] = "Subject updated successfully.";
      }
    } catch (Exception $e) {
      $_SESSION['error'] = "Failed to update subject.";
    }
    $this->redirect('/staff/subjects');
  }

  public function delete($id) {
    try {
      $this->subjectRepo->delete($id);
      $_SESSION['success'] = "Subject deleted.";
    } catch (Exception $e) {
      $_SESSION['error'] = "Cannot delete subject linked to grades or schedules.";
    }
    $this->redirect('/staff/subjects');
  }
}