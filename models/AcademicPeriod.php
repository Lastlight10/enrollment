<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model {
    protected $table = 'academic_periods';
    public $timestamps = true;

    protected $fillable = [
      'acad_year',
      'semester',
      'is_active'
    ];
    protected $casts = [
    'is_active' => 'boolean',
  ];

}