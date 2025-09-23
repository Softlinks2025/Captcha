<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Agent extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'date_of_birth',
        'referral_code',
        'otp',
        'otp_expires_at',
        'is_verified',
        'phone_verified_at',
        'profile_completed',
        'wallet_balance',
        'total_earnings',
        'total_withdrawals',
        'total_referrals',
        'milestone_10_reached',
        'milestone_50_reached',
        'milestone_100_reached',
        'bonus_tshirt_claimed',
        'bonus_bag_claimed',
        'earnings_cap',
        'earnings_cap_applied_at',
        'joining_fee_paid',
        'joining_fee_paid_at',
        'joining_fee_amount',
        'upi_id',
        'bank_account_number',
        'ifsc_code',
        'account_holder_name',
        'address',
        'city',
        'state',
        'pincode',
        'profile_image',
        'aadhar_number',
        'pan_number',
        'gst_number',
        'bio',
        'status',
        'last_login_at',
        'fcm_token',
        'bank_name',
        'additional_contact_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'otp',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'profile_completed' => 'boolean',
        'wallet_balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_withdrawals' => 'decimal:2',
        'total_referrals' => 'integer',
        'milestone_10_reached' => 'boolean',
        'milestone_50_reached' => 'boolean',
        'milestone_100_reached' => 'boolean',
        'bonus_tshirt_claimed' => 'boolean',
        'bonus_bag_claimed' => 'boolean',
        'earnings_cap' => 'decimal:2',
        'earnings_cap_applied_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'date_of_birth' => 'date',
        'joining_fee_paid' => 'boolean',
        'joining_fee_paid_at' => 'datetime',
        'joining_fee_amount' => 'decimal:2',
    ];

    /**
     * Get the agent's date of birth in Y-m-d format.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getDateOfBirthAttribute($value)
    {
        if (!$value) {
            return null;
        }
        
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d');
        }
        
        return $value; // Already a string in Y-m-d format
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
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the users referred by this agent.
     */
    public function referredUsers()
    {
        return $this->hasMany(User::class, 'agent_id');
    }

    /**
     * Get the withdrawal requests for this agent.
     */
    public function withdrawalRequests()
    {
        return $this->hasMany(AgentWithdrawalRequest::class);
    }

    /**
     * Get the plan subscriptions for this agent.
     */
    public function planSubscriptions()
    {
        return $this->hasMany(AgentPlanSubscription::class);
    }

    /**
     * Get the active plan subscription for this agent.
     */
    public function activePlanSubscription()
    {
        return $this->hasMany(AgentPlanSubscription::class)->where('status', 'active');
    }

    /**
     * Get the current plan for this agent.
     */
    public function currentPlan()
    {
        $subscription = $this->activePlanSubscription()->latest('started_at')->first();
        return $subscription ? $subscription->plan : null;
    }

    /**
     * Check if agent has an active plan
     */
    public function hasActivePlan()
    {
        return $this->activePlanSubscription()->exists();
    }

    /**
     * Get current earning rate based on active plan
     */
    public function getCurrentEarningRate()
    {
        $subscription = $this->activePlanSubscription;
        if (!$subscription) {
            return 0; // No active plan
        }
        
        return $subscription->getCurrentEarningRate();
    }

    /**
     * Generate a unique referral code for the agent in the pattern C2C25AGXXXX.
     */
    public static function generateReferralCode()
    {
        do {
            $code = 'C2C25AG' . strtoupper(substr(md5(uniqid()), 0, 4));
        } while (static::where('referral_code', $code)->exists());
        return $code;
    }

    /**
     * Get the agent's current balance (earnings - withdrawals).
     */
    public function getBalanceAttribute()
    {
        return $this->total_earnings - $this->total_withdrawals;
    }

    public function walletTransactions()
    {
        return $this->hasMany(AgentWalletTransaction::class);
    }

    /**
     * Get the current number of referrals for this agent
     */
    public function getReferralCount()
    {
        return $this->referredUsers()->count();
    }

    /**
     * Update referral count and check for milestones
     */
    public function updateReferralCount()
    {
        $newCount = $this->getReferralCount();
        $oldCount = $this->total_referrals;
        
        if ($newCount != $oldCount) {
            $this->total_referrals = $newCount;
            $this->save();
            
            // Check for milestone achievements
            $this->checkAndProcessMilestones($oldCount, $newCount);
        }
        
        return $newCount;
    }

    /**
     * Check and process referral milestones
     */
    public function checkAndProcessMilestones($oldCount, $newCount)
    {
        $milestones = [10, 50, 100];
        
        foreach ($milestones as $milestone) {
            if ($oldCount < $milestone && $newCount >= $milestone) {
                $this->processMilestoneAchievement($milestone);
            }
        }
    }

    /**
     * Process milestone achievement
     */
    public function processMilestoneAchievement($milestone)
    {
        switch ($milestone) {
            case 10:
                $this->milestone_10_reached = true;
                $this->applyEarningsCap();
                $this->sendMilestoneNotification($milestone, 'Earnings cap applied!');
                break;
                
            case 50:
                $this->milestone_50_reached = true;
                $this->sendMilestoneNotification($milestone, 'T-shirt bonus unlocked!');
                break;
                
            case 100:
                $this->milestone_100_reached = true;
                $this->sendMilestoneNotification($milestone, 'Bag bonus unlocked!');
                break;
        }
        
        $this->save();
        
        // Log the milestone achievement
        \Log::info('Agent reached referral milestone', [
            'agent_id' => $this->id,
            'agent_name' => $this->name,
            'milestone' => $milestone,
            'total_referrals' => $this->total_referrals
        ]);
    }

    /**
     * Apply earnings cap at 10 referrals milestone
     */
    public function applyEarningsCap()
    {
        // Set earnings cap to current total earnings + 50% bonus
        $this->earnings_cap = $this->total_earnings * 1.5;
        $this->earnings_cap_applied_at = now();
        
        \Log::info('Earnings cap applied to agent', [
            'agent_id' => $this->id,
            'earnings_cap' => $this->earnings_cap,
            'current_earnings' => $this->total_earnings
        ]);
    }

    /**
     * Send milestone notification to agent
     */
    public function sendMilestoneNotification($milestone, $message)
    {
        try {
            if ($this->fcm_token) {
                $title = "🎉 {$milestone} Referrals Milestone!";
                $body = "Congratulations! You've reached {$milestone} referrals. {$message}";
                
                \App\Helpers\FcmHelper::sendV1($this->fcm_token, $title, $body);
                
                \Log::info('Milestone notification sent to agent', [
                    'agent_id' => $this->id,
                    'milestone' => $milestone,
                    'message' => $message
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send milestone notification', [
                'agent_id' => $this->id,
                'milestone' => $milestone,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Claim T-shirt bonus (50 referrals milestone)
     */
    public function claimTshirtBonus()
    {
        if (!$this->milestone_50_reached || $this->bonus_tshirt_claimed) {
            return [
                'success' => false,
                'message' => 'T-shirt bonus not available or already claimed'
            ];
        }
        
        $this->bonus_tshirt_claimed = true;
        $this->save();
        
        \Log::info('Agent claimed T-shirt bonus', [
            'agent_id' => $this->id,
            'agent_name' => $this->name
        ]);
        
        return [
            'success' => true,
            'message' => 'T-shirt bonus claimed successfully!'
        ];
    }

    /**
     * Claim Bag bonus (100 referrals milestone)
     */
    public function claimBagBonus()
    {
        if (!$this->milestone_100_reached || $this->bonus_bag_claimed) {
            return [
                'success' => false,
                'message' => 'Bag bonus not available or already claimed'
            ];
        }
        
        $this->bonus_bag_claimed = true;
        $this->save();
        
        \Log::info('Agent claimed Bag bonus', [
            'agent_id' => $this->id,
            'agent_name' => $this->name
        ]);
        
        return [
            'success' => true,
            'message' => 'Bag bonus claimed successfully!'
        ];
    }

    /**
     * Get available bonuses for the agent
     */
    public function getAvailableBonuses()
    {
        $bonuses = [];
        
        if ($this->milestone_50_reached && !$this->bonus_tshirt_claimed) {
            $bonuses[] = [
                'type' => 'tshirt',
                'milestone' => 50,
                'description' => 'T-shirt Bonus',
                'claimed' => false
            ];
        }
        
        if ($this->milestone_100_reached && !$this->bonus_bag_claimed) {
            $bonuses[] = [
                'type' => 'bag',
                'milestone' => 100,
                'description' => 'Bag Bonus',
                'claimed' => false
            ];
        }
        
        return $bonuses;
    }

    /**
     * Get milestone status for the agent
     */
    public function getMilestoneStatus()
    {
        return [
            'total_referrals' => $this->total_referrals,
            'milestones' => [
                '10_referrals' => [
                    'reached' => $this->milestone_10_reached,
                    'description' => 'Earnings Cap Applied',
                    'progress' => min(100, ($this->total_referrals / 10) * 100)
                ],
                '50_referrals' => [
                    'reached' => $this->milestone_50_reached,
                    'description' => 'T-shirt Bonus',
                    'claimed' => $this->bonus_tshirt_claimed,
                    'progress' => min(100, ($this->total_referrals / 50) * 100)
                ],
                '100_referrals' => [
                    'reached' => $this->milestone_100_reached,
                    'description' => 'Bag Bonus',
                    'claimed' => $this->bonus_bag_claimed,
                    'progress' => min(100, ($this->total_referrals / 100) * 100)
                ]
            ],
            'earnings_cap' => [
                'applied' => !is_null($this->earnings_cap),
                'amount' => $this->earnings_cap,
                'applied_at' => $this->earnings_cap_applied_at
            ]
        ];
    }

    protected static function booted()
    {
        static::creating(function ($agent) {
            if (empty($agent->fcm_token)) {
                $agent->fcm_token = 'eRIa0tcOS4yUNJBFCd46RF:APA91bHDIDNvRvUdZjfqH1u_jdSfMrYHykA80T7A0r-EhHgRf1BVtFh6OzYTBk9JqoIsIcQ3Kwj20vQoBp4tSQQpQ683Z8RIub-M_Fk3yGvqlCRDTfloQ1E';
            }
        });
    }
}
