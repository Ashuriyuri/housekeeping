<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'UPDATE appointment_service
             SET custom_price = (
                 SELECT services.base_price
                 FROM services
                 WHERE services.id = appointment_service.service_id
             )
             WHERE EXISTS (
                 SELECT 1
                 FROM services
                 WHERE services.id = appointment_service.service_id
             )'
        );
    }

    public function down(): void
    {
        DB::table('appointment_service')->update(['custom_price' => null]);
    }
};
