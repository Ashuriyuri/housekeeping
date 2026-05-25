<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

try {
    // Get the migration repository
    $migrator = app('migrator');
    $repository = app('migration.repository');
    
    // Create the migrations table first
    if (!$repository->repositoryExists()) {
        $repository->createRepository();
        echo "✓ Created migrations table\n";
    }
    
    // Run pending migrations
    echo "Running migrations...\n";
    $migrator->run([
        'path' => 'database/migrations',
    ]);
    
    echo "✓ Migrations completed successfully!\n";
    echo "\nDatabase tables created:\n";
    
    $tables = DB::select("SHOW TABLES FROM hk_db");
    foreach ($tables as $table) {
        $tableName = array_values((array) $table)[0];
        echo "  ✓ $tableName\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
