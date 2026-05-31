@extends('layouts.app')
@section('title', 'Manage User')

@section('content')
@php
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'hr_admin' => 'HR Admin',
        'manager' => 'Manager',
        'employee' => 'Employee',
    ];
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
                <p class="text-gray-500">{{ $user->email }}</p>
                @if($user->employee)
                    <p class="text-sm text-gray-400 mt-1">{{ $user->employee->employee_code }} - {{ $user->employee->department->name ?? '-' }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">{{ $roleLabels[$user->role] ?? $user->role }}</span>
                @if($user->is_active)
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Active</span>
                @else
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Blocked</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Account Details</h3>
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-control">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-control">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" required class="form-control" @disabled($user->id === auth()->id())>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>{{ $roleLabels[$role] ?? $role }}</option>
                            @endforeach
                        </select>
                        @if($user->id === auth()->id())
                            <p class="mt-1 text-xs text-gray-400">You cannot change your own role.</p>
                            <input type="hidden" name="role" value="{{ $user->role }}">
                        @endif
                        @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Status</label>
                        <select name="is_active" required class="form-control" @disabled($user->id === auth()->id())>
                            <option value="1" {{ old('is_active', (int) $user->is_active) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', (int) $user->is_active) == 0 ? 'selected' : '' }}>Blocked</option>
                        </select>
                        @if($user->id === auth()->id())
                            <p class="mt-1 text-xs text-gray-400">You cannot block your own account.</p>
                            <input type="hidden" name="is_active" value="1">
                        @endif
                        @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if($user->role !== 'super_admin')
                <div x-data="{ selectedRole: '{{ old('role', $user->role) }}' }" x-init="$el.closest('form').querySelector('select[name=role]')?.addEventListener('change', e => selectedRole = e.target.value)">
                    <div x-show="selectedRole === 'super_admin'" x-cloak class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="confirm_super_admin" value="1" class="form-checkbox mt-0.5">
                            <span><strong>Warning:</strong> Super Admin has unrestricted access. Confirm this role change.</span>
                        </label>
                        @error('confirm_super_admin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif

                <div class="flex justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Reset Password</h3>
                <form method="POST" action="{{ route('users.reset-password', $user) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="password" required class="form-control">
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="form-control">
                    </div>
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Reset Password</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Quick Access</h3>
                <a href="{{ route('users.permissions', ['role' => $user->role]) }}" class="mb-3 block text-sm text-blue-600 hover:text-blue-800">View role permissions</a>
                @if($user->employee)
                    <a href="{{ route('employees.show', $user->employee) }}" class="block text-sm text-blue-600 hover:text-blue-800">View employee profile</a>
                @else
                    <p class="text-sm text-gray-500">This user does not have an employee profile.</p>
                    @if($user->role !== 'super_admin')
                        <a href="{{ route('employees.create', ['user' => $user->id]) }}" class="mt-3 inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Create Employee Profile</a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="font-semibold text-gray-800">Permissions for {{ $roleLabels[$user->role] ?? $user->role }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Module</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Access</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($permissions as $permission)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $permission->module }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['can_view' => 'View', 'can_edit' => 'Edit', 'can_delete' => 'Delete', 'can_manage' => 'Manage'] as $field => $label)
                                        <span class="rounded-full px-2 py-1 text-xs {{ $permission->$field ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
