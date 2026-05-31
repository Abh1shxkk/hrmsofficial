@extends('layouts.app')
@section('title', 'Calendar & Holidays')

@section('content')
@php
    $typeColors = [
        'national' => 'bg-red-100 text-red-700 border-red-200',
        'regional' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'company' => 'bg-blue-100 text-blue-700 border-blue-200',
    ];
@endphp

<div class="space-y-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Calendar & Holidays</h2>
            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                <span class="rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-red-700">National: <strong>{{ $summary['national'] ?? 0 }}</strong></span>
                <span class="rounded-full border border-yellow-200 bg-yellow-50 px-2.5 py-1 text-yellow-700">Regional: <strong>{{ $summary['regional'] ?? 0 }}</strong></span>
                <span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-blue-700">Company: <strong>{{ $summary['company'] ?? 0 }}</strong></span>
            </div>
        </div>
    </div>

    @if($canManageHolidays)
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ $editHoliday ? 'Edit Holiday' : 'Add Holiday' }}</h3>
            @if($editHoliday)
                <a href="{{ route('holidays.index', request()->except('edit')) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">Cancel edit</a>
            @endif
        </div>
        <form method="POST" action="{{ $editHoliday ? route('holidays.update', $editHoliday) : route('holidays.store') }}" class="grid grid-cols-1 items-start gap-4 lg:grid-cols-4">
            @csrf
            @if($editHoliday)
                @method('PUT')
            @endif
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $editHoliday?->name) }}" required placeholder="Holiday name..." class="form-control">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Date</label>
                @if($editHoliday)
                    <input type="date" value="{{ $editHoliday->date->toDateString() }}" disabled class="form-control bg-gray-100 text-gray-500">
                    <p class="mt-1 text-xs text-gray-500">Date cannot be edited. Remove and add a new holiday if the date is wrong.</p>
                @else
                    <input type="date" name="date" value="{{ old('date') }}" required class="form-control">
                    @error('date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                @endif
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                <select name="type" required class="form-control">
                    <option value="national" {{ old('type', $editHoliday?->type) === 'national' ? 'selected' : '' }}>National</option>
                    <option value="regional" {{ old('type', $editHoliday?->type) === 'regional' ? 'selected' : '' }}>Regional</option>
                    <option value="company" {{ old('type', $editHoliday?->type) === 'company' ? 'selected' : '' }}>Company</option>
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="h-10 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">{{ $editHoliday ? 'Update Holiday' : 'Add Holiday' }}</button>
        </form>
    </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 p-4">
            <form method="GET" action="{{ route('holidays.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Month</label>
                    <select name="month" class="form-control-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $calendarMonth == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Year</label>
                    <select name="year" class="form-control-sm">
                        @for($y = now()->year + 1; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $calendarYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Type</label>
                    <select name="type" class="form-control-sm">
                        <option value="">All Types</option>
                        <option value="national" {{ request('type') === 'national' ? 'selected' : '' }}>National</option>
                        <option value="regional" {{ request('type') === 'regional' ? 'selected' : '' }}>Regional</option>
                        <option value="company" {{ request('type') === 'company' ? 'selected' : '' }}>Company</option>
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Filter</button>
                @if(request()->hasAny(['month', 'year', 'type']))
                    <a href="{{ route('holidays.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </div>

        <div class="p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">{{ DateTime::createFromFormat('!m', $calendarMonth)->format('F') }} {{ $calendarYear }}</h3>
                <p class="text-xs text-gray-500">Holidays are excluded from leave and payroll working-day calculations.</p>
            </div>
            <div class="grid grid-cols-7 border-l border-t border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500">
                @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                    <div class="border-b border-r border-gray-200 bg-gray-50 px-2 py-2">{{ $day }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7 border-l border-gray-200">
                @foreach($calendarWeeks as $week)
                    @foreach($week as $day)
                        <div class="min-h-[104px] border-b border-r border-gray-200 p-2 {{ $day['in_month'] ? 'bg-white' : 'bg-gray-50 text-gray-400' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold {{ $day['is_today'] ? 'rounded-full bg-blue-600 px-2 py-0.5 text-white' : 'text-gray-700' }}">{{ $day['date']->day }}</span>
                            </div>
                            <div class="mt-2 space-y-1">
                                @foreach($day['holidays'] as $holiday)
                                    <div class="rounded-md border px-2 py-1 {{ $typeColors[$holiday->type] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                        <p class="truncate font-medium">{{ $holiday->name }}</p>
                                        <p class="text-[10px] capitalize">{{ $holiday->type }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Holiday Records <span class="font-normal text-gray-400">({{ $holidays->count() }})</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Day</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                        @if($canManageHolidays)
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($holidays as $holiday)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $holiday->name }}</td>
                        <td class="px-4 py-3">{{ $holiday->date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $holiday->date->format('l') }}</td>
                        <td class="px-4 py-3"><span class="rounded-full border px-2 py-1 text-xs {{ $typeColors[$holiday->type] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">{{ ucfirst($holiday->type) }}</span></td>
                        @if($canManageHolidays)
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('holidays.index', array_merge(request()->query(), ['edit' => $holiday->id])) }}" class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-800">Edit</a>
                            <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" class="inline" onsubmit="return confirm('Remove holiday {{ $holiday->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Remove</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="{{ $canManageHolidays ? 5 : 4 }}" class="px-6 py-12 text-center text-gray-500">No holidays found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
