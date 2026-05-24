<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Appointment Module</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Appointments</h1>
                <p class="mt-2 text-sm text-slate-500">Manage schedules, service areas, assignments, and sqm-based costs.</p>
            </div>
            <a href="{{ route('appointments.create') }}" class="hk-button-primary w-full sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                New Appointment
            </a>
        </div>
    </x-slot>

    <!-- Top Bar: Filter Button + Status Tabs -->
    <div class="flex flex-col gap-4 mb-6">
        <!-- Filter Trigger Button -->
        <div class="flex items-center justify-between">
            <button id="filterToggle" type="button" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 4v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4Z" />
                </svg>
                Filters
            </button>
        </div>

        <!-- Collapsible Filter Panel -->
        <div id="filterPanel" class="hidden bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
            <form action="{{ route('appointments.index') }}" method="GET" class="space-y-4">
                <!-- Status Filter (Optional, since tabs exist) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ ($status ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ ($status ?? '') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed" {{ ($status ?? '') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <!-- Date Range with Calendar -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">From</label>
                        <input type="text" name="from_date" class="appointment-date-picker w-full px-3 py-2 text-sm border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="mm/dd/yyyy">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">To</label>
                        <input type="text" name="to_date" class="appointment-date-picker w-full px-3 py-2 text-sm border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="mm/dd/yyyy">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 pt-2 border-t border-slate-200">
                    <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded hover:bg-blue-700 transition-colors">
                        Apply
                    </button>
                    <a href="{{ route('appointments.index') }}" class="flex-1 px-3 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded hover:bg-slate-50 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Tabs (Main Control) -->
    <div class="border-b border-slate-200 mb-6 flex gap-6">
        <a href="{{ route('appointments.index') }}" class="px-3 py-3 text-sm font-semibold transition-colors {{ !($status ?? '') ? 'text-slate-950 border-b-2 border-slate-950' : 'text-slate-600 border-b-2 border-transparent hover:text-slate-900' }}">
            All
        </a>
        <a href="{{ route('appointments.index', ['status' => 'Pending']) }}" class="px-3 py-3 text-sm font-semibold transition-colors {{ ($status ?? '') == 'Pending' ? 'text-slate-950 border-b-2 border-slate-950' : 'text-slate-600 border-b-2 border-transparent hover:text-slate-900' }}">
            Pending
        </a>
        <a href="{{ route('appointments.index', ['status' => 'In Progress']) }}" class="px-3 py-3 text-sm font-semibold transition-colors {{ ($status ?? '') == 'In Progress' ? 'text-slate-950 border-b-2 border-slate-950' : 'text-slate-600 border-b-2 border-transparent hover:text-slate-900' }}">
            In Progress
        </a>
        <a href="{{ route('appointments.index', ['status' => 'Completed']) }}" class="px-3 py-3 text-sm font-semibold transition-colors {{ ($status ?? '') == 'Completed' ? 'text-slate-950 border-b-2 border-slate-950' : 'text-slate-600 border-b-2 border-transparent hover:text-slate-900' }}">
            Completed
        </a>
    </div>

    @if($appointments->isEmpty())
        <div class="hk-empty">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3M17 3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v11.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-slate-950">No appointments found</h2>
            <p class="mt-2 text-sm text-slate-500">{{ ($status ?? '') ? 'Try adjusting your filters or create a new appointment.' : 'Create the first schedule and attach service area pricing.' }}</p>
            <a href="{{ route('appointments.create') }}" class="hk-button-primary mt-5">Create Appointment</a>
        </div>
    @else
        <!-- Appointments Table -->
        <div class="hidden lg:block rounded-lg overflow-hidden border border-slate-200 bg-white shadow-sm">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wide">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wide">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wide">Schedule</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wide">Area</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wide">Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($appointments as $appointment)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm">
                                <div class="font-semibold text-slate-900">{{ $appointment->customer_name }}</div>
                                <div class="text-xs text-slate-500">{{ $appointment->services->count() }} service(s)</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ Str::limit($appointment->address, 40) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-semibold text-slate-900">{{ $appointment->schedule_date->format('M d, Y') }}</div>
                                <div class="text-xs text-slate-500">{{ $appointment->schedule_date->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ number_format($appointment->total_square_meter, 2) }} sqm</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-bold text-emerald-600">₱{{ number_format($appointment->total_price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <form method="POST" action="{{ route('appointments.status.update', $appointment) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white cursor-pointer">
                                        <option value="Pending" {{ $appointment->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="In Progress" {{ $appointment->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Completed" {{ $appointment->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-sm text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('appointments.show', $appointment) }}" title="View" class="p-2 hover:bg-slate-100 rounded transition-colors">
                                        <svg class="h-4 w-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                                    </a>
                                    <a href="{{ route('appointments.edit', $appointment) }}" title="Edit" class="p-2 hover:bg-slate-100 rounded transition-colors">
                                        <svg class="h-4 w-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4 16.5-.5 4 4-.5L19 8.5 15.5 5 4 16.5Z" /><path stroke-linecap="round" d="m14 6 4 4" /></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="space-y-3 lg:hidden">
            @foreach($appointments as $appointment)
                <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900">{{ $appointment->customer_name }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($appointment->address, 50) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3 p-2 bg-slate-50 rounded">
                        <div>
                            <p class="text-xs text-slate-500 font-semibold">Date & Time</p>
                            <p class="text-sm font-semibold text-slate-900">{{ $appointment->schedule_date->format('M d, h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-semibold">Area</p>
                            <p class="text-sm font-semibold text-slate-900">{{ number_format($appointment->total_square_meter, 2) }} sqm</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-slate-500 font-semibold">Total Cost</p>
                            <p class="text-lg font-bold text-emerald-600">₱{{ number_format($appointment->total_price, 2) }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-slate-500 font-semibold">Status</p>
                            <form method="POST" action="{{ route('appointments.status.update', $appointment) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white cursor-pointer">
                                    <option value="Pending" {{ $appointment->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="In Progress" {{ $appointment->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="Completed" {{ $appointment->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('appointments.show', $appointment) }}" class="flex-1 px-2 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded hover:bg-slate-50 transition text-center">View</a>
                        <a href="{{ route('appointments.edit', $appointment) }}" class="flex-1 px-2 py-2 bg-slate-900 text-white text-sm font-semibold rounded hover:bg-slate-800 transition text-center">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($appointments instanceof \Illuminate\Pagination\Paginator)
            <div class="mt-8">
                {{ $appointments->links() }}
            </div>
        @endif
    @endif
</x-app-layout>

<!-- Calendar Date Picker and InputMask -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"></script>
<script>
    $(function() {
        $(".filter-date").inputmask("99/99/9999", {"placeholder": "mm/dd/yyyy"});
        
        // Calendar picker for appointment date range fields
        flatpickr(".appointment-date-picker", {
            dateFormat: "m/d/Y",
            allowInput: true
        });
    });

    // Toggle filter panel
    document.getElementById('filterToggle').addEventListener('click', function() {
        const filterPanel = document.getElementById('filterPanel');
        filterPanel.classList.toggle('hidden');
    });
</script>
