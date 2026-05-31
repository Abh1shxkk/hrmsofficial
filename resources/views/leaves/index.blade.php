@extends('layouts.app')
@section('title', 'My Leave Applications')

@section('content')
<div class="space-y-4">
    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('leaves.my') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="form-control-sm">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Leave Type</label>
                <select name="leave_type_id" class="form-control-sm">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Filter</button>
            @if(request()->hasAny(['status', 'leave_type_id']))
                <a href="{{ route('leaves.my') }}" class="border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-sm hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-5 border-b flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">My Leave Applications</h2>
                <p class="text-sm text-gray-500">Track pending, approved and rejected leave requests.</p>
            </div>
            <a href="{{ route('leaves.apply') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Apply Leave</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Type</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">From</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">To</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Days</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $app->leaveType->code }} - {{ $app->leaveType->name }}</td>
                        <td class="px-6 py-4">{{ $app->from_date->format('d M Y') }}</td>
                        <td class="px-6 py-4">{{ $app->to_date->format('d M Y') }}</td>
                        <td class="px-6 py-4">{{ $app->total_days }}</td>
                        <td class="px-6 py-4">
                            @php $colors = ['pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700']; @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $colors[$app->status] }}">{{ ucfirst($app->status) }}</span>
                        </td>
                        <td class="px-6 py-4 max-w-xs truncate">{{ $app->reason }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No leave applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
            <div class="p-4 border-t">{{ $applications->links() }}</div>
        @endif
    </div>
</div>
@endsection
