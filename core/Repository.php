<?php

namespace App\Core;

use App\Core\Logger;
use Illuminate\Database\Capsule\Manager as DB;

abstract class Repository
{
    public function __construct()
    {
      Connection::init(); 
      Logger::log("Initializing connection in Repository.");
    }
    public static function beginTransaction($message)
    {
        DB::beginTransaction();
        Logger::log($message);
    }

    public static function commit($message)
    {
        DB::commit();
        Logger::log($message);
        
    }

    public static function rollback($message)
    {
        DB::rollBack();
        Logger::log($message);
    }
}