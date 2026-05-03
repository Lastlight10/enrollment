<?php
namespace Controllers;

use App\Core\Controller;
use Models\StudentCourses;
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
  public function profile() {
    $userId = $_SESSION['user_id'];
    
    // Using your existing User model to find the logged-in student
    $user = User::find($userId);
    $user_course = StudentCourses::where('user_id', $userId)->first();

    if (!$user) {
      $_SESSION['error'] = "User not found.";
      return $this->redirect('/student/dashboard');
    }

    // This renders the profile.php template within your layout
    $this->studentView('student/profile', [
      'title' => 'My Profile',
      'user'  => $user,
      'user_course' => $user_course,
    ]);
  }

  public function dashboard() {
    $userId = $_SESSION['user_id'];
    $user = User::find($userId);

    // 1. Get Enrollment History
    $enrollmentRepo = new \App\Repositories\StudentRepositories\EnrollmentRepository();
    $history = $enrollmentRepo->getStudentHistory($userId);

    // 2. Fetch the OFFICIAL assigned course (the one staff just updated)
    // You likely have a method like this in your UserRepository
    $userRepo = new \App\Repositories\UserAccounts\UserRepository();
    $officialCourse = $userRepo->getEnrolledCourseDetails($userId); 

    // 3. Payment logic for the most recent enrollment
    $currentEnrollment = $history->first(); 
    $is_paid = false;
    if ($currentEnrollment) {
        $is_paid = $currentEnrollment->payments()->where('status', 'verified')->exists();
    }

    $periodRepo = new \App\Repositories\StaffRepositories\AcademicPeriodRepository();
    $courseRepo = new \App\Repositories\StaffRepositories\CourseRepository();
    $subjectRepo = new \App\Repositories\StaffRepositories\SubjectRepository();

    $this->studentView('student/dashboard', [
      'title'       => 'Student Dashboard',
      'user_name'   => $user->username,
      'status'      => $user->status,
      'history'     => $history,
      'is_paid'     => $is_paid,
      
      // CHANGE: Pass the official course record instead of the enrollment history record
      'user_course' => $officialCourse, 
      
      'periods'     => $periodRepo->all(),
      'courses'     => $courseRepo->all(),
      'subjects'    => $subjectRepo->all()
    ]);
}
  public function updateProfile() {
    $userId = $_SESSION['user_id'];
    $userRepo = new \App\Repositories\UserAccounts\UserRepository();

    // 1. Gather input from POST
    $data = [
      'first_name' => $_POST['first_name'],
      'mid_name'   => $_POST['mid_name'],
      'last_name'  => $_POST['last_name'],
      'email'      => $_POST['email'],
      'birth_date' => $_POST['birth_date'],
      'username'   => $_POST['username'],
    ];

    // 2. Logic: Handle Password Change
    if (!empty($_POST['password'])) {
      if ($_POST['password'] !== $_POST['password_confirmation']) {
        $_SESSION['error'] = "Passwords do not match.";
        return $this->redirect('/student/profile');
      }
      // Hash the password before saving
      $data['password'] = $_POST['password'];
    }

    // 3. Security: Check for duplicate email
    $existingUser = $userRepo->findByEmail($data['email']);
    if ($existingUser && $existingUser->id != $userId) {
      $_SESSION['error'] = "That email is already in use by another account.";
      return $this->redirect('/student/profile');
    }

    // 4. Update via Repository
    $result = $userRepo->update($userId, $data);

    if ($result === 'no_changes') {
      $_SESSION['info'] = "No changes were made to your profile.";
    } elseif ($result) {
      $_SESSION['success'] = "Profile updated successfully!";
    } else {
      $_SESSION['error'] = "Failed to update profile. Please try again.";
    }

    $this->redirect('/student/profile');
  }

}