@extends('layouts.app')
@section('title', 'Attendance Report')

@section('content')
@php
    $monthName = DateTime::createFromFormat('!m', (int) $month)->format('F');
    $statusColors = [
        'present' => 'bg-green-100 text-green-700',
        'absent' => 'bg-red-100 text-red-700',
        'half_day' => 'bg-yellow-100 text-yellow-700',
        'wfh' => 'bg-blue-100 text-blue-700',
    ];
@endphp

<div class="space-y-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Attendance Report</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $monthName }} {{ $year }} attendance summary and detailed records.</p>
        </div>
        @if(auth()->user()->hasAnyRole(['super_admin', 'hr_admin']))
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('attendance.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Export CSV</a>
                <a href="{{ route('attendance.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Export PDF</a>
            </div>
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('attendance.report') }}" class="flex flex-wrap items-end gap-3 p-4">
            <div class="min-w-[150px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Month</label>
                <select name="month" class="form-control">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="min-w-[120px] flex-1">
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Year</label>
                <input type="number" name="year" value="{{ $year }}" min="2000" max="2100" class="form-control">
            </div>
            @if(!auth()->user()->hasRole('employee'))
                <div class="min-w-[180px] flex-1">
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ (string) $departmentId === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[180px] flex-1">
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Employee</label>
                    <select name="employee_id" class="form-control">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) $employeeId === (string) $employee->id ? 'selected' : '' }}>{{ $employee->user->name }} ({{ $employee->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex min-w-[160px] shrink-0 gap-2">
                <button type="submit" class="h-10 flex-1 rounded-md bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                <a href="{{ route('attendance.report') }}" class="flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 px-3 text-sm font-medium text-gray-600 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach([
            'Total' => ['value' => $totals['total'], 'class' => 'text-gray-900'],
            'Present' => ['value' => $totals['present'], 'class' => 'text-green-600'],
            'Absent' => ['value' => $totals['absent'], 'class' => 'text-red-600'],
            'Half Day' => ['value' => $totals['half_day'], 'class' => 'text-yellow-600'],
            'WFH' => ['value' => $totals['wfh'], 'class' => 'text-blue-600'],
        ] as $label => $item)
            <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-medium text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-xl font-bold {{ $item['class'] }}">{{ $item['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">Monthly Report Per Employee</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Employee</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">P</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">A</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">HD</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">WFH</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Eff. Present</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($employeeSummary as $row)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $row['employee']->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $row['employee']->employee_code }}</p>
                                </td>
                                <td class="px-4 py-3 text-center text-green-600">{{ $row['present'] }}</td>
                                <td class="px-4 py-3 text-center text-red-600">{{ $row['absent'] }}</td>
                                <td class="px-4 py-3 text-center text-yellow-600">{{ $row['half_day'] }}</td>
                                <td class="px-4 py-3 text-center text-blue-600">{{ $row['wfh'] }}</td>
                                <td class="px-4 py-3 text-center font-medium">{{ $row['total'] }}</td>
                                <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ $row['effective_present'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No employee summary available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">Department-wise Summary</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Department</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">P</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">A</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">HD</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">WFH</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Eff. Present</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($departmentSummary as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row['department']->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-green-600">{{ $row['present'] }}</td>
                                <td class="px-4 py-3 text-center text-red-600">{{ $row['absent'] }}</td>
                                <td class="px-4 py-3 text-center text-yellow-600">{{ $row['half_day'] }}</td>
                                <td class="px-4 py-3 text-center text-blue-600">{{ $row['wfh'] }}</td>
                                <td class="px-4 py-3 text-center font-medium">{{ $row['total'] }}</td>
                                <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ $row['effective_present'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No department summary available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Attendance Records <span class="font-normal text-gray-400">({{ $attendances->count() }})</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Check In</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Check Out</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $att->employee->user->name }}</td>
                            <td class="px-4 py-3">{{ $att->employee->department->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $att->date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusColors[$att->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucwords(str_replace('_', ' ', $att->status)) }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $att->check_in ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $att->check_out ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $att->remarks ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No records for this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
