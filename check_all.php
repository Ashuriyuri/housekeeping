<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = app('db');
    
    echo "=== Final Database Status ===\n\n";
    
    // Check all tables
    $tables = $db->select("SHOW TABLES");
    echo "Tables in database: " . count($tables) . "\n";
    foreach ($tables as $table) {
        $name = array_values((array) $table)[0];
        echo "  ✓ $name\n";
    }
    
    // Verify key tables
    echo "\n=== Key Tables Status ===\n";
    $keyTables = ['users', 'appointments', 'employees', 'services', 'appointment_service', 'appointment_employee', 'payments', 'employee_availability'];
    
    foreach ($keyTables as $table) {
        try {
            $count = $db->table($table)->count();
            echo "  ✓ $table (records: $count)\n";
        } catch (\Exception $e) {
            echo "  ✗ $table MISSING!\n";
        }
    }
    
    // Check stored procedures
    echo "\n=== Stored Procedures ===\n";
    $procs = $db->select("SHOW PROCEDURE STATUS WHERE Db = 'hk_db'");
    echo "Count: " . count($procs) . "\n";
    foreach ($procs as $p) {
        echo "  ✓ " . $p->Name . "\n";
    }
    
    // Check triggers
    echo "\n=== Triggers ===\n";
    $triggers = $db->select("SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = 'hk_db'");
    echo "Count: " . count($triggers) . "\n";
    foreach ($triggers as $t) {
        echo "  ✓ " . $t->TRIGGER_NAME . "\n";
    }
    
    // Check views
    echo "\n=== Views ===\n";
    $views = $db->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'hk_db' AND TABLE_TYPE = 'VIEW'");
    echo "Count: " . count($views) . "\n";
    foreach ($views as $v) {
        echo "  ✓ " . $v->TABLE_NAME . "\n";
    }
    
    echo "\n✓ DATABASE READY FOR USE!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
