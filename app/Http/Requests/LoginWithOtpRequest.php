<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginWithOtpRequest extends FormRequest
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
            'phone' => [
                'required',
                'string',
                'min:6',
                'max:20',
                'regex:/^[+0-9\- ]+$/',
                'exists:users,phone',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::where('phone', $value)->first();
                    if ($user && $user->deleted_at) {
                        $fail('This account has been deactivated. Please contact support.');
                    }
                }
            ],
            'otp' => [
                'required',
                'string',
                'size:6',
            ],
            'role' => [
                'sometimes',
                'string',
                'in:user,agent',
            ],
            'fcm_token' => [
                'nullable',
                'string',
            ],
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
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'Please enter a valid 10-digit phone number.',
            'phone.exists' => 'No account found with this phone number. Please register first.',
            'otp.required' => 'The OTP is required.',
            'otp.size' => 'The OTP must be 6 digits.',
        ];
    }
}
