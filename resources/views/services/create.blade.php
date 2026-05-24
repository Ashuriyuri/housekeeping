<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Service Catalog</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Add Service</h1>
                <p class="mt-2 text-sm text-slate-500">Create a housekeeping service with per-sqm or fixed pricing.</p>
            </div>
            <a href="{{ route('services.index') }}" class="hk-button-secondary w-full sm:w-auto">Back to Services</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <div class="hk-card p-6">
            <form action="{{ route('services.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="hk-label" for="service_name">Service Name</label>
                    <input id="service_name" type="text" name="service_name" class="hk-input @error('service_name') border-rose-500 @enderror" value="{{ old('service_name') }}" required placeholder="Deep Cleaning">
                    @error('service_name') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="hk-label" for="description">Description</label>
                    <textarea id="description" name="description" class="hk-textarea @error('description') border-rose-500 @enderror" rows="4" placeholder="Comprehensive cleaning for rooms, furniture, and surfaces.">{{ old('description') }}</textarea>
                    @error('description') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="hk-label" for="pricing_type">Pricing Type</label>
                        <select id="pricing_type" name="pricing_type" class="hk-select @error('pricing_type') border-rose-500 @enderror" required>
                            <option value="per_sqm" {{ old('pricing_type', 'per_sqm') === 'per_sqm' ? 'selected' : '' }}>Per square meter</option>
                            <option value="fixed" {{ old('pricing_type') === 'fixed' ? 'selected' : '' }}>Fixed price</option>
                        </select>
                        @error('pricing_type') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="hk-label" for="base_price">Price</label>
                        <input id="base_price" type="number" name="base_price" step="0.01" min="0" class="hk-input @error('base_price') border-rose-500 @enderror" value="{{ old('base_price') }}" required placeholder="55.00">
                        <p class="hk-help">Use this as the sqm rate or fixed amount.</p>
                        @error('base_price') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Pricing Examples</p>
                    <p class="mt-2 text-sm font-medium text-emerald-800">Normal Cleaning: PHP 55 / sqm</p>
                    <p class="mt-1 text-sm font-medium text-emerald-800">Sofa Cleaning: fixed service price</p>
                </div>

                <div class="grid grid-cols-1 gap-3 border-t border-slate-200 pt-5 sm:grid-cols-2">
                    <button type="submit" class="hk-button-success w-full">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Create Service
                    </button>
                    <a href="{{ route('services.index') }}" class="hk-button-secondary w-full">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
