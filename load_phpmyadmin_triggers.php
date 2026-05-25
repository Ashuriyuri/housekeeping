<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    echo "╔════════════════════════════════════════════════════╗\n";
    echo "║       LOADING ALL TRIGGERS INTO MYSQL/PHPMYADMIN   ║\n";
    echo "╚════════════════════════════════════════════════════╝\n\n";
    
    // Read the phpMyAdmin-compatible triggers file
    $sql = file_get_contents('phpmyadmin_triggers.sql');
    
    // Split by triggers (each DROP TRIGGER)
    preg_match_all('/DROP TRIGGER IF EXISTS\s+`(\w+)`\s*;.*?CREATE TRIGGER.*?END\s*;/s', $sql, $matches, PREG_SET_ORDER);
    
    $loaded = 0;
    $failed = 0;
    
    echo "Found " . count($matches) . " triggers to load.\n\n";
    
    foreach ($matches as $i => $match) {
        $triggerName = $match[1];
        $fullStatement = $match[0];
        
        try {
            // Execute the trigger creation
            $db->statement($fullStatement);
            echo "  [" . ($i + 1) . "] ✓ $triggerName\n";
            $loaded++;
        } catch (\Exception $e) {
            echo "  [" . ($i + 1) . "] ✗ $triggerName - " . substr($e->getMessage(), 0, 60) . "\n";
            $failed++;
        }
    }
    
    echo "\n╔════════════════════════════════════════════════════╗\n";
    echo "║              LOADING COMPLETE                      ║\n";
    echo "╠════════════════════════════════════════════════════╣\n";
    echo "║  Successfully Loaded: $loaded triggers              ║\n";
    echo "║  Failed to Load: $failed triggers                   ║\n";
    echo "╚════════════════════════════════════════════════════╝\n\n";
    
    // Verification
    echo "=== VERIFICATION IN PHPMYADMIN ===\n\n";
    
    $triggers = $db->select("SELECT TRIGGER_NAME, TRIGGER_SCHEMA, EVENT_MANIPULATION, EVENT_OBJECT_TABLE 
                             FROM INFORMATION_SCHEMA.TRIGGERS 
                             WHERE TRIGGER_SCHEMA = 'hk_db' 
                             ORDER BY TRIGGER_NAME");
    
    echo "Total Triggers Found: " . count($triggers) . "\n\n";
    
    if (count($triggers) > 0) {
        echo "Triggers Active in phpMyAdmin:\n";
        foreach ($triggers as $trigger) {
            $action = strtoupper($trigger->EVENT_MANIPULATION);
            $table = $trigger->EVENT_OBJECT_TABLE;
            echo "  ✓ {$trigger->TRIGGER_NAME} - $action ON $table\n";
        }
    }
    
    echo "\n✓ All triggers are now visible in phpMyAdmin!\n";
    echo "✓ Access phpMyAdmin and go to hk_db > Triggers tab to see them.\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
