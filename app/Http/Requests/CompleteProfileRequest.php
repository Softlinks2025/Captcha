<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use App\Models\User;

class CompleteProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will be handled by auth middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = auth()->id();
        
        return [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$userId],
            'profile_photo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($this->hasFile('profile_photo')) {
                        if (!$this->file('profile_photo')->isValid()) {
                            $fail('The profile photo file is not valid.');
                        }
                        if (!in_array($this->file('profile_photo')->extension(), ['jpg', 'jpeg', 'png', 'webp'])) {
                            $fail('The profile photo must be a file of type: jpg, jpeg, png, webp.');
                        }
                        if ($this->file('profile_photo')->getSize() > 2 * 1024 * 1024) {
                            $fail('The profile photo must not be greater than 2MB.');
                        }
                    } else if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('The profile photo must be a valid URL or an image file.');
                    }
                }
            ],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'upi_id' => ['required', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:32'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'account_holder_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'additional_contact_number' => ['nullable', 'string', 'max:20'],
            'user_referral_code' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($userId) {
                    if (empty($value)) {
                        return; // Skip if no referral code provided
                    }
                    
                    // If both referral codes are provided, prioritize user_referral_code
                    if ($this->has('agent_referral_code') && !empty($this->input('agent_referral_code'))) {
                        // Remove agent_referral_code from input
                        $this->merge(['agent_referral_code' => null]);
                    }
                    
                    $user = User::find($userId);
                    try {
                        \Log::info('Referral code validation started', [
                            'referral_code' => $value,
                            'user_id' => $user ? $user->id : null
                        ]);

                        if ($user && $user->referral_code_used) {
                            $message = 'User has already used a referral code';
                            \Log::warning($message, ['user_id' => $user->id]);
                            $fail('You have already used a referral code.');
                            return;
                        }
                        
                        // Check if the referral code exists in users table
                        $userReferrer = User::where('referral_code', $value)->first();
                        $agentReferrer = null;
                        
                        // If not found in users, check in agents table
                        if (!$userReferrer) {
                            $agentReferrer = \App\Models\Agent::where('referral_code', $value)
                                ->where('status', 'active')
                                ->first();
                                
                            if (!$agentReferrer) {
                                $message = 'Referral code not found in users or agents table';
                                \Log::warning($message, ['referral_code' => $value]);
                                $fail('The selected referral code is invalid.');
                                return;
                            }
                            
                            // Store agent referrer in request for later use
                            $this->merge(['referred_by_agent_id' => $agentReferrer->id]);
                            \Log::info('Agent referrer found', ['agent_id' => $agentReferrer->id]);
                        } else {
                            // Store user referrer in request for later use
                            $this->merge(['referred_by_user_id' => $userReferrer->id]);
                            \Log::info('User referrer found', ['user_id' => $userReferrer->id]);
                            
                            // Check if user is trying to use their own referral code
                            if ($userReferrer->id === $user->id) {
                                $message = 'User tried to use their own referral code';
                                \Log::warning($message, ['user_id' => $user->id]);
                                $fail('You cannot use your own referral code.');
                                return;
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error during referral code validation', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $fail('An error occurred while validating the referral code.');
                    }
                },
            ],
            'agent_referral_code' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($userId) {
                    if (empty($value)) {
                        return; // Skip if no agent code provided
                    }
                    
                    // If user_referral_code is provided, skip agent_referral_code validation
                    if ($this->has('user_referral_code') && !empty($this->input('user_referral_code'))) {
                        return;
                    }
                    
                    $user = User::find($userId);
                    if ($user && $user->agent_id) {
                        $fail('You have already used an agent referral code.');
                        return;
                    }
                    
                    try {
                        $agent = \App\Models\Agent::where('referral_code', $value)
                            ->where('status', 'active')
                            ->first();
                            
                        if (!$agent) {
                            $message = 'Agent referral code not found or agent is inactive';
                            \Log::warning($message, ['referral_code' => $value]);
                            $fail('The selected agent referral code is invalid or the agent is inactive.');
                            return;
                        }
                        
                        // Store agent referrer in request for later use
                        $this->merge(['referred_by_agent_id' => $agent->id]);
                        \Log::info('Agent referrer found via agent_referral_code', [
                            'agent_id' => $agent->id,
                            'user_id' => $userId
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error during agent referral validation', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $fail('An error occurred while validating the agent referral code.');
                    }
                },
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
            'profile_photo.image' => 'The file must be an image.',
            'profile_photo.max' => 'The image must not be larger than 2MB.',
            'profile_photo.dimensions' => 'The image dimensions should be maximum 1000x1000px.',
            'email.unique' => 'This email is already registered.',
            'date_of_birth.before' => 'The date of birth must be a date before today.',
        ];
    }
}
