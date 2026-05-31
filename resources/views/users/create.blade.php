@extends('layouts.app')
@section('title', 'Create User')

@section('content')
@php
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'hr_admin' => 'HR Admin',
        'manager' => 'Manager',
        'employee' => 'Employee',
    ];
@endphp

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Create User Account</h2>
        <p class="mt-1 text-sm text-gray-500">Create a login account and assign the correct application role.</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Enter full name" class="form-control">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@company.com" class="form-control">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Role *</label>
                    <select name="role" required class="form-control">
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role', 'employee') === $role ? 'selected' : '' }}>{{ $roleLabels[$role] ?? $role }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Account Status *</label>
                    <select name="is_active" required class="form-control">
                        <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Blocked</option>
                    </select>
                    @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters" class="form-control">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password" class="form-control">
                </div>
            </div>

            <div x-data="{ role: '{{ old('role', 'employee') }}' }">
                <div x-show="false">
                    {{-- Bind role select to alpine --}}
                </div>
                <script>
                    document.querySelector('select[name=role]').addEventListener('change', function() {
                        document.querySelector('[x-data]').__x.$data.role = this.value;
                    });
                </script>
                <div x-show="role === 'super_admin'" x-cloak class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="confirm_super_admin" value="1" class="form-checkbox mt-0.5">
                        <span><strong>Warning:</strong> Super Admin has unrestricted access to all modules, data and user management. Confirm this assignment.</span>
                    </label>
                    @error('confirm_super_admin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                This creates a login account only. Employee profile, payroll and HR details can be added from the Employees module.
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection
