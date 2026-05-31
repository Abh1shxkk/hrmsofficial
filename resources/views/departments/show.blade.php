@extends('layouts.app')
@section('title', 'Department Management Report')

@section('content')
<div class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Department Management: {{ $department->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $department->description ?: 'No description added.' }}</p>
                <p class="mt-2 text-sm text-gray-600">Manager: <span class="font-medium">{{ $department->manager?->user?->name ?? 'Not assigned' }}</span></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('departments.edit', $department) }}" class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-600">Edit</a>
                <a href="{{ route('departments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-7">
        @foreach([
            'Total' => ['value' => $report['total'], 'class' => 'text-gray-900'],
            'Active' => ['value' => $report['active'], 'class' => 'text-green-600'],
            'Inactive' => ['value' => $report['inactive'], 'class' => 'text-yellow-600'],
            'Terminated' => ['value' => $report['terminated'], 'class' => 'text-red-600'],
            'Full Time' => ['value' => $report['full_time'], 'class' => 'text-blue-600'],
            'Part Time' => ['value' => $report['part_time'], 'class' => 'text-purple-600'],
            'Contract' => ['value' => $report['contract'], 'class' => 'text-slate-600'],
        ] as $label => $item)
            <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-medium text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-xl font-bold {{ $item['class'] }}">{{ $item['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('departments.show', $department) }}" class="flex flex-wrap items-end gap-3 p-4">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, code, email..." class="form-control">
            </div>
            <div class="min-w-[150px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    @foreach(['active', 'inactive', 'terminated'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Type</label>
                <select name="employment_type" class="form-control">
                    <option value="">All Types</option>
                    @foreach(['full_time', 'part_time', 'contract'] as $type)
                        <option value="{{ $type }}" {{ request('employment_type') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex min-w-[160px] shrink-0 gap-2">
                <button type="submit" class="h-10 flex-1 rounded-md bg-blue-600 px-3 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                <a href="{{ route('departments.show', $department) }}" class="flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 px-3 text-sm font-medium text-gray-600 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Department Management Employees <span class="font-normal text-gray-400">({{ $employees->total() }})</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Designation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $employee->user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $employee->user->email }}</p>
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-600">{{ $employee->employee_code }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $employee->designation }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ ucwords(str_replace('_', ' ', $employee->employment_type)) }}</td>
                            <td class="px-4 py-3">
                                @php $colors = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-yellow-100 text-yellow-700', 'terminated' => 'bg-red-100 text-red-700']; @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $colors[$employee->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($employee->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('employees.show', $employee) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No employees found in this department.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="border-t border-gray-100 p-4">{{ $employees->links() }}</div>
        @endif
    </div>
</div>
@endsection
