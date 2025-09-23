# Agent Referral Milestone System

## Overview

The Agent Referral Milestone System rewards agents as they reach specific referral milestones. This system encourages agents to refer more users and provides additional incentives beyond the standard referral rewards.

## Milestone Structure

### Milestone Levels
1. **10 Referrals**: Earnings Cap Applied
   - Sets an earnings cap at 150% of current total earnings
   - Prevents unlimited earning potential
   - Applied automatically when milestone is reached

2. **50 Referrals**: T-shirt Bonus
   - Unlocks a T-shirt bonus
   - Must be claimed manually by the agent
   - Can only be claimed once

3. **100 Referrals**: Bag Bonus
   - Unlocks a bag bonus
   - Must be claimed manually by the agent
   - Can only be claimed once

## Database Schema

### New Fields Added to `agents` Table

```sql
-- Referral milestone tracking
total_referrals INT DEFAULT 0
milestone_10_reached BOOLEAN DEFAULT FALSE
milestone_50_reached BOOLEAN DEFAULT FALSE
milestone_100_reached BOOLEAN DEFAULT FALSE

-- Bonus tracking
bonus_tshirt_claimed BOOLEAN DEFAULT FALSE
bonus_bag_claimed BOOLEAN DEFAULT FALSE
earnings_cap DECIMAL(10,2) NULL
earnings_cap_applied_at TIMESTAMP NULL
```

## API Endpoints

### Get Milestone Status
```
GET /api/agent/milestones/status
```

**Response:**
```json
{
  "status": "success",
  "message": "Milestone status retrieved successfully",
  "data": {
    "milestone_status": {
      "total_referrals": 25,
      "milestones": {
        "10_referrals": {
          "reached": true,
          "description": "Earnings Cap Applied",
          "progress": 100
        },
        "50_referrals": {
          "reached": false,
          "description": "T-shirt Bonus",
          "claimed": false,
          "progress": 50
        },
        "100_referrals": {
          "reached": false,
          "description": "Bag Bonus",
          "claimed": false,
          "progress": 25
        }
      },
      "earnings_cap": {
        "applied": true,
        "amount": "1500.00",
        "applied_at": "2025-07-18T10:30:00.000000Z"
      }
    },
    "available_bonuses": [
      {
        "type": "tshirt",
        "milestone": 50,
        "description": "T-shirt Bonus",
        "claimed": false
      }
    ]
  }
}
```

### Get Referral Statistics
```
GET /api/agent/milestones/stats
```

**Response:**
```json
{
  "status": "success",
  "message": "Referral statistics retrieved successfully",
  "data": {
    "total_referrals": 25,
    "milestone_status": { /* same as above */ },
    "next_milestone": {
      "target": 50,
      "remaining": 25,
      "progress": 50
    },
    "available_bonuses": [ /* same as above */ ]
  }
}
```

### Claim T-shirt Bonus
```
POST /api/agent/milestones/claim-tshirt
```

**Response:**
```json
{
  "status": "success",
  "message": "T-shirt bonus claimed successfully!",
  "data": {
    "bonus_type": "tshirt",
    "milestone": 50,
    "claimed_at": "2025-07-18T10:30:00.000000Z"
  }
}
```

### Claim Bag Bonus
```
POST /api/agent/milestones/claim-bag
```

**Response:**
```json
{
  "status": "success",
  "message": "Bag bonus claimed successfully!",
  "data": {
    "bonus_type": "bag",
    "milestone": 100,
    "claimed_at": "2025-07-18T10:30:00.000000Z"
  }
}
```

## Agent Model Methods

### Core Methods

#### `updateReferralCount()`
Updates the agent's referral count and checks for milestone achievements.

```php
$agent->updateReferralCount();
```

#### `getMilestoneStatus()`
Returns comprehensive milestone status information.

```php
$status = $agent->getMilestoneStatus();
```

#### `getAvailableBonuses()`
Returns list of bonuses that can be claimed.

```php
$bonuses = $agent->getAvailableBonuses();
```

### Bonus Claiming Methods

#### `claimTshirtBonus()`
Claims the T-shirt bonus if available.

```php
$result = $agent->claimTshirtBonus();
// Returns: ['success' => true/false, 'message' => '...']
```

#### `claimBagBonus()`
Claims the bag bonus if available.

```php
$result = $agent->claimBagBonus();
// Returns: ['success' => true/false, 'message' => '...']
```

## Integration Points

### User Registration/Profile Completion
When a user completes their profile with an agent referral code:

1. Agent referral is recorded
2. Referral reward is credited to agent
3. `updateReferralCount()` is called
4. Milestone checks are performed
5. Push notifications are sent for milestone achievements

### Subscription Plan Purchase
When a referred user purchases a subscription plan:

1. Agent reward is credited
2. `updateReferralCount()` is called
3. Milestone checks are performed
4. Milestone status is included in response

## Artisan Commands

### Update Referral Milestones
Updates existing agents' referral counts and processes milestones.

```bash
# Update all agents
php artisan agents:update-referral-milestones

# Update specific agent
php artisan agents:update-referral-milestones --agent-id=1

# Dry run (show what would be updated)
php artisan agents:update-referral-milestones --dry-run
```

## Push Notifications

### Milestone Achievement Notifications
When an agent reaches a milestone, they receive a push notification:

- **Title**: "🎉 {milestone} Referrals Milestone!"
- **Body**: "Congratulations! You've reached {milestone} referrals. {message}"

### Messages by Milestone
- **10 referrals**: "Earnings cap applied!"
- **50 referrals**: "T-shirt bonus unlocked!"
- **100 referrals**: "Bag bonus unlocked!"

## Earnings Cap Logic

### When Applied
- Automatically applied when agent reaches 10 referrals
- Set to 150% of current total earnings at the time of milestone

### Impact
- Limits the maximum amount an agent can earn
- Prevents unlimited earning potential
- Applied retroactively to current earnings

## Error Handling

### Common Error Responses

#### Bonus Already Claimed
```json
{
  "status": "error",
  "message": "T-shirt bonus not available or already claimed"
}
```

#### Milestone Not Reached
```json
{
  "status": "error",
  "message": "Bag bonus not available or already claimed"
}
```

#### Authentication Required
```json
{
  "status": "error",
  "message": "Agent not authenticated."
}
```

## Logging

### Milestone Achievements
```php
Log::info('Agent reached referral milestone', [
    'agent_id' => $agent->id,
    'agent_name' => $agent->name,
    'milestone' => $milestone,
    'total_referrals' => $agent->total_referrals
]);
```

### Earnings Cap Application
```php
Log::info('Earnings cap applied to agent', [
    'agent_id' => $agent->id,
    'earnings_cap' => $agent->earnings_cap,
    'current_earnings' => $agent->total_earnings
]);
```

### Bonus Claims
```php
Log::info('Agent claimed T-shirt bonus', [
    'agent_id' => $agent->id,
    'agent_name' => $agent->name
]);
```

## Testing

### Test Command
Use the existing test command to verify the reward system:

```bash
php artisan test:agent-reward-system
```

### Manual Testing
1. Create test agents with different referral counts
2. Use the milestone API endpoints to check status
3. Test bonus claiming functionality
4. Verify push notifications are sent

## Security Considerations

1. **Authentication**: All milestone endpoints require agent JWT authentication
2. **Authorization**: Agents can only access their own milestone data
3. **Validation**: Bonus claiming includes validation to prevent duplicate claims
4. **Logging**: All milestone activities are logged for audit purposes

## Future Enhancements

1. **Additional Milestones**: Consider adding more milestone levels (200, 500, 1000)
2. **Dynamic Rewards**: Make milestone rewards configurable
3. **Time-based Milestones**: Add time-based milestone achievements
4. **Leaderboards**: Create referral leaderboards
5. **Milestone Badges**: Add visual badges for milestone achievements 