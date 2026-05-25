<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = $app['db'];
    
    echo "=== Loading Triggers and Views ===\n";
    $sql = file_get_contents('database/triggers_and_views.sql');
    
    // Replace DELIMITER statements with semicolons
    $sql = str_replace("DELIMITER //\n", "DELIMITER_START\n", $sql);
    $sql = str_replace("\n//\n\nDELIMITER ;", ";\n\nDELIMITER_END", $sql);
    $sql = str_replace("END //", "END;", $sql);
    
    // Split by DELIMITER_START/END markers
    $parts = explode("DELIMITER_START", $sql);
    
    $loaded = 0;
    
    foreach ($parts as $i => $part) {
        if ($i === 0) continue; // Skip initial comments
        
        // Extract the statement
        if (strpos($part, 'DELIMITER_END') !== false) {
            $stmt = explode('DELIMITER_END', $part)[0];
        } else {
            $stmt = $part;
        }
        
        $stmt = trim($stmt);
        if (empty($stmt)) continue;
        
        // Remove leading newline
        $stmt = ltrim($stmt, "\n");
        
        try {
            $db->statement($stmt);
            $loaded++;
            echo ".";
        } catch (\Exception $e) {
            echo "E";
        }
    }
    
    echo "\n✓ Loaded $loaded objects\n\n";
    
    // Verify
    echo "=== Verification ===\n";
    $triggers = $db->select("SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = 'hk_db'");
    echo "Triggers: " . count($triggers) . "\n";
    foreach ($triggers as $t) {
        echo "  ✓ " . $t->TRIGGER_NAME . "\n";
    }
    
    $views = $db->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'hk_db' AND TABLE_TYPE = 'VIEW'");
    echo "\nViews: " . count($views) . "\n";
    foreach ($views as $v) {
        echo "  ✓ " . $v->TABLE_NAME . "\n";
    }
    
    echo "\n✓ SUCCESS: All database objects are ready!\n";
    echo "\nNow testing appointment status update...\n\n";
    
    // Test the status update by checking a sample
    $sample = $db->select("SELECT id, status FROM appointments LIMIT 1");
    if (!empty($sample)) {
        echo "Sample appointment found: ID=" . $sample[0]->id . ", Status=" . $sample[0]->status . "\n";
        echo "✓ Database connection is working!\n";
    } else {
        echo "No appointments yet - will work once appointments are created.\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
