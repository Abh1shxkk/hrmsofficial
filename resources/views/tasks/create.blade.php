@extends('layouts.app')
@section('title', 'Task Assignment')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-xl font-semibold mb-6">Assign New Task</h2>

    <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="form-control">
            @error('title')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Assign To *</label>
            <select name="assigned_to" required class="form-control">
                <option value="">Select employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>{{ $emp->user->name }} ({{ $emp->employee_code }}) - {{ $emp->department->name ?? 'No Department' }}</option>
                @endforeach
            </select>
            @error('assigned_to')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                <select name="priority" required class="form-control">
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                </select>
                @error('priority')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" required class="form-control">
                @error('due_date')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('tasks.index') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Assign Task</button>
        </div>
    </form>
</div>
@endsection
