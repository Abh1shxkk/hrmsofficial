@extends('layouts.app')
@section('title', 'Department Management')

@section('content')
<div class="max-w-2xl mx-auto rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Add Department Management</h2>
        <p class="mt-1 text-sm text-gray-500">Create a department and optionally assign a reporting manager.</p>
    </div>

    <form method="POST" action="{{ route('departments.store') }}" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Engineering, Sales, HR..." class="form-control">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
            <select name="manager_id" class="form-control">
                <option value="">None</option>
                @foreach($managers as $mgr)
                    <option value="{{ $mgr->id }}" {{ old('manager_id') == $mgr->id ? 'selected' : '' }}>{{ $mgr->user->name }} ({{ $mgr->employee_code }})</option>
                @endforeach
            </select>
            @error('manager_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('departments.index') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create</button>
        </div>
    </form>
</div>
@endsection
