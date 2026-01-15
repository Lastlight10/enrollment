<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $table = 'users';
    public $timestamps = true;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'mid_name',
        'last_name',
        'birth_date',
        'type',
        'status',
        'otp_code',
        'otp_expires_at',
        'verification_token'
    ];

    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'birth_date' => 'date',

    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_BCRYPT);
    }
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->mid_name} {$this->last_name}";
    }
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    public function verified()
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }
    
}