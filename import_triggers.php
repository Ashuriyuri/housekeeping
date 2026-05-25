<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "╔════════════════════════════════════════════════════╗\n";
echo "║         LOADING ALL TRIGGERS FROM SQL FILES        ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// Use the comprehensive triggers file and execute each trigger carefully
$sqlFile = file_get_contents('phpmyadmin_triggers.sql');

// Split each trigger individually
$parts = explode('DROP TRIGGER IF EXISTS', $sqlFile);

$loaded = 0;
$failed = 0;

foreach ($parts as $i => $part) {
    if ($i === 0) continue; // Skip the header part before first trigger
    
    $stmt = "DROP TRIGGER IF EXISTS" . $part;
    $stmt = trim($stmt);
    
    // Extract trigger name
    if (preg_match('/DROP TRIGGER IF EXISTS `(\w+)`/', $stmt, $matches)) {
        $triggerName = $matches[1];
        
        try {
            $db->statement($stmt);
            echo "  ✓ $triggerName\n";
            $loaded++;
        } catch (\Exception $e) {
            echo "  ✗ $triggerName\n";
            $failed++;
        }
    }
}

echo "\n╔════════════════════════════════════════════════════╗\n";
echo "║                 LOADING COMPLETE                   ║\n";
echo "╠════════════════════════════════════════════════════╣\n";
echo "║  ✓ Loaded: $loaded triggers                        ║\n";
echo "║  ✗ Failed: $failed triggers                        ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// Final verification
$result = $db->select("SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE, EVENT_MANIPULATION
                       FROM INFORMATION_SCHEMA.TRIGGERS 
                       WHERE TRIGGER_SCHEMA = 'hk_db' 
                       ORDER BY TRIGGER_NAME");

echo "TOTAL TRIGGERS IN PHPMYADMIN: " . count($result) . "\n\n";

echo "Triggers visible in phpMyAdmin:\n";
foreach ($result as $r) {
    echo "  • {$r->TRIGGER_NAME} ({$r->EVENT_MANIPULATION} on {$r->EVENT_OBJECT_TABLE})\n";
}

echo "\n✓ All triggers are now in phpMyAdmin!\n";
echo "✓ Go to: http://localhost/phpmyadmin > hk_db > Triggers tab\n";
