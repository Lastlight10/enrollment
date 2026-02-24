<?php
namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use Models\Enrollment;
use Illuminate\Support\Facades\DB;

class EnrollmentRepository extends Repository{
  public function findById($id) {
    return Enrollment::with(['user', 'course', 'subjects', 'payments'])->findOrFail($id);
  }
  public function all() {
    // Replace Enrollment::all() with this:
    return Enrollment::with(['user', 'course', 'period'])
            ->orderBy('created_at', 'desc')
            ->get();
  }

  public function updateStatus($id, $status, $comments = null) {
    $data = ['status' => $status];
    
    // If a comment is provided (for rejections), add it to the update array
    if ($comments !== null) {
        $data['staff_comments'] = $comments;
    }

    return Enrollment::where('id', $id)->update($data);
  }

  public function createSinglePayment($enrollmentId, $data) {
    $enrollment = Enrollment::findOrFail($enrollmentId);
    return $enrollment->payments()->create([
      'payment_type' => $data['payment_type'],
      'amount' => $data['amount'],
      'remarks' => $data['remarks'] ?? null,
      'status' => 'unpaid'
    ]);
  }
  
  public function createApplication(array $data, array $subjectIds) {
    return DB::transaction(function () use ($data, $subjectIds) {
      // Create the enrollment record
      $enrollment = Enrollment::create($data);

      // Sync subjects to the junction table (enrolled_subjects)
      if (!empty($subjectIds)) {
        $enrollment->subjects()->attach($subjectIds);
      }

      return $enrollment;
    });
  }

  public function getPendingWithDetails() {
    return Enrollment::with(['user', 'course', 'subjects'])
      ->where('status', 'pending')
      ->get();
  }
  public function approveWithFees(int $id, array $fees) {
    return DB::transaction(function () use ($id, $fees) {
      $enrollment = Enrollment::findOrFail($id);

      // 1. Update Enrollment status
      $enrollment->update(['status' => 'enrolled']);

      // 2. Insert manual fees into payments table
      foreach ($fees as $fee) {
        $enrollment->payments()->create([
          'payment_type' => $fee['type'], // downpayment, prelim, etc.
          'amount' => $fee['amount'],
          'status' => 'unpaid'
        ]);
      }

      return $enrollment;
    });
  }
  public function updatePaymentStatus($paymentId, $data)
{
    return DB::table('payments')->where('id', $paymentId)->update([
        'status' => $data['status'],
        'remarks' => $data['remarks'],
        'verified_by' => $data['verified_by'],
    ]);
}
public function findForStudent($userId, $enrollmentId)
  {
    // Changed 'users' to 'user' assuming a standard BelongsTo relationship
    return Enrollment::with(['course', 'subjects', 'payments', 'user']) 
      ->where('user_id', $userId)
      ->where('id', $enrollmentId)
      ->first();
  }
  
}
?>