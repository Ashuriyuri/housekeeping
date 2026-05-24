<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\EmployeeAvailability;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::query();

        // Filter by date (mm/dd/yyyy format)
        if ($request->filled('filter_date')) {
            try {
                $date = Carbon::createFromFormat('m/d/Y', $request->filter_date);
                $query->whereDate('schedule_date', $date->toDateString());
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            try {
                $fromDate = Carbon::createFromFormat('m/d/Y', $request->from_date);
                $toDate = Carbon::createFromFormat('m/d/Y', $request->to_date);
                $query->whereBetween('schedule_date', [$fromDate, $toDate->endOfDay()]);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        $appointments = $query->with('services', 'employees', 'payment')
                             ->orderBy('schedule_date', 'desc')
                             ->paginate(15);

        return view('appointments.index', [
            'appointments' => $appointments,
            'filterDate' => $request->filter_date,
            'status' => $request->status,
        ]);
    }

    public function create()
    {
        $employees = Employee::where('status', 'Active')->get();
        $services = Service::all();

        return view('appointments.create', compact('employees', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'area_sqm' => 'nullable|numeric|min:1',
            'schedule_date' => 'required|date_format:Y-m-d\TH:i',
            'notes' => 'nullable|string',
            'services' => 'array',
            'services.*' => 'exists:services,id',
            'service_quantity' => 'array',
            'service_quantity.*' => 'nullable|numeric|min:1',
            'employees' => 'array',
            'employees.*' => 'exists:employees,id',
            'employee_tasks' => 'array',
            'employee_tasks.*' => 'nullable|string|max:255',
            'employee_start_time' => 'array',
            'employee_start_time.*' => 'nullable|date_format:Y-m-d\TH:i',
            'employee_end_time' => 'array',
            'employee_end_time.*' => 'nullable|date_format:Y-m-d\TH:i',
        ]);

        $appointment = Appointment::create($this->appointmentData($validated));
        $this->syncServices($request, $appointment);
        $this->syncEmployees($request, $appointment);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment created successfully.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load('services', 'employees', 'payment');

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $employees = Employee::where('status', 'Active')->get();
        $services = Service::all();
        $appointment->load('services', 'employees');

        return view('appointments.edit', compact('appointment', 'employees', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'area_sqm' => 'nullable|numeric|min:1',
            'schedule_date' => 'required|date_format:Y-m-d\TH:i',
            'status' => 'required|in:Pending,In Progress,Completed',
            'notes' => 'nullable|string',
            'services' => 'array',
            'services.*' => 'exists:services,id',
            'service_quantity' => 'array',
            'service_quantity.*' => 'nullable|numeric|min:1',
            'employees' => 'array',
            'employees.*' => 'exists:employees,id',
            'employee_tasks' => 'array',
            'employee_tasks.*' => 'nullable|string|max:255',
        ]);

        $appointment->update($this->appointmentData($validated));
        $this->syncServices($request, $appointment);
        $this->syncEmployees($request, $appointment);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment updated successfully.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,In Progress,Completed',
        ]);

        // Additional validation before completion
        if ($validated['status'] === 'Completed') {
            // Check if appointment has an area (required for payment calculation)
            if (is_null($appointment->area_sqm) || $appointment->area_sqm == 0) {
                return redirect()->route('appointments.edit', $appointment)
                    ->with('error', 'Cannot complete appointment: Area (sqm) is required for payment calculation. Please update the appointment with the area.');
            }

            // Check if appointment has services
            if ($appointment->services()->count() === 0) {
                return redirect()->route('appointments.edit', $appointment)
                    ->with('error', 'Cannot complete appointment: At least one service must be assigned.');
            }

            // Check if appointment has employees assigned
            if ($appointment->employees()->count() === 0) {
                return redirect()->route('appointments.edit', $appointment)
                    ->with('error', 'Cannot complete appointment: At least one employee must be assigned.');
            }
        }

        $appointment->update($validated);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment status updated successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Appointment deleted successfully.');
    }

    /**
     * Get appointments grouped by status
     */
    public function appointmentsByStatus(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date_format:m/d/Y',
        ]);

        $query = Appointment::with('services', 'employees', 'payment');

        if ($validated['date'] ?? null) {
            try {
                $date = Carbon::createFromFormat('m/d/Y', $validated['date']);
                $query->whereDate('schedule_date', $date->toDateString());
            } catch (\Exception $e) {
                // Invalid date format
            }
        }

        $appointments = $query->get()->groupBy('status');

        return response()->json([
            'pending' => $appointments->get('Pending', collect())->count(),
            'in_progress' => $appointments->get('In Progress', collect())->count(),
            'completed' => $appointments->get('Completed', collect())->count(),
        ]);
    }

    /**
     * Get available employees for a time slot
     */
    public function getAvailableEmployees($date, $time)
    {
        try {
            $startTime = Carbon::createFromFormat('Y-m-d H:i', "$date $time");
            $endTime = $startTime->copy()->addHours(2);

            $employees = Employee::active()
                ->with('appointments')
                ->get()
                ->filter(function ($employee) use ($startTime, $endTime) {
                    return !$this->hasConflict($employee->id, $startTime, $endTime);
                })
                ->values();

            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date/time format'], 400);
        }
    }

    /**
     * Check if employee has booking conflict
     */
    private function hasConflict($employeeId, $startTime, $endTime)
    {
        return DB::table('appointment_employee')
            ->where('employee_id', $employeeId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })
            ->exists();
    }

    /**
     * Get employee availability calendar
     */
    public function employeeAvailabilityCalendar($employeeId, $month, $year)
    {
        $employee = Employee::with('appointments')->findOrFail($employeeId);
        
        $availability = [];
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        for ($day = 1; $day <= $days; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $bookedSlots = $employee->getBookedSlots($date);
            $availableSlots = $employee->getAvailableSlots($date);

            $availability[$date->toDateString()] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('l'),
                'booked_count' => $bookedSlots->count(),
                'available_count' => count($availableSlots),
                'available_slots' => $availableSlots,
            ];
        }

        return response()->json($availability);
    }

    private function syncServices(Request $request, Appointment $appointment): void
    {
        $serviceData = [];
        $serviceIds = $request->input('services', []);
        $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

        foreach ($serviceIds as $key => $serviceId) {
            $service = $services->get((int) $serviceId);

            if (!$service) {
                continue;
            }

            $serviceData[$serviceId] = [
                'quantity' => $request->input("service_quantity.$key", 1),
                'custom_price' => $service->base_price,
            ];
        }

        $appointment->services()->sync($serviceData);
    }

    private function syncEmployees(Request $request, Appointment $appointment): void
    {
        $employeeData = [];
        $employeeIds = $request->input('employees', []);

        foreach ($employeeIds as $key => $employeeId) {
            $startTime = $request->input("employee_start_time.$key");
            $endTime = $request->input("employee_end_time.$key");

            $employeeData[$employeeId] = [
                'task' => $request->input("employee_tasks.$key"),
                'is_available' => true,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];

            // Mark employee as unavailable in availability table
            if ($startTime && $endTime) {
                try {
                    $startDt = Carbon::parse($startTime);
                    $endDt = Carbon::parse($endTime);
                    
                    EmployeeAvailability::create([
                        'employee_id' => $employeeId,
                        'appointment_id' => $appointment->id,
                        'available_from' => $startDt,
                        'available_to' => $endDt,
                        'is_available' => false,
                        'reason' => 'Booked for appointment #' . $appointment->id,
                    ]);
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }
        }

        $appointment->employees()->sync($employeeData);
    }

    private function appointmentData(array $validated): array
    {
        return array_intersect_key($validated, array_flip([
            'customer_name',
            'address',
            'area_sqm',
            'schedule_date',
            'status',
            'notes',
        ]));
    }
}
