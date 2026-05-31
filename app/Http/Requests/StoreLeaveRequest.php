<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|min:10|max:1000',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $from = $this->input('from_date');
            $to = $this->input('to_date');

            if ($from && $to && date('Y', strtotime($from)) !== date('Y', strtotime($to))) {
                $validator->errors()->add('to_date', 'Leave cannot span across different years. Please apply separately for each year.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'from_date.after_or_equal' => 'Leave start date cannot be in the past.',
            'to_date.after_or_equal' => 'Leave end date must be on or after the start date.',
            'reason.min' => 'Please provide a reason with at least 10 characters.',
        ];
    }
}
