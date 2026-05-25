<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "╔═════════════════════════════════════════════════════════╗\n";
echo "║       DISABLING AUTO-UPGRADE TO IN PROGRESS TRIGGER     ║\n";
echo "╚═════════════════════════════════════════════════════════╝\n\n";

try {
    // Drop the trigger that auto-upgrades status to In Progress
    $db->statement("DROP TRIGGER IF EXISTS `trg_auto_update_appointment_status`");
    echo "✓ Dropped trigger: trg_auto_update_appointment_status\n\n";
    
    // Verify it's gone
    $triggers = $db->select("SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = 'hk_db' AND TRIGGER_NAME = 'trg_auto_update_appointment_status'");
    
    if (empty($triggers)) {
        echo "✓ Confirmed: Trigger is removed\n\n";
        echo "NEW FLOW:\n";
        echo "  1. Create appointment → Status = Pending\n";
        echo "  2. Assign employees → Status stays Pending (you control it)\n";
        echo "  3. You manually change to In Progress\n";
        echo "  4. You manually change to Completed\n";
        echo "  5. Cannot go back down (In Progress → Pending blocked)\n";
    } else {
        echo "✗ Trigger still exists\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
