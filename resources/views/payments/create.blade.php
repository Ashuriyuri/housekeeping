<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Record Payment</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $appointment->customer_name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Payment amount is generated from the selected service pricing.</p>
            </div>
            <a href="{{ route('payments.index') }}" class="hk-button-secondary w-full sm:w-auto">Back to Payments</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="hk-card p-6 lg:col-span-1">
            <h2 class="hk-section-title">Appointment Summary</h2>
            <dl class="mt-5 space-y-4">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</dt>
                    <dd class="mt-1 font-semibold text-slate-950">{{ $appointment->customer_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Address</dt>
                    <dd class="mt-1 text-sm leading-6 text-slate-700">{{ $appointment->address }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pricing</dt>
                    <dd class="mt-1 font-semibold text-slate-950">{{ $appointment->services->count() }} selected services</dd>
                </div>
            </dl>
        </div>

        <div class="hk-card p-6 lg:col-span-2">
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Total Amount</p>
                <p class="mt-2 text-4xl font-extrabold text-emerald-600">PHP {{ number_format($totalPrice, 2) }}</p>
            </div>

            <form action="{{ route('payments.store', $appointment) }}" method="POST" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label class="hk-label" for="amount">Amount Paid</label>
                    <input id="amount" type="number" name="amount" step="0.01" min="0" class="hk-input @error('amount') border-rose-500 @enderror" value="{{ old('amount', $totalPrice) }}" required>
                    @error('amount') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="hk-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="hk-select" required>
                        <option value="">Select method</option>
                        <option value="Cash" {{ old('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="GCash" {{ old('payment_method') === 'GCash' ? 'selected' : '' }}>GCash</option>
                        <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                    @error('payment_method') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="hk-label" for="payment_status">Payment Status</label>
                    <select id="payment_status" name="payment_status" class="hk-select" required>
                        <option value="Pending" {{ old('payment_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Paid" {{ old('payment_status', 'Paid') === 'Paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                    @error('payment_status') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 gap-3 border-t border-slate-200 pt-5 sm:grid-cols-2">
                    <button type="submit" class="hk-button-success w-full">Record Payment</button>
                    <a href="{{ route('payments.index') }}" class="hk-button-secondary w-full">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
