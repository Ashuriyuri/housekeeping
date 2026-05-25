<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    // Create migrations table
    echo "Creating migrations table...\n";
    $db->statement("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        )
    ");
    echo "✓ Migrations table created\n\n";
    
    // Get list of migration files
    $migrationPath = 'database/migrations';
    $files = glob($migrationPath . '/*.php');
    sort($files);
    
    echo "Running migrations:\n";
    $batch = 1;
    
    foreach ($files as $file) {
        $migrationClass = 'Database\\Migrations\\' . basename($file, '.php');
        $migrationName = basename($file, '.php');
        
        // Check if already migrated
        $exists = $db->table('migrations')->where('migration', $migrationName)->exists();
        if ($exists) {
            echo "  ⊘ Skipping (already migrated): $migrationName\n";
            continue;
        }
        
        // Include and run the migration
        try {
            include $file;
            $class = 'CreateLaravelSanctumTokensTable'; // Fallback
            
            // Try to find the class in the file
            $content = file_get_contents($file);
            preg_match('/class\s+(\w+)/', $content, $matches);
            if (!empty($matches[1])) {
                $className = $matches[1];
            }
            
            // Extract just the class name from the filename
            $className = str_replace('_', '', ucwords(str_replace('_', ' ', basename($file, '.php'))));
            
            // Build the full class name - this is a workaround
            eval(file_get_contents($file));
            
            // Get all defined classes from this file
            $classes = get_declared_classes();
            $lastClass = end($classes);
            
            if (class_exists($lastClass)) {
                $migration = new $lastClass();
                $migration->up();
                
                // Record migration
                $db->table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch
                ]);
                
                echo "  ✓ Migrated: $migrationName\n";
            }
        } catch (\Exception $e) {
            echo "  ✗ Error in $migrationName: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nDatabase setup complete!\n";
    
    // Show tables
    echo "\nTables created:\n";
    $tables = $db->select("SHOW TABLES FROM hk_db");
    foreach ($tables as $table) {
        $tableName = array_values((array) $table)[0];
        echo "  ✓ $tableName\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
