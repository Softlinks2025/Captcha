# Agent Reward System for Subscription Plan Purchases

## Overview

The agent reward system has been enhanced to provide rewards to agents when users they referred purchase subscription plans. This creates an additional incentive for agents to refer users to the platform.

## How It Works

### 1. User Registration with Agent Referral
- When a user registers using an agent's referral code, they are linked to that agent
- The user's `agent_id` and `agent_referral_code` fields are populated

### 2. Subscription Plan Purchase
- When a user purchases a subscription plan, the system checks if they were referred by an agent
- If the user has an `agent_id`, the system processes the reward

### 3. Agent Reward Calculation
- The reward amount is based on the agent's current active plan
- The `referral_reward` field in the agent's plan determines the reward amount
- If the plan has "unlimited" referral reward, a default amount of ₹300 is used

### 4. Reward Processing
- The agent's wallet balance is increased by the reward amount
- The agent's total earnings are updated
- A transaction is logged in `agent_wallet_transactions` table
- A push notification is sent to the agent (if FCM token is available)

## Implementation Details

### User Model Method
```php
public function handleAgentRewardForSubscription($plan)
```
This method handles the entire reward process and returns a result array with:
- `rewarded`: boolean indicating if reward was given
- `agent_id`: ID of the rewarded agent (if applicable)
- `reward_amount`: amount rewarded (if applicable)
- `message`: description of the result

### API Endpoint
The subscription plan purchase API (`/api/v1/plans/purchase`) now includes agent reward processing and returns reward information in the response.

### Database Transaction
All reward processing is wrapped in a database transaction to ensure data consistency.

## Configuration

### Agent Plan Settings
- Each agent plan has a `referral_reward` field
- This can be a numeric value (e.g., 100, 200) or "unlimited"
- For "unlimited" rewards, the system uses a default amount of ₹300

### Default Unlimited Reward
The default reward for "unlimited" referral rewards can be modified in the `handleAgentRewardForSubscription` method.

## Testing

Use the provided test command to verify the system:
```bash
php artisan test:agent-reward-system
```

This command will:
1. Find an agent with an active plan
2. Find a user without agent referral
3. Assign the user to the agent
4. Test the reward system
5. Display the results
6. Clean up the test data

## Logging

The system logs all reward transactions and any errors that occur during the process. Check the Laravel logs for detailed information.

## Push Notifications

Agents receive push notifications when they earn rewards from subscription purchases. The notification includes:
- Title: "Subscription Purchase Reward!"
- Body: "A user you referred has purchased a subscription plan. You earned ₹[amount]!"

## Error Handling

The system includes comprehensive error handling:
- Invalid agent referrals are logged but don't break the purchase process
- Push notification failures are logged but don't affect the reward
- Database transaction rollback on any errors
- Detailed error logging for debugging

## Future Enhancements

Potential improvements to consider:
1. Configurable default reward amounts for unlimited plans
2. Different reward amounts based on subscription plan cost
3. Reward tiers based on agent performance
4. Additional notification channels (email, SMS)
5. Reward analytics and reporting 