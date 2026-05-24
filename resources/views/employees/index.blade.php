<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Team Management</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Employees</h1>
                <p class="mt-2 text-sm text-slate-500">Manage employee availability and assignment readiness.</p>
            </div>
            <a href="{{ route('employees.create') }}" class="hk-button-primary w-full sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Add Employee
            </a>
        </div>
    </x-slot>

    @if($employees->isEmpty())
        <div class="hk-empty">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19a4 4 0 0 0-8 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 20a3.5 3.5 0 0 0-3-3.46M4 20a3.5 3.5 0 0 1 3-3.46" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-slate-950">No employees yet</h2>
            <a href="{{ route('employees.create') }}" class="hk-button-primary mt-5">Add Employee</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($employees as $employee)
                <article class="hk-card hk-card-hover p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-base font-bold text-blue-700">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="truncate font-bold text-slate-950">{{ $employee->name }}</h2>
                                    <p class="mt-1 text-sm text-slate-500">{{ $employee->position }}</p>
                                </div>
                                @if($employee->status === 'Active')
                                    <span class="hk-badge-active">Active</span>
                                @else
                                    <span class="hk-badge-inactive">Inactive</span>
                                @endif
                            </div>
                            <p class="mt-4 text-sm font-medium text-slate-600">{{ $employee->phone }}</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-3 gap-2 border-t border-slate-100 pt-4">
                        <a href="{{ route('employees.show', $employee) }}" class="hk-button-secondary px-3">View</a>
                        <a href="{{ route('employees.edit', $employee) }}" class="hk-button-secondary px-3">Edit</a>
                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Delete this employee?');">
                            @csrf
                            @method('DELETE')
                            <button class="hk-button-danger w-full px-3">Delete</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
