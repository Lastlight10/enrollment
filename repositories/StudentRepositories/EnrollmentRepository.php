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
  
  // ... inside EnrollmentRepository class

/**
 * Finds if a student has ANY record for the period that is currently active,
 * regardless of which course it was for.
 */
public function findActiveInPeriod($userId, $periodId)
{
    return Enrollment::where('user_id', $userId)
        ->where('period_id', $periodId)
        ->whereIn('status', ['pending', 'enrolled'])
        ->first();
}

/**
 * Updates status (used for marking old records as 'shifted')
 */
public function updateStatus($enrollmentId, $status)
{
    return Enrollment::where('id', $enrollmentId)->update([
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

public function enroll($userId, array $data, array $subjectIds) {
    return Capsule::transaction(function() use ($userId, $data, $subjectIds) {
        
        // Since the Controller already handles the "Shifting" logic, 
        // we just perform a final safety check for exact duplicates.
        $alreadyApplied = Enrollment::where('user_id', $userId)
            ->where('period_id', $data['period_id'])
            ->where('course_id', $data['course_id'])
            ->whereIn('status', ['pending', 'enrolled'])
            ->exists();

        if ($alreadyApplied) {
            throw new \Exception("You already have an active application for this specific course.");
        }

        // Create the new Enrollment record
        $enrollment = Enrollment::create([
            'user_id'      => $userId,
            'period_id'    => $data['period_id'],
            'course_id'    => $data['course_id'], 
            'grade_year'   => $data['grade_year'],
            'id_number'    => $data['id_number'],
            'scholar_type' => $data['scholar_type'],
            'status'       => 'pending'
        ]);

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
      'status'     => 'need_verification',
      'remarks'    => null, 
      'updated_at' => date('Y-m-d H:i:s') 
    ]);
  }
  // In AcademicPeriodRepository.php
  public function findExistingEnrollment($userId, $periodId, $courseId)
  {
    return Enrollment::where('user_id', $userId)
      ->where('period_id', $periodId)
      ->where('course_id', $courseId) // Filter by course now
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