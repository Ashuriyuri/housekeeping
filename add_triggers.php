<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    echo "╔════════════════════════════════════════════════════╗\n";
    echo "║       LOADING TRIGGERS INTO MYSQL DATABASE         ║\n";
    echo "╚════════════════════════════════════════════════════╝\n\n";
    
    // Read the triggers from the original file
    $sql = file_get_contents('database/triggers_and_views.sql');
    
    // Split by line breaks and process
    $lines = explode("\n", $sql);
    $currentStatement = "";
    $triggerCount = 0;
    $failCount = 0;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        $currentStatement .= $line . "\n";
        
        // Check if we have a complete statement
        if (strpos($line, 'END') !== false && strpos($line, 'DELIMITER') === false) {
            // Extract trigger name for display
            if (preg_match('/CREATE TRIGGER `(\w+)`/', $currentStatement, $matches)) {
                $triggerName = $matches[1];
                
                try {
                    // Remove DELIMITER statements
                    $stmt = str_replace("DELIMITER //", "", $currentStatement);
                    $stmt = str_replace("DELIMITER ;", "", $stmt);
                    
                    $db->statement(trim($stmt));
                    echo "  ✓ $triggerName\n";
                    $triggerCount++;
                } catch (\Exception $e) {
                    echo "  ✗ $triggerName\n";
                    $failCount++;
                }
                
                $currentStatement = "";
            }
        }
    }
    
    echo "\n╔════════════════════════════════════════════════════╗\n";
    echo "║              LOADING COMPLETE                      ║\n";
    echo "╠════════════════════════════════════════════════════╣\n";
    echo "║  Successfully Loaded: $triggerCount triggers        ║\n";
    echo "║  Failed to Load: $failCount triggers                ║\n";
    echo "╚════════════════════════════════════════════════════╝\n\n";
    
    // Verification
    echo "=== TRIGGERS IN PHPMYADMIN ===\n\n";
    
    $triggers = $db->select("SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE, EVENT_MANIPULATION
                             FROM INFORMATION_SCHEMA.TRIGGERS 
                             WHERE TRIGGER_SCHEMA = 'hk_db' 
                             ORDER BY TRIGGER_NAME");
    
    echo "Total Triggers: " . count($triggers) . "\n\n";
    
    if (count($triggers) > 0) {
        echo "Available Triggers:\n";
        foreach ($triggers as $t) {
            echo "  • {$t->TRIGGER_NAME} - {$t->EVENT_MANIPULATION} on {$t->EVENT_OBJECT_TABLE}\n";
        }
    }
    
    echo "\n✓ Check phpMyAdmin: Database hk_db > Triggers tab\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
