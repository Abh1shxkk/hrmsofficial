@extends('layouts.app')
@section('title', 'Payroll Management')

@section('content')
<div class="space-y-4">
    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('payroll.index') }}" class="flex flex-wrap items-end gap-3">
            @if(!auth()->user()->hasRole('employee'))
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search Employee</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or code..."
                       class="form-control">
            </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Month</label>
                <select name="month" class="form-control-sm">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Year</label>
                <select name="year" class="form-control-sm">
                    <option value="">All Years</option>
                    @for($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="form-control-sm">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processed</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Filter</button>
            @if(request()->hasAny(['search', 'month', 'year', 'status']))
                <a href="{{ route('payroll.index') }}" class="border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-sm hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Payroll Management <span class="text-sm font-normal text-gray-400">({{ $slips->total() }})</span></h2>
            @if(auth()->user()->hasAnyRole(['super_admin', 'hr_admin']))
                <a href="{{ route('payroll.process') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                    Process Payroll
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Employee</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Period</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500">Gross</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500">Deductions</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500">Net Salary</th>
                        <th class="px-6 py-3 text-center font-medium text-gray-500">Status</th>
                        <th class="px-6 py-3 text-center font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($slips as $slip)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($slip->employee->photo)
                                    <img src="{{ Storage::url($slip->employee->photo) }}" class="w-8 h-8 rounded-full mr-3 object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center mr-3 text-xs font-bold">
                                        {{ strtoupper(substr($slip->employee->user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-800">{{ $slip->employee->user->name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $slip->employee->employee_code }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ DateTime::createFromFormat('!m', $slip->month)->format('F') }} {{ $slip->year }}</td>
                        <td class="px-6 py-4 text-right">{{ number_format($slip->gross_salary, 2) }}</td>
                        <td class="px-6 py-4 text-right text-red-600">{{ number_format($slip->total_deductions, 2) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-green-700">{{ number_format($slip->net_salary, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusClasses = [
                                    'draft' => 'bg-gray-100 text-gray-700',
                                    'processed' => 'bg-blue-100 text-blue-700',
                                    'paid' => 'bg-green-100 text-green-700',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $statusClasses[$slip->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($slip->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <a href="{{ route('salary-slip.show', $slip) }}"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                                <a href="{{ route('salary-slip.download', $slip) }}"
                                   class="text-green-600 hover:text-green-800 text-sm font-medium">PDF</a>
                                @if(auth()->user()->hasAnyRole(['super_admin', 'hr_admin']))
                                    @if($slip->status === 'processed')
                                        <form method="POST" action="{{ route('salary-slip.mark-paid', $slip) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-800 text-sm font-medium">Paid</button>
                                        </form>
                                    @endif
                                    @if($slip->status !== 'paid')
                                        <form method="POST" action="{{ route('salary-slip.destroy', $slip) }}" class="inline"
                                              onsubmit="return confirm('Delete this salary slip? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Del</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No salary slips found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($slips->hasPages())
            <div class="p-4 border-t">{{ $slips->links() }}</div>
        @endif
    </div>
</div>
@endsection
