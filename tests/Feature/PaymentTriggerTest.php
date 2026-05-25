<?php

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Service;

it('creates a pending payment when an appointment is completed', function () {
    $appointment = Appointment::create([
        'customer_name' => 'Ada Lovelace',
        'address' => '12 Computation Lane',
        'area_sqm' => 10,
        'schedule_date' => now()->addDay(),
        'status' => 'Pending',
    ]);

    $perSquareMeterService = Service::create([
        'service_name' => 'Standard Cleaning',
        'pricing_type' => 'per_sqm',
        'base_price' => 55,
    ]);

    $fixedService = Service::create([
        'service_name' => 'Window Cleaning',
        'pricing_type' => 'fixed',
        'base_price' => 100,
    ]);

    $appointment->services()->attach($perSquareMeterService->id, [
        'quantity' => 1,
        'custom_price' => 55,
    ]);

    $appointment->services()->attach($fixedService->id, [
        'quantity' => 2,
        'custom_price' => 100,
    ]);

    $appointment->update(['status' => 'Completed']);

    $payment = Payment::where('appointment_id', $appointment->id)->first();

    expect($payment)->not->toBeNull()
        ->and((float) $payment->amount)->toBe(750.0)
        ->and($payment->payment_status)->toBe('Pending');
});
