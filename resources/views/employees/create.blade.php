<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Team Management</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Add Employee</h1>
                <p class="mt-2 text-sm text-slate-500">Create an employee profile for appointment assignment.</p>
            </div>
            <a href="{{ route('employees.index') }}" class="hk-button-secondary w-full sm:w-auto">Back to Employees</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <div class="hk-card p-6">
            <form action="{{ route('employees.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="hk-label" for="name">Full Name</label>
                    <input id="name" type="text" name="name" class="hk-input @error('name') border-rose-500 @enderror" value="{{ old('name') }}" required placeholder="Maria Cruz">
                    @error('name') <span class="hk-error">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="hk-label" for="phone">Phone Number</label>
                        <input id="phone" type="text" name="phone" class="hk-input @error('phone') border-rose-500 @enderror" value="{{ old('phone') }}" required placeholder="09123456789">
                        @error('phone') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="hk-label" for="position">Position</label>
                        <input id="position" type="text" name="position" class="hk-input @error('position') border-rose-500 @enderror" value="{{ old('position') }}" required placeholder="Cleaner">
                        @error('position') <span class="hk-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="hk-label" for="status">Status</label>
                    <select id="status" name="status" class="hk-select" required>
                        <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-3 border-t border-slate-200 pt-5 sm:grid-cols-2">
                    <button type="submit" class="hk-button-success w-full">Add Employee</button>
                    <a href="{{ route('employees.index') }}" class="hk-button-secondary w-full">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
