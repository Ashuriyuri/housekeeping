<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Service Catalog</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Services</h1>
                <p class="mt-2 text-sm text-slate-500">Manage per-square-meter and fixed-price services.</p>
            </div>
            <a href="{{ route('services.create') }}" class="hk-button-primary w-full sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Add Service
            </a>
        </div>
    </x-slot>

    @if($services->isEmpty())
        <div class="hk-empty">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16M6 7.5l1 12h10l1-12M9 7.5V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2.5M9 12h6M10 16h4" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-slate-950">No services yet</h2>
            <a href="{{ route('services.create') }}" class="hk-button-primary mt-5">Create Service</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($services as $service)
                <article class="hk-card hk-card-hover overflow-hidden">
                    <div class="h-1.5 bg-indigo-600"></div>
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-bold text-slate-950">{{ $service->service_name }}</h2>
                                <p class="mt-2 min-h-12 text-sm leading-6 text-slate-500">{{ Str::limit($service->description, 92) ?: 'Housekeeping service' }}</p>
                            </div>
                            <span class="hk-stat-icon bg-indigo-50 text-indigo-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16M6 7.5l1 12h10l1-12M9 7.5V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2.5M9 12h6M10 16h4" />
                                </svg>
                            </span>
                        </div>

                        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $service->isPricedPerSquareMeter() ? 'Price per sqm' : 'Fixed price' }}</p>
                            <p class="mt-1 text-3xl font-extrabold text-emerald-600">PHP {{ number_format($service->base_price, 2) }}</p>
                            <p class="mt-1 text-xs font-semibold text-emerald-700">{{ $service->isPricedPerSquareMeter() ? 'Calculated by service area' : 'Calculated by quantity' }}</p>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <a href="{{ route('appointments.create') }}" class="hk-button-primary sm:col-span-3">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                </svg>
                                Add to Appointment
                            </a>
                            <a href="{{ route('services.show', $service) }}" class="hk-button-secondary px-3">View</a>
                            <a href="{{ route('services.edit', $service) }}" class="hk-button-secondary px-3">Edit</a>
                            <form action="{{ route('services.destroy', $service) }}" method="POST" onsubmit="return confirm('Delete this service?');">
                                @csrf
                                @method('DELETE')
                                <button class="hk-button-danger w-full px-3">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
