<?php

namespace App\Core; // <-- Define the namespace for this class

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv; // Use the Dotenv class directly
use Illuminate\Database\Capsule\Manager as DB; // Ensure this is aliased as DB
// IMPORTANT: Remove require_once 'core/Logger.php'; here
// because Logger is now also namespaced and will be autoloaded.
// Also, remove require_once 'vendor/autoload.php'; if it's already in your index.php.

class Connection
{
  /**
   * @var Capsule|null The Eloquent Capsule instance.
   */
  protected static ?Capsule $capsule = null;
  public static function init(): void
  {
    
    // Prevent multiple initializations
    if (static::$capsule !== null) {
      Logger::log("DB_INFO: Database connection already initialized. Skipping.");
      return;
    }

    // Enable error reporting for debugging (optional, but good for development)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    try {
      Logger::log("DB_INFO: Attempting to set up database connection...");
      $autoloadPath = __DIR__ . '/../vendor/autoload.php'; // Adjust path relative to Connection.php
      if (!file_exists($autoloadPath)) {
          throw new \Exception("Composer autoloader not found at " . $autoloadPath);
      }
      require_once $autoloadPath;
      Logger::log("DB_INFO: Composer autoloader loaded (if not already).");
      static::$capsule = new Capsule;

      static::$capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => 'localhost',    // Or 'localhost'
        'port'      => '3306',         // Default MySQL port
        'database'  => 'enrollment',   // The name of the database you created
        'username'  => 'lastlight10',         // Your MySQL username (default is root)
        'password'  => 'rootguard0810',             // Your MySQL password (empty by default in XAMPP)
        'charset'   => 'utf8mb4',      // Best practice for modern apps
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
      ]);
      static::$capsule->setAsGlobal();

      // Setup the Eloquent ORM... (this is important!)
      static::$capsule->bootEloquent();

      Logger::log("DB_INFO: Database connection and Eloquent ORM successfully set up.");

    } catch (\Exception $e) {
      $errorMessage = "DATABASE SETUP FAILED: " . $e->getMessage();
      echo $errorMessage . PHP_EOL; // Display error for immediate feedback
      Logger::log("DB_ERROR: $errorMessage");
      exit(1); // Exit with an error code if DB connection is critical
    }
  }


  public static function getCapsule(): ?Capsule
  {
      return static::$capsule;
  }
}