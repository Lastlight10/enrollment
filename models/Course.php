<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model {
    protected $table = 'courses';
    protected $fillable = ['course_code', 'course_name'];

    // 1. Traditional relation (if subjects table still has course_id)
    public function subjects() {
        return $this->hasMany(Subject::class, 'course_id');
    }

    // 2. The Curriculum relation (The "New Way")
    public function curriculumSubjects() {
        return $this->belongsToMany(Subject::class, 'curriculums', 'course_id', 'subject_id')
                    ->withPivot('year_level', 'semester') // Removed is_required
                    ->withTimestamps();
    }
}