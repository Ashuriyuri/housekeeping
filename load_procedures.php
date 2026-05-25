<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = $app['db'];
    
    echo "Loading stored procedures...\n";
    $sql = file_get_contents('database/create_stored_procedures.sql');
    
    // Split by DELIMITER and execute each statement
    $statements = explode('DELIMITER ;', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        // Remove DELIMITER // if present
        $statement = str_replace('DELIMITER //', '', $statement);
        $statement = str_replace('DROP PROCEDURE', 'DROP PROCEDURE IF EXISTS', $statement);
        
        try {
            $db->statement($statement);
            echo ".";
        } catch (\Exception $e) {
            echo "E";
        }
    }
    
    echo "\n\nLoading triggers and views...\n";
    $sql = file_get_contents('database/comprehensive_triggers.sql');
    
    // Split by DELIMITER and execute each statement
    $statements = explode('DELIMITER ;', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        // Remove DELIMITER // if present
        $statement = str_replace('DELIMITER //', '', $statement);
        
        try {
            $db->statement($statement);
            echo ".";
        } catch (\Exception $e) {
            echo "E";
        }
    }
    
    echo "\n\nCompleted! All procedures and triggers loaded.\n";
    
    // Verify procedures exist
    echo "\n\nVerifying procedures...\n";
    $procedures = $db->select("SHOW PROCEDURE STATUS WHERE Db = 'hk_db'");
    echo "Found " . count($procedures) . " procedures:\n";
    foreach ($procedures as $proc) {
        echo "  - " . $proc->Name . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
