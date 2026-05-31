@extends('layouts.app')
@section('title', 'User Management')

@section('content')
@php
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'hr_admin' => 'HR Admin',
        'manager' => 'Manager',
        'employee' => 'Employee',
    ];
@endphp

<div class="space-y-3">
    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
        <div class="min-w-0">
            <h2 class="text-xl font-bold text-gray-900">User Management</h2>
            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                <span class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-gray-600">Total: <strong class="text-gray-900">{{ $stats['total'] }}</strong></span>
                <span class="rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-green-700">Active: <strong>{{ $stats['active'] }}</strong></span>
                <span class="rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-red-700">Blocked: <strong>{{ $stats['blocked'] }}</strong></span>
                <span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-blue-700">Admins: <strong>{{ $stats['admins'] }}</strong></span>
            </div>
        </div>
        <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
            <a href="{{ route('users.permissions') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Manage Role Permissions
            </a>
            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Create User
            </a>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-4 py-3">
            <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 gap-2 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-5">
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, employee code..." class="form-control">
                </div>
                <div class="lg:col-span-3">
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $roleLabels[$role] ?? $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-400">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>
                <div class="flex gap-2 lg:col-span-2">
                    <button type="submit" class="h-10 flex-1 rounded-md bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                    <a href="{{ route('users.index') }}" class="flex h-10 items-center rounded-md border border-gray-300 px-3 text-sm font-medium text-gray-600 hover:bg-gray-50">Clear</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Profile</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Account</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $roleLabels[$user->role] ?? $user->role }}</span>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($user->employee)
                                    <p class="font-medium text-gray-700">{{ $user->employee->employee_code }}</p>
                                    <p class="text-xs text-gray-400">{{ $user->employee->department->name ?? '-' }}</p>
                                @else
                                    <span class="text-gray-400">Login only</span>
                                    @if($user->role !== 'super_admin')
                                        <p><a href="{{ route('employees.create', ['user' => $user->id]) }}" class="text-xs font-medium text-blue-600 hover:text-blue-800">Create employee profile</a></p>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($user->is_active)
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">Active</span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">Blocked</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-wrap items-center justify-end gap-x-3 gap-y-1">
                                    <a href="{{ route('users.edit', $user) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Manage</a>
                                    @if(!$user->employee && $user->role !== 'super_admin')
                                        <a href="{{ route('employees.create', ['user' => $user->id]) }}" class="text-sm font-medium text-green-700 hover:text-green-900">Create Profile</a>
                                    @endif
                                    <a href="{{ route('users.permissions', ['role' => $user->role]) }}" class="text-sm font-medium text-slate-600 hover:text-slate-800">Permissions</a>
                                    @if($user->id !== auth()->id())
                                        @if($user->is_active)
                                            <form method="POST" action="{{ route('users.deactivate', $user) }}" onsubmit="return confirm('Block login access for {{ $user->name }}?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Block</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('users.activate', $user) }}" onsubmit="return confirm('Activate login access for {{ $user->name }}?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-sm font-medium text-green-600 hover:text-green-800">Activate</button>
                                            </form>
                                        @endif
                                        @if(!$user->employee)
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete {{ $user->name }} permanently?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-900">Delete</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">HR linked</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">Current user</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="border-t border-gray-100 p-4">{{ $users->links() }}</div>
        @endif
    </div>

</div>
@endsection
