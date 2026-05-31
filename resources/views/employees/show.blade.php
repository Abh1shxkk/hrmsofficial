@extends('layouts.app')
@section('title', 'Employee Details')

@section('content')
@if($employee)
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                @if($employee->photo)
                    <img src="{{ Storage::url($employee->photo) }}" class="w-20 h-20 rounded-full object-cover border-4 border-blue-100">
                @else
                    <div class="w-20 h-20 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-2xl font-bold">
                        {{ strtoupper(substr($employee->user->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $employee->user->name }}</h2>
                    <p class="text-gray-500">{{ $employee->designation }} -- {{ $employee->department->name }}</p>
                    <p class="text-sm text-gray-400 font-mono">{{ $employee->employee_code }}</p>
                    @php $sc = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-yellow-100 text-yellow-700', 'terminated' => 'bg-red-100 text-red-700']; @endphp
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full {{ $sc[$employee->status] ?? '' }}">{{ ucfirst($employee->status) }}</span>
                        @if($employee->user->is_active)
                            <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Login Active</span>
                        @else
                            <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Login Blocked</span>
                        @endif
                    </div>
                </div>
            </div>
            @if(auth()->user()->hasAnyRole(['super_admin', 'hr_admin']))
                <div class="flex items-center gap-2">
                    <a href="{{ route('employees.edit', $employee) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-600">Edit</a>
                    @if($employee->user->is_active)
                        <form method="POST" action="{{ route('employees.deactivate', $employee) }}" onsubmit="return confirm('Deactivate {{ $employee->user->name }}? This will block login access.')">
                            @csrf @method('PATCH')
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">Deactivate</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('employees.activate', $employee) }}" onsubmit="return confirm('Reactivate {{ $employee->user->name }}? This will restore login access.')">
                            @csrf @method('PATCH')
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">Reactivate</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-3">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Personal Info</h3>
            <p><span class="text-gray-500 inline-block w-24">Email:</span> {{ $employee->user->email }}</p>
            <p><span class="text-gray-500 inline-block w-24">Phone:</span> {{ $employee->phone }}</p>
            <p><span class="text-gray-500 inline-block w-24">DOB:</span> {{ $employee->date_of_birth->format('d M Y') }}</p>
            <p><span class="text-gray-500 inline-block w-24">Address:</span> {{ $employee->address }}</p>
            <p><span class="text-gray-500 inline-block w-24">Aadhar:</span> {{ $employee->aadhar_number ?? 'N/A' }}</p>
            <p><span class="text-gray-500 inline-block w-24">PAN:</span> {{ $employee->pan_number ?? 'N/A' }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-3">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Employment Info</h3>
            <p><span class="text-gray-500 inline-block w-28">Joining Date:</span> {{ $employee->date_of_joining->format('d M Y') }}</p>
            <p><span class="text-gray-500 inline-block w-28">Type:</span> {{ ucwords(str_replace('_', ' ', $employee->employment_type)) }}</p>
            <p><span class="text-gray-500 inline-block w-28">Department:</span> {{ $employee->department->name }}</p>
            <p><span class="text-gray-500 inline-block w-28">Designation:</span> {{ $employee->designation }}</p>
        </div>
    </div>

    @include('employees.partials.documents-list')

    {{-- Salary Management --}}
    @if($employee->salaryStructures && $employee->salaryStructures->where('is_active', true)->count())
        @php $structure = $employee->salaryStructures->where('is_active', true)->first(); @endphp
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Active Salary Management</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                <div><p class="text-gray-400">Basic</p><p class="font-medium">{{ number_format($structure->basic, 2) }}</p></div>
                <div><p class="text-gray-400">HRA</p><p class="font-medium">{{ number_format($structure->hra, 2) }}</p></div>
                <div><p class="text-gray-400">Transport</p><p class="font-medium">{{ number_format($structure->transport_allowance ?? 0, 2) }}</p></div>
                <div><p class="text-gray-400">Other</p><p class="font-medium">{{ number_format($structure->other_allowances ?? 0, 2) }}</p></div>
                <div><p class="text-gray-400">Effective From</p><p class="font-medium">{{ $structure->effective_from->format('d M Y') }}</p></div>
            </div>
        </div>
    @endif

    {{-- Leave Balances --}}
    @if($employee->leaveBalances && $employee->leaveBalances->count())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Leave Balances ({{ now()->year }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($employee->leaveBalances->where('year', now()->year) as $bal)
                <div class="border rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700">{{ $bal->leaveType->code }} - {{ $bal->leaveType->name }}</p>
                    <div class="mt-2 flex justify-between text-xs text-gray-500">
                        <span>Allocated: {{ $bal->allocated }}</span>
                        <span>Carry: {{ $bal->carried_forward ?? '0.00' }}</span>
                        <span>Used: {{ $bal->used }}</span>
                        <span class="font-bold {{ $bal->balance <= 2 ? 'text-red-600' : 'text-green-600' }}">Balance: {{ $bal->balance }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide">Leave History</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Dates</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Days</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($employee->leaveApplications->sortByDesc('created_at') as $leave)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $leave->leaveType->code }} - {{ $leave->leaveType->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $leave->from_date->format('d M Y') }} to {{ $leave->to_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $leave->total_days }}</td>
                            <td class="px-4 py-3">
                                @php $colors = ['pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700']; @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $colors[$leave->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($leave->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 max-w-xs truncate">
                                {{ $leave->status === 'rejected' && $leave->rejection_reason ? $leave->rejection_reason : $leave->reason }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No leave history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">Employee profile not found.</div>
@endif
@endsection
