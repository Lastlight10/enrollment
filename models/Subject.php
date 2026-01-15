<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model {
    protected $table = 'subjects';
    public $timestamps = true;

    protected $fillable = [
        'course_id',
        'subject_code',
        'subject_title',
        'units',
        
    ];
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

}