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
      'scholar_type',
      'status'
    ];
    public function payments() {
    return $this->hasMany(Payment::class);
}

}