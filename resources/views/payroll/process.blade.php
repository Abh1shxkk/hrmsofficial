@extends('layouts.app')
@section('title', 'Process Payroll Management')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Generate Payroll</h2>

    <form method="POST" action="{{ route('payroll.generate') }}" class="space-y-5"
          x-data="{
              employeeId: '{{ old('employee_id', '') }}',
              month: '{{ old('month', now()->month) }}',
              year: '{{ old('year', now()->year) }}',
              existing: @js($existingSlips),
              get isDuplicate() {
                  if (!this.employeeId || !this.month || !this.year) return false;
                  return this.existing.includes(this.employeeId + '-' + this.month + '-' + this.year);
              }
          }">
        @csrf

        <div>
            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
            <select name="employee_id" id="employee_id" required x-model="employeeId"
                    class="form-control">
                <option value="">Select employee</option>
                @foreach($employees as $emp)
                    @if($emp->salaryStructures->where('is_active', true)->count())
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->name }} ({{ $emp->employee_code }})
                        </option>
                    @endif
                @endforeach
            </select>
            @error('employee_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            {{-- Show employees without salary structure --}}
            @php
                $noStructure = $employees->filter(fn($e) => $e->salaryStructures->where('is_active', true)->isEmpty());
            @endphp
            @if($noStructure->count())
                <p class="mt-2 text-xs text-amber-600">
                    {{ $noStructure->count() }} employee(s) hidden — no active salary structure.
                    <a href="{{ route('salary-structures.create') }}" class="underline">Assign salary first</a>
                </p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month *</label>
                <select name="month" id="month" required x-model="month"
                        class="form-control">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ old('month', now()->month) == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
                @error('month')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                <input type="number" name="year" id="year" x-model="year"
                       value="{{ old('year', now()->year) }}" min="2020" max="2099" required
                       class="form-control">
                @error('year')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Duplicate Warning --}}
        <div x-show="isDuplicate" x-cloak
             class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
            <strong>Warning:</strong> A salary slip already exists for this employee for the selected month/year. Submitting will show an error.
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('payroll.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    :class="isDuplicate ? 'opacity-50 cursor-not-allowed' : ''"
                    :disabled="isDuplicate">
                Generate Salary Slip
            </button>
        </div>
    </form>
</div>
@endsection
