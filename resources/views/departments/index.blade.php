@extends('layouts.app')
@section('title', 'Department Management')

@section('content')
<div class="space-y-3">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Department Management</h2>
            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                <span class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-gray-600">Departments: <strong class="text-gray-900">{{ $summary['total'] }}</strong></span>
                <span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-blue-700">Employees: <strong>{{ $summary['employees'] }}</strong></span>
                <span class="rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-green-700">Active: <strong>{{ $summary['active'] }}</strong></span>
            </div>
        </div>
        <a href="{{ route('departments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Department</a>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('departments.index') }}" class="flex flex-wrap items-end gap-3 p-4">
            <div class="min-w-[240px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Department name or description..." class="form-control">
            </div>
            <div class="flex min-w-[160px] shrink-0 gap-2">
                <button type="submit" class="h-10 flex-1 rounded-md bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700">Search</button>
                <a href="{{ route('departments.index') }}" class="flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 px-3 text-sm font-medium text-gray-600 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-900">Departments <span class="font-normal text-gray-400">({{ $departments->total() }})</span></h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Manager</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Employees</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Active</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $dept->name }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ Str::limit($dept->description, 80) ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $dept->manager?->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $dept->employees_count }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">{{ $dept->active_employees_count }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-x-3 gap-y-1">
                                    <a href="{{ route('departments.show', $dept) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Report</a>
                                    <a href="{{ route('departments.edit', $dept) }}" class="text-sm font-medium text-yellow-600 hover:text-yellow-800">Edit</a>
                                    @if($dept->employees_count === 0)
                                        <form method="POST" action="{{ route('departments.destroy', $dept) }}" onsubmit="return confirm('Delete department {{ $dept->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">Has employees</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No departments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($departments->hasPages())
            <div class="border-t border-gray-100 p-4">{{ $departments->links() }}</div>
        @endif
    </div>
</div>
@endsection
