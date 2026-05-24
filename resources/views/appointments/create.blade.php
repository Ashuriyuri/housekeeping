<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">New Appointment</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Create Appointment</h1>
                <p class="mt-2 text-sm text-slate-500">Add customer details, service sqm, employee tasks, and pricing preview.</p>
            </div>
            <a href="{{ route('appointments.index') }}" class="hk-button-secondary w-full sm:w-auto">Back to Appointments</a>
        </div>
    </x-slot>

    <form action="{{ route('appointments.store') }}" method="POST" class="grid grid-cols-1 gap-6 xl:grid-cols-3" data-appointment-form>
        @csrf

        <div class="space-y-6 xl:col-span-2">
            <section class="hk-card p-6">
                <h2 class="hk-section-title">Customer & Schedule</h2>
                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="hk-label" for="customer_name">Customer Name</label>
                        <input id="customer_name" type="text" name="customer_name" class="hk-input @error('customer_name') border-rose-500 @enderror" value="{{ old('customer_name') }}" required>
                        @error('customer_name') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="hk-label" for="schedule_date">Schedule Date</label>
                        <input id="schedule_date" type="datetime-local" name="schedule_date" class="hk-input @error('schedule_date') border-rose-500 @enderror" value="{{ old('schedule_date') }}" required>
                        @error('schedule_date') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="hk-label" for="address">Address</label>
                        <input id="address" type="text" name="address" class="hk-input @error('address') border-rose-500 @enderror" value="{{ old('address') }}" required>
                        @error('address') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="hk-label" for="total_square_meter">Total Area (sqm)</label>
                        <input id="total_square_meter" type="number" name="area_sqm" step="0.01" min="1" class="hk-input @error('area_sqm') border-rose-500 @enderror" value="{{ old('area_sqm') }}" data-total-area>
                        @error('area_sqm') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Pricing</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-700">Per service</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="hk-label" for="notes">Notes</label>
                        <textarea id="notes" name="notes" class="hk-textarea" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>

            <section id="service-selection" class="hk-card p-6">
                <h2 class="hk-section-title">Services Selected</h2>
                <p class="hk-section-subtitle">Per-sqm services use area, fixed-price services use quantity.</p>

                <div class="mt-5 grid grid-cols-1 gap-4">
                    @forelse($services as $service)
                        @php
                            $oldServices = collect(old('services', []));
                            $oldQuantities = old('service_quantity', []);
                            $oldIndex = $oldServices->search((string) $service->id);
                            if ($oldIndex === false) {
                                $oldIndex = $oldServices->search($service->id);
                            }
                            $isChecked = $oldIndex !== false;
                            $oldArea = $isChecked ? ($oldQuantities[$oldIndex] ?? 1) : 1;
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
                                            <input type="number" name="service_quantity[]" min="1" step="0.01" value="{{ $oldArea }}" class="hk-input" data-service-area data-enable-on-check {{ $isChecked ? '' : 'disabled' }}>
                                        </div>
                                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Line Total</p>
                                            <p class="mt-1 text-lg font-bold text-emerald-600" data-line-total>PHP 0.00</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                            No services available.
                        </div>
                    @endforelse
                </div>
            </section>

            <section id="employee-assignment" class="hk-card p-6">
                <h2 class="hk-section-title">Employee Assignment</h2>
                <p class="hk-section-subtitle">Assign team members and define each task.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @forelse($employees as $employee)
                        @php
                            $oldEmployees = collect(old('employees', []));
                            $oldTasks = old('employee_tasks', []);
                            $oldIndex = $oldEmployees->search((string) $employee->id);
                            if ($oldIndex === false) {
                                $oldIndex = $oldEmployees->search($employee->id);
                            }
                            $isChecked = $oldIndex !== false;
                            $oldTask = $isChecked ? ($oldTasks[$oldIndex] ?? '') : '';
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
                                        <input type="text" name="employee_tasks[]" value="{{ $oldTask }}" class="hk-input" placeholder="Deep Cleaning Bathroom" data-employee-task data-enable-on-check {{ $isChecked ? '' : 'disabled' }}>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 lg:col-span-2">
                            No active employees available.
                        </div>
                    @endforelse
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
                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Create Appointment
                    </button>
                    <a href="{{ route('appointments.index') }}" class="hk-button-secondary w-full">Cancel</a>
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
