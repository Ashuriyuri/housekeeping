<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between print:hidden">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Receipt</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Receipt #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</h1>
                <p class="mt-2 text-sm text-slate-500">Payment receipt for {{ $payment->appointment->customer_name }}.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <button type="button" class="hk-button-primary w-full sm:w-auto" onclick="window.print()">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8V4h10v4M7 17H5a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-2M7 14h10v7H7v-7Z" />
                    </svg>
                    Print Receipt
                </button>
                <a href="{{ route('payments.index') }}" class="hk-button-secondary w-full sm:w-auto">Back to Payments</a>
            </div>
        </div>
    </x-slot>

    @php
        $appointment = $payment->appointment;
        $receiptNumber = 'RCPT-' . $payment->created_at->format('Ymd') . '-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
    @endphp

    <div class="mx-auto max-w-4xl">
        <div class="hk-card overflow-hidden print:border-0 print:shadow-none">
            <div class="border-b border-slate-200 bg-slate-950 p-6 text-white print:bg-white print:text-slate-950">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-200 print:text-slate-500">Housekeeping Admin</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Official Receipt</h2>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-sm text-slate-300 print:text-slate-500">Receipt No.</p>
                        <p class="mt-1 text-lg font-bold">{{ $receiptNumber }}</p>
                        <p class="mt-3 text-sm text-slate-300 print:text-slate-500">{{ $payment->created_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bill To</p>
                        <p class="mt-2 text-lg font-bold text-slate-950">{{ $appointment->customer_name }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $appointment->address }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment Details</p>
                        <div class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Method</span>
                                <span class="font-semibold text-slate-950">{{ $payment->payment_method }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Status</span>
                                <span class="font-semibold text-slate-950">{{ $payment->payment_status }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Schedule</span>
                                <span class="font-semibold text-slate-950">{{ $appointment->schedule_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Service</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Qty / Area</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Rate</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($appointment->services as $service)
                                @php
                                    $quantity = (float) ($service->pivot->quantity ?? 0);
                                    $unitPrice = (float) ($service->pivot->custom_price ?? $service->base_price);
                                    $lineTotal = $quantity * $unitPrice;
                                    $unitLabel = $service->isPricedPerSquareMeter() ? 'sqm' : 'qty';
                                @endphp
                                <tr>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-slate-950">{{ $service->service_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $service->description ?: 'Housekeeping service' }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-medium text-slate-700">{{ number_format($quantity, 2) }} {{ $unitLabel }}</td>
                                    <td class="px-4 py-4 text-right text-sm font-medium text-slate-700">PHP {{ number_format($unitPrice, 2) }}</td>
                                    <td class="px-4 py-4 text-right text-sm font-bold text-slate-950">PHP {{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-4 font-semibold text-slate-950">Housekeeping Service</td>
                                    <td class="px-4 py-4 text-right text-sm font-medium text-slate-700">{{ number_format($appointment->total_square_meter, 2) }} sqm</td>
                                    <td class="px-4 py-4 text-right text-sm font-medium text-slate-700">PHP {{ number_format($appointment->rate_per_square_meter, 2) }}</td>
                                    <td class="px-4 py-4 text-right text-sm font-bold text-slate-950">PHP {{ number_format($appointment->total_price, 2) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-sm rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                        <div class="flex justify-between gap-4 border-b border-emerald-200 pb-3 text-sm">
                            <span class="font-medium text-emerald-700">Measured Area</span>
                            <span class="font-bold text-slate-950">{{ number_format($appointment->total_square_meter, 2) }} sqm</span>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-emerald-200 py-3 text-sm">
                            <span class="font-medium text-emerald-700">Amount Due</span>
                            <span class="font-bold text-slate-950">PHP {{ number_format($appointment->total_price, 2) }}</span>
                        </div>
                        <div class="pt-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Amount Paid</p>
                            <p class="mt-1 text-3xl font-extrabold text-emerald-600">PHP {{ number_format($payment->amount, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row print:hidden">
                    <a href="{{ route('payments.edit', $payment) }}" class="hk-button-secondary w-full sm:w-auto">Edit Payment</a>
                    <a href="{{ route('payments.index') }}" class="hk-button-primary w-full sm:w-auto">Back to Payments</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
