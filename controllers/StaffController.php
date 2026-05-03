<?php

namespace Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Request; // Import Request
use Models\User;
use Models\Payment;
use Models\Enrollment;
use App\Repositories\UserAccounts\UserRepository;
use App\Repositories\StaffRepositories\CourseRepository;
use Exception;


class StaffController extends Controller
{
  private $userRepo;

  public function __construct()
  {
    if (!isset($_SESSION['user_id'])) {
      $_SESSION['error'] = "Please log in to access the dashboard.";
      $this->redirect('/auth/login');
    }

    if ($_SESSION['user_type'] !== 'staff' && $_SESSION['user_type'] !== 'admin') {
      $_SESSION['error'] = "Unauthorized access.";
      $this->redirect('/auth/login');
    }
    $this->userRepo = new UserRepository();
  }

  public function dashboard()
  {
      // Assuming you have models for User, Enrollment, and Payment
      $activeStudents = User::where('type', 'student')->where('status', 'active')->count();
      $pendingEnrollments = Enrollment::where('status', 'pending')->count();
      
      // Payment Statistics
      $paidPayments = Payment::where('status', 'paid')->count();
      $unverifiedPayments = Payment::where('status', 'need_verification')->count();
      $unpaidPayments = Payment::where('status', 'unpaid')->count();
      $totalRevenue = Payment::where('status', 'paid')->sum('amount');

      // Recent Enrollments for the table
      $recent = Enrollment::with(['user', 'course'])
                  ->orderBy('created_at', 'DESC')
                  ->limit(5)
                  ->get();

      $this->staffView('staff/dashboard', [
          'title'             => 'Staff Home',
          'activeCount'       => $activeStudents,
          'pendingCount'      => $pendingEnrollments,
          'paidCount'         => $paidPayments,
          'unverifiedCount'   => $unverifiedPayments,
          'unpaidCount'       => $unpaidPayments,
          'totalRevenue'      => $totalRevenue,
          'recentEnrollments' => $recent
      ]);
  }
  public function payments()
  {
    return $this->staffView('staff/payments', [
      'title' => 'Manage Payments',
    ]);
  }

  public function user_accounts()
  {
    $courseRepo = new CourseRepository();
    $users = User::where('type', '!=', 'admin')
        ->leftJoin('student_courses', 'users.id', '=', 'student_courses.user_id')
        ->leftJoin('courses', 'student_courses.course_id', '=', 'courses.id')
        ->select('users.*', 'courses.course_name', 'courses.id as course_id')
        ->get();
    return $this->staffView('staff/user_accounts', [
      'users' => $users,
      'title' => 'Manage Accounts',
      'courses' => $courseRepo->allNoGe(),
    ]);
  }

  // FIX: Added Request $request
  public function addAccount(Request $request)
  {
    $data = $request->all(); // Use request object instead of $_POST

    if (empty($data['username']) || empty($data['email'])) {
      $_SESSION['error'] = "Username and Email are required fields.";
      return $this->redirect('/staff/user_accounts');
    }

    if ($this->userRepo->exists('email', $data['email'])) {
      $_SESSION['error'] = "The email '{$data['email']}' is already registered.";
      return $this->redirect('/staff/user_accounts');
    }
    
    if ($this->userRepo->exists('username', $data['username'])) {
      $_SESSION['error'] = "The username '{$data['username']}' is already taken.";
      return $this->redirect('/staff/user_accounts');
    }

    try {
      $token = bin2hex(random_bytes(32));
      $data['status'] = 'inactive'; 
      $data['verification_token'] = $token;

      $user = $this->userRepo->createAccount($data);
      if ($user) {
        $this->userRepo->sendRegisteredStaffEmail($user->email);
        $_SESSION['success'] = "Staff account created! Verification email sent to {$user->email}.";
      }
    } catch (Exception $e) {
      Logger::log("Critical Error in addAccount: " . $e->getMessage());
      $_SESSION['error'] = "System error: Could not complete registration.";
    }

    $this->redirect('/staff/user_accounts');
  }

  // FIX: Added Request $request, $id
  public function updateAccount(Request $request, $id)
  {
    $data = $request->all();
    
    // 1. Update the basic User record (names, email, status, etc.)
    $result = $this->userRepo->updateAccount($id, $data);

    if ($result) {
      $user = $this->userRepo->find($id);

      // 2. Handle Student-Specific Course Locking
      // Check the 'type' from the hidden input or the user object
      if ($user->type === 'student' && !empty($data['course_id'])) {
        $currentCourseId = $this->userRepo->getEnrolledCourse($id);

        if ($currentCourseId != $data['course_id']) {
          $this->userRepo->updateStudentCourse($id, $data['course_id']);
          $this->userRepo->sendStudentCourse($id,$data['course_id']);
          $_SESSION['success'] = "Student course updated successfully.";
        } else {
          if (!$user->isDirty()) {
            $_SESSION['info'] = "No changes were made to the account or course.";
          }
        }
      }

      // 3. Send Notification
      

      if ($result === 'no_changes') {
        $_SESSION['info'] = "No changes were made to the personal information.";
      } else {
        $_SESSION['success'] = "Personal Information updated. Notification email has been sent.";
        $this->userRepo->sendPendingApprovalEmail($user->email);
      }
    } else {
      $_SESSION['error'] = "Failed to update account.";
    }
    
    return $this->redirect('/staff/user_accounts');
  }

  // FIX: Added Request $request, $id
  public function deleteAccount(Request $request, $id)
  {
    if ($id == $_SESSION['user_id']) {
      $_SESSION['error'] = "You cannot delete your own account.";
      return $this->redirect('/staff/user_accounts');
    }

    try {
      $this->userRepo->deleteAccount($id);
      $_SESSION['success'] = "Account deleted successfully.";
    } catch (Exception $e) {
      Logger::log("Delete error: " . $e->getMessage());
      $_SESSION['error'] = "Could not delete account. It may be linked to other records.";
    }
    
    $this->redirect('/staff/user_accounts');
  }
  public function printUserReport(Request $request) {
    try {
        // 1. Gather filters from the request
        $filters = [
            'type'   => $request->input('type'),
            'search' => $request->input('search')
        ];

        // 2. Fetch filtered data (we'll define this method in the Repo next)
        $users = $this->userRepo->getFilteredUsersForReport($filters);

        $projectRoot = realpath(__DIR__ . '/../');
        // 2. Setup Dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', $projectRoot);
        
        $dompdf = new \Dompdf\Dompdf($options);
        // 4. Capture the HTML View
        ob_start();
        include __DIR__ . '/../views/staff/user_accounts_pdf.php';
        $html = ob_get_clean();

        // 5. Generate and Stream
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        
        $dompdf->stream("User_Accounts_Report_" . date('Ymd') . ".pdf", ["Attachment" => false]);
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Print Error: " . $e->getMessage();
        return $this->redirect('/staff/user_accounts');
    }
}
}