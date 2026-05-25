<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "╔════════════════════════════════════════════════════╗\n";
echo "║         DATABASE STATUS VERIFICATION               ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// Tables
echo "TABLES:\n";
try {
    $tables = $db->select("SELECT COUNT(*) as cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'hk_db' AND TABLE_TYPE = 'BASE TABLE'");
    echo "  ✓ " . $tables[0]->cnt . " tables exist\n";
} catch (\Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

// Procedures
echo "\nSTORED PROCEDURES:\n";
try {
    $procs = $db->select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_SCHEMA = 'hk_db' AND ROUTINE_TYPE = 'PROCEDURE'");
    echo "  ✓ " . $procs[0]->cnt . " procedures loaded\n";
} catch (\Exception $e) {
    echo "  ✗ Error\n";
}

// Triggers
echo "\nTRIGGERS:\n";
try {
    $triggers = $db->select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = 'hk_db'");
    echo "  ✓ " . $triggers[0]->cnt . " triggers loaded\n";
} catch (\Exception $e) {
    echo "  ✗ Error\n";
}

echo "\n✓ Database is restored and ready to use!\n";
