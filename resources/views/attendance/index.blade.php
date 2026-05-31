@extends('layouts.app')
@section('title', 'Attendance Management')

@section('content')
<div class="space-y-6">
    {{-- Mark Attendance --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Mark Attendance</h3>
        <form method="POST" action="{{ route('attendance.mark') }}" class="grid grid-cols-1 gap-4 md:grid-cols-7 md:items-end">
            @csrf
            @if($employees->count())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select name="employee_id" required class="form-control">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="employee_id" value="{{ auth()->user()->employee?->id }}">
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" required class="form-control">
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="half_day">Half Day</option>
                    <option value="wfh">WFH</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Check In</label>
                <input type="time" name="check_in" class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Check Out</label>
                <input type="time" name="check_out" class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                <input type="text" name="remarks" placeholder="Optional" class="form-control">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 h-10">Mark</button>
        </form>
    </div>

    {{-- Filter + Records --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-wrap items-end gap-3">
                @if(!auth()->user()->hasRole('employee'))
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Search Employee</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Employee name..."
                           class="form-control">
                </div>
                @endif
                <div class="min-w-[160px]">
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}"
                           class="form-control">
                </div>
                <div class="min-w-[160px]">
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="half_day" {{ request('status') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                        <option value="wfh" {{ request('status') === 'wfh' ? 'selected' : '' }}>WFH</option>
                    </select>
                </div>
                <div class="flex min-w-[160px] gap-2">
                    <button type="submit" class="h-10 flex-1 rounded-md bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                    <a href="{{ route('attendance.index') }}" class="flex h-10 flex-1 items-center justify-center rounded-md border border-gray-300 px-3 text-sm font-medium text-gray-600 hover:bg-gray-50">Clear</a>
                </div>
            </form>
        </div>

        <div class="space-y-4 p-4">
            @php
                $statusColors = ['present' => 'bg-green-100 text-green-700', 'absent' => 'bg-red-100 text-red-700', 'half_day' => 'bg-yellow-100 text-yellow-700', 'wfh' => 'bg-blue-100 text-blue-700'];
                $groupedAttendances = $attendances->getCollection()->groupBy(fn($attendance) => $attendance->date->format('Y-m-d'));
            @endphp

            @forelse($groupedAttendances as $date => $records)
                <section class="overflow-hidden rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between bg-gray-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $records->count() }} attendance records</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="rounded-full bg-green-100 px-2 py-1 text-green-700">Present: {{ $records->where('status', 'present')->count() }}</span>
                            <span class="rounded-full bg-red-100 px-2 py-1 text-red-700">Absent: {{ $records->where('status', 'absent')->count() }}</span>
                            <span class="rounded-full bg-yellow-100 px-2 py-1 text-yellow-700">Half Day: {{ $records->where('status', 'half_day')->count() }}</span>
                            <span class="rounded-full bg-blue-100 px-2 py-1 text-blue-700">WFH: {{ $records->where('status', 'wfh')->count() }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Check In</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Check Out</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($records as $att)
                                    @continue(! $att->employee || ! $att->employee->user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                @if($att->employee->photo)
                                                    <img src="{{ Storage::url($att->employee->photo) }}" class="mr-3 h-8 w-8 rounded-full object-cover">
                                                @else
                                                    <div class="mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                                                        {{ strtoupper(substr($att->employee->user->name, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $att->employee->user->name }}</p>
                                                    <p class="text-xs text-gray-400">{{ $att->employee->employee_code }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusColors[$att->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucwords(str_replace('_', ' ', $att->status)) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ $att->check_in ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $att->check_out ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $att->remarks ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 px-6 py-12 text-center text-gray-500">
                    No attendance records found.
                </div>
            @endforelse
        </div>

        @if($attendances->hasPages())
            <div class="p-4 border-t">{{ $attendances->links() }}</div>
        @endif
    </div>
</div>
@endsection
