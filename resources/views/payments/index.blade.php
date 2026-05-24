<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Payment Center</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Payments</h1>
            <p class="mt-2 text-sm text-slate-500">Completed appointments appear here for payment processing.</p>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    @if(isset($totals))
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
        <div class="hk-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Paid Amount</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">₱{{ number_format($totals['paid_amount'], 2) }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="hk-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Pending Amount</p>
                    <p class="mt-2 text-3xl font-bold text-yellow-600">₱{{ number_format($totals['pending_amount'], 2) }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-50 text-yellow-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c.865-1.728 2.888-2.891 5.303-2.891s4.438 1.163 5.303 2.891M3.75 21h16.5A2.25 2.25 0 0021 18.75V4.5A2.25 2.25 0 0018.75 2.25h-16.5A2.25 2.25 0 002.25 4.5v14.25A2.25 2.25 0 003.75 21z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="hk-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Amount</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">₱{{ number_format($totals['total_amount'], 2) }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0l.879-.659m-3.5 2.159l.879.659c1.171.879 3.07.879 4.242 0l.879-.659m-3.5 2.159l.879.659c1.171.879 3.07.879 4.242 0l.879-.659M3.75 13.5a6.75 6.75 0 1113.5 0M3 11.25A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Bar: Filter Button -->
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
            <form action="{{ route('payments.index') }}" method="GET" class="space-y-4">
                <!-- Date Range with Calendar -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">From</label>
                        <input type="text" name="from_date" class="payment-date-picker w-full px-3 py-2 text-sm border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="mm/dd/yyyy" value="{{ $fromDate ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">To</label>
                        <input type="text" name="to_date" class="payment-date-picker w-full px-3 py-2 text-sm border border-slate-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="mm/dd/yyyy" value="{{ $toDate ?? '' }}">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 pt-2 border-t border-slate-200">
                    <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded hover:bg-blue-700 transition-colors">
                        Apply
                    </button>
                    <a href="{{ route('payments.index') }}" class="flex-1 px-3 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded hover:bg-slate-50 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($payments->isEmpty())
        <div class="hk-empty">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z" />
                    <path stroke-linecap="round" d="M4 9h16M8 15h4" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-slate-950">No payments found</h2>
            <p class="mt-2 text-sm text-slate-500">Process a completed appointment to record a payment.</p>
            <a href="{{ route('appointments.index') }}" class="hk-button-primary mt-5">View Appointments</a>
        </div>
    @else
        <div class="hidden lg:block">
            <div class="hk-table-wrap">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-2 py-2 text-left font-semibold text-slate-700">Customer</th>
                            <th class="px-2 py-2 text-left font-semibold text-slate-700">Amount</th>
                            <th class="px-2 py-2 text-left font-semibold text-slate-700">Method</th>
                            <th class="px-2 py-2 text-center font-semibold text-slate-700">Status</th>
                            <th class="px-2 py-2 text-left font-semibold text-slate-700">Date</th>
                            <th class="px-2 py-2 text-right font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-2 py-1.5">
                                    <div class="font-medium text-slate-900 text-xs">{{ Str::limit($payment->appointment->customer_name, 15) }}</div>
                                    <div class="text-xs text-slate-500">{{ Str::limit($payment->appointment->address, 20) }}</div>
                                </td>
                                <td class="px-2 py-1.5 font-semibold text-emerald-600">₱{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-2 py-1.5 text-xs">{{ $payment->payment_method }}</td>
                                <td class="px-2 py-1.5 text-center">
                                    @if($payment->payment_status === 'Paid')
                                        <span class="inline-block px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded text-xs font-semibold">Paid</span>
                                    @else
                                        <span class="inline-block px-1.5 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs font-semibold">Pending</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs text-slate-600">{{ $payment->created_at->format('m/d/Y') }}</td>
                                <td class="px-2 py-1.5 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('payments.show', $payment) }}" target="_blank" class="px-2 py-1 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition-colors" title="View Receipt">View</a>
                                        <a href="{{ route('payments.show', $payment) }}" target="_blank" onclick="window.open(this.href); return false;" class="px-2 py-1 bg-green-600 text-white rounded text-xs font-medium hover:bg-green-700 transition-colors" title="Print Receipt">Print</a>
                                        <a href="{{ route('payments.edit', $payment) }}" class="px-2 py-1 border border-slate-300 text-slate-700 rounded text-xs font-medium hover:bg-slate-50 transition-colors">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4 lg:hidden">
            @foreach($payments as $payment)
                <div class="hk-mobile-card">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-slate-950">{{ $payment->appointment->customer_name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $payment->appointment->address }}</p>
                        </div>
                        @if($payment->payment_status === 'Paid')
                            <span class="hk-badge-completed">Paid</span>
                        @else
                            <span class="hk-badge-pending">Pending</span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 rounded-lg bg-slate-50 p-3">
                        <div>
                            <p class="text-xs font-medium text-slate-500">Amount</p>
                            <p class="mt-1 text-lg font-bold text-emerald-600">₱{{ number_format($payment->amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500">Method</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $payment->payment_method }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs font-medium text-slate-500">Date</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $payment->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('payments.show', $payment) }}" target="_blank" class="flex-1 px-2 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors text-center">View</a>
                        <a href="{{ route('payments.show', $payment) }}" target="_blank" onclick="window.open(this.href); return false;" class="flex-1 px-2 py-2 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition-colors text-center">Print</a>
                        <a href="{{ route('payments.edit', $payment) }}" class="flex-1 px-2 py-2 border border-slate-300 text-slate-700 text-xs font-semibold rounded hover:bg-slate-50 transition-colors text-center">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($payments instanceof \Illuminate\Pagination\Paginator)
            <div class="mt-6">
                {{ $payments->links() }}
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
        
        // Calendar picker for payment date range fields
        flatpickr(".payment-date-picker", {
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
