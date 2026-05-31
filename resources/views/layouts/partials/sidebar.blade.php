@php
    $role = auth()->user()->role ?? 'employee';
    $emp = auth()->user()->employee;
@endphp

<aside class="fixed inset-y-0 left-0 z-30 bg-gray-900 text-white flex flex-col transition-all duration-300"
       :class="sidebarOpen ? 'w-64' : 'w-16'">

    {{-- Logo --}}
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-700">
        <span class="text-xl font-bold" x-show="sidebarOpen">HRMS</span>
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="scrollbar-hidden flex-1 overflow-y-auto py-4 space-y-1">
        <x-sidebar-link route="dashboard" icon="home" label="Dashboard" />

        @if($role === 'super_admin')
            <x-sidebar-link route="users.index" icon="users" label="User Management" />
            <x-sidebar-link route="settings.smtp.edit" icon="mail" label="SMTP Settings" />
        @endif

        @if(in_array($role, ['super_admin', 'hr_admin']))
            <x-sidebar-link route="employees.index" icon="users" label="Employee Management" />
            <x-sidebar-link route="departments.index" icon="building" label="Department Management" />
            <x-sidebar-link route="salary-structures.index" icon="currency" label="Salary Management" />
        @endif

        @if(in_array($role, ['super_admin', 'hr_admin']))
            <x-sidebar-link route="payroll.index" icon="currency" label="Payroll Management" />
        @elseif($role === 'employee')
            <x-sidebar-link route="payroll.index" icon="currency" label="Payroll History" />
        @endif

        @if(in_array($role, ['super_admin', 'hr_admin', 'manager']))
            <x-sidebar-link route="leaves.approvals" icon="clipboard-check" label="Leave Approvals" />
        @endif

        <x-sidebar-link route="attendance.index" icon="calendar" label="Attendance Management" />
        <x-sidebar-link route="leaves.balance" icon="clipboard" label="Leave Management" />
        <x-sidebar-link route="leaves.apply" icon="plus-circle" label="Apply Leave" />
        <x-sidebar-link route="leaves.my" icon="list" label="My Leaves" />
        <x-sidebar-link route="tasks.index" icon="list" label="Task Assignment" />
        <x-sidebar-link route="holidays.index" icon="star" label="Calendar & Holidays" />

        @if(in_array($role, ['super_admin', 'hr_admin', 'manager']))
            <x-sidebar-link route="tasks.create" icon="plus" label="Assign Task" />
        @endif
    </nav>

    {{-- User Info --}}
    <div class="border-t border-gray-700 p-4">
        <a href="{{ route('profile.show') }}" class="flex items-center hover:opacity-80">
            @if($emp?->photo)
                <img src="{{ Storage::url($emp->photo) }}" class="w-9 h-9 rounded-full object-cover">
            @else
                <div class="w-9 h-9 rounded-full bg-gray-700 text-gray-300 flex items-center justify-center text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
            @endif
            <div class="ml-3" x-show="sidebarOpen">
                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $role) }}</p>
            </div>
        </a>
    </div>
</aside>
