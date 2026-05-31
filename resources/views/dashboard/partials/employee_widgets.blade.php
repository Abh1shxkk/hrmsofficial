<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Present This Month" :value="$data['attendance_present'] ?? 0" color="green" />
    <x-stat-card title="Absent This Month" :value="$data['attendance_absent'] ?? 0" color="red" />
    <x-stat-card title="Open Tasks" :value="$data['pending_tasks'] ?? 0" color="purple" />
    <x-stat-card title="Salary Slips" :value="$data['salary_slips_count'] ?? 0" color="blue" />
</div>

@if(isset($data['leave_balances']) && $data['leave_balances']->count())
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @foreach($data['leave_balances'] as $lb)
            <x-stat-card :title="$lb->leaveType->code . ' Balance'" :value="$lb->balance . ' / ' . ($lb->allocated + ($lb->carried_forward ?? 0))" color="blue" />
        @endforeach
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">My Tasks</h3>
            <a href="{{ route('tasks.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">View All</a>
        </div>
        <div class="space-y-3">
            @forelse($data['my_tasks'] ?? [] as $task)
                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                        <p class="text-xs text-gray-500">Due {{ $task->due_date->format('d M Y') }} | {{ ucfirst($task->priority) }}</p>
                    </div>
                    <span class="rounded-full px-2 py-1 text-xs {{ $task->status === 'completed' ? 'bg-green-100 text-green-700' : ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">{{ ucwords(str_replace('_', ' ', $task->status)) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No tasks assigned.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">Payroll</h3>
            <a href="{{ route('payroll.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">History</a>
        </div>
        @if(isset($data['latest_slip']) && $data['latest_slip'])
            @php $slip = $data['latest_slip']; @endphp
            <div class="rounded-lg bg-green-50 p-4">
                <p class="text-sm text-gray-600">{{ DateTime::createFromFormat('!m', $slip->month)->format('F') }} {{ $slip->year }}</p>
                <p class="mt-1 text-2xl font-bold text-green-700">Rs. {{ number_format($slip->net_salary, 2) }}</p>
                <div class="mt-3 flex gap-3">
                    <a href="{{ route('salary-slip.show', $slip) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">View Slip</a>
                    <a href="{{ route('salary-slip.download', $slip) }}" class="text-sm font-medium text-green-700 hover:text-green-900">Download PDF</a>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500">No salary slip generated yet.</p>
        @endif
    </div>
</div>

<div class="mt-6 bg-white rounded-xl shadow-sm p-5">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900">Recent Leave Requests</h3>
        <a href="{{ route('leaves.my') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">View All</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
        @forelse($data['recent_leaves'] ?? [] as $leave)
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-sm font-medium text-gray-900">{{ $leave->leaveType->code }} | {{ $leave->from_date->format('d M') }} - {{ $leave->to_date->format('d M Y') }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $leave->total_days }} day(s)</p>
                <span class="mt-2 inline-block rounded-full px-2 py-1 text-xs {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">{{ ucfirst($leave->status) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No leave requests yet.</p>
        @endforelse
    </div>
</div>
