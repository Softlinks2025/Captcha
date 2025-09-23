<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use App\Models\UserReferral;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The guard for the model.
     * We'll override the getDefaultGuardName method instead of using $guard_name
     * to properly handle multiple guards
     */
    /**
     * Override the default guard name for the model.
     *
     * @return string
     */
    public function getDefaultGuardName()
    {
        return 'web';
    }
    
    /**
     * Get the roles that belong to the model with proper guard handling.
     */
    public function roles()
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        )->where(function($query) {
            $query->whereNull('roles.guard_name')
                  ->orWhereIn('roles.guard_name', ['web', 'api', 'agent']);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'otp',
        'otp_expires_at',
        'phone_verified_at',
        'is_verified',
        'profile_photo_path',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'agent_id',
        'agent_referral_code',
        'subscription_name',
        'purchased_date',
        'total_amount_paid',
        'level',
        'wallet_balance',
        'profile_completed',
        'upi_id',
        'fcm_token',
        'bank_name',
        'bank_account_number',
        'ifsc_code',
        'account_holder_name',
        'pan_number',
        'additional_contact_number',
        'referral_code',
        'milestone_10_reached',
        'milestone_50_reached',
        'milestone_100_reached',
        'bonus_claimed_10',
        'bonus_claimed_50',
        'bonus_claimed_100',
        'total_referrals',
        'last_bonus_claimed_at',
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
        'otp',
        'otp_expires_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'profile_completed' => 'boolean',
        'date_of_birth' => 'date:Y-m-d',
        'purchased_date' => 'datetime',
        'last_login_at' => 'datetime',
        'milestone_10_reached' => 'boolean',
        'milestone_50_reached' => 'boolean',
        'milestone_100_reached' => 'boolean',
        'bonus_claimed_10' => 'boolean',
        'bonus_claimed_50' => 'boolean',
        'bonus_claimed_100' => 'boolean',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime'
        ];
    }

    /**
     * Set the user's date of birth.
     *
     * @param  string  $value
     * @return void
     */
    public function setDateOfBirthAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['date_of_birth'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
        }
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    /**
     * Handle user referral reward when a referred user purchases a subscription
     * 
     * @param \App\Models\SubscriptionPlan $plan
     * @return array
     */
    public function handleUserReferralReward($plan)
    {
        // Log the start of referral reward processing
        \Log::info('Starting referral reward processing', [
            'user_id' => $this->id,
            'referred_by' => $this->referred_by,
            'plan_id' => $plan->id ?? null,
            'user_subscription' => $this->subscription_name,
            'has_subscription' => $this->hasSubscription()
        ]);

        // If user has no referrer, return early
        if (!$this->referred_by) {
            $message = 'No referrer found for this user.';
            \Log::info($message, [
                'user_id' => $this->id,
                'referred_by' => $this->referred_by,
                'subscription_name' => $this->subscription_name
            ]);
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => $message
            ];
        }
        
        // Get the referrer
        $referrer = self::find($this->referred_by);
        
        // Check if referrer exists
        if (!$referrer) {
            $message = 'Referrer not found.';
            \Log::warning($message, ['referred_by' => $this->referred_by]);
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => $message
            ];
        }
        
        // Log referrer details
        \Log::info('Referrer found', [
            'referrer_id' => $referrer->id,
            'referrer_subscription' => $referrer->subscription_name,
            'referrer_plan' => $referrer->subscriptionPlan ? $referrer->subscriptionPlan->toArray() : null
        ]);
        
        // Get the referrer's subscription plan to check if they're eligible for rewards
        $referrerPlan = $referrer->subscriptionPlan;
        if (!$referrerPlan) {
            $message = 'Referrer does not have an active subscription plan.';
            \Log::warning($message, [
                'referrer_id' => $referrer->id,
                'referrer_subscription' => $referrer->subscription_name,
                'has_subscription' => $referrer->hasSubscription()
            ]);
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => $message
            ];
        }
        
        // Get the referral reward amount
        $referralReward = $referrerPlan->referral_earning_per_ref ?? 0;
        
        // Log plan details
        \Log::info('Referral reward details', [
            'referrer_plan_id' => $referrerPlan->id,
            'referrer_plan_name' => $referrerPlan->name,
            'referral_earning_per_ref' => $referralReward,
            'is_unlimited' => $referrerPlan->is_unlimited ?? false
        ]);
        
        if ($referralReward <= 0) {
            $message = 'This plan does not offer referral rewards.';
            \Log::warning($message, ['plan_id' => $referrerPlan->id]);
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => $message
            ];
        }
        
        // Start database transaction to ensure data consistency
        \DB::beginTransaction();
        
        try {
            // Get the current wallet balance and calculate new balance
            $currentBalance = (float) $referrer->wallet_balance;
            $newBalance = $currentBalance + $referralReward;
            
            // Update the referrer's wallet balance
            $referrer->wallet_balance = $newBalance;
            $referrer->save();
            
            // Find existing referral or create a new one
            $referral = UserReferral::firstOrNew([
                'referrer_id' => $referrer->id,
                'referred_id' => $this->id
            ]);
            
            // Update referral details
            $referral->fill([
                'referral_code' => $referrer->referral_code,
                'status' => 'completed',
                'reward_amount' => $referralReward,
                'used_at' => now(),
                'reward_credited' => true
            ]);
            
            // Save the referral record
            $referral->save();
            
            // Create a wallet transaction record without reference fields
            $transaction = WalletTransaction::create([
                'user_id' => $referrer->id,
                'amount' => $referralReward,
                'type' => 'referral_earning',
                'description' => 'Referral reward for user #' . $this->id . ' (' . $this->name . ')'
            ]);
            
            // Log the referral update
            \Log::info('Updated referral record', [
                'referral_id' => $referral->id,
                'transaction_id' => $transaction->id,
                'referrer_id' => $referrer->id,
                'referred_id' => $this->id,
                'reward_amount' => $referralReward,
                'status' => 'completed'
            ]);
            
            // Commit the transaction
            \DB::commit();
            
            // Log the successful referral
            \Log::info('Referral reward credited', [
                'referrer_id' => $referrer->id,
                'referred_user_id' => $this->id,
                'amount' => $referralReward,
                'current_balance' => $currentBalance,
                'new_balance' => $newBalance,
                'transaction_id' => $transaction->id,
                'referral_id' => $referral->id
            ]);
            
            return [
                'rewarded' => true,
                'reward_amount' => $referralReward,
                'message' => 'Referral reward credited successfully.'
            ];
            
        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            \DB::rollBack();
            
            // Log the error
            \Log::error('Failed to process referral reward', [
                'referrer_id' => $referrer->id ?? null,
                'referred_user_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => 'Failed to process referral reward: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle agent reward when a referred user purchases a subscription
     * 
     * @param \App\Models\SubscriptionPlan $plan
     * @return array
     */
    public function handleAgentRewardForSubscription($plan)
    {
        // If user has no agent, return early
        if (!$this->agent_id) {
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => 'No agent associated with this user.'
            ];
        }
        
        // Get the agent model
        $agent = \App\Models\Agent::find($this->agent_id);
        if (!$agent) {
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => 'Agent not found.'
            ];
        }
        
        // Check if agent has paid the joining fee
        if (!$agent->joining_fee_paid) {
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => 'Agent has not paid the joining fee.'
            ];
        }
        
        // Get the agent's total verified referrals
        $referralCount = self::where('agent_id', $agent->id)
            ->where('id', '<>', $this->id) // Exclude the current user
            ->whereNotNull('subscription_name')
            ->whereNotNull('purchased_date')
            ->count();
            
        // Include the current user if they are already counted as referred
        $totalReferrals = $referralCount + 1;
        
        // Get the appropriate commission tier for this plan and referral count
        $commissionTier = $plan->agentCommissionTiers()
            ->where('min_referrals', '<=', $totalReferrals)
            ->where(function($query) use ($totalReferrals) {
                $query->where('max_referrals', '>=', $totalReferrals)
                      ->orWhereNull('max_referrals');
            })
            ->orderBy('min_referrals', 'desc')
            ->first();
            
        if (!$commissionTier) {
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => 'No commission tier found for the current referral count.'
            ];
        }
        
        // Calculate commission amount
        $commissionAmount = $commissionTier->commission_amount;
        
        // Start database transaction to ensure data consistency
        \DB::beginTransaction();
        
        try {
            // Get the agent model using the agent_id from the user
            $agentModel = \App\Models\Agent::find($this->agent_id);
            
            if (!$agentModel) {
                throw new \Exception('Agent record not found for agent ID: ' . $this->agent_id);
            }
            
            // Credit the agent's wallet in Agent model only
            $agentModel->wallet_balance += $commissionAmount;
            $agentModel->total_earnings += $commissionAmount;
            $agentModel->save();
            
            // Create an agent wallet transaction record
            $transaction = \App\Models\AgentWalletTransaction::create([
                'agent_id' => $agent->id,
                'amount' => $commissionAmount,
                'type' => 'credit',
                'description' => 'Commission for referral #' . $this->id . ' purchasing ' . $plan->name . ' plan'
            ]);
            
            if (!$transaction) {
                throw new \Exception('Failed to create wallet transaction');
            }
            
            // Commit the transaction
            \DB::commit();
        
            // Log the commission
            \Log::info('Agent commission credited', [
                'agent_id' => $agentModel->id,
                'user_id' => $this->id,
                'plan_id' => $plan->id,
                'commission_amount' => $commissionAmount,
                'referral_count' => $totalReferrals,
                'tier_id' => $commissionTier->id,
                'wallet_balance' => $agentModel->wallet_balance,
                'total_earnings' => $agentModel->total_earnings
            ]);
        
            return [
                'rewarded' => true,
                'reward_amount' => $commissionAmount,
                'message' => 'Commission of ₹' . number_format($commissionAmount, 2) . ' credited to agent.'
            ];
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            \DB::rollBack();
            \Log::error('Failed to credit agent commission: ' . $e->getMessage());
            
            return [
                'rewarded' => false,
                'reward_amount' => 0,
                'message' => 'Failed to credit commission. Please try again.'
            ];
        }
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'requires_profile_completion' => !$this->isProfileComplete(),
        ];
    }
    
    /**
     * Check if user has completed their profile
     *
     * @return bool
     */
    public function isProfileComplete(): bool
    {
        return !empty($this->name) && 
               !empty($this->email) && 
               $this->phone_verified_at !== null;
    }
    
    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
                    ? Storage::url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
    }
    
    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the agent who referred this user.
     */
    public function referringAgent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    /**
     * Get the users referred by this user.
     */
    public function referredUsers()
    {
        return $this->hasMany(UserReferral::class, 'referrer_id', 'id')
            ->with('referredUser');
    }

    /**
     * Get the user who referred this user.
     */
    public function referrer()
    {
        return $this->hasOne(UserReferral::class, 'referred_id', 'id')
            ->with('referrer');
    }

    /**
     * Get the user's subscription plan.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_name', 'name');
    }
    
    /**
     * Check if user has an active subscription
     * 
     * @return bool
     */
    public function hasSubscription()
    {
        try {
            // Debug log to check subscription status
            $logData = [
                'user_id' => $this->id,
                'subscription_name' => $this->subscription_name,
                'purchased_date' => $this->purchased_date ? $this->purchased_date->toDateTimeString() : null,
                'current_time' => now()->toDateTimeString()
            ];
            
            // Check if user has a subscription plan assigned
            if (empty($this->subscription_name) || empty($this->purchased_date)) {
                \Log::info('No subscription found - missing name or purchase date', ['user_id' => $this->id]);
                return false;
            }

            // Ensure purchased_date is a Carbon instance with timezone
            $purchasedDate = \Carbon\Carbon::parse($this->purchased_date, 'UTC');
            
            // Get subscription plan details
            $plan = $this->subscriptionPlan;
            if (!$plan) {
                \Log::warning('Subscription plan not found', [
                    'user_id' => $this->id,
                    'subscription_name' => $this->subscription_name
                ]);
                return false;
            }

            // Check if it's an unlimited/lifetime plan
            if ($plan->is_unlimited) {
                \Log::info('Unlimited/Lifetime subscription found', [
                    'user_id' => $this->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'is_unlimited' => true
                ]);
                return true;
            }

            // For time-limited plans, use duration_days from the plan
            // Default to 30 days if not specified
            $durationDays = $plan->duration_days > 0 ? (int)$plan->duration_days : 30;
            
            // Calculate expiry date (end of day)
            $expiryDate = $purchasedDate->copy()
                ->addDays($durationDays)
                ->endOfDay();
                
            $currentTime = now('UTC');
            $isActive = $currentTime->lt($expiryDate);

            // Detailed logging
            $logData = array_merge($logData, [
                'plan_id' => $plan->id,
                'duration_days' => $durationDays,
                'expiry_date' => $expiryDate->toDateTimeString(),
                'is_active' => $isActive,
                'time_remaining' => $currentTime->diffForHumans($expiryDate, true)
            ]);
            
            \Log::info('Subscription status check', $logData);

            return $isActive;
            
        } catch (\Exception $e) {
            \Log::error('Error checking subscription status', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get the user's subscription plan details
     * 
     * @return \App\Models\SubscriptionPlan|null
     */
    public function getSubscriptionPlanAttribute()
    {
        if (empty($this->subscription_name)) {
            return null;
        }
        
        return \App\Models\SubscriptionPlan::where('name', $this->subscription_name)->first();
    }

    /**
     * Get the wallet transactions for the user.
     */
    public function walletTransactions()
    {
        return $this->hasMany(\App\Models\WalletTransaction::class, 'user_id');
    }

    /**
     * Get the user's milestone status
     */
    public function getMilestoneStatus()
    {
        return [
            10 => [
                'reached' => $this->milestone_10_reached,
                'claimed' => $this->bonus_claimed_10,
                'reward' => '10% bonus on earnings'
            ],
            50 => [
                'reached' => $this->milestone_50_reached,
                'claimed' => $this->bonus_claimed_50,
                'reward' => 'Exclusive T-shirt'
            ],
            100 => [
                'reached' => $this->milestone_100_reached,
                'claimed' => $this->bonus_claimed_100,
                'reward' => 'Premium Gift Box'
            ]
        ];
    }

    /**
     * Get available bonuses that can be claimed
     */
    public function getAvailableBonuses()
    {
        $bonuses = [];
        $milestones = [
            10 => ['type' => 'bonus_10', 'reward' => '10% bonus on earnings'],
            50 => ['type' => 'tshirt', 'reward' => 'Exclusive T-shirt'],
            100 => ['type' => 'gift_box', 'reward' => 'Premium Gift Box']
        ];

        foreach ($milestones as $count => $bonus) {
            $reachedField = "milestone_{$count}_reached";
            $claimedField = "bonus_claimed_{$count}";
            
            if ($this->$reachedField && !$this->$claimedField) {
                $bonuses[] = [
                    'milestone' => $count,
                    'type' => $bonus['type'],
                    'reward' => $bonus['reward']
                ];
            }
        }

        return $bonuses;
    }

    /**
     * Check and update milestone status
     */
    public function checkAndUpdateMilestones()
    {
        $referredCount = $this->referredUsers()->count();
        $this->total_referrals = $referredCount;
        
        // Check each milestone
        foreach ([10, 50, 100] as $milestone) {
            $field = "milestone_{$milestone}_reached";
            if ($referredCount >= $milestone && !$this->$field) {
                $this->$field = true;
                // You can dispatch an event here for notifications
                // event(new MilestoneReached($this, $milestone));
            }
        }
        
        $this->save();
        return $this;
    }

    /**
     * Claim a milestone bonus
     */
    public function claimBonus($milestone)
    {
        $milestone = (int)$milestone;
        if (!in_array($milestone, [10, 50, 100])) {
            return [
                'success' => false,
                'message' => 'Invalid milestone.'
            ];
        }

        $reachedField = "milestone_{$milestone}_reached";
        $claimedField = "bonus_claimed_{$milestone}";

        if (!$this->$reachedField) {
            return [
                'success' => false,
                'message' => 'Milestone not reached yet.'
            ];
        }

        if ($this->$claimedField) {
            return [
                'success' => false,
                'message' => 'Bonus already claimed.'
            ];
        }

        // Process the bonus
        $this->$claimedField = true;
        $this->last_bonus_claimed_at = now();
        $this->save();

        // You can add specific bonus processing logic here
        // For example, adding bonus to wallet or dispatching a job
        $this->processMilestoneBonus($milestone);

        return [
            'success' => true,
            'message' => 'Bonus claimed successfully.'
        ];
    }

    /**
     * Process milestone bonus
     */
    protected function processMilestoneBonus($milestone)
    {
        switch ($milestone) {
            case 10:
                // Add 10% bonus to wallet
                $bonusAmount = $this->total_earnings * 0.10;
                $this->wallet_balance += $bonusAmount;
                break;
                
            case 50:
                // Trigger T-shirt delivery process
                // dispatch(new ProcessTShirtDelivery($this));
                break;
                
            case 100:
                // Trigger gift box delivery process
                // dispatch(new ProcessGiftBoxDelivery($this));
                break;
        }
        
        $this->save();
    }

    /**
     * Generate a unique referral code for the user in the pattern C2C25URXXXX.
     */
    public static function generateReferralCode()
    {
        do {
            $code = 'C2C25UR' . strtoupper(substr(md5(uniqid()), 0, 4));
        } while (static::where('referral_code', $code)->exists());
        return $code;
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateReferralCode();
            }
            if (empty($user->fcm_token)) {
                $user->fcm_token = 'eRIa0tcOS4yUNJBFCd46RF:APA91bHDIDNvRvUdZjfqH1u_jdSfMrYHykA80T7A0r-EhHgRf1BVtFh6OzYTBk9JqoIsIcQ3Kwj20vQoBp4tSQQpQ683Z8RIub-M_Fk3yGvqlCRDTfloQ1E';
            }
        });
    }
}
