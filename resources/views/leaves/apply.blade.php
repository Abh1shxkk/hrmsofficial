@extends('layouts.app')
@section('title', 'Apply Leave')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Apply for Leave</h2>
        <p class="text-sm text-gray-500">Submitted requests stay pending until Manager, HR or Super Admin approval.</p>
    </div>

    <form method="POST" action="{{ route('leaves.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type *</label>
            <select name="leave_type_id" required class="form-control">
                <option value="">Select type</option>
                @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }} ({{ $lt->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date *</label>
                <input type="date" name="from_date" value="{{ old('from_date') }}" required class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date *</label>
                <input type="date" name="to_date" value="{{ old('to_date') }}" required class="form-control">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
            <textarea name="reason" rows="3" required class="form-control">{{ old('reason') }}</textarea>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('leaves.my') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Submit Request</button>
        </div>
    </form>
</div>
@endsection
