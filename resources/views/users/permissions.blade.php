@extends('layouts.app')
@section('title', 'Role Permissions')

@section('content')
@php
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'hr_admin' => 'HR Admin',
        'manager' => 'Manager',
        'employee' => 'Employee',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Role Permissions</h2>
            <p class="mt-1 text-sm text-gray-500">Select a role and manage view, edit, delete and manage access module-wise.</p>
        </div>
        <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Back to Users
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('users.permissions') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4 md:items-end">
            <div class="md:col-span-3">
                <label class="mb-1 block text-xs font-medium text-gray-500">Role</label>
                <select name="role" class="form-control">
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }}>{{ $roleLabels[$role] ?? $role }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">View Permissions</button>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900">{{ $roleLabels[$selectedRole] ?? $selectedRole }} Permissions</h3>
            <p class="mt-1 text-sm text-gray-500">Changes apply to all users assigned to this role.</p>
        </div>

        <form method="POST" action="{{ route('users.permissions.update') }}">
            @csrf @method('PUT')
            <input type="hidden" name="role" value="{{ $selectedRole }}">

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Module</th>
                            <th class="px-6 py-3 text-center font-medium text-gray-500">View</th>
                            <th class="px-6 py-3 text-center font-medium text-gray-500">Edit</th>
                            <th class="px-6 py-3 text-center font-medium text-gray-500">Delete</th>
                            <th class="px-6 py-3 text-center font-medium text-gray-500">Manage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($permissions as $permission)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $permission->module }}</td>
                                @foreach(['can_view', 'can_edit', 'can_delete', 'can_manage'] as $field)
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" name="permissions[{{ $permission->id }}][{{ $field }}]" value="1" class="form-checkbox" @checked($permission->$field)>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end border-t border-gray-100 p-5">
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Permissions</button>
            </div>
        </form>
    </div>
</div>
@endsection
