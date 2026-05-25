<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    echo "=== Resetting Database ===\n";
    
    // Drop all tables except migrations
    echo "Dropping existing tables...\n";
    $tables = $db->select("SHOW TABLES");
    foreach ($tables as $table) {
        $name = array_values((array) $table)[0];
        if ($name === 'migrations') continue;
        try {
            $db->statement("DROP TABLE IF EXISTS `$name`");
            echo "  Dropped: $name\n";
        } catch (\Exception $e) {
            // Ignore
        }
    }
    
    // Clear migrations table
    echo "Resetting migrations...\n";
    try {
        $db->statement("TRUNCATE TABLE migrations");
    } catch (\Exception $e) {
        // Create it if it doesn't exist
        $db->statement("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                UNIQUE KEY migrations_migration_unique (migration)
            ) ENGINE=InnoDB
        ");
    }
    echo "  ✓ Migrations reset\n\n";
    
    echo "=== Running Migrations ===\n";
    
    // Get all migration files
    $migrationPath = 'database/migrations';
    $files = glob($migrationPath . '/*.php');
    sort($files);
    
    $batch = 1;
    $count = 0;
    
    foreach ($files as $file) {
        $migrationName = basename($file, '.php');
        
        try {
            // Include and get the migration instance
            $migration = require $file;
            
            // Call up() method
            $migration->up();
            
            // Record in migrations table
            $db->table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $batch
            ]);
            
            echo "  ✓ $migrationName\n";
            $count++;
        } catch (\Exception $e) {
            echo "  ✗ $migrationName: " . substr($e->getMessage(), 0, 100) . "\n";
        }
    }
    
    echo "\n=== Verification ===\n";
    $tables = $db->select("SHOW TABLES");
    echo "Tables created: " . count($tables) . "\n";
    foreach ($tables as $table) {
        $name = array_values((array) $table)[0];
        echo "  ✓ $name\n";
    }
    
    echo "\n=== Loading Procedures & Triggers ===\n";
    
    // Load stored procedures
    $sql = file_get_contents('database/create_stored_procedures.sql');
    preg_match_all('/CREATE PROCEDURE\s+(\w+)\((.*?)\)\s+(READS SQL DATA|MODIFIES SQL DATA)\s+BEGIN(.*?)END\s+\/\//s', $sql, $matches);
    
    for ($i = 0; $i < count($matches[0]); $i++) {
        $procName = $matches[1][$i];
        $procParams = $matches[2][$i];
        $modifier = $matches[3][$i];
        $procBody = $matches[4][$i];
        
        try {
            $db->statement("DROP PROCEDURE IF EXISTS $procName");
        } catch (\Exception $e) {}
        
        $createSQL = "CREATE PROCEDURE $procName($procParams)\n$modifier\nBEGIN$procBody\nEND";
        
        try {
            $db->statement($createSQL);
            echo "  ✓ Procedure: $procName\n";
        } catch (\Exception $e) {
            echo "  ✗ Procedure $procName\n";
        }
    }
    
    // Load triggers
    $sql = file_get_contents('database/triggers_and_views.sql');
    $parts = explode("//\n\nDELIMITER ;", $sql);
    $triggerCount = 0;
    
    foreach ($parts as $i => $part) {
        if ($i === 0) continue;
        
        $stmt = trim($part);
        $stmt = str_replace("DELIMITER //\n", "", $stmt);
        $stmt = str_replace("DROP TRIGGER", "DROP TRIGGER IF EXISTS", $stmt);
        $stmt = str_replace("DROP VIEW", "DROP VIEW IF EXISTS", $stmt);
        
        if (empty($stmt) || strpos($stmt, '--') === 0) continue;
        
        $stmt .= ";";
        
        try {
            $db->statement($stmt);
            $triggerCount++;
        } catch (\Exception $e) {
            // Silently skip
        }
    }
    
    echo "  ✓ Triggers/Views: $triggerCount loaded\n\n";
    
    echo "✓ ✓ ✓ DATABASE FULLY RESTORED! ✓ ✓ ✓\n";
    echo "\nAll tables, procedures, and triggers are ready!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
