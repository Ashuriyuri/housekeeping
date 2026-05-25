<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    // First, try to connect to MySQL root database to check if database exists
    echo "Checking database status...\n";
    
    try {
        // Try to select from information schema (works on root database too)
        $result = $db->select("SELECT COUNT(*) as count FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = 'hk_db'");
        echo "Database hk_db exists: " . ($result[0]->count > 0 ? "YES" : "NO") . "\n\n";
    } catch (\Exception $e) {
        echo "Checking via different method...\n";
    }
    
    // Create migrations table
    echo "Creating migrations table...\n";
    $db->statement("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            UNIQUE KEY migrations_migration_unique (migration)
        ) ENGINE=InnoDB
    ");
    echo "✓ Migrations table created\n\n";
    
    // Show existing tables
    echo "Existing tables in database:\n";
    $tables = $db->select("SHOW TABLES");
    if (!empty($tables)) {
        foreach ($tables as $table) {
            $name = array_values((array) $table)[0];
            echo "  ✓ $name\n";
        }
    } else {
        echo "  (none yet)\n";
    }
    
    echo "\n✓ Database is ready for migrations\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting: Check your .env file DB settings\n";
    exit(1);
}
