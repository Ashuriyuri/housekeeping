<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('pricing_type')->default('per_sqm')->after('description');
        });

        $services = [
            'Normal Cleaning' => ['pricing_type' => 'per_sqm', 'base_price' => 55.00],
            'Deep Cleaning' => ['pricing_type' => 'per_sqm', 'base_price' => 75.00],
            'Sofa Cleaning' => ['pricing_type' => 'fixed', 'base_price' => 1500.00],
            'Carpet Cleaning' => ['pricing_type' => 'per_sqm', 'base_price' => 65.00],
            'Bathroom Cleaning' => ['pricing_type' => 'fixed', 'base_price' => 800.00],
            'Kitchen Cleaning' => ['pricing_type' => 'fixed', 'base_price' => 1000.00],
            'Window Cleaning' => ['pricing_type' => 'fixed', 'base_price' => 600.00],
            'Move-in/Move-out Cleaning' => ['pricing_type' => 'per_sqm', 'base_price' => 85.00],
        ];

        foreach ($services as $serviceName => $values) {
            DB::table('services')
                ->where('service_name', $serviceName)
                ->update($values);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });

        
    }
};
