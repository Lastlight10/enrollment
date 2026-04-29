<?php

namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use App\Core\Logger;
use Models\Payment;

class PaymentRepository extends Repository
{
    public function all() {
        return Payment::all()->sortBy('created_at')->all();
    }
}

?>