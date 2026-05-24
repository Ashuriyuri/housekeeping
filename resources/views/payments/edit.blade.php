<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Edit Payment</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $payment->appointment->customer_name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Update method, amount, and payment status.</p>
            </div>
            <a href="{{ route('payments.show', $payment) }}" class="hk-button-secondary w-full sm:w-auto">Back to Receipt</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="hk-card p-6 lg:col-span-1">
            <h2 class="hk-section-title">Amount Due</h2>
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $payment->appointment->services->count() }} selected services</p>
                <p class="mt-2 text-4xl font-extrabold text-emerald-600">PHP {{ number_format($payment->appointment->total_price, 2) }}</p>
            </div>
        </div>

        <div class="hk-card p-6 lg:col-span-2">
            <form action="{{ route('payments.update', $payment) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="hk-label" for="amount">Amount Paid</label>
                    <input id="amount" type="number" name="amount" step="0.01" min="0" class="hk-input @error('amount') border-rose-500 @enderror" value="{{ old('amount', $payment->amount) }}" required>
                    @error('amount') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="hk-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="hk-select" required>
                        <option value="Cash" {{ old('payment_method', $payment->payment_method) === 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="GCash" {{ old('payment_method', $payment->payment_method) === 'GCash' ? 'selected' : '' }}>GCash</option>
                        <option value="Bank Transfer" {{ old('payment_method', $payment->payment_method) === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                    @error('payment_method') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="hk-label" for="payment_status">Payment Status</label>
                    <select id="payment_status" name="payment_status" class="hk-select" required>
                        <option value="Pending" {{ old('payment_status', $payment->payment_status) === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Paid" {{ old('payment_status', $payment->payment_status) === 'Paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                    @error('payment_status') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 gap-3 border-t border-slate-200 pt-5 sm:grid-cols-3">
                    <button type="submit" class="hk-button-primary w-full">Save Changes</button>
                    <a href="{{ route('payments.show', $payment) }}" class="hk-button-secondary w-full">Cancel</a>
                </div>
            </form>

            <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this payment?');">
                @csrf
                @method('DELETE')
                <button class="hk-button-danger w-full sm:w-auto">Delete Payment</button>
            </form>
        </div>
    </div>
</x-app-layout>
