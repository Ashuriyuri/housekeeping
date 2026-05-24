<div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/40 print:hidden lg:hidden" @click="sidebarOpen = false"></div>

<aside
    class="fixed inset-y-0 left-0 z-50 flex w-72 transform flex-col border-r border-slate-200 bg-white transition duration-200 print:hidden lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    <div class="flex h-16 items-center justify-between border-b border-slate-200 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5 12 4l9 7.5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 10.5V20h14v-9.5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20v-6h6v6" />
                </svg>
            </span>
            <span>
                <span class="block text-base font-bold text-slate-950">Housekeeping</span>
                <span class="block text-xs font-medium text-slate-500">Admin console</span>
            </span>
        </a>

        <button type="button" class="hk-icon-button lg:hidden" @click="sidebarOpen = false" aria-label="Close sidebar">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-5">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition">
            <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 13h7V4H4v9Zm0 7h7v-4H4v4Zm9 0h7v-9h-7v9Zm0-12h7V4h-7v4Z" />
            </svg>
            Dashboard
        </a>

        <a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition">
            <svg class="h-5 w-5 {{ request()->routeIs('appointments.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3M17 3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v11.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
            </svg>
            Appointments
        </a>

        <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition">
            <svg class="h-5 w-5 {{ request()->routeIs('employees.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 19a4 4 0 0 0-8 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 20a3.5 3.5 0 0 0-3-3.46M4 20a3.5 3.5 0 0 1 3-3.46" />
            </svg>
            Employees
        </a>

        <a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition">
            <svg class="h-5 w-5 {{ request()->routeIs('services.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16M6 7.5l1 12h10l1-12M9 7.5V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2.5M9 12h6M10 16h4" />
            </svg>
            Services
        </a>

        <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition">
            <svg class="h-5 w-5 {{ request()->routeIs('payments.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z" />
                <path stroke-linecap="round" d="M4 9h16M8 15h4" />
            </svg>
            Payments
        </a>
    </nav>

    <div class="border-t border-slate-200 p-4">
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Pricing</p>
            <div class="mt-2 flex items-end justify-between gap-3">
                <p class="text-2xl font-bold text-emerald-700">Flexible</p>
                <p class="pb-1 text-sm font-medium text-emerald-700">per service</p>
            </div>
        </div>
    </div>
</aside>
