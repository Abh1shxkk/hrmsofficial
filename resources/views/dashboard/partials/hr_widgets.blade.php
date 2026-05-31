<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Active Employees" :value="$data['active_employees'] ?? 0" color="blue" />
    <x-stat-card title="Pending Leaves" :value="$data['pending_leaves'] ?? 0" color="yellow" />
    <x-stat-card title="Present Today" :value="$data['today_present'] ?? 0" color="green" />
    <x-stat-card title="Absent Today" :value="$data['today_absent'] ?? 0" color="red" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-4">HR Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('employees.create') }}" class="rounded-lg bg-blue-50 p-3 text-center text-sm font-medium text-blue-700 hover:bg-blue-100">Add Employee</a>
            <a href="{{ route('departments.index') }}" class="rounded-lg bg-slate-50 p-3 text-center text-sm font-medium text-slate-700 hover:bg-slate-100">Department Management</a>
            <a href="{{ route('leaves.approvals') }}" class="rounded-lg bg-yellow-50 p-3 text-center text-sm font-medium text-yellow-700 hover:bg-yellow-100">Leave Approvals</a>
            <a href="{{ route('salary-structures.index') }}" class="rounded-lg bg-indigo-50 p-3 text-center text-sm font-medium text-indigo-700 hover:bg-indigo-100">Salary Management</a>
            <a href="{{ route('payroll.process') }}" class="rounded-lg bg-purple-50 p-3 text-center text-sm font-medium text-purple-700 hover:bg-purple-100">Process Payroll</a>
            <a href="{{ route('attendance.report') }}" class="rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-700 hover:bg-green-100">Attendance Report</a>
        </div>
        @if(($data['employees_without_salary'] ?? 0) > 0)
            <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-700">{{ $data['employees_without_salary'] }} active employee(s) do not have salary setup.</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Recent Leave Requests</h3>
        <div class="space-y-3">
            @forelse($data['recent_leaves'] ?? [] as $leave)
                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $leave->employee->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $leave->leaveType->code }} | {{ $leave->from_date->format('d M') }} - {{ $leave->to_date->format('d M Y') }}</p>
                    </div>
                    <span class="rounded-full px-2 py-1 text-xs {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">{{ ucfirst($leave->status) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No leave requests yet.</p>
            @endforelse
        </div>
    </div>
</div>
