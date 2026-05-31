<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.exists' => 'Selected employee does not exist.',
            'month.between' => 'Month must be between 1 and 12.',
            'year.min' => 'Year must be 2020 or later.',
            'year.max' => 'Year cannot be more than next year.',
        ];
    }
}
