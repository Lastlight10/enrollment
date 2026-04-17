<?php

namespace Models;
use Illuminate\Database\Eloquent\Model;

class StudentCourses extends Model {
    protected $table = 'student_courses';
    protected $fillable = ['user_id', 'course_id'];
}
?>