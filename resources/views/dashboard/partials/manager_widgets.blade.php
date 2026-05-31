<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Team Members" :value="isset($data['my_team']) ? $data['my_team']->count() : 0" color="blue" />
    <x-stat-card title="Team Present Today" :value="$data['team_present_today'] ?? 0" color="green" />
    <x-stat-card title="Pending Approvals" :value="$data['pending_approvals'] ?? 0" color="yellow" />
    <x-stat-card title="Open Tasks" :value="$data['pending_tasks'] ?? 0" color="purple" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Manager Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('tasks.create') }}" class="rounded-lg bg-purple-50 p-3 text-center text-sm font-medium text-purple-700 hover:bg-purple-100">Assign Task</a>
            <a href="{{ route('tasks.index') }}" class="rounded-lg bg-slate-50 p-3 text-center text-sm font-medium text-slate-700 hover:bg-slate-100">Task Assignment</a>
            <a href="{{ route('leaves.approvals') }}" class="rounded-lg bg-yellow-50 p-3 text-center text-sm font-medium text-yellow-700 hover:bg-yellow-100">Leave Approvals</a>
            <a href="{{ route('attendance.report') }}" class="rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-700 hover:bg-green-100">Team Attendance</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Recent Assigned Tasks</h3>
        <div class="space-y-3">
            @forelse($data['assigned_tasks'] ?? [] as $task)
                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                        <p class="text-xs text-gray-500">{{ $task->assignedEmployee->user->name }} | Due {{ $task->due_date->format('d M Y') }}</p>
                    </div>
                    <span class="rounded-full px-2 py-1 text-xs {{ $task->status === 'completed' ? 'bg-green-100 text-green-700' : ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">{{ ucwords(str_replace('_', ' ', $task->status)) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No tasks assigned yet.</p>
            @endforelse
        </div>
    </div>
</div>
