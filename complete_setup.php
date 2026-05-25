<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    echo "=== Verification ===\n";
    $tables = $db->select("SHOW TABLES");
    echo "Tables created: " . count($tables) . "\n";
    foreach ($tables as $table) {
        $name = array_values((array) $table)[0];
        echo "  ✓ $name\n";
    }
    
    echo "\n=== Loading Stored Procedures ===\n";
    $sql = file_get_contents('database/create_stored_procedures.sql');
    
    // Split by procedure
    preg_match_all('/DROP PROCEDURE IF EXISTS\s+(\w+)\s+\/\//i', $sql, $drops);
    foreach ($drops[1] as $proc) {
        try {
            $db->statement("DROP PROCEDURE IF EXISTS $proc");
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
            echo "  ✓ $procName\n";
        } catch (\Exception $e) {
            echo "  ✗ $procName: " . substr($e->getMessage(), 0, 80) . "\n";
        }
    }
    
    echo "\n=== Loading Triggers and Views ===\n";
    $sql = file_get_contents('database/triggers_and_views.sql');
    
    // Split by DELIMITER marker
    $parts = explode("//\n\nDELIMITER ;", $sql);
    $loaded = 0;
    $failed = 0;
    
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
            $loaded++;
            echo ".";
        } catch (\Exception $e) {
            $failed++;
            echo "E";
        }
    }
    
    echo "\n  Loaded: $loaded | Failed: $failed\n\n";
    
    echo "=== Final Verification ===\n";
    
    $procs = $db->select("SHOW PROCEDURE STATUS WHERE Db = 'hk_db'");
    echo "Procedures: " . count($procs) . " loaded\n";
    
    $triggers = $db->select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = 'hk_db'");
    echo "Triggers: " . $triggers[0]->count . " loaded\n";
    
    $views = $db->select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'hk_db' AND TABLE_TYPE = 'VIEW'");
    echo "Views: " . $views[0]->count . " loaded\n";
    
    echo "\n✓ DATABASE SETUP COMPLETE!\n";
    echo "\nYou can now test the appointment status update.\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
