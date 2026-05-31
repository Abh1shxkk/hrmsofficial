<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                $userId
                    ? Rule::unique('users', 'email')->ignore($userId)
                    : 'unique:users,email',
            ],
            'role' => ['required', Rule::in(User::ROLES)],
            'is_active' => 'required|boolean',
        ];

        if (! $userId) {
            $rules['password'] = 'required|min:8|confirmed';
        }

        // Require explicit confirmation when assigning super_admin role
        if ($this->input('role') === 'super_admin') {
            $rules['confirm_super_admin'] = 'required|accepted';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'password.min' => 'Password must be at least 8 characters.',
            'email.unique' => 'A user with this email already exists.',
            'confirm_super_admin.required' => 'You must confirm Super Admin role assignment.',
            'confirm_super_admin.accepted' => 'You must confirm Super Admin role assignment.',
        ];
    }
}
