@extends('layouts.app')
@section('title', 'Add Employee')

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-sm p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Add New Employee</h2>
        <p class="mt-1 text-sm text-gray-500">{{ $linkedUser ? 'Complete HR profile for an existing login account.' : 'Create login, personal profile, job details and supporting documents.' }}</p>
    </div>

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($linkedUser)
            <input type="hidden" name="existing_user_id" value="{{ $linkedUser->id }}">
        @endif

        <div class="rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700">Personal & Login Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $linkedUser?->name) }}" required class="form-control" @readonly($linkedUser)>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email', $linkedUser?->email) }}" required class="form-control" @readonly($linkedUser)>
            </div>
            @unless($linkedUser)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                <input type="password" name="password" required class="form-control">
            </div>
            @endunless
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee Code *</label>
                <input type="text" name="employee_code" value="{{ old('employee_code') }}" placeholder="EMP001" required class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                <select name="department_id" required class="form-control">
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Designation *</label>
                <input type="text" name="designation" value="{{ old('designation') }}" required class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Joining *</label>
                <input type="date" name="date_of_joining" value="{{ old('date_of_joining') }}" required class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                <input type="file" name="photo" accept="image/*" class="form-file-control">
            </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700">Job Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type *</label>
                <select name="employment_type" required class="form-control">
                    <option value="full_time" {{ old('employment_type') === 'full_time' ? 'selected' : '' }}>Full Time</option>
                    <option value="part_time" {{ old('employment_type') === 'part_time' ? 'selected' : '' }}>Part Time</option>
                    <option value="contract" {{ old('employment_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                @if($linkedUser)
                    <input type="text" value="{{ ucwords(str_replace('_', ' ', $linkedUser->role)) }}" disabled class="form-control bg-gray-100 text-gray-500">
                    <p class="mt-1 text-xs text-gray-500">Role is managed from User Management.</p>
                @else
                    <select name="role" required class="form-control">
                        <option value="employee">Employee</option>
                        <option value="manager">Manager</option>
                        <option value="hr_admin">HR Admin</option>
                    </select>
                @endif
            </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 p-4">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700">Identity Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Aadhar Number</label>
                <input type="text" name="aadhar_number" value="{{ old('aadhar_number') }}" class="form-control">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PAN Number</label>
                <input type="text" name="pan_number" value="{{ old('pan_number') }}" class="form-control">
            </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
            <textarea name="address" rows="3" required class="form-control">{{ old('address') }}</textarea>
        </div>

        @include('employees.partials.document-fields')

        <div class="flex justify-end space-x-3">
            <a href="{{ route('employees.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create Employee</button>
        </div>
    </form>
</div>
@endsection
