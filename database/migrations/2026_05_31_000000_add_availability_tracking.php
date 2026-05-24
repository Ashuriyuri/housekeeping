<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns to appointment_employee table
        if (Schema::hasTable('appointment_employee')) {
            Schema::table('appointment_employee', function (Blueprint $table) {
                if (!Schema::hasColumn('appointment_employee', 'is_available')) {
                    $table->boolean('is_available')->default(false)->after('task');
                }
                if (!Schema::hasColumn('appointment_employee', 'start_time')) {
                    $table->dateTime('start_time')->nullable()->after('is_available');
                }
                if (!Schema::hasColumn('appointment_employee', 'end_time')) {
                    $table->dateTime('end_time')->nullable()->after('start_time');
                }
            });
        }

        // Create employee_availability table
        Schema::create('employee_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
            $table->dateTime('available_from');
            $table->dateTime('available_to');
            $table->boolean('is_available')->default(false)->comment('0=Booked/Unavailable, 1=Available');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('appointment_id');
            $table->index('available_from');
            $table->unique(['employee_id', 'available_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_employee', function (Blueprint $table) {
            if (Schema::hasColumn('appointment_employee', 'is_available')) {
                $table->dropColumn('is_available');
            }
            if (Schema::hasColumn('appointment_employee', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('appointment_employee', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });

        Schema::dropIfExists('employee_availability');
    }
};
