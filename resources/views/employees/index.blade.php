@extends('layouts.app')
@section('title', 'Employee Management')

@section('content')
<div class="space-y-3">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Employee Management</h2>
            <p class="mt-1 text-sm text-gray-500">Manage employee records, departments, documents and account status.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Employee</a>
    </div>

    {{-- Search & Filters --}}
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('employees.index') }}" class="flex flex-wrap items-end gap-3 p-4">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, code, email..."
                       class="form-control">
            </div>
            <div class="min-w-[180px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Department</label>
                <select name="department" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>
            <div class="min-w-[150px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Type</label>
                <select name="employment_type" class="form-control">
                    <option value="">All Types</option>
                    <option value="full_time" {{ request('employment_type') === 'full_time' ? 'selected' : '' }}>Full Time</option>
                    <option value="part_time" {{ request('employment_type') === 'part_time' ? 'selected' : '' }}>Part Time</option>
                    <option value="contract" {{ request('employment_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                </select>
            </div>
            <div class="flex min-w-[160px] shrink-0 gap-2">
                <button type="submit" class="h-10 flex-1 rounded-md bg-blue-600 px-3 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                <a href="{{ route('employees.index') }}" class="flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 px-3 text-sm font-medium text-gray-600 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-900">Employees <span class="font-normal text-gray-400">({{ $employees->total() }})</span></h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Designation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Docs</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Account</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                @if($emp->photo)
                                    <img src="{{ Storage::url($emp->photo) }}" class="w-9 h-9 rounded-full mr-3 object-cover">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center mr-3 text-xs font-bold">
                                        {{ strtoupper(substr($emp->user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-800">{{ $emp->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $emp->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $emp->employee_code }}</td>
                        <td class="px-4 py-3">{{ $emp->department->name }}</td>
                        <td class="px-4 py-3">{{ $emp->designation }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $emp->employment_type)) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">{{ $emp->documents_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $sc = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-yellow-100 text-yellow-700', 'terminated' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $sc[$emp->status] ?? '' }}">{{ ucfirst($emp->status) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($emp->user->is_active)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Login Active</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Login Blocked</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-end gap-x-3 gap-y-1">
                                <a href="{{ route('employees.show', $emp) }}" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                <a href="{{ route('employees.edit', $emp) }}" class="text-yellow-600 hover:text-yellow-800 text-sm">Edit</a>
                                @if($emp->user->is_active)
                                    <form method="POST" action="{{ route('employees.deactivate', $emp) }}" class="inline"
                                          onsubmit="return confirm('Deactivate {{ $emp->user->name }}? This will block login access.')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Deactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('employees.activate', $emp) }}" class="inline"
                                          onsubmit="return confirm('Reactivate {{ $emp->user->name }}? This will restore login access.')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-green-600 hover:text-green-800 text-sm">Reactivate</button>
                                    </form>
                                @endif
                                @if(auth()->user()->hasRole('super_admin'))
                                    <form method="POST" action="{{ route('employees.destroy', $emp) }}" class="inline"
                                          onsubmit="return confirm('Permanently delete {{ $emp->user->name }} and all linked employee records?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-700 hover:text-red-900 text-sm">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-6 py-12 text-center text-gray-500">No employees found matching your criteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="p-4 border-t">{{ $employees->links() }}</div>
        @endif
    </div>
</div>
@endsection
