<?php
namespace Controllers;

use App\Repositories\UserAccounts\UserRepository;
use App\Repositories\StaffRepositories\CourseRepository;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Logger;
use Exception;

class CourseController extends Controller {
  // Properties must be declared outside the constructor
  private $userRepo;
  private $courseRepo;

  public function __construct() {
    if (!isset($_SESSION['user_id'])) {
      $_SESSION['error'] = "Please log in to access the dashboard.";
      $this->redirect('/auth/login');
    }
    if ($_SESSION['user_type'] !== 'staff' && $_SESSION['user_type'] !== 'admin') {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/staff/dashboard');
    }

    $this->userRepo = new UserRepository();
    $this->courseRepo = new CourseRepository();
  }

  public function courses() {
    return $this->staffView('staff/courses', [
      'courses' => $this->courseRepo->all(),
      'title' => 'Manage Courses'
    ]);
  }

  public function addCourse(Request $request) { // Add Request parameter
        try {
            // Use $request->all() instead of $_POST for consistency
            $this->courseRepo->create($request->all());
            $_SESSION['success'] = "Course created successfully.";
        } catch (Exception $e) {
            Logger::log("Course Create Error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to create course. Code may already exist.";
        }
        $this->redirect('/staff/courses');
    }

    public function updateCourse(Request $request, $id) { // FIX: Added Request $request
        try {
            $result = $this->courseRepo->update($id, $request->all());

            if ($result === 'no_changes') {
                $_SESSION['info'] = "No changes were made to the course.";
            } else {
                $_SESSION['success'] = "Course updated successfully.";
            }
        } catch (Exception $e) {
            Logger::log("Course Update Error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update course.";
        }
        $this->redirect('/staff/courses');
    }

    public function deleteCourse(Request $request, $id) { // FIX: Added Request $request
        try {
            $this->courseRepo->delete($id);
            $_SESSION['success'] = "Course deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Cannot delete course while subjects or students are linked to it.";
        }
        $this->redirect('/staff/courses');
    }
}