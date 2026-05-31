<?php

namespace App\Http\Requests;

use App\Models\EmployeeDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;
        $userId = $this->route('employee')?->user_id;

        $rules = [
            'existing_user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                $employeeId || $this->filled('existing_user_id')
                    ? 'unique:users,email,' . ($userId ?: $this->input('existing_user_id'))
                    : 'unique:users,email',
            ],
            'department_id' => 'required|exists:departments,id',
            'designation' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:-18 years',
            'date_of_joining' => 'required|date|before_or_equal:today',
            'phone' => ['required', 'string', 'max:15', 'regex:/^[6-9]\d{9}$/'],
            'address' => 'required|string|max:1000',
            'photo' => 'nullable|image|max:2048',
            'aadhar_number' => ['nullable', 'string', 'regex:/^\d{12}$/'],
            'pan_number' => ['nullable', 'string', 'regex:/^[A-Z]{5}\d{4}[A-Z]$/'],
            'employment_type' => 'required|in:full_time,part_time,contract',
            'documents' => 'nullable|array',
            'documents.*.type' => ['required_with:documents.*.file', Rule::in(array_keys(EmployeeDocument::TYPES))],
            'documents.*.title' => 'nullable|string|max:255',
            'documents.*.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ];

        if (! $employeeId && ! $this->filled('existing_user_id')) {
            $rules['password'] = 'required|min:8';
            $rules['employee_code'] = 'required|string|max:20|unique:employees,employee_code';
            $rules['role'] = 'required|in:employee,manager,hr_admin';
        } elseif (! $employeeId) {
            $rules['employee_code'] = 'required|string|max:20|unique:employees,employee_code';
        } else {
            $rules['status'] = 'required|in:active,inactive,terminated';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'date_of_birth.before' => 'Employee must be at least 18 years old.',
            'date_of_joining.before_or_equal' => 'Joining date cannot be in the future.',
            'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'aadhar_number.regex' => 'Aadhar number must be exactly 12 digits.',
            'pan_number.regex' => 'PAN must be in format ABCDE1234F.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}
