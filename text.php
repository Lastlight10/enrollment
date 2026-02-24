<?php

use App\Core\Connection;

require_once __DIR__ . '/vendor/autoload.php';

Connection::init();
use Models\User;

try {
    User::create([
        'username'   => 'reconuser',
        'password'   => 'reconpassword',
        'email'      => 'root.ayen0810@gmail.com',
        'first_name' => 'Clarenz Anthony',
        'mid_name'   => 'Lunar',
        'last_name'  => 'Recon',
        'birth_date' => '2004-08-10',
        'type'       => 'student',
        'status'     => 'active' // Set to active so you can bypass OTP for this test
    ]);
    echo "Test user created successfully!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}