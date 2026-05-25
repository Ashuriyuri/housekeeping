<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = $app['db'];
    
    echo "=== Loading from triggers_and_views.sql ===\n";
    $sql = file_get_contents('database/triggers_and_views.sql');
    
    // Split statements properly
    $parts = explode("DELIMITER ;", $sql);
    
    $loaded = 0;
    $failed = 0;
    
    foreach ($parts as $part) {
        // Remove DELIMITER // and other markers
        $part = str_replace("DELIMITER //\n", "", $part);
        $part = str_replace("DELIMITER //", "", $part);
        $part = str_replace("//", "", $part);
        $part = trim($part);
        
        if (empty($part) || strpos($part, '--') === 0) continue;
        
        // Add semicolon if needed
        if (!str_ends_with(trim($part), ';')) {
            $part .= ';';
        }
        
        try {
            $db->statement($part);
            $loaded++;
            echo ".";
        } catch (\Exception $e) {
            $failed++;
            echo "E: " . substr($e->getMessage(), 0, 50) . "\n";
        }
    }
    
    echo "\n✓ Loaded $loaded objects (Failed: $failed)\n\n";
    
    // Verify
    echo "=== Verification ===\n";
    $triggers = $db->select("SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = 'hk_db'");
    echo "Triggers Found: " . count($triggers) . "\n";
    
    foreach ($triggers as $t) {
        echo "  ✓ " . $t->TRIGGER_NAME . "\n";
    }
    
    $views = $db->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'hk_db' AND TABLE_TYPE = 'VIEW'");
    echo "\nViews Found: " . count($views) . "\n";
    
    foreach ($views as $v) {
        echo "  ✓ " . $v->TABLE_NAME . "\n";
    }
    
    echo "\n✓ Database setup complete!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
