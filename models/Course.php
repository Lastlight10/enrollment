<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model {
    protected $table = 'courses';
    public $timestamps = true;

    protected $fillable = [
      'course_code',
      'course_name'
    ];
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'course_id');
    }

}