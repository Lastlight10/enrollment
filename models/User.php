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
        'otp_expires_at'
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
}