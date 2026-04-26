<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;

class StudentCourses extends Model {
    protected $table = 'student_courses';
    protected $fillable = ['user_id', 'course_id'];

    public function studentCourse()
    {
        return $this->belongsTo(User::class,'course_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class,'course_id');
    }
}
?>