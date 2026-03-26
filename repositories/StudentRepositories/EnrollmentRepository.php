<?php
namespace App\Repositories\StudentRepositories;

use App\Core\Repository;
use Models\Enrollment;
use Models\EnrolledSubject;
use Models\Payment;
use Models\Curriculum;
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
  public function getCurriculumSubjects($courseId, $yearLevel, $semester)
  {
    return Curriculum::where('course_id', $courseId)
      ->where('year_level', $yearLevel)
      ->where('semester', $semester)
      ->with('subject') // This loads the subject details automatically
      ->get()
      ->map(function($item) {
        return [
          'id'            => $item->subject->id,
          'subject_code'  => $item->subject->subject_code,
          'subject_title' => $item->subject->subject_title,
          'units'         => $item->subject->units
        ];
      })
      ->toArray();
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
  public function findExistingEnrollment($userId, $periodId)
  {
    // Use the model instead of $this->db for consistency
    return Enrollment::where('user_id', $userId)
      ->where('period_id', $periodId)
      ->first(); 
  }

  /**
   * Removes a previous 'rejected' attempt so the student can re-apply.
   */
  public function clearPreviousAttempt($enrollmentId)
  {
    // Use the model's connection to handle the transaction
    $connection = Enrollment::getConnectionResolver()->connection();
    $connection->beginTransaction();

    try {
      // 1. Delete associated subjects (Assuming a pivot table or related model)
      EnrolledSubject::where('enrollment_id', $enrollmentId)->delete();

      // 2. Delete the main enrollment record
      Enrollment::where('id', $enrollmentId)->delete();

      $connection->commit();
      return true;
    } catch (\Exception $e) {
      $connection->rollBack();
      throw new \Exception("Failed to clear previous enrollment: " . $e->getMessage());
    }
  }
}