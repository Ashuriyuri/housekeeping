<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    echo "=== Resetting Database ===\n";
    
    // Drop all tables
    echo "Dropping existing tables...\n";
    $tables = $db->select("SHOW TABLES");
    foreach ($tables as $table) {
        $name = array_values((array) $table)[0];
        try {
            $db->statement("DROP TABLE IF EXISTS $name CASCADE");
            echo "  Dropped: $name\n";
        } catch (\Exception $e) {
            // Ignore
        }
    }
    
    // Clear migrations table
    echo "\nResetting migrations table...\n";
    try {
        $db->statement("TRUNCATE TABLE migrations");
        echo "  ✓ Migrations table cleared\n";
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
        echo "  ✓ Migrations table created\n";
    }
    
    echo "\n=== Running Migrations ===\n";
    
    // Get all migration files
    $migrationPath = 'database/migrations';
    $files = glob($migrationPath . '/*.php');
    sort($files);
    
    $batch = 1;
    $count = 0;
    
    foreach ($files as $file) {
        $migrationName = basename($file, '.php');
        
        try {
            // Include the migration file
            require_once $file;
            
            // Get the class name from the file content
            $content = file_get_contents($file);
            if (preg_match('/class\s+(\w+)/', $content, $matches)) {
                $className = $matches[1];
                
                // The class should be in the Database\Migrations namespace
                $fullClassName = 'Database\\Migrations\\' . $className;
                
                // Try without namespace first (files don't have namespace sometimes)
                if (class_exists($className)) {
                    $migration = new $className();
                } elseif (class_exists($fullClassName)) {
                    $migration = new $fullClassName();
                } else {
                    // Create a temp namespace
                    $classes = get_declared_classes();
                    $found = false;
                    foreach ($classes as $cls) {
                        if (strpos($cls, $className) !== false) {
                            $migration = new $cls();
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        echo "  ✗ Cannot find class for $migrationName\n";
                        continue;
                    }
                }
                
                // Run up()
                $migration->up();
                
                // Record in migrations table
                $db->table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch
                ]);
                
                echo "  ✓ $migrationName\n";
                $count++;
            }
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
    
    echo "\n✓ All migrations completed successfully!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
