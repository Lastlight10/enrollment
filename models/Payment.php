<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

use function PHPSTORM_META\map;

class Payment extends Model {
  protected $table = 'payments';
  public $timestamps = true;

  protected $fillable = [
    'enrollment_id',
    'payment_type',
    'amount',
    'status',
    'proof_path',
    'verified_by',
    'remarks'
 
  ];
  public function enrollment() {
    return $this->belongsTo(Enrollment::class);
  }

  public function verifier() {
      return $this->belongsTo(User::class, 'verified_by');
    }

}