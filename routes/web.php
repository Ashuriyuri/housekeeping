<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () { 
        $totalAppointments = \App\Models\Appointment::count();
        $pendingAppointments = \App\Models\Appointment::where('status', 'Pending')->count();
        $inProgressAppointments = \App\Models\Appointment::where('status', 'In Progress')->count();
        $completedAppointments = \App\Models\Appointment::where('status', 'Completed')->count();
        $totalEmployees = \App\Models\Employee::count();
        $totalServices = \App\Models\Service::count();
        $totalRevenue = \App\Models\Payment::where('payment_status', 'Paid')->sum('amount');

        $response = response()->view('dashboard', compact(
            'totalAppointments',
            'pendingAppointments',
            'inProgressAppointments',
            'completedAppointments',
            'totalEmployees',
            'totalServices',
            'totalRevenue'
        ));

        return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');
    })->name('dashboard');

    // Appointments
    Route::resource('appointments', AppointmentController::class);
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status.update');
    Route::get('appointments/by-status', [AppointmentController::class, 'appointmentsByStatus'])->name('appointments.by-status');
    Route::get('appointments/available-employees/{date}/{time}', [AppointmentController::class, 'getAvailableEmployees'])->name('appointments.available-employees');

    // Employees
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{employee}/filter-tasks', [EmployeeController::class, 'filterTasksByDate'])->name('employees.filter-tasks');
    Route::get('employees/{employee}/workload', [EmployeeController::class, 'getWorkloadReport'])->name('employees.workload');
    Route::get('employees/{employee}/availability/{month}/{year}', [AppointmentController::class, 'employeeAvailabilityCalendar'])->name('employees.availability.calendar');

    // Services
    Route::resource('services', ServiceController::class);

    // Payments
    Route::resource('payments', PaymentController::class, ['except' => ['create', 'store']]);
    Route::get('appointments/{appointment}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('appointments/{appointment}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/summary', [PaymentController::class, 'paymentSummary'])->name('payments.summary');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
