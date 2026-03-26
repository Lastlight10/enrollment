<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class EnrolledSubject extends Model {
  protected $table = 'enrolled_subjects';
  
  // Usually, pivot tables don't need timestamps unless you specifically added them
  public $timestamps = false;

  protected $fillable = [
    'enrollment_id',
    'subject_id'
  ];

  /**
   * Relationship back to the main Enrollment header
   */
  public function enrollment() {
    return $this->belongsTo(Enrollment::class, 'enrollment_id');
  }

  /**
   * Relationship to the specific Subject details
   */
  public function subject() {
    return $this->belongsTo(Subject::class, 'subject_id');
  }
}