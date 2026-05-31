@extends('layouts.app')
@section('title', 'Task Assignment')

@section('content')
<div class="space-y-4">
    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('tasks.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px] max-w-xs">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Task title..."
                       class="form-control">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="form-control-sm">
                    <option value="">All Status</option>
                    <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>Todo</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                <select name="priority" class="form-control-sm">
                    <option value="">All Priority</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Filter</button>
            @if(request()->hasAny(['search', 'status', 'priority']))
                <a href="{{ route('tasks.index') }}" class="border border-gray-300 text-gray-600 px-3 py-2 rounded-lg text-sm hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-xl font-semibold">Task Assignment <span class="text-sm font-normal text-gray-400">({{ $tasks->total() }})</span></h2>
            @if(auth()->user()->hasAnyRole(['super_admin', 'hr_admin', 'manager']))
                <a href="{{ route('tasks.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Assign Task</a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Title</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Assigned To</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Priority</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Due Date</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium">{{ $task->title }}</p>
                            @if($task->description)
                                <p class="text-xs text-gray-400 mt-1">{{ Str::limit($task->description, 60) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($task->assignedEmployee->photo)
                                    <img src="{{ Storage::url($task->assignedEmployee->photo) }}" class="w-7 h-7 rounded-full mr-2 object-cover">
                                @else
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center mr-2 text-xs font-bold">
                                        {{ strtoupper(substr($task->assignedEmployee->user->name, 0, 2)) }}
                                    </div>
                                @endif
                                        <div>
                                            <p>{{ $task->assignedEmployee->user->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $task->assignedEmployee->department->name ?? '-' }}</p>
                                        </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php $pColors = ['high' => 'bg-red-100 text-red-700', 'medium' => 'bg-yellow-100 text-yellow-700', 'low' => 'bg-green-100 text-green-700']; @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $pColors[$task->priority] }}">{{ ucfirst($task->priority) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="{{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-red-600 font-medium' : '' }}">
                                {{ $task->due_date->format('d M Y') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php $sColors = ['completed' => 'bg-green-100 text-green-700', 'in_progress' => 'bg-blue-100 text-blue-700', 'todo' => 'bg-gray-100 text-gray-700']; @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $sColors[$task->status] }}">{{ ucwords(str_replace('_', ' ', $task->status)) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                @if($task->can_update_status)
                                    <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="flex items-center space-x-2">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-control-sm text-xs">
                                            <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>Todo</option>
                                            <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                        <button type="submit" class="text-blue-600 hover:underline text-xs">Update</button>
                                    </form>
                                @endif
                                @if($task->can_delete)
                                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline"
                                          onsubmit="return confirm('Delete this task?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Del</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No task assignments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tasks->hasPages())
            <div class="p-4 border-t">{{ $tasks->links() }}</div>
        @endif
    </div>
</div>
@endsection
