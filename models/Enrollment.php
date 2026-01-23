<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model {
  protected $table = 'enrollments';
  public $timestamps = true;

  protected $fillable = [
    'user_id',
    'period_id',
    'course_id',
    'grade_year',
    'id_number',
    'scholar_type',
    'status'
  ];
  public function payments() {
    return $this->hasMany(Payment::class);
  }
  public function subjects() {
    // Many-to-Many relationship via enrolled_subjects table
    return $this->belongsToMany(Subject::class, 'enrolled_subjects', 'enrollment_id', 'subject_id');
  }
  public function period() {
    return $this->belongsTo(AcademicPeriod::class, 'period_id');
  }

  public function course() {
    return $this->belongsTo(Course::class, 'course_id');
  }
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function getStatusColor() {
    return match($this->status) {
      'enrolled' => 'badge-status-enrolled',
      'pending'  => 'badge-status-pending',
      'rejected' => 'bg-danger',
      default    => 'bg-secondary'
    };
  }
}