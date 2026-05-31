@extends('layouts.app')
@section('title', 'Leave Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-5 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Leave Management - {{ now()->year }}</h2>
            <p class="text-sm text-gray-500">CL, SL and EL balance tracking for employees.</p>
        </div>
        <a href="{{ route('leaves.my') }}" class="self-start sm:self-auto border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-50">Leave History</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500">Employee</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500">Leave Type</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500">Allocated</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500">Carry Forward</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500">Used</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500">Pending</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($balances as $empBalances)
                    @foreach($empBalances as $bal)
                    <tr class="hover:bg-gray-50">
                        @if($loop->first)
                            <td class="px-6 py-4 align-top" rowspan="{{ $empBalances->count() }}">
                                <div class="flex items-center">
                                    @if($bal->employee->photo)
                                        <img src="{{ Storage::url($bal->employee->photo) }}" class="w-8 h-8 rounded-full mr-3 object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center mr-3 text-xs font-bold">
                                            {{ strtoupper(substr($bal->employee->user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $bal->employee->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $bal->employee->employee_code }}</p>
                                    </div>
                                </div>
                            </td>
                        @endif
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-800">{{ $bal->leaveType->code }}</span>
                            <span class="text-gray-500">- {{ $bal->leaveType->name }}</span>
                        </td>
                        <td class="px-6 py-4">{{ $bal->allocated }}</td>
                        <td class="px-6 py-4">{{ $bal->carried_forward ?? '0.00' }}</td>
                        <td class="px-6 py-4">{{ $bal->used }}</td>
                        <td class="px-6 py-4">{{ $bal->pending }}</td>
                        <td class="px-6 py-4 font-bold {{ $bal->balance <= 2 ? 'text-red-600' : 'text-green-600' }}">{{ $bal->balance }}</td>
                    </tr>
                    @endforeach
                @empty
                <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No leave balances found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
