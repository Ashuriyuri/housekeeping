<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load('appointments.services');
        
        // Get all assignments with details
        $assignments = $employee->appointments()
            ->with('services')
            ->get()
            ->map(function ($appointment) {
                $pivot = $appointment->pivot;
                return [
                    'appointment_id' => $appointment->id,
                    'customer_name' => $appointment->customer_name,
                    'address' => $appointment->address,
                    'schedule_date' => $appointment->schedule_date,
                    'status' => $appointment->status,
                    'task' => $pivot->task,
                    'start_time' => $pivot->start_time ? Carbon::parse($pivot->start_time) : null,
                    'end_time' => $pivot->end_time ? Carbon::parse($pivot->end_time) : null,
                    'services' => $appointment->services->pluck('service_name'),
                ];
            });

        return view('employees.show', compact('employee', 'assignments'));
    }

    public function filterTasksByDate(Request $request, $employeeId)
    {
        $validated = $request->validate([
            'date' => 'nullable|date_format:m/d/Y',
            'status' => 'nullable|in:Pending,In Progress,Completed',
        ]);

        $employee = Employee::findOrFail($employeeId);
        
        $query = $employee->appointments()->with('services');

        if ($validated['date'] ?? null) {
            try {
                $date = Carbon::createFromFormat('m/d/Y', $validated['date']);
                $query->whereDate('appointment_employee.start_time', $date);
            } catch (\Exception $e) {
                // Invalid date format
            }
        }

        if ($validated['status'] ?? null) {
            $query->where('appointments.status', $validated['status']);
        }

        $assignments = $query->get()
            ->map(function ($appointment) {
                $pivot = $appointment->pivot;
                return [
                    'appointment_id' => $appointment->id,
                    'customer_name' => $appointment->customer_name,
                    'address' => $appointment->address,
                    'status' => $appointment->status,
                    'task' => $pivot->task,
                    'start_time' => $pivot->start_time ? Carbon::parse($pivot->start_time) : null,
                    'end_time' => $pivot->end_time ? Carbon::parse($pivot->end_time) : null,
                    'duration' => $pivot->start_time && $pivot->end_time ? 
                                 Carbon::parse($pivot->start_time)->diffInMinutes(Carbon::parse($pivot->end_time)) : 0,
                    'services' => $appointment->services->pluck('service_name'),
                ];
            });

        if ($request->wantsJson()) {
            return response()->json([
                'date' => $validated['date'] ?? null,
                'employee' => $employee->name,
                'task_count' => $assignments->count(),
                'assignments' => $assignments,
            ]);
        }

        return view('employees.show', compact('employee', 'assignments'));
    }

    /**
     * Get employee workload report for date range
     */
    public function getWorkloadReport(Request $request, $employeeId)
    {
        $validated = $request->validate([
            'from_date' => 'required|date_format:m/d/Y',
            'to_date' => 'required|date_format:m/d/Y|after_or_equal:from_date',
        ]);

        $employee = Employee::findOrFail($employeeId);
        
        try {
            $from = Carbon::createFromFormat('m/d/Y', $validated['from_date']);
            $to = Carbon::createFromFormat('m/d/Y', $validated['to_date']);

            $assignments = $employee->appointments()
                ->whereBetween('appointment_employee.start_time', [$from, $to->endOfDay()])
                ->with('services')
                ->get();

            $totalHours = 0;
            $byStatus = ['Pending' => 0, 'In Progress' => 0, 'Completed' => 0];

            $assignments->each(function ($appt) use (&$totalHours, &$byStatus) {
                if ($appt->pivot->start_time && $appt->pivot->end_time) {
                    $totalHours += Carbon::parse($appt->pivot->start_time)
                                        ->diffInHours(Carbon::parse($appt->pivot->end_time));
                }
                $byStatus[$appt->status]++;
            });

            return response()->json([
                'employee' => $employee->name,
                'period' => "{$validated['from_date']} to {$validated['to_date']}",
                'total_assignments' => $assignments->count(),
                'total_hours' => $totalHours,
                'by_status' => $byStatus,
                'assignments' => $assignments,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
