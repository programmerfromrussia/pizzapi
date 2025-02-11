<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        // Allow all users to access this request
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:users,phone',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'is_admin' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'phone.unique' => 'The phone number has already been taken.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 6 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'is_admin.boolean' => 'The is_admin field must be a boolean.',
        ];
    }
}
