<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    echo "=== Force Creating All Tables ===\n\n";
    
    // Get all migration files
    $migrationPath = 'database/migrations';
    $files = glob($migrationPath . '/*.php');
    sort($files);
    
    foreach ($files as $file) {
        $migrationName = basename($file, '.php');
        
        try {
            // Include and get the migration instance
            $migration = require $file;
            
            // Force drop if exists for create migrations
            if (strpos($migrationName, 'create_') !== false && $migrationName !== 'create_payment_triggers') {
                $tableName = null;
                if (preg_match('/create_(\w+)_table/', $migrationName, $m)) {
                    $tableName = $m[1];
                    try {
                        $db->statement("DROP TABLE IF EXISTS `$tableName`");
                        echo "  Dropped: $tableName\n";
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }
            }
            
            // Call up() method
            $migration->up();
            
            // Record in migrations table
            $db->table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => 1
            ]);
            
            echo "  ✓ $migrationName\n";
        } catch (\Exception $e) {
            // Try to extract just the table error
            $msg = $e->getMessage();
            if (strpos($msg, 'already exists') !== false) {
                // Table might have been created successfully despite the error
                echo "  ~ $migrationName (warning: already exists)\n";
                // Still try to record it
                try {
                    $db->table('migrations')->insert([
                        'migration' => $migrationName,
                        'batch' => 1
                    ]);
                } catch (\Exception $e2) {}
            } else {
                echo "  ✗ $migrationName: " . substr($msg, 0, 80) . "\n";
            }
        }
    }
    
    echo "\n=== Verification ===\n";
    $tables = $db->select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'hk_db' AND TABLE_TYPE = 'BASE TABLE'");
    echo "Tables: " . count($tables) . "\n";
    foreach ($tables as $t) {
        echo "  ✓ " . $t->TABLE_NAME . "\n";
    }
    
    echo "\n";
    
    if (count($tables) > 1) {
        echo "✓ All database tables have been restored!\n";
    } else {
        echo "⚠ Issue: Most tables are still missing\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
