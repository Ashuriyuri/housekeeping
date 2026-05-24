<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Appointment Detail</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $appointment->customer_name }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $appointment->schedule_date->format('l, F d, Y') }} at {{ $appointment->schedule_date->format('h:i A') }}</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('appointments.edit', $appointment) }}" class="hk-button-secondary w-full sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 16.5-.5 4 4-.5L19 8.5 15.5 5 4 16.5Z" />
                        <path stroke-linecap="round" d="m14 6 4 4" />
                    </svg>
                    Edit
                </a>
                <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" onsubmit="return confirm('Delete this appointment?');">
                    @csrf
                    @method('DELETE')
                    <button class="hk-button-danger w-full sm:w-auto">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M8 7l1-3h6l1 3M7 7l1 14h8l1-14" />
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    @php
        $totalArea = $appointment->total_square_meter;
        $totalCost = $appointment->total_price;
    @endphp

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-1">
            <div class="hk-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="hk-section-title">Customer Info</h2>
                        <p class="hk-section-subtitle">Primary appointment details.</p>
                    </div>
                </div>

                <form action="{{ route('appointments.status.update', $appointment) }}" method="POST" class="mt-5 rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                    @csrf
                    @method('PATCH')
                    <label class="hk-label" for="appointment-status">Appointment Status</label>
                    <select id="appointment-status" name="status" class="hk-select" onchange="this.form.submit()">
                        <option value="Pending" {{ $appointment->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ $appointment->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed" {{ $appointment->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    <p class="hk-help">Set to Completed to record or edit payment.</p>
                </form>

                <dl class="mt-6 space-y-5">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer Name</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-950">{{ $appointment->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Address</dt>
                        <dd class="mt-1 text-sm font-medium leading-6 text-slate-800">{{ $appointment->address }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Schedule</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $appointment->schedule_date->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Time</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $appointment->schedule_date->format('h:i A') }}</dd>
                        </div>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Area</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($totalArea, 2) }} sqm</dd>
                    </div>
                </dl>

                @if($appointment->notes)
                    <div class="mt-6 rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $appointment->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="hk-card p-6">
                <h2 class="hk-section-title">Employees Assigned</h2>
                <p class="hk-section-subtitle">Team members and task notes.</p>

                <div class="mt-5 space-y-3">
                    @forelse($appointment->employees as $employee)
                        <div class="flex items-start gap-3 rounded-lg border border-slate-200 p-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-sm font-bold text-blue-700">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate font-semibold text-slate-950">{{ $employee->name }}</p>
                                    <span class="{{ $employee->status === 'Active' ? 'hk-badge-active' : 'hk-badge-inactive' }}">{{ $employee->status }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $employee->position }}</p>
                                <p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">{{ $employee->pivot->task ?: 'Task pending assignment' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">No employees assigned.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6 xl:col-span-2">
            <div class="hk-card p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="hk-section-title">Service Breakdown</h2>
                        <p class="hk-section-subtitle">Each selected service uses its own pricing rule.</p>
                    </div>
                    <span class="hk-badge-completed">Mixed Pricing</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($appointment->services as $service)
                        @php
                            $serviceQuantity = (float) ($service->pivot->quantity ?? 0);
                            $unitPrice = (float) ($service->pivot->custom_price ?? $service->base_price);
                            $serviceTotal = $serviceQuantity * $unitPrice;
                            $unitLabel = $service->isPricedPerSquareMeter() ? 'sqm' : 'qty';
                        @endphp
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-950">{{ $service->service_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $service->description ?: 'Housekeeping service' }}</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-sm font-semibold text-slate-700">{{ number_format($serviceQuantity, 2) }} {{ $unitLabel }} x PHP {{ number_format($unitPrice, 2) }}</p>
                                    <p class="mt-1 text-xl font-bold text-emerald-600">PHP {{ number_format($serviceTotal, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">No services selected.</div>
                    @endforelse
                </div>

                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Measured Area</p>
                            <p class="mt-1 text-lg font-bold text-slate-950">{{ number_format($totalArea, 2) }} sqm</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Pricing</p>
                            <p class="mt-1 text-lg font-bold text-slate-950">Per service</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Total Cost</p>
                            <p class="mt-1 text-3xl font-extrabold text-emerald-600">PHP {{ number_format($totalCost, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="hk-card p-6">
                    <h2 class="hk-section-title">Payment Summary</h2>
                    <p class="hk-section-subtitle">Amount is based on selected service pricing.</p>

                    <div class="mt-5 rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Amount</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-600">PHP {{ number_format($totalCost, 2) }}</p>
                    </div>

                    @if($appointment->payment)
                        <div class="mt-5 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <span class="text-sm font-medium text-slate-500">Method</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $appointment->payment->payment_method }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <span class="text-sm font-medium text-slate-500">Paid Amount</span>
                                <span class="text-sm font-semibold text-slate-900">PHP {{ number_format($appointment->payment->amount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-500">Status</span>
                                @if($appointment->payment->payment_status === 'Paid')
                                    <span class="hk-badge-completed">Paid</span>
                                @else
                                    <span class="hk-badge-pending">Pending</span>
                                @endif
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <a href="{{ route('payments.show', $appointment->payment) }}" class="hk-button-primary w-full">View Receipt</a>
                                <a href="{{ route('payments.edit', $appointment->payment) }}" class="hk-button-secondary w-full">Edit Payment</a>
                            </div>
                        </div>
                    @elseif($appointment->status === 'Completed')
                        <a href="{{ route('payments.create', $appointment) }}" class="hk-button-success mt-5 w-full">Record Payment</a>
                    @else
                        <div class="mt-5 rounded-lg border border-dashed border-slate-300 p-4 text-sm font-medium text-slate-500">Payment opens when the appointment is completed.</div>
                    @endif
                </div>

                <div class="hk-card p-6">
                    <h2 class="hk-section-title">Appointment Controls</h2>
                    <p class="hk-section-subtitle">Status and assignment actions.</p>

                    <div class="mt-5 space-y-3">
                        <a href="{{ route('appointments.edit', $appointment) }}#service-selection" class="hk-button-secondary w-full justify-between">
                            Edit Services
                            <span>{{ $appointment->services->count() }}</span>
                        </a>
                        <a href="{{ route('appointments.edit', $appointment) }}#employee-assignment" class="hk-button-secondary w-full justify-between">
                            Assign Employees
                            <span>{{ $appointment->employees->count() }}</span>
                        </a>
                        <a href="{{ route('appointments.index') }}" class="hk-button-primary w-full">Back to Appointments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
