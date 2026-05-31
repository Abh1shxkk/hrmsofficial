<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Users" :value="$data['total_users'] ?? 0" color="blue" />
    <x-stat-card title="Active Employees" :value="$data['total_employees'] ?? 0" color="green" />
    <x-stat-card title="Departments" :value="$data['total_departments'] ?? 0" color="blue" />
    <x-stat-card title="Current Payroll" :value="'Rs. ' . number_format($data['monthly_payroll'] ?? 0)" color="purple" />
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card title="Pending Leaves" :value="$data['pending_leaves'] ?? 0" color="yellow" />
    <x-stat-card title="Open Tasks" :value="$data['open_tasks'] ?? 0" color="purple" />
    <x-stat-card title="Blocked Users" :value="$data['inactive_users'] ?? 0" color="red" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-4">System Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('users.index') }}" class="rounded-lg bg-slate-50 p-3 text-center text-sm font-medium text-slate-700 hover:bg-slate-100">User Management</a>
            <a href="{{ route('employees.create') }}" class="rounded-lg bg-blue-50 p-3 text-center text-sm font-medium text-blue-700 hover:bg-blue-100">Add Employee</a>
            <a href="{{ route('salary-structures.index') }}" class="rounded-lg bg-indigo-50 p-3 text-center text-sm font-medium text-indigo-700 hover:bg-indigo-100">Salary Management</a>
            <a href="{{ route('payroll.process') }}" class="rounded-lg bg-purple-50 p-3 text-center text-sm font-medium text-purple-700 hover:bg-purple-100">Process Payroll</a>
            <a href="{{ route('attendance.report') }}" class="rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-700 hover:bg-green-100">Attendance Report</a>
            <a href="{{ route('holidays.index') }}" class="rounded-lg bg-amber-50 p-3 text-center text-sm font-medium text-amber-700 hover:bg-amber-100">Calendar & Holidays</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Recent Salary Slips</h3>
        <div class="space-y-3">
            @forelse($data['recent_slips'] ?? [] as $slip)
                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $slip->employee->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ DateTime::createFromFormat('!m', $slip->month)->format('F') }} {{ $slip->year }}</p>
                    </div>
                    <p class="text-sm font-semibold text-green-700">Rs. {{ number_format($slip->net_salary, 2) }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">No salary slips generated yet.</p>
            @endforelse
        </div>
    </div>
</div>
