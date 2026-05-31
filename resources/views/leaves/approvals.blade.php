@extends('layouts.app')
@section('title', 'Leave Approvals')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('leaves.approvals') }}" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[220px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search Employee</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Employee name..." class="form-control-sm w-full">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="form-control-sm w-full">
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Filter</button>
            @if(request()->hasAny(['search', 'status']) && $statusFilter !== 'pending')
                <a href="{{ route('leaves.approvals') }}" class="border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-sm hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-5 border-b">
            <h2 class="text-xl font-semibold text-gray-900">Leave Approvals <span class="text-sm font-normal text-gray-400">({{ $applications->total() }})</span></h2>
            <p class="text-sm text-gray-500">Managers see their department requests. HR and Super Admin see all requests.</p>
        </div>

        <div class="divide-y">
            @forelse($applications as $app)
            <div class="p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <div class="flex items-start space-x-4">
                    @if($app->employee->photo)
                        <img src="{{ Storage::url($app->employee->photo) }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                            {{ strtoupper(substr($app->employee->user->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-medium text-gray-800">{{ $app->employee->user->name }}</p>
                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $app->employee->department->name ?? 'No Department' }}</span>
                            @php $colors = ['pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700']; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $colors[$app->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($app->status) }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">{{ $app->leaveType->code }} - {{ $app->leaveType->name }} | {{ $app->from_date->format('d M') }} to {{ $app->to_date->format('d M Y') }} | {{ $app->total_days }} days</p>
                        <p class="text-sm text-gray-400 mt-1">Reason: {{ $app->reason }}</p>
                        @if($app->status === 'rejected' && $app->rejection_reason)
                            <p class="text-sm text-red-500 mt-1">Rejection: {{ $app->rejection_reason }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($app->status === 'pending')
                        <form method="POST" action="{{ route('leaves.approve', $app) }}">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">Approve</button>
                        </form>
                        <div x-data="{ showReject: false }">
                            <button @click="showReject = !showReject" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">Reject</button>
                            <form method="POST" action="{{ route('leaves.reject', $app) }}" x-show="showReject" x-cloak class="flex flex-wrap items-center gap-2 mt-2">
                                @csrf
                                <input type="text" name="rejection_reason" placeholder="Reason" required class="form-control-sm">
                                <button type="submit" class="bg-red-700 text-white px-3 py-2 rounded-lg text-sm">Confirm</button>
                            </form>
                        </div>
                    @else
                        <span class="px-3 py-1.5 text-xs font-medium rounded-full {{ $colors[$app->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($app->status) }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-gray-500">No leave applications found.</div>
            @endforelse
        </div>
        @if($applications->hasPages())
            <div class="p-4 border-t">{{ $applications->links() }}</div>
        @endif
    </div>
</div>
@endsection
