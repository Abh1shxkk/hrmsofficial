<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'basic' => 'required|numeric|min:1',
            'hra' => 'required|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'basic.min' => 'Basic salary must be at least 1.',
            'employee_id.exists' => 'Selected employee does not exist.',
        ];
    }
}
