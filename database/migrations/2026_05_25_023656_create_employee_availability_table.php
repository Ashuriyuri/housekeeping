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
        Schema::create('employee_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_to')->nullable();
            $table->boolean('is_available')->default(1);
            $table->string('reason')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_availability');
    }
};
