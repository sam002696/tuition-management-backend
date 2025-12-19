<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role'     => ['required', Rule::in(['teacher', 'student'])],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'role.in' => 'Role must be either teacher or student.',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
