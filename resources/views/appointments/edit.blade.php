<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Edit Appointment</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $appointment->customer_name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Update schedule, sqm pricing, selected services, and assigned employees.</p>
            </div>
            <a href="{{ route('appointments.show', $appointment) }}" class="hk-button-secondary w-full sm:w-auto">Back to Details</a>
        </div>
    </x-slot>

    <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="grid grid-cols-1 gap-6 xl:grid-cols-3" data-appointment-form>
        @csrf
        @method('PUT')

        <div class="space-y-6 xl:col-span-2">
            <section class="hk-card p-6">
                <h2 class="hk-section-title">Customer & Schedule</h2>
                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="hk-label" for="customer_name">Customer Name</label>
                        <input id="customer_name" type="text" name="customer_name" class="hk-input @error('customer_name') border-rose-500 @enderror" value="{{ old('customer_name', $appointment->customer_name) }}" required>
                        @error('customer_name') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="hk-label" for="schedule_date">Schedule Date</label>
                        <input id="schedule_date" type="datetime-local" name="schedule_date" class="hk-input @error('schedule_date') border-rose-500 @enderror" value="{{ old('schedule_date', $appointment->schedule_date->format('Y-m-d\TH:i')) }}" required>
                        @error('schedule_date') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="hk-label" for="address">Address</label>
                        <input id="address" type="text" name="address" class="hk-input @error('address') border-rose-500 @enderror" value="{{ old('address', $appointment->address) }}" required>
                        @error('address') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="hk-label" for="total_square_meter">Total Area (sqm)</label>
                        <input id="total_square_meter" type="number" name="area_sqm" step="0.01" min="1" class="hk-input @error('area_sqm') border-rose-500 @enderror" value="{{ old('area_sqm', $appointment->area_sqm) }}" data-total-area>
                        @error('area_sqm') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="hk-label" for="status">Status</label>
                        <select id="status" name="status" class="hk-select" required>
                            <option value="Pending" {{ old('status', $appointment->status) === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="In Progress" {{ old('status', $appointment->status) === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Completed" {{ old('status', $appointment->status) === 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="hk-label" for="notes">Notes</label>
                        <textarea id="notes" name="notes" class="hk-textarea" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                    </div>
                </div>
            </section>

            <section id="service-selection" class="hk-card p-6">
                <h2 class="hk-section-title">Services Selected</h2>
                <p class="hk-section-subtitle">Per-sqm services use area, fixed-price services use quantity.</p>

                <div class="mt-5 grid grid-cols-1 gap-4">
                    @foreach($services as $service)
                        @php
                            $oldServices = old('services');
                            $oldQuantities = old('service_quantity', []);
                            $selectedService = $appointment->services->firstWhere('id', $service->id);
                            $oldIndex = is_array($oldServices) ? collect($oldServices)->search((string) $service->id) : false;
                            if ($oldIndex === false && is_array($oldServices)) {
                                $oldIndex = collect($oldServices)->search($service->id);
                            }
                            $isChecked = is_array($oldServices) ? $oldIndex !== false : (bool) $selectedService;
                            $serviceArea = is_array($oldServices)
                                ? ($isChecked ? ($oldQuantities[$oldIndex] ?? 1) : 1)
                                : ($selectedService?->pivot->quantity ?? 1);
                        @endphp
                        <label class="hk-service-choice" data-service-row data-service-rate="{{ $service->base_price }}" data-service-type="{{ $service->pricing_type }}">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-service-check {{ $isChecked ? 'checked' : '' }}>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-950">{{ $service->service_name }}</p>
                                            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $service->description ?: 'Housekeeping service' }}</p>
                                        </div>
                                        <span class="hk-badge-completed whitespace-nowrap">{{ $service->price_label }}</span>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="hk-label text-xs">{{ $service->quantity_label }}</label>
                                            <input type="number" name="service_quantity[]" min="1" step="0.01" value="{{ $serviceArea }}" class="hk-input" data-service-area data-enable-on-check {{ $isChecked ? '' : 'disabled' }}>
                                        </div>
                                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Line Total</p>
                                            <p class="mt-1 text-lg font-bold text-emerald-600" data-line-total>PHP 0.00</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>

            <section id="employee-assignment" class="hk-card p-6">
                <h2 class="hk-section-title">Employee Assignment</h2>
                <p class="hk-section-subtitle">Assign team members and define each task.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach($employees as $employee)
                        @php
                            $oldEmployees = old('employees');
                            $oldTasks = old('employee_tasks', []);
                            $selectedEmployee = $appointment->employees->firstWhere('id', $employee->id);
                            $oldIndex = is_array($oldEmployees) ? collect($oldEmployees)->search((string) $employee->id) : false;
                            if ($oldIndex === false && is_array($oldEmployees)) {
                                $oldIndex = collect($oldEmployees)->search($employee->id);
                            }
                            $isChecked = is_array($oldEmployees) ? $oldIndex !== false : (bool) $selectedEmployee;
                            $task = is_array($oldEmployees)
                                ? ($isChecked ? ($oldTasks[$oldIndex] ?? '') : '')
                                : ($selectedEmployee?->pivot->task ?? '');
                        @endphp
                        <label class="hk-employee-choice" data-employee-row>
                            <div class="flex gap-4">
                                <input type="checkbox" name="employees[]" value="{{ $employee->id }}" class="mt-3 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500" data-employee-check {{ $isChecked ? 'checked' : '' }}>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-sm font-bold text-blue-700">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                            <span>
                                                <span class="block font-semibold text-slate-950">{{ $employee->name }}</span>
                                                <span class="block text-sm text-slate-500">{{ $employee->position }}</span>
                                            </span>
                                        </div>
                                        <span class="hk-badge-active">Active</span>
                                    </div>
                                    <div class="mt-4">
                                        <label class="hk-label text-xs">Assigned Task</label>
                                        <input type="text" name="employee_tasks[]" value="{{ $task }}" class="hk-input" placeholder="Sofa Cleaning" data-employee-task data-enable-on-check {{ $isChecked ? '' : 'disabled' }}>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="space-y-6 xl:col-span-1">
            <div class="hk-card sticky top-24 p-6">
                <h2 class="hk-section-title">Cost Summary</h2>
                <div class="mt-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-sm font-medium text-slate-500">Measured Area</span>
                        <span class="text-sm font-bold text-slate-950" data-summary-area>0 sqm</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-sm font-medium text-slate-500">Pricing</span>
                        <span class="text-sm font-bold text-slate-950">Service rules</span>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Total Cost</p>
                        <p class="mt-2 text-3xl font-extrabold text-emerald-600" data-summary-total>PHP 0.00</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-3">
                    <button type="submit" class="hk-button-primary w-full">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                        Save Changes
                    </button>
                    <a href="{{ route('appointments.show', $appointment) }}" class="hk-button-secondary w-full">Cancel</a>
                </div>
            </div>
        </aside>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('[data-appointment-form]');
                if (!form) return;

                const fallbackRate = 55;
                const totalAreaInput = form.querySelector('[data-total-area]');
                const summaryArea = form.querySelector('[data-summary-area]');
                const summaryTotal = form.querySelector('[data-summary-total]');
                const currency = new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const updatePricing = () => {
                    let selectedArea = 0;
                    let selectedTotal = 0;

                    form.querySelectorAll('[data-service-row]').forEach((row) => {
                        const checkbox = row.querySelector('[data-service-check]');
                        const areaInput = row.querySelector('[data-service-area]');
                        const lineTotal = row.querySelector('[data-line-total]');
                        const enabled = checkbox.checked;
                        const rate = Number(row.dataset.serviceRate || 0);
                        const pricingType = row.dataset.serviceType;

                        row.querySelectorAll('[data-enable-on-check]').forEach((input) => {
                            input.disabled = !enabled;
                        });

                        const quantity = enabled ? Number(areaInput.value || 0) : 0;
                        const total = quantity * rate;

                        if (pricingType === 'per_sqm') {
                            selectedArea += quantity;
                        }

                        selectedTotal += total;
                        lineTotal.textContent = `PHP ${currency.format(total)}`;
                    });

                    const fallbackArea = Number(totalAreaInput.value || 0);
                    const totalArea = selectedArea > 0 ? selectedArea : fallbackArea;
                    const totalCost = selectedTotal > 0 ? selectedTotal : totalArea * fallbackRate;

                    summaryArea.textContent = `${currency.format(totalArea)} sqm`;
                    summaryTotal.textContent = `PHP ${currency.format(totalCost)}`;
                };

                form.querySelectorAll('[data-service-check], [data-service-area], [data-total-area]').forEach((element) => {
                    element.addEventListener('input', updatePricing);
                    element.addEventListener('change', updatePricing);
                });

                form.querySelectorAll('[data-employee-check]').forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        const row = checkbox.closest('[data-employee-row]');
                        row.querySelectorAll('[data-enable-on-check]').forEach((input) => {
                            input.disabled = !checkbox.checked;
                        });
                    });
                });

                updatePricing();
            });
        </script>
    @endpush
</x-app-layout>
