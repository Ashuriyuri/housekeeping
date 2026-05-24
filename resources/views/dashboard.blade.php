<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Admin Dashboard</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">RJ CLEANING DAVAO APPOINTMENT SYSTEM</h1>
                <p class="mt-2 text-sm text-slate-500">Housekeeping Appointment System</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('appointments.create') }}" class="hk-button-primary w-full sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>
                    New Appointment
                </a>
                <a href="{{ route('payments.index') }}" class="hk-button-secondary w-full sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z" />
                        <path stroke-linecap="round" d="M4 9h16M8 15h4" />
                    </svg>
                    Payments
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $stats = [
            ['title' => 'Total Appointments', 'value' => number_format($totalAppointments), 'tone' => 'indigo', 'icon' => 'calendar'],
            ['title' => 'Pending', 'value' => number_format($pendingAppointments), 'tone' => 'amber', 'icon' => 'clock'],
            ['title' => 'In Progress', 'value' => number_format($inProgressAppointments ?? 0), 'tone' => 'blue', 'icon' => 'activity'],
            ['title' => 'Completed', 'value' => number_format($completedAppointments), 'tone' => 'emerald', 'icon' => 'check'],
            ['title' => 'Total Employees', 'value' => number_format($totalEmployees), 'tone' => 'slate', 'icon' => 'users'],
            ['title' => 'Total Revenue', 'value' => 'PHP ' . number_format($totalRevenue ?? 0, 2), 'tone' => 'green', 'icon' => 'wallet'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach($stats as $stat)
            <div class="hk-card hk-card-hover p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $stat['title'] }}</p>
                        <p class="mt-3 text-2xl font-bold tracking-tight text-slate-950">{{ $stat['value'] }}</p>
                    </div>
                    <div class="hk-stat-icon
                        @if($stat['tone'] === 'indigo') bg-indigo-50 text-indigo-600
                        @elseif($stat['tone'] === 'amber') bg-amber-50 text-amber-600
                        @elseif($stat['tone'] === 'blue') bg-blue-50 text-blue-600
                        @elseif($stat['tone'] === 'emerald') bg-emerald-50 text-emerald-600
                        @elseif($stat['tone'] === 'green') bg-green-50 text-green-600
                        @else bg-slate-100 text-slate-600
                        @endif">
                        @if($stat['icon'] === 'calendar')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3M17 3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v11.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" /></svg>
                        @elseif($stat['icon'] === 'clock')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8" /><path stroke-linecap="round" d="M12 8v4l3 2" /></svg>
                        @elseif($stat['icon'] === 'activity')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 13h4l2-6 4 12 2-6h4" /></svg>
                        @elseif($stat['icon'] === 'check')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                        @elseif($stat['icon'] === 'users')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19a4 4 0 0 0-8 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 20a3.5 3.5 0 0 0-3-3.46M4 20a3.5 3.5 0 0 1 3-3.46" /></svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z" /><path stroke-linecap="round" d="M4 9h16M8 15h4" /></svg>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="hk-card p-6 xl:col-span-2">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="hk-section-title">Operational Shortcuts</h2>
                    <p class="hk-section-subtitle">Fast access to common admin workflows.</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('appointments.index') }}" class="rounded-lg border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50">
                    <span class="hk-stat-icon bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3M17 3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v11.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" /></svg>
                    </span>
                    <p class="mt-4 font-semibold text-slate-950">Appointments</p>
                    <p class="mt-1 text-sm text-slate-500">View schedules and status.</p>
                </a>

                <a href="{{ route('employees.index') }}" class="rounded-lg border border-slate-200 p-4 transition hover:border-blue-200 hover:bg-blue-50">
                    <span class="hk-stat-icon bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19a4 4 0 0 0-8 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 20a3.5 3.5 0 0 0-3-3.46M4 20a3.5 3.5 0 0 1 3-3.46" /></svg>
                    </span>
                    <p class="mt-4 font-semibold text-slate-950">Employees</p>
                    <p class="mt-1 text-sm text-slate-500">Assign cleaners and tasks.</p>
                </a>

                <a href="{{ route('services.index') }}" class="rounded-lg border border-slate-200 p-4 transition hover:border-emerald-200 hover:bg-emerald-50">
                    <span class="hk-stat-icon bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16M6 7.5l1 12h10l1-12M9 7.5V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2.5M9 12h6M10 16h4" /></svg>
                    </span>
                    <p class="mt-4 font-semibold text-slate-950">Services</p>
                    <p class="mt-1 text-sm text-slate-500">Manage the service catalog.</p>
                </a>

                <a href="{{ route('payments.index') }}" class="rounded-lg border border-slate-200 p-4 transition hover:border-amber-200 hover:bg-amber-50">
                    <span class="hk-stat-icon bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z" /><path stroke-linecap="round" d="M4 9h16M8 15h4" /></svg>
                    </span>
                    <p class="mt-4 font-semibold text-slate-950">Payments</p>
                    <p class="mt-1 text-sm text-slate-500">Track paid and pending bills.</p>
                </a>
            </div>
        </div>

        <div class="hk-card border-emerald-200 bg-emerald-50 p-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Flexible Service Pricing</p>
            <div class="mt-5 rounded-lg border border-emerald-200 bg-white p-5">
                <p class="text-sm font-medium text-slate-500">Rules</p>
                <p class="mt-2 text-xl font-bold text-slate-950">Per sqm or fixed price</p>
                <div class="mt-5 rounded-lg bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-950">Normal Cleaning: PHP 55 / sqm</p>
                    <p class="mt-2 text-sm font-semibold text-slate-950">Sofa Cleaning: fixed price</p>
                    <p class="mt-3 text-sm text-slate-500">Totals are calculated per selected service.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
