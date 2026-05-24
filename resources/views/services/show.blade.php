<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Service Detail</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $service->service_name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Service information and fixed sqm pricing.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('services.edit', $service) }}" class="hk-button-secondary w-full sm:w-auto">Edit</a>
                <form action="{{ route('services.destroy', $service) }}" method="POST" onsubmit="return confirm('Delete this service?');">
                    @csrf
                    @method('DELETE')
                    <button class="hk-button-danger w-full sm:w-auto">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="hk-card p-6 lg:col-span-2">
            <h2 class="hk-section-title">Description</h2>
            <p class="mt-4 leading-7 text-slate-700">{{ $service->description ?: 'No description provided.' }}</p>

            <div class="mt-8 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Pricing Rule</p>
                <p class="mt-2 text-xl font-bold text-slate-950">{{ $service->isPricedPerSquareMeter() ? 'Service area x rate' : 'Quantity x fixed price' }}</p>
                <p class="mt-3 text-4xl font-extrabold text-emerald-600">{{ $service->price_label }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <a href="{{ route('appointments.create') }}" class="hk-button-primary w-full">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Add to Appointment
            </a>
            <a href="{{ route('services.index') }}" class="hk-button-secondary w-full">Back to Services</a>
        </div>
    </div>
</x-app-layout>
