<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model {
    protected $table = 'curriculums';
    public $timestamps = true;
    // Updated to match your SQL columns
    protected $fillable = [
      'course_id',
      'subject_id',
      'year_level',
      'semester',
    ];

    // Get the Course this curriculum entry belongs to
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Get the Subject for this specific curriculum entry
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}