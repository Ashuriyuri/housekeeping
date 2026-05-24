<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Employee Profile</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $employee->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $employee->position }}</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('employees.edit', $employee) }}" class="hk-button-secondary w-full sm:w-auto">Edit</a>
                <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Delete this employee?');">
                    @csrf
                    @method('DELETE')
                    <button class="hk-button-danger w-full sm:w-auto">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="hk-card p-6 lg:col-span-2">
            <div class="flex items-start gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-blue-50 text-xl font-bold text-blue-700">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-950">{{ $employee->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $employee->phone }}</p>
                    <div class="mt-3">
                        @if($employee->status === 'Active')
                            <span class="hk-badge-active">Active</span>
                        @else
                            <span class="hk-badge-inactive">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Position</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $employee->position }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Appointments</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600">{{ count($assignments) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $employee->status }}</p>
                </div>
            </div>

            <!-- Task Filter Section -->
            <div class="mt-8 p-4 bg-slate-50 rounded-lg border border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Filter Tasks</h3>
                <form action="{{ route('employees.filter-tasks', $employee) }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Date (mm/dd/yyyy)</label>
                            <input type="text" name="date" class="employee-date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="mm/dd/yyyy">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tasks List -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Assigned Tasks</h3>
                @if(count($assignments) > 0)
                    <div class="space-y-4">
                        @foreach($assignments as $assignment)
                            <div class="border border-slate-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-slate-900">{{ $assignment['customer_name'] ?? 'N/A' }}</h4>
                                        <p class="text-sm text-slate-600 mt-1">📍 {{ $assignment['address'] ?? 'N/A' }}</p>
                                        @if(isset($assignment['schedule_date']) && $assignment['schedule_date'])
                                            <p class="text-sm text-slate-600">📅 {{ \Carbon\Carbon::parse($assignment['schedule_date'])->format('M d, Y g:i A') }}</p>
                                        @endif
                                        @if($assignment['task'] ?? null)
                                            <p class="text-sm text-slate-700 mt-2"><strong>Task:</strong> {{ $assignment['task'] }}</p>
                                        @endif
                                        @if(($assignment['start_time'] ?? null) && ($assignment['end_time'] ?? null))
                                            <p class="text-sm text-slate-700">⏰ {{ $assignment['start_time']->format('g:i A') }} - {{ $assignment['end_time']->format('g:i A') }} ({{ $assignment['duration'] ?? 0 }} min)</p>
                                        @endif
                                        <div class="mt-2">
                                            @if(($assignment['status'] ?? '') === 'Pending')
                                                <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded">🟡 Pending</span>
                                            @elseif(($assignment['status'] ?? '') === 'In Progress')
                                                <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded">🔵 In Progress</span>
                                            @else
                                                <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">✅ Completed</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($assignment['appointment_id'] ?? null)
                                        <a href="{{ route('appointments.show', $assignment['appointment_id']) }}" class="text-blue-600 hover:text-blue-800 font-medium">View Appointment →</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-slate-500">
                        <p>No tasks assigned to this employee.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <a href="{{ route('appointments.create') }}" class="hk-button-primary w-full">Assign to Appointment</a>
            <a href="{{ route('employees.index') }}" class="hk-button-secondary w-full">Back to Employees</a>
        </div>
    </div>
</x-app-layout>

<!-- Calendar Date Picker and InputMask -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"></script>
<script>
    $(function() {
        $(".filter-date").inputmask("99/99/9999", {"placeholder": "mm/dd/yyyy"});
        
        // Calendar picker for employee task filter date
        flatpickr(".employee-date-picker", {
            dateFormat: "m/d/Y",
            allowInput: true
        });
    });
</script>

<!-- InputMask for date formatting -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"></script>
<script>
    $(function() {
        $(".filter-date").inputmask("99/99/9999", {"placeholder": "mm/dd/yyyy"});
    });
</script>
