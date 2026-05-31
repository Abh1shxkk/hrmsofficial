@extends('layouts.app')
@section('title', 'Salary Management')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Assign Salary</h2>

    <form method="POST" action="{{ route('salary-structures.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
            <select name="employee_id" id="employee_id" required
                    class="form-control">
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->user->name }} ({{ $emp->employee_code }})
                    </option>
                @endforeach
            </select>
            @error('employee_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="basic" class="block text-sm font-medium text-gray-700 mb-1">Basic Salary *</label>
                <input type="number" name="basic" id="basic" value="{{ old('basic') }}" step="0.01" min="1" required
                       class="form-control"
                       placeholder="e.g. 35000">
                @error('basic')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="hra" class="block text-sm font-medium text-gray-700 mb-1">HRA *</label>
                <input type="number" name="hra" id="hra" value="{{ old('hra') }}" step="0.01" min="0" required
                       class="form-control"
                       placeholder="e.g. 14000">
                @error('hra')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="transport_allowance" class="block text-sm font-medium text-gray-700 mb-1">Transport Allowance</label>
                <input type="number" name="transport_allowance" id="transport_allowance"
                       value="{{ old('transport_allowance') }}" step="0.01" min="0"
                       class="form-control"
                       placeholder="e.g. 2000">
                @error('transport_allowance')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="other_allowances" class="block text-sm font-medium text-gray-700 mb-1">Other Allowances</label>
                <input type="number" name="other_allowances" id="other_allowances"
                       value="{{ old('other_allowances') }}" step="0.01" min="0"
                       class="form-control"
                       placeholder="e.g. 1000">
                @error('other_allowances')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-1">Effective From *</label>
            <input type="date" name="effective_from" id="effective_from"
                   value="{{ old('effective_from', date('Y-m-d')) }}" required
                   class="form-control">
            @error('effective_from')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Live Gross Calculation --}}
        <div class="p-4 bg-green-50 rounded-lg" x-data="{
            get gross() {
                let b = parseFloat(document.getElementById('basic')?.value) || 0;
                let h = parseFloat(document.getElementById('hra')?.value) || 0;
                let t = parseFloat(document.getElementById('transport_allowance')?.value) || 0;
                let o = parseFloat(document.getElementById('other_allowances')?.value) || 0;
                return (b + h + t + o).toFixed(2);
            }
        }" x-effect="$el.querySelector('#gross_display').textContent = gross"
           @input.window="$el.querySelector('#gross_display').textContent = gross">
            <p class="text-sm text-gray-500">Estimated Gross Salary</p>
            <p class="text-2xl font-bold text-green-700" id="gross_display">0.00</p>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('salary-structures.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Assign Salary</button>
        </div>
    </form>
</div>
@endsection
