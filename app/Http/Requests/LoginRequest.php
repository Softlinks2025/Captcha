<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required', 
                'email', 
                'exists:users,email,deleted_at,NULL', // Only non-deleted users
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::where('email', $value)->first();
                    if ($user && !$user->email_verified_at) {
                        $fail('Please verify your email address before logging in.');
                    }
                }
            ],
            'password' => ['required', 'string', 'min:6'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.exists' => 'The provided credentials do not match our records.',
            'password.required' => 'The password is required.',
            'password.min' => 'The password must be at least :min characters.',
        ];
    }
}
