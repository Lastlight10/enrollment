<?php
namespace App\Repositories\StudentRepositories;

use App\Core\Repository;
use Models\Enrollment;
use Models\Payment;
use Models\AcademicPeriod;
// Use the Capsule Manager instead of the Facade
use Illuminate\Database\Capsule\Manager as Capsule;

class EnrollmentRepository extends Repository{
  
  public function enroll($userId, array $data, array $subjectIds) {
    // Using Capsule::transaction handles the "Facade root" issue
    return Capsule::transaction(function() use ($userId, $data, $subjectIds) {
      $enrollment = Enrollment::create([
        'user_id'      => $userId,
        'period_id'    => $data['period_id'],
        'course_id'    => $data['course_id'],
        'grade_year'   => $data['grade_year'],
        'id_number'    => $data['id_number'],
        'scholar_type' => $data['scholar_type'],
        'status'       => 'pending'
      ]);

      // Attach the subjects to the pivot table
      if (!empty($subjectIds)) {
        $enrollment->subjects()->attach($subjectIds);
      }

      return $enrollment;
    });
  }
  public function getByStudent($userId)
  {
    return Enrollment::with(['course', 'period'])
      ->where('user_id', $userId)
      ->orderBy('created_at', 'desc')
      ->get();
  }

  public function getStudentHistory($userId) {
    return Enrollment::where('user_id', $userId)
      ->with(['period', 'course', 'subjects']) // Added period and course for your dashboard
      ->orderBy('created_at', 'desc')
      ->get();
  }

  public function findForStudent($userId, $enrollmentId)
  {
    // Changed 'users' to 'user' assuming a standard BelongsTo relationship
    return Enrollment::with(['course', 'subjects', 'payments', 'user']) 
      ->where('user_id', $userId)
      ->where('id', $enrollmentId)
      ->first();
  }

  public function updatePaymentProof($paymentId, $fileName)
  {
    return Payment::where('id', $paymentId)->update([
      'proof_path' => $fileName,
      'status' => 'unpaid' // Ensure it stays unpaid until staff verifies
    ]);
  }
  // In AcademicPeriodRepository.php
  
}