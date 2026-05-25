<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "=== Services Table Structure ===\n";
$services = $db->select("DESCRIBE services");
foreach ($services as $col) {
    echo "  - {$col->Field} ({$col->Type})\n";
}

echo "\n=== Employee Availability Table Structure ===\n";
try {
    $availability = $db->select("DESCRIBE employee_availability");
    foreach ($availability as $col) {
        echo "  - {$col->Field} ({$col->Type})\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
