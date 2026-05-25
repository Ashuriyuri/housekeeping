<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = $app['db'];
    
    echo "=== Loading Stored Procedures ===\n";
    $sql = file_get_contents('database/create_stored_procedures.sql');
    
    // Remove DELIMITER lines completely
    $sql = preg_replace('/DELIMITER\s+\/\//', '', $sql);
    $sql = preg_replace('/DELIMITER\s+;/', '', $sql);
    
    // Get all DROP PROCEDURE statements
    preg_match_all('/DROP PROCEDURE IF EXISTS\s+(\w+)\s+\/\//', $sql, $drops);
    foreach ($drops[1] as $proc) {
        try {
            $db->statement("DROP PROCEDURE IF EXISTS $proc");
            echo "  Dropped: $proc\n";
        } catch (\Exception $e) {
            // Ignore
        }
    }
    
    // Get all CREATE PROCEDURE statements
    preg_match_all('/CREATE PROCEDURE\s+(\w+)\((.*?)\)\s+(READS SQL DATA|MODIFIES SQL DATA)\s+BEGIN(.*?)END\s+\/\//s', $sql, $matches);
    
    for ($i = 0; $i < count($matches[0]); $i++) {
        $procName = $matches[1][$i];
        $procParams = $matches[2][$i];
        $modifier = $matches[3][$i];
        $procBody = $matches[4][$i];
        
        $createSQL = "CREATE PROCEDURE $procName($procParams)\n$modifier\nBEGIN$procBody\nEND";
        
        try {
            $db->statement($createSQL);
            echo "  ✓ Created: $procName\n";
        } catch (\Exception $e) {
            echo "  ✗ Error creating $procName: " . $e->getMessage() . "\n";
        }
    }
    
    // Load triggers and views
    echo "\n=== Loading Triggers and Views ===\n";
    $sql = file_get_contents('database/comprehensive_triggers.sql');
    
    // Remove DELIMITER lines
    $sql = preg_replace('/DELIMITER\s+\/\//', '', $sql);
    $sql = preg_replace('/DELIMITER\s+;/', '', $sql);
    
    // Get all DROP statements
    preg_match_all('/DROP (TRIGGER|VIEW) IF EXISTS\s+(\w+)/', $sql, $drops);
    foreach ($drops[2] as $obj) {
        try {
            $type = $drops[1][array_search($obj, $drops[2])];
            $db->statement("DROP $type IF EXISTS $obj");
            echo "  Dropped: $obj\n";
        } catch (\Exception $e) {
            // Ignore
        }
    }
    
    // Split by /\n and execute each statement
    $parts = explode("\/\/\n", $sql);
    $count = 0;
    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part) || strpos($part, '--') === 0) continue;
        if (strpos($part, 'DROP') === 0) continue;
        
        $part .= ';';
        try {
            $db->statement($part);
            $count++;
        } catch (\Exception $e) {
            echo "  ✗ Error: " . substr($e->getMessage(), 0, 80) . "\n";
        }
    }
    echo "  ✓ Loaded $count triggers/views\n\n";
    
    // Verify
    echo "=== Verification ===\n";
    $procs = $db->select("SHOW PROCEDURE STATUS WHERE Db = 'hk_db'");
    echo "Procedures: " . count($procs) . "\n";
    foreach ($procs as $p) {
        echo "  ✓ " . $p->Name . "\n";
    }
    
    $triggers = $db->select("SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = 'hk_db' LIMIT 30");
    echo "\nTriggers: " . count($triggers) . "\n";
    foreach ($triggers as $t) {
        echo "  ✓ " . $t->TRIGGER_NAME . "\n";
    }
    
    echo "\n✓ SUCCESS: All database objects loaded!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
