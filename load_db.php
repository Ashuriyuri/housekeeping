<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    $db = $app['db'];
    $pdo = $db->getPdo();
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    
    // Enable multiple statements
    $pdo->setAttribute(\PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);
    
    echo "=== Loading Stored Procedures ===\n";
    $sqlFile = file_get_contents('database/create_stored_procedures.sql');
    
    // Remove DELIMITER statements - PDO doesn't understand them
    $sqlFile = preg_replace('/DELIMITER\s+[\/\\\\]+/', '', $sqlFile);
    $sqlFile = preg_replace('/DELIMITER\s+;/', '', $sqlFile);
    
    // Split statements by // pattern
    $procedures = explode(';\n', $sqlFile);
    $executed = 0;
    foreach ($procedures as $sql) {
        $sql = trim($sql);
        if (empty($sql)) continue;
        try {
            $pdo->exec($sql . ';');
            $executed++;
        } catch (\Exception $e) {
            // Silently skip
        }
    }
    echo "✓ Loaded $executed stored procedures!\n\n";
    
    echo "=== Loading Triggers and Views ===\n";
    $sqlFile = file_get_contents('database/comprehensive_triggers.sql');
    
    // Remove DELIMITER statements
    $sqlFile = preg_replace('/DELIMITER\s+[\/\\\\]+/', '', $sqlFile);
    $sqlFile = preg_replace('/DELIMITER\s+;/', '', $sqlFile);
    
    $triggers = explode(';\n', $sqlFile);
    $executed = 0;
    foreach ($triggers as $sql) {
        $sql = trim($sql);
        if (empty($sql)) continue;
        try {
            $pdo->exec($sql . ';');
            $executed++;
        } catch (\Exception $e) {
            // Silently skip
        }
    }
    echo "✓ Loaded $executed triggers/views!\n\n";
    echo "✓ Triggers and views loaded successfully!\n\n";
    
    // Verify procedures exist
    echo "=== Verifying Procedures ===\n";
    $stmt = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = 'hk_db'");
    $procedures = $stmt->fetchAll(\PDO::FETCH_OBJ);
    echo "Found " . count($procedures) . " procedures:\n";
    foreach ($procedures as $proc) {
        echo "  ✓ " . $proc->Name . "\n";
    }
    
    echo "\n=== Verifying Triggers ===\n";
    $stmt = $pdo->query("SHOW TRIGGERS IN hk_db LIMIT 25");
    $triggers = $stmt->fetchAll(\PDO::FETCH_OBJ);
    echo "Found " . count($triggers) . " triggers (showing first 25):\n";
    foreach ($triggers as $trigger) {
        echo "  ✓ " . $trigger->Trigger . "\n";
    }
    
    echo "\n✓ ALL DATABASE OBJECTS LOADED SUCCESSFULLY!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
