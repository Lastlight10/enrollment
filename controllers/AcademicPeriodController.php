<?php
namespace Controllers;

use App\Core\Controller;
use App\Repositories\StaffRepositories\AcademicPeriodRepository;
use Exception;

class AcademicPeriodController extends Controller {
  private $periodRepo;

  public function __construct() {
  // Ensure we check if the ID exists FIRST
  if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to continue.";
    $this->redirect('/auth/login');
  }

  // Group the user type checks so PHP evaluates them as a single "Authorized" block
  if ($_SESSION['user_type'] !== 'staff' && $_SESSION['user_type'] !== 'admin') {
    $_SESSION['error'] = "Invalid User type.";
    $this->redirect('/auth/login');
  }

  $this->periodRepo = new AcademicPeriodRepository();
}

  public function academic_periods() {
    return $this->staffView('staff/academic_periods', [
      'periods' => $this->periodRepo->all(),
      'title' => 'Academic Periods'
    ]);
  }

  public function store() {
    $this->periodRepo->create($_POST);
    $_SESSION['success'] = "Academic period added.";
    $this->redirect('/staff/academic_periods');
  }

  public function update($id) {
    $result = $this->periodRepo->update($id, $_POST);
    if ($result === 'no_changes') {
      $_SESSION['info'] = "No changes made.";
    } else {
      $_SESSION['success'] = "Period updated successfully.";
    }
    $this->redirect('/staff/academic_periods');
  }
  public function delete($id) {
  try {
    $this->periodRepo->delete($id);
    $_SESSION['success'] = "Academic period deleted successfully.";
  } catch (Exception $e) {
    // This triggers if the period is linked to subjects, students, or grades
    $_SESSION['error'] = "Cannot delete this period because it is currently linked to other records.";
  }
  $this->redirect('/staff/academic_periods');
}
}